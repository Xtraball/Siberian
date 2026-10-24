# Siberian in Docker

Three images, built from one `Dockerfile` (build context = repository root), plus the stock `mariadb`:

| image | target | contents |
|---|---|---|
| `siberian-app` | `app` | php-fpm with the extensions Siberian needs, the code, and the cron loop (`command: cron`) |
| `siberian-web` | `web` | nginx with the Siberian vhost; no code, it serves the docroot volume |
| `siberian` | `allinone` | app + nginx + cron in one container, for a demo or a plain `docker run` (database stays external) |

`PHP_VERSION` (build arg, 8.2 to 8.5) picks the PHP minor of `app` and `allinone`. Every version is gated by
`tests/php-upgrade/ci.sh` before its image is published (`.github/workflows/docker-images.yml`).

## Quick start

```sh
cd docker
cp .env.example .env     # set MARIADB_ROOT_PASSWORD and MARIADB_PASSWORD
docker compose up -d --build --wait
```

Open http://localhost:8080 and follow the installer: database host `db`, database and user from `.env`.
`tests/smoke.sh` does the same through HTTP (installer, every module, admin, first application, login page) and is
what the CI runs against each PHP version.

## How the code is handled

The docroot is the `siberian` volume. On first start `siberian-app` copies the code of the image into it (marker file
`.image-revision`, written last), because the installer (`app/configs/app.ini`), the in-app updater and the uploads write
inside the docroot. After that the volume owns its code: a newer image never overwrites it, the entrypoint only logs a
notice when the revisions differ. Upgrades go through the in-app updater as before. An image-aware upgrade (code in the
image, only data in volumes) is a later step.

## Notes

- Logs go to stdout/stderr (php-fpm, nginx, cron).
- `app` starts as root only to seed the volume, then php-fpm workers and the cron loop run as `www-data` (33).
- Put TLS in front (reverse proxy); nginx trusts `X-Forwarded-Proto` for HTTPS detection.
- The database is never baked in. MariaDB 11.8 with `sql_mode=NO_ENGINE_SUBSTITUTION`, utf8mb4.
- Published images (when the workflow runs on trunk): `ghcr.io/<owner>/siberian-app:php8.5`, `siberian-web:latest`,
  `siberian:php8.5`; set `SIBERIAN_APP_IMAGE` / `SIBERIAN_WEB_IMAGE` in `.env` to use them instead of building.
