# Backoffice UI recipes

Small, repeatable patterns for the backoffice (AngularJS 1 + Zend templates, default theme `theme-default`). Each recipe
ships with a regression test, so a page is converted the same way every time.

## Sections with a side navigation (`sb-section-nav`)

Use it when a page stacks unrelated cards (Settings > Email: Support, Custom SMTP, E-Mail render preview).

```html
<sb-section-nav>
    <sb-section-pane icon="fa-life-ring" title="'Support'">
        <form class="form-horizontal" ng-submit="save(['support_name', 'support_email'])">
            ... fields (ng-model="configs.support_name.value") ...
            <button type="submit" class="btn btn-primary right">Save</button>
        </form>
    </sb-section-pane>
    <sb-section-pane icon="fa-eye" title="'Preview'"> ... nothing to save, no form ... </sb-section-pane>
</sb-section-nav>
```

- A pane is an `sb-section` (same card, title, optional `button`) that is shown when selected. The first pane is shown by
  default and its menu item is highlighted.
- Panes are hidden, never removed (`ng-show`), so their models stay bound.
- **A section saves only what it shows.** No page-wide form around the nav: each pane that has fields gets its own
  `<form ng-submit="save([codes])">` and its own Save button; a pane with nothing to edit (a preview) has neither.
  `SettingsController.save(codes)` posts only those settings (`save()` with no argument still posts everything, for
  the pages that are not sectioned) and does not reload the page, which would send the user back to the first pane.
  Because there is no reload, `Settings.findAll` is not cached (a cached read would show the old values when the user
  comes back to the page). The server `_save` only touches the codes it receives, so no PHP change is needed. A test checks that the codes of
  each Save are exactly the `configs.<code>` models of its pane.
- `icon` is optional (Font Awesome 4 class). `title` is an expression, like `sb-section`. The backoffice ships its own
  `css/font-awesome.min.css`, which is not complete (no `fa-server`, for one): an icon that is not in it renders as
  nothing and shifts the label. The test checks every pane icon against that file.
- Menu items are one line each (ellipsis if too long), so keep titles short. The list is as wide as its longest label.
- The page wrapper is widened (`.wrapper:has(.sb-section-nav)`) because the menu takes a share of the default 1440px.
- Conversion of a page: `<sb-section title=` becomes `<sb-section-pane icon="..." title=`, `</sb-section>` becomes
  `</sb-section-pane>`, wrap them in `<sb-section-nav>`, move the page-wide `<form>` inside each pane and scope its save.
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
4. Look at it in a real browser once (QA on siberian01): layout problems (a wrapped menu item, a missing icon, too narrow
   a page) never show in source tests, they were found by the user at the first QA of Settings > Email.
5. Check the console for JS errors on every action of the page (click, save, navigate): in a real browser when it can
   reach the page, otherwise the real AngularJS in jsdom. Source tests do not catch a runtime error: a controller that
   used `$window` without declaring it ended every save in a ReferenceError. `BackofficeControllerInjectionTest` now
   checks that a controller declares the services it uses.
6. The MAE and PE repositories override some backoffice files (System `general.phtml`, `settings.js`, the controllers):
   check `siberian/app/mae` and `siberian/app/pe` for a file with the same path and port the change there.
7. New texts go in `siberian/languages/base/c_<context>.po` via `p__('<context>', ...)`, never the legacy csv.

## Top bar

The bar content (`.top .content`) has the width of the page body (1440px, `calc(100% - 32px)` on small screens) and is a
flex row: logo, menus on the left (`.backoffice_menu`, takes the free space), user menu on the right (`.backoffice_user`).

The menu tree built in `header.phtml` (and extended by the `backoffice.menu.ready` hook) holds the navigation only.
The session actions live in the user dropdown: an avatar made of the initials of the user, the name, a link to the
account edit form and Log-out. A backoffice user has an email only, so the name is the part before the `@` and the
initials come from its words (`jane.doe` is `JD`). No third party avatar service is called (privacy).

The labels are `$rootScope.currentUser()`, `userName()` and `userInitials()` in `js/app.js`, backed by the `Auth`
factory; `layout.phtml` seeds `Auth.user.id` and `Auth.user.email` from the session at load, the login response
fills them afterwards. The dropdown opens on hover and on keyboard focus (`:focus-within`), no JavaScript.

## Logo and messages

The stock logo (`images/header/logo.png`) is white, for the dark bars. The default theme has a light bar and uses
`logo-color.png` (accent color, same alpha) instead, chosen in `header.phtml` only when the logo is the stock one.
A custom or white label logo is shown as is.

The messages at the top come from the `Message` service: `isError(bool)`, `information()` (info), `warning()`,
and a `type` (success, error, warning, info) that selects the color and the icon (`getIcon()`, icons of the
backoffice FA 4.2 font). The `error` class is kept for the other themes.
