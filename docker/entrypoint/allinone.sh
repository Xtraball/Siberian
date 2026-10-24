#!/bin/bash
# siberian "allinone": nginx + php-fpm + the cron loop in one container. If any of them dies the container stops,
# so the restart policy of the runtime brings all three back. Not for production sizing, use app + web there.
set -eu

envsubst '${SIBERIAN_FPM}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# seeds the docroot once, before the three processes start
siberian-entrypoint true
siberian-entrypoint php-fpm -F &
siberian-entrypoint cron &
nginx -g 'daemon off;' &

wait -n
echo "siberian-allinone: a process exited, stopping" >&2
exit 1
