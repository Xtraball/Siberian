// The Android advanced settings of the publication page: every checkbox sits left of its label, label and help text
// line up, whatever the width of the column (a floated checkbox dropped a long label above itself).
// The markup is the real output of Application_Form_Android (fixtures/android-form.html, made by rig/render-android-form.php),
// the rules are read from the real publication template, the page around them is the editor with its own CSS.
const { test, expect } = require('@playwright/test');
const fs = require('fs');

const FORM = fs.readFileSync(__dirname + '/../fixtures/android-form.html', 'utf8');
const TEMPLATE = process.env.SOURCES_PHTML
  || '/siberian/app/sae/modules/Application/resources/design/desktop/flat/template/application/customization/publication/sources.phtml';
const RULES = (fs.readFileSync(TEMPLATE, 'utf8').match(/#form-general-information-android[^{]*\{[^}]*\}/g) || []).join('\n');

async function login(page) {
  await page.goto('/');
  if (await page.locator('input[name=email]').count()) {
    await page.fill('input[name=email]', 'admin@example.com');
    await page.fill('input[name=password]', 'Passw0rd!x');
    await Promise.all([page.waitForLoadState('networkidle'), page.keyboard.press('Enter')]);
  }
}

for (const width of [560, 640, 720, 900]) {
  test(`advanced settings at ${width}px: checkbox, label and help text line up`, async ({ page }, info) => {
    expect(RULES, 'the template carries the layout rules of the form').toContain('.form-group-checkbox');
    await page.setViewportSize({ width: 1500, height: 1000 });
    await login(page);
    await page.goto('/application/customization_publication_infos');
    await page.waitForTimeout(1500);
    await page.evaluate(([form, rules, w]) => {
      const host = document.querySelector('.application') || document.body;
      host.innerHTML = '<style>' + rules + '</style>'
        + '<div class="content-feature container-fluid" style="max-width:' + w + 'px;background:#dcdcde;padding:15px">'
        + '<div class="form-horizontal" id="android-settings"><div class="form-group"><div class="col-md-12">' + form + '</div></div></div></div>';
    }, [FORM, RULES, width]);
    await page.waitForTimeout(300);
    await page.locator('#android-settings').screenshot({ path: `results/advanced-settings-${width}.png` });

    const rows = await page.evaluate(() => [...document.querySelectorAll('#form-general-information-android .form-group-checkbox')].map((group) => {
      const box = group.querySelector('input[type=checkbox]').parentElement.getBoundingClientRect();
      const label = group.querySelector('label').getBoundingClientRect();
      const help = group.nextElementSibling.querySelector('.col-md-12');
      return {
        id: group.querySelector('input[type=checkbox]').id,
        boxLeft: box.left, boxTop: box.top, boxRight: box.right,
        labelLeft: label.left, labelTop: label.top,
        helpLeft: help.getBoundingClientRect().left + parseFloat(getComputedStyle(help).paddingLeft),
      };
    }));

    expect(rows.map((r) => r.id)).toEqual(['disable_battery_optimization', 'disable_location', 'disable_media_permissions']);
    for (const r of rows) {
      expect(r.labelLeft, `${r.id}: the label is right of the checkbox`).toBeGreaterThanOrEqual(r.boxRight - 0.5);
      expect(Math.abs(r.labelTop - r.boxTop), `${r.id}: the label starts on the row of the checkbox`).toBeLessThanOrEqual(6);
      expect(Math.abs(r.helpLeft - r.boxLeft), `${r.id}: the help text starts under the checkbox`).toBeLessThanOrEqual(1);
    }
    // the three options share the same two columns
    expect(new Set(rows.map((r) => Math.round(r.boxLeft))).size, 'one checkbox column').toBe(1);
    expect(new Set(rows.map((r) => Math.round(r.labelLeft))).size, 'one label column').toBe(1);
  });
}
