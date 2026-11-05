// The preview of the app (phone on the right of the editor) fits its column, and on small screens opens from a button.
const { test, expect } = require('@playwright/test');

const DESIGN = '/application/customization_design_style/edit';
const BREAKPOINT = 1200;

async function openDesign(page, width, height = 900) {
  await page.setViewportSize({ width, height });
  await page.goto('/');
  if (await page.locator('input[name=email]').count()) {
    await page.fill('input[name=email]', 'admin@example.com');
    await page.fill('input[name=password]', 'Passw0rd!x');
    await Promise.all([page.waitForLoadState('networkidle'), page.keyboard.press('Enter')]);
  }
  await page.goto(DESIGN);
  await page.waitForSelector('#iphone', { state: 'attached' });
  await page.waitForTimeout(2500); // the app inside the phone loads, the layout settles
  // A new app opens the template picker over the page: close it, it is not what is tested here.
  const picker = page.locator("#modal_content_preview .fa-times").first();
  if (await picker.count() && await picker.isVisible()) {
    await picker.click();
    await page.waitForTimeout(600);
  }
}

const box = (page, sel) => page.locator(sel).first().boundingBox();

for (const width of [1920, 1500, 1300, 1210]) {
  test(`wide ${width}px: the phone fits its column and does not overlap the editor`, async ({ page }, info) => {
    await openDesign(page, width);
    await page.screenshot({ path: `results/wide-${width}-${info.project.name || 'run'}.png` });

    const phone = await box(page, '#iphone .iphone_content');
    const column = await box(page, '#overview');
    const editor = await box(page, '.app-features .responsive-part');
    expect(phone, 'the phone is shown').not.toBeNull();

    // inside the viewport, inside its own column, and clear of the editor content
    expect(phone.x + phone.width, 'right edge inside the window').toBeLessThanOrEqual(width + 0.5);
    expect(phone.x, 'left edge inside its column').toBeGreaterThanOrEqual(column.x - 0.5);
    expect(phone.x, 'no overlap with the editor content').toBeGreaterThanOrEqual(editor.x + editor.width - 0.5);
    // proportions of the phone are kept
    expect(phone.width / phone.height).toBeCloseTo(48.88 / 87, 1);
    // never taller than the window
    expect(phone.height).toBeLessThanOrEqual(900);
    // the toggle button is for small screens only
    await expect(page.locator('#overview-toggle')).toBeHidden();
  });
}

for (const width of [1100, 800]) {
  test(`small ${width}px: a button opens the phone over the page and closes it`, async ({ page }, info) => {
    await openDesign(page, width);
    await page.screenshot({ path: `results/small-${width}-closed-${info.project.name || 'run'}.png` });

    await expect(page.locator('#overview')).toBeHidden();
    const toggle = page.locator('#overview-toggle');
    await expect(toggle, 'the Preview button is there').toBeVisible();

    await toggle.click();
    await expect(page.locator('#iphone')).toBeVisible();
    await page.waitForTimeout(2500);
    await page.screenshot({ path: `results/small-${width}-open-${info.project.name || 'run'}.png` });
    const phone = await box(page, '#iphone .iphone_content');
    expect(phone.x).toBeGreaterThanOrEqual(0);
    expect(phone.x + phone.width).toBeLessThanOrEqual(width + 0.5);
    expect(phone.y + phone.height).toBeLessThanOrEqual(900 + 0.5);

    await toggle.click();
    await expect(page.locator('#overview')).toBeHidden();
  });
}

test('resizing from small to wide closes the overlay and shows the phone in its column', async ({ page }) => {
  await openDesign(page, 1000);
  await page.locator('#overview-toggle').click();
  await expect(page.locator('#iphone')).toBeVisible();
  await page.setViewportSize({ width: 1500, height: 900 });
  await page.waitForTimeout(500);
  await expect(page.locator('#overview-toggle')).toBeHidden();
  const phone = await box(page, '#iphone .iphone_content');
  const editor = await box(page, '.app-features .responsive-part');
  expect(phone.x).toBeGreaterThanOrEqual(editor.x + editor.width - 0.5);
});

test('the phone keeps the same size when the page is scrolled', async ({ page }, info) => {
  await openDesign(page, 1300, 900); // the phone is limited by its column here, not by the height
  const before = await box(page, '#iphone .iphone_content');
  const columnBefore = await box(page, '#overview');
  await page.evaluate(() => window.scrollTo(0, 400));
  await page.waitForTimeout(500);
  expect(await page.evaluate(() => window.scrollY), 'the page did scroll').toBeGreaterThan(100);
  await page.screenshot({ path: `results/scrolled-${info.project.name || 'run'}.png` });
  const after = await box(page, '#iphone .iphone_content');
  const columnAfter = await box(page, '#overview');
  expect(after.width, 'same width').toBeCloseTo(before.width, 0);
  expect(after.height, 'same height').toBeCloseTo(before.height, 0);
  expect(columnAfter.width, 'same column width').toBeCloseTo(columnBefore.width, 0);
  expect(after.x + after.width, 'still inside the window').toBeLessThanOrEqual(1300.5);
});
