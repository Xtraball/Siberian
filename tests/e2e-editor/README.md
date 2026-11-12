# Editor visual tests (Playwright)

Browser tests of the editor, run against a local Siberian (SAE, PHP 8.2, MariaDB) in Docker with test credentials only. They check the geometry of the page (where the boxes are at several window widths) first, and keep screenshots for review.

`tests/preview.spec.js`: the preview of the app (phone on the right) fits its column at 1920, 1500, 1300 and 1210px, and below 1200px a "Preview" button opens it over the page.

## Run (dev box, CM03)

The rig is a copy of the module-update rig (`tests/e2e-module-updates`) with its own project name, database and port (28850):

```
sh rig/setup.sh      # copy of the rig, SAE tree of this checkout, docker compose up
sh rig/install.sh    # the web installer, with curl (copy it from the module-update rig, port 28850)
sh rig/mkapp.sh      # a test API user and an app "Preview Test"
sh rig/run.sh preview            # Playwright 1.63 in the official Docker image, results/ holds the screenshots
```

`rig/swap.sh trunk|pr` puts the files of a change or their trunk version into the rig tree, to see a test fail before and pass after.

The rig paths (`/srv/data/pw-editor`, the checkout under `/srv/data/sib-pr/rel519`) are those of the dev box; adjust them elsewhere.

Known: the app inside the phone does not load in the rig (no built app), so the phone stays on its loader. The tests look at the phone, not at the app.

## Advanced settings of the publication page

`tests/advanced-settings.spec.js`: the Android advanced settings (battery, location, photos & videos) keep checkbox, label and help text in line at 560 to 900px. The test puts the real markup of `Application_Form_Android` (`fixtures/android-form.html`, made again with `php rig/render-android-form.php <checkout> > fixtures/android-form.html`) in the editor page and applies the rules it reads from `sources.phtml`. The publication page itself needs a page, images, a domain and a subscription on the test app, which is why the form is injected. `rig/run.sh` mounts the rig tree as `/siberian` for that.
