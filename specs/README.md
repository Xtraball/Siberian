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
