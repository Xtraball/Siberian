<?php
/**
 * Backoffice top bar on small windows: the bar is the full width of the window (the base stylesheet forced a 1250px
 * minimum, so anything narrower scrolled sideways) and below 1280px the menus fold under a toggle button.
 */
$backoffice = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice';
$base = file_get_contents($backoffice . '/css/style.css');
$header = file_get_contents($backoffice . '/template/page/layout/header.phtml');
$app = file_get_contents($backoffice . '/js/app.js');
$layout = file_get_contents($backoffice . '/template/page/layout.phtml');

/**
 * @param string $css
 * @param string $selector the exact selector, written as in the stylesheet
 * @return string the declarations of the rule, or '' when it is missing
 */
$rule = function ($css, $selector) {
    return preg_match('/(?:^|\}|\*\/)\s*' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/', $css, $m) === 1 ? $m[1] : '';
};

return [
    'the bar has no minimum width any more' => function () use ($backoffice, $base, $rule) {
        $declarations = $rule($base, '.top .content');
        t_true(preg_match('/(?<![-\w])width:\s*100%/', $declarations) === 1, 'full width');
        t_true(preg_match('/min-width:\s*0/', $declarations) === 1, 'no minimum');
        t_true(strpos($base, 'min-width: 1250px') === false, 'the 1250px minimum is gone');
        $theme = $rule(file_get_contents($backoffice . '/css/theme-default/styles.css'), '.top .content');
        t_true(strpos($theme, '1440px') === false, 'the default theme no longer contains the bar');
    },

    'below 1280px the menu folds under the toggle' => function () use ($base) {
        t_true(preg_match('/@media \(max-width: 1279px\) \{.*?\.top #backoffice_menu \{\s*display:\s*none;/s', $base) === 1, 'hidden by default');
        t_true(preg_match('/\.top #backoffice_menu\.open \{\s*display:\s*block;/', $base) === 1, 'open state shows it');
        t_true(preg_match('/\.backoffice_menu_toggle \{\s*display:\s*none;/', $base) === 1, 'the toggle is hidden on wide windows');
        t_true(preg_match('/\.top \.content \.backoffice_menu_toggle \{\s*display:\s*block;/', $base) === 1, 'and shown on narrow ones');
        t_true(preg_match('/ul\.l2,\s*\.top #backoffice_menu ul\.l1 li ul\.l2 li ul\.l3 \{\s*position:\s*static;/', $base) === 1, 'submenus are listed inline');
    },

    'the header carries the toggle and the open state' => function () use ($header) {
        t_true(preg_match('/<button type="button"\s+class="backoffice_menu_toggle"\s+ng-if="isLoggedIn"\s+ng-click="toggleMainMenu\(\)"/', $header) === 1, 'toggle button');
        t_true(strpos($header, 'aria-controls="backoffice_menu"') !== false, 'names the menu it controls');
        t_true(strpos($header, 'open: mainMenu.open') !== false, 'class open follows mainMenu.open');
        t_true(strpos($header, 'ng-click="mainMenu.open = false"') !== false, 'a click on an entry folds it again');
    },

    'the controller keeps the state and closes it on page change' => function () use ($app) {
        t_true(strpos($app, '$rootScope.mainMenu = {open: false};') !== false, 'state object');
        t_true(preg_match('/\$rootScope\.toggleMainMenu = function \(\) \{\s*\$rootScope\.mainMenu\.open = !\$rootScope\.mainMenu\.open;/', $app) === 1, 'toggle');
        t_true(preg_match('/\$routeChangeSuccess.*?\$rootScope\.mainMenu\.open = false;/s', $app) === 1, 'closed after every route change');
    },

    'phones render at their own width' => function () use ($layout) {
        t_true(strpos($layout, '<meta name="viewport" content="width=device-width, initial-scale=1" />') !== false, 'viewport meta');
    },
];
