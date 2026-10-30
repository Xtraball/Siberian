<?php
/**
 * Backoffice user dropdown (top bar): it is opened by CSS only (:hover, :focus-within) and the backoffice changes page
 * without a reload, so after a click on "Edit my account" the entry kept the focus and the pointer stayed over it:
 * the menu never closed. A click now closes it until the pointer leaves the user menu.
 */
$backoffice = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice';
$header = file_get_contents($backoffice . '/template/page/layout/header.phtml');
$app = file_get_contents($backoffice . '/js/app.js');
$css = file_get_contents($backoffice . '/css/style.css');

return [
    'the user menu is closed by the click on its entries' => function () use ($header) {
        t_true(preg_match('/id="backoffice_user"[^>]*ng-class="\{closed: userMenu\.closed\}"/s', $header) === 1, 'class closed follows userMenu.closed');
        t_true(preg_match('/id="backoffice_user"[^>]*ng-mouseleave="userMenu\.closed = false"/s', $header) === 1, 'it reopens when the pointer leaves');
        t_equal(1, substr_count($header, 'closeUserMenu($event)'), 'the account link closes it');
        t_true(strpos($header, 'ng-click="logout()"') !== false, 'log-out is untouched: the menu goes away with the session');
    },

    'the controller helper blurs the clicked entry and marks the menu closed' => function () use ($app) {
        t_true(strpos($app, '$rootScope.userMenu = {closed: false};') !== false, 'state object');
        t_true(preg_match('/\$rootScope\.closeUserMenu = function \(\$event\) \{.*?\$event\.currentTarget\.blur\(\);.*?\$rootScope\.userMenu\.closed = true;/s', $app) === 1, 'blur, then closed');
    },

    'the closed class wins over :hover and :focus-within' => function () use ($css) {
        $hover = strpos($css, '.backoffice_user:focus-within ul.backoffice_user_dropdown {');
        $closed = strpos($css, '.backoffice_user.closed ul.backoffice_user_dropdown {');
        t_true($hover !== false && $closed !== false, 'both rules exist');
        t_true($closed > $hover, 'the closed rule comes later: same specificity, later wins');
        t_true(preg_match('/\.backoffice_user\.closed ul\.backoffice_user_dropdown \{\s*display:\s*none;/', $css) === 1, 'display none');
    },
];
