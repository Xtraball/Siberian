#!/bin/bash
# Entrypoint of the siberian-app image.
#   php-fpm (default)  run php-fpm
#   cron               run `php cron.php` every minute, once the installer has finished
#   anything else      exec as given (e.g. `php -v`, `bash`), as the app user
# Before any of them, an empty docroot volume is seeded from the code baked in the image.
set -eu

SRC=/usr/src/siberian
REV=/usr/src/siberian.revision
DST=/var/www/html
APP_UID=33 # www-data

seed() {
    [ "$(id -u)" = 0 ] || return 0
    if [ ! -e "$DST/.image-revision" ]; then
        echo "siberian: seeding $DST from the image ($(cat "$REV"))" >&2
        cp -a "$SRC/." "$DST/"
        chown -R "$APP_UID:$APP_UID" "$DST"
        # marker last: a seed that was interrupted is redone on the next start
        cp "$REV" "$DST/.image-revision"
        chown "$APP_UID:$APP_UID" "$DST/.image-revision"
    elif ! cmp -s "$REV" "$DST/.image-revision"; then
        # The volume keeps its own code (the in-app updater maintains it): an image upgrade never overwrites it.
        echo "siberian: notice, $DST holds revision $(cat "$DST/.image-revision"), this image carries $(cat "$REV")" >&2
    fi
}

as_app() {
    if [ "$(id -u)" = 0 ]; then
        exec setpriv --reuid="$APP_UID" --regid="$APP_UID" --init-groups "$@"
    fi
    exec "$@"
}

seed

case "${1:-php-fpm}" in
    php-fpm)
        shift || true
        exec php-fpm "$@"
        ;;
    cron)
        cron_loop() {
            while true; do
                if grep -qs 'isInstalled = .1.' "$DST/app/configs/app.ini"; then
                    php -f "$DST/cron.php" >/dev/null 2>&1 || true
                fi
                sleep 60
            done
        }
        export -f cron_loop
        export DST
        as_app bash -c cron_loop
        ;;
    *)
        as_app "$@"
        ;;
esac
