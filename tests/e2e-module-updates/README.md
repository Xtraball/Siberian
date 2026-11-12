# Module updates, end-to-end rig

Runs the whole update flow on a dev box: a real Siberian (PHP 8.2, MariaDB) talks over real TLS (private CA, strict verification) to the downloader branch `feat-module-update-api` (PHP 8.5). The SiberianCMS licence service and the EDD store are faked (`e2e/fake.php`). Nothing talks to production.

## Run

Needs Docker, `jq`, `openssl`, and this repo checked out with `siberian/` present. From a dev box, with the downloader branch copied to `downloader/` next to these files:

```
sh setup.sh                                   # CA, Siberian config.user.php, downloader var/config.php
docker compose build
docker compose run --rm --no-deps dl sh -c "php /e2e/fixture.php && php scripts/generate-signing-key.php && php scripts/build-manifest.php"
docker compose up -d
sh install-siberian.sh                        # drives the web installer with curl
sh seed-module.sh                             # installs DemoUpd 1.0.0 the regular way
sh flow.sh                                    # 40 checks
```

`flow.sh` is not repeatable on the same database (it installs the update). To run it again, `docker compose down -v`, remove `siberian/config.php`, `siberian/app/configs/app.ini` and `siberian/app/local/modules/DemoUpd`, and start from `install-siberian.sh`.

Set `SIB_SRC` if the `siberian/` directory is not at `/home/claude/siberian-module-updates/siberian`.

## What it covers

List and version comparison, an incompatible module, a module without update, terms (wrong hash, POST only, who accepted), bad and already-used module licences, a terms change on the downloader while the page is open (HTTP 409 path), the real download, copy and install through the existing installer, decryption with the installed licence pair, the downloader being down (stale list), the kill switch, an anonymous request, and the downloader audit log.

## Not covered

The backoffice screens (the Browser pane cannot load LAN scripts, see `docs/module-updates.md`), and the daily cron entry point.

## Gotchas found building it

- Siberian cannot be mounted at a directory named `/app`: `Core_Model_Directory::getBasePathTo()` skips the prefix when the path already contains the base path, so `app/sae/modules` resolves to `/sae/modules`. The rig mounts it at `/srv/sib`.
- The web installer only answers requests that carry `X-Requested-With: XMLHttpRequest`.
- Backoffice login is JSON to `backoffice/account_login/post`.
- `.test` addresses are rejected as e-mail addresses by the installer's validator; the admin is `admin@example.com`.
