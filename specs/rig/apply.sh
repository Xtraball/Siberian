#!/bin/sh
# Puts the controller-spec rig in a state where Siberian cannot reach the internet and every external service answers
# from a local mock. Idempotent. Usage: sh specs/rig/apply.sh [RIG_DIR]   (default /srv/data/sibtest, project sibtest2)
#
#  - the `n` network becomes internal: no route out, so an unmocked call fails at once instead of hanging
#  - every host in hosts.txt resolves to the `tls` container, which answers for it with a certificate from the rig CA
#    and forwards to specs/rig/mock.php (the licence and store fakes keep their own behaviour, anything else gets 200 {})
#  - the Siberian container runs php -S with workers, so the specs can run in parallel
#  - a `front` container publishes 127.0.0.1:28800 (an internal network cannot publish ports itself)
set -eu
HERE=$(cd "$(dirname "$0")" && pwd)
RIG=${1:-/srv/data/sibtest}
PROJECT=${SPEC_PROJECT:-sibtest2}
cd "$RIG"

# certificate with every mocked host as a subject alternative name
SAN="DNS:downloader.test,DNS:services.test,DNS:store.test"
for h in $(cat "$HERE/hosts.txt"); do SAN="$SAN,DNS:$h"; done
printf 'subjectAltName=%s\n' "$SAN" > certs/ext.cnf
openssl req -newkey rsa:2048 -nodes -keyout certs/server.key -out certs/server.csr -subj "/CN=downloader.test" 2>/dev/null
openssl x509 -req -in certs/server.csr -CA certs/ca.pem -CAkey certs/ca.key -CAcreateserial -out certs/server.pem -days 365 -extfile certs/ext.cnf 2>/dev/null
chmod 644 certs/*.pem certs/server.key

cp "$HERE/mock.php" e2e/fake.php

# nginx: the three rig names as before, plus a default server for every other mocked host (http and https)
cat > nginx.conf <<'CONF'
server {
    listen 443 ssl;
    server_name downloader.test;
    ssl_certificate /certs/server.pem;
    ssl_certificate_key /certs/server.key;
    client_max_body_size 4k;
    location / {
        proxy_pass http://dl:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Proto https;
        proxy_read_timeout 120s;
    }
}
server {
    listen 80 default_server;
    listen 443 ssl default_server;
    server_name _;
    ssl_certificate /certs/server.pem;
    ssl_certificate_key /certs/server.key;
    client_max_body_size 64m;
    location / {
        proxy_pass http://fake:8081;
        proxy_set_header Host $host;
    }
}
CONF

# front: the published port
cat > front.conf <<'CONF'
server {
    listen 8000;
    client_max_body_size 256m;
    location / {
        proxy_pass http://siberian:8000;
        proxy_set_header Host $http_host;
        proxy_read_timeout 120s;
    }
}
CONF

{
  echo 'networks:'
  echo '  n:'
  echo '    internal: true'
  echo '  edge: {}'
  echo 'services:'
  echo '  tls:'
  echo '    networks:'
  echo '      n:'
  echo '        aliases:'
  for h in $(cat "$HERE/hosts.txt"); do echo "          - $h"; done
  echo '  siberian:'
  echo '    ports: !reset []'
  echo '    environment:'
  echo '      PHP_CLI_SERVER_WORKERS: "3"'
  echo '  front:'
  echo '    image: nginx:alpine'
  echo '    volumes:'
  echo '      - ./front.conf:/etc/nginx/conf.d/default.conf:ro'
  echo '    ports: ["127.0.0.1:28800:8000"]'
  echo '    networks: [n, edge]'
  echo '    depends_on: [siberian]'
} > docker-compose.specs.yml

F="-f docker-compose.yml -f docker-compose.override.yml -f docker-compose.specs.yml"
docker compose -p "$PROJECT" $F down --remove-orphans >/dev/null 2>&1 || true   # never -v: the database lives in an external volume
docker compose -p "$PROJECT" $F up -d
echo "rig up: waiting for Siberian"
for i in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:28800/ || true)
  [ "$code" != 000 ] && [ "$code" != 502 ] && { echo "answers $code"; break; }
  sleep 2
done
# prove the isolation: no route out, mocks answer
docker compose -p "$PROJECT" $F exec -T siberian sh -c 'php -r "echo @file_get_contents(\"https://example.com\", false, stream_context_create([\"http\"=>[\"timeout\"=>3]])) === false ? \"egress blocked\n\" : \"EGRESS OPEN\n\";"'
docker compose -p "$PROJECT" $F exec -T siberian sh -c 'php -r "echo substr((string) @file_get_contents(\"https://maps.googleapis.com/maps/api/geocode/json?address=x\"), 0, 60), \"\n\";"'
