#!/bin/bash
cd /srv/data/pw-editor/rig
docker compose exec -T db mariadb -uroot -proot siberian -e "insert ignore into api_user (user_id,username,password,bearer_token,acl,is_visible,created_at,updated_at) values (1,'pwtest',sha1('pwtest-pass'),'pwtestbearer','{\"application\":{\"create\":true,\"update\":true}}',0,now(),now())"
docker compose exec -T siberian sh -c 'curl -s -m 90 -X POST http://127.0.0.1:8000/application/api/create -H "Api-Auth-Bearer: Bearer pwtestbearer" -d "name=Preview Test&user_id=1&key=pwtest" ; echo'
docker compose exec -T db mariadb -uroot -proot siberian -e "select app_id,name,\`key\`,domain from application"
