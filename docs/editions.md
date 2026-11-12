# Editions: SAE, MAE and PE

Siberian ships as three editions. This repository is **SAE**, the base that holds nearly all of the code. **MAE** (many
applications instead of one) and **PE** (MAE plus whitelabels with custom domains for resellers) are private overlays that are
cloned into `siberian/app/mae` and `siberian/app/pe` of the same tree. Their code never lives in this repository.

## Layers

The edition of an install is `\Siberian\Version::TYPE` (`SAE`, `MAE`, `PE`). A file with the same relative path in two layers is
**replaced** by the upper one, not merged.

```
local  >  pe  >  mae  >  sae           siberian/app/<layer>/<same relative path>
```

| what | how the layers are combined | where |
|---|---|---|
| PHP classes, models, `init.php` | PHP include path in the order `local`, `pe`, `mae`, `sae`; the first file found wins | `Bootstrap::_initAutoload` |
| Controllers | module directories registered `sae`, `mae`, `pe`, `local`; the last registered wins ("priorities are inverted for controllers") | `Bootstrap::_initModuleDirectory` |
| Templates, JS, CSS, layout XML | one pre-built `design/design-cache.json` per layer, loaded `sae`, `mae`, `pe`, `local`; a later layer replaces an entry of an earlier one | `Siberian\Cache\Design` |
| Translations | catalogs of each layer fetched in the same order | `Siberian\Cache\Translation` |

`Siberian\Cache::$editions` lists the layers each edition loads: `sae` = sae+local, `mae` = sae+mae+local, `pe` = sae+mae+pe+local.
Some code also branches on the edition (`Siberian_Version::is('SAE')`, `\Siberian\Version::TYPE`), marked `@migration sae/mae/pe`.

## What this means when you change this repository

A file of `siberian/app/sae` that MAE or PE also carries **does not take effect on those editions** until the overlay is updated.
This is the usual reason why "it works on SAE but nothing changed on the MAE install".

- Files at risk: any path that exists under `app/mae` or `app/pe`. Today that is a few dozen files, mostly the backoffice
  (System configuration, Admin accounts and lists, the backoffice templates and their `design-cache.json`), the `Core` base
  model and controller, and a handful of installer pages.
- The exact list, with for each file the SAE commit it was forked from, how many SAE commits it has missed and how much of it is
  the edition's own, is generated into the **private** repositories: `docs/editions/OVERRIDES.md` in `siberian-mae` and in
  `siberian-pe`. It is not reproduced here because it names files of the private code.
- When a pull request touches a backoffice, Admin, System, Core, Application or Template file, ask a maintainer with access to
  the overlays to check it against that list. If the file is overridden, the same change goes to the overlay in the same piece of work.
- A change to design files (`design/`, `resources/design/`) needs the layer's `design-cache.json` rebuilt: the release routine
  does it for the three pre-built layers, `local` is rebuilt on demand.

## Releases

A release build checks out the three repositories at the same tag (`v<version>`, and `v<lastversion>` for the delta update),
so a tag that exists here must exist in `siberian-mae` and `siberian-pe` too. The overlays are released from their `master`;
`develop` is not the deployed line.

## Tracing it

The overlay map is regenerated with a read-only script from the private release repository. It compares every file of each
overlay with the lower layers and records the baseline commit, the drift and the own changes. Rerun it when an SAE change touches
a file the map lists, and commit the new map with the overlay change.

## Design caches

`app/sae/design/design-cache.json`, `app/mae/design/design-cache.json` and `app/pe/design/design-cache.json` are pre-built lists of every design file of an edition, `app/local/design/design-cache.json` is the one of the local modules, and `var/cache/zend_cache---default_js_desktop` / `..._css_desktop` hold the script and stylesheet lists derived from them. The layout reads these lists, never the folders: a design file that is not in them is not sent to the browser.

Symptoms of a stale cache: a page renders flat or half styled, a new directive or stylesheet has no effect, an update seems not to change the backoffice (siberian01: the Settings > Communications page lost its section navigation because `sb-section-nav.js` was added after the committed SAE cache was built).

Fix and rule:

1. After an update or deploy, and first thing when debugging a missing css/js/template, rebuild: `php cli cache:design <sae|mae|pe>` as the web user in the install root, then move `var/cache/zend_cache---default_js_desktop` and `..._css_desktop` aside (they are rebuilt on the next request).
2. A pull request that adds, renames or deletes a design file commits the rebuilt caches of the edition it touches.
3. `php tests/php-upgrade/design-cache-check.php [root]` lists the design files missing from their cache and fails when there is one.
