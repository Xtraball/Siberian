<?php
/**
 * Backoffice top bar: the bar content is the full width of the window, the menus sit on the left and the
 * "Log-out" menu entry is replaced by a user dropdown (avatar, name, link to the account, log-out).
 */
$backoffice = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice';
$header = $backoffice . '/template/page/layout/header.phtml';
$layout = $backoffice . '/template/page/layout.phtml';
$app = $backoffice . '/js/app.js';
$theme = $backoffice . '/css/theme-default/styles.css';
$base = $backoffice . '/css/style.css';
$icons = $backoffice . '/css/font-awesome.min.css';
$catalog = dirname(__DIR__, 3) . '/siberian/languages/base/c_backoffice.po';

/**
 * @param string $css
 * @param string $selector the exact selector, written as in the stylesheet
 * @return string the declarations of the rule, or '' when it is missing
 */
$rule = function ($css, $selector) {
    return preg_match('/(?:^|\}|\*\/)\s*' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/', $css, $m) === 1 ? $m[1] : '';
};

return [
    'the top bar content is the full width of the window' => function () use ($theme, $rule) {
        $declarations = $rule(file_get_contents($theme), '.top .content');
        t_true(preg_match('/(?<![-\w])width:\s*100%/', $declarations) === 1, 'full width');
        t_true(preg_match('/max-width:\s*none/', $declarations) === 1, 'no 1440px container any more');
        t_true(preg_match('/min-width:\s*0/', $declarations) === 1, 'the 1250px minimum of the base stylesheet is gone');
        t_true(preg_match('/display:\s*flex/', $declarations) === 1, 'logo, menus and user menu are one row');
    },

    'the logo is centered by the bar, without the old top margin' => function () use ($theme, $rule, $header) {
        $declarations = $rule(file_get_contents($theme), '.top img#logo');
        t_true(preg_match('/margin-top:\s*0/', $declarations) === 1, 'no top margin');
        t_true(preg_match('/height:\s*34px/', $declarations) === 1, '34px high');
        t_true(strpos(file_get_contents($header), 'max-height: 34px;') !== false, 'inline limit follows the height');
    },

    'the menus are pushed to the left, the user menu to the right' => function () use ($theme, $rule) {
        $css = file_get_contents($theme);
        t_true(preg_match('/flex:\s*1 1 auto/', $rule($css, '.backoffice_menu')) === 1, 'the menu takes the free space');
        t_true(preg_match('/flex:\s*0 0 auto/', $rule($css, '.backoffice_user')) === 1, 'the user menu keeps its size');
    },

    'the log-out entry left the menu tree and lives in the user dropdown' => function () use ($header) {
        $html = file_get_contents($header);
        t_true(strpos($html, "'logout' =>") === false, 'no logout entry in the menu tree');
        t_equal(1, substr_count($html, 'ng-click="logout()"'), 'one log-out action');
        t_true(preg_match('/class="backoffice_user"[^>]*ng-if="isLoggedIn"/s', $html) === 1, 'only for a logged in user');
        t_true(strpos($html, 'ng-bind="userInitials()"') !== false, 'avatar shows the initials');
        t_true(strpos($html, 'ng-bind="userName()"') !== false, 'the name is shown');
        t_true(strpos($html, "/user_id/{{ currentUser().id }}") !== false, 'the account link targets the logged user');
        t_true(strpos($html, "p__('backoffice', 'Edit my account')") !== false, 'the new text has a context');
    },

    'the account link is a route of the account controller' => function () use ($backoffice) {
        $js = file_get_contents($backoffice . '/js/controllers/account.js');
        t_true(strpos($js, '"/backoffice/account_view/user_id/:user_id"') !== false, 'route exists');
    },

    'the user labels are scope functions backed by the Auth factory' => function () use ($app) {
        $js = file_get_contents($app);
        foreach (['currentUser', 'userName', 'userInitials'] as $name) {
            t_equal(1, substr_count($js, '$rootScope.' . $name . ' = function'), $name . ' defined once');
        }
    },

    'the email of the session user is handed to the Auth factory at load, escaped for a script' => function () use ($layout) {
        $html = file_get_contents($layout);
        t_true(strpos($html, 'Auth.user.email = <?php') !== false, 'email set');
        t_true(strpos($html, 'JSON_HEX_TAG') !== false, 'tags escaped');
        // getBackofficeUser() instantiates Backoffice_Model_Backoffice, which does not exist: fatal when logged out
        t_true(strpos($html, 'getBackofficeUser()') === false, 'no session getBackofficeUser() in the layout');
        t_true(strpos($html, '(new Backoffice_Model_User())->find($backofficeUserId)') !== false, 'user read by id');
        t_true(strpos($html, '$backofficeUserId ?') !== false, 'only when somebody is logged in');
    },

    'the icons exist in the font shipped with the backoffice' => function () use ($icons, $header) {
        $css = file_get_contents($icons);
        foreach (['fa-user', 'fa-power-off', 'fa-caret-down'] as $icon) {
            t_true(strpos($css, '.' . $icon . ':before') !== false, $icon . ' in the font');
            t_true(strpos(file_get_contents($header), $icon) !== false, $icon . ' used');
        }
    },

    'the dropdown opens on hover and on keyboard focus' => function () use ($theme, $base) {
        foreach ([$theme, $base] as $file) {
            $css = file_get_contents($file);
            t_true(strpos($css, '.backoffice_user:hover ul.backoffice_user_dropdown') !== false, basename(dirname($file)) . ' hover');
            t_true(strpos($css, '.backoffice_user:focus-within ul.backoffice_user_dropdown') !== false, basename(dirname($file)) . ' focus');
        }
    },

    'the new text is in the context catalog' => function () use ($catalog) {
        $po = file_get_contents($catalog);
        t_true(strpos($po, "msgctxt \"backoffice\"\nmsgid \"Edit my account\"") !== false, 'catalog entry');
    },
];
