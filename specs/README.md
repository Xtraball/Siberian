# Controller specs

HTTP specs for the backend endpoints, one folder per module (`specs/backoffice/`, ...). Each endpoint has at least one valid and one invalid case; endpoints that read a JSON body also have the empty-body cases (`{}`, no body, broken JSON), which used to end without any response (HTTP 404).

```
sh specs/run.sh              # everything
sh specs/run.sh backoffice   # files whose path contains "backoffice"
```

Needs `curl` and `jq`, and an installed Siberian to talk to. Defaults match the throwaway instance of `tests/e2e-module-updates/`:

| Variable | Default |
|---|---|
| `SPEC_BASE` | `http://127.0.0.1:28800` |
| `SPEC_EMAIL` | `admin@example.com` |
| `SPEC_PASSWORD` | `Passw0rd!x` |

The specs create the records they need and delete them. Run them against a test instance, never against production.

## Writing one

`specs/<module>/<name>.spec.sh`, sourcing `lib.sh`:

```sh
. "$(dirname "$0")/../lib.sh"
login
spec_name "account_view/save"
request POST /backoffice/account_view/save '{"email":"not-an-email"}'
expect_status "invalid (bad email)" 400
expect_json "invalid (bad email)" .error 1
spec_done
```

`request METHOD PATH [JSON]` sets `CODE` and `BODY`; `json FILTER` reads `BODY` with jq. Siberian answers an action error with HTTP 400 and `{"error":1,"message":...}`, success with 200 and `{"success":1}`.

## Coverage

| Module | Endpoints covered |
|---|---|
| Backoffice | `account_view` (load, find, save), `account_list` (load, findall, delete), `account_login` (post, forgottenpassword, logout) |

905 actions exist across 57 modules (`siberian/app/sae/modules/*/controllers`); the rest are added module by module.

## Critical path: the backoffice updater

`sh specs/run.sh updater` (about 60 s, 58 checks) guards Settings > Updates & Modules, the screen every instance is updated from. It covers the two ways something gets installed:

- drag and drop: `upload` -> `copy` -> `install`, then a second archive with a higher version over the first (the update itself);
- remote: `downloadupdate` -> `copy` -> `install`, with the Siberian update server played by `specs/rig/mock.php` (scenarios `none`, `available`, `check-500`, `check-garbage`, `download-500`, `empty-download`, chosen through `/tmp/specs-updater/scenario` in the mock container).

Each step is also fed what a real instance meets: no file, a text file, an archive without `package.json`, an archive carrying Nwicode code, path traversal inside the archive and in the `file` parameter of `copy`, no session, an update server that answers 500, garbage or nothing. The first lines check that every URL the updater's JavaScript calls has an action behind it and that the drop zone is still in the template.

It installs a throw-away module `SpecUpd` on the rig and removes its files and its `module` row on exit, so it can run again at once. Never point it at a real instance. The module-update flow with the downloader (signed manifests, terms, licences) stays in `tests/e2e-module-updates`.

Bugs it found when first run: the controller imported `Siberian\Exception`, so its `catch (Exception)` blocks missed every other exception and a bad answer from the update server ended in a redirect to the dashboard instead of an error message; the same bad answer was also decoded before its HTTP status was looked at.
