# Store account (Settings > Advanced > Store account)

Lets the admin of an install connect their account on https://extensions.siberiancms.com and consult their
purchases and license keys inside the backoffice. **Read only**: nothing in Siberian uses this data (no module
install or update is gated by it).

## What the admin sees

- Connect: a code and a link. The admin opens the link, signs in on the store with their normal account (MFA applies)
  and approves the code. Siberian picks it up within a few seconds. The store password never reaches Siberian.
- Purchases: each order with its downloads (a purchase can hold several), and per download the module name,
  license key (masked, reveal and copy), license status, expiry, the linked site (generally the Siberian license key,
  marked "this install" when it is this one) and activations used.
- Refresh and Disconnect buttons, and the time of the last sync.

## How it works

OAuth 2.0 device authorization grant (RFC 8628) against the store plugin `Xtraball/siberian-edd-purchases-api`.

| Piece | File |
|---|---|
| Client (framework free, unit tested) | `siberian/lib/Siberian/StoreAccount.php` |
| Glue (settings, private folder) | `siberian/app/sae/modules/Backoffice/Model/StoreAccount.php` |
| Backoffice actions | `siberian/app/sae/modules/Backoffice/controllers/Advanced/StoreController.php` |
| Page, factory, controller, menu | `design/desktop/backoffice/template/backoffice/advanced/store.phtml`, `js/factories/advanced.js`, `js/controllers/advanced.js`, `layout/backoffice.xml`, `template/page/layout/header.phtml` |
| Tests | `tests/php-upgrade/regression/StoreAccountTest.php` |

- The connection lasts until the admin disconnects here or revokes it on the store. The access token (15 minutes) is
  renewed silently with a rotating refresh token; renewals are serialized with a lock so the token is never used twice at once.
- Tokens are encrypted (libsodium secretbox) with a key file in `var/cache/store-account/`, outside the database.
  The purchases copy is a file in the same folder (mode 0600).
- Cache: the copy is shown instantly; it is refreshed in the background when older than 24 hours and the page is opened,
  or with the Refresh button (conditional request, ETag). When the store is unreachable the last copy stays visible, marked
  stale, and nothing is disconnected. After a failed refresh the store is left alone for 5 minutes.
- If the store revokes the connection, the page asks to connect again; nothing else is lost.
- The store URL is `https://extensions.siberiancms.com`; only `config.php` (`store_account_base_url`) can point somewhere
  else, never the database. HTTPS only.

## Tests

`php tests/php-upgrade/run.php StoreAccount` (18 checks, no network). The contract test against the real plugin lives in
the plugin repo: `tests/contract.sh`.
