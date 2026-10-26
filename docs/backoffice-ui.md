# Backoffice UI recipes

Small, repeatable patterns for the backoffice (AngularJS 1 + Zend templates, default theme `theme-default`). Each recipe
ships with a regression test, so a page is converted the same way every time.

## Sections with a side navigation (`sb-section-nav`)

Use it when a page stacks unrelated cards (Settings > Email: Support, Custom SMTP, E-Mail render preview).

```html
<form ng-submit="save()" class="form-horizontal">
    <sb-section-nav>
        <sb-section-pane icon="fa-life-ring" title="'Support'"> ... </sb-section-pane>
        <sb-section-pane icon="fa-server" title="'Custom SMTP Configuration'"> ... </sb-section-pane>
    </sb-section-nav>
</form>
```

- A pane is an `sb-section` (same card, title, optional `button`) that is shown when selected. The first pane is shown by
  default and its menu item is highlighted.
- Panes are hidden, never removed: their fields stay in the form and are saved with it. Keep the nav inside the `<form>`.
- `icon` is optional (Font Awesome 4 class). `title` is an expression, like `sb-section`.
- Conversion of a page: `<sb-section title=` becomes `<sb-section-pane icon="..." title=`, `</sb-section>` becomes
  `</sb-section-pane>`, wrap them in `<sb-section-nav>`. Nothing else moves.
- Structure (flex layout, small screens) is in `css/style.css`, shared by every theme; the look is in
  `css/theme-default/styles.css`.

## Progress bars in tables

A `.progress` directly in a `.table` cell has its margin reset by the default theme. Any progress bar placed elsewhere
needs its own rule (a test enforces it).

## Checklist for a converted page

1. Regression test in `tests/php-upgrade/regression/` (structure of the template, order, assets). It must fail on trunk.
2. Run it on PHP 8.2 and 8.5, then `sh tests/php-upgrade/check-pr.sh origin/trunk`.
3. For a directive, check the behaviour with the real AngularJS in jsdom (click, active item, hidden panes stay in the
   DOM) instead of relying on source assertions only.
4. New texts go in `siberian/languages/base/c_<context>.po` via `p__('<context>', ...)`, never the legacy csv.
