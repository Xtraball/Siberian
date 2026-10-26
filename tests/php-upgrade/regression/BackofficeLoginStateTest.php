<?php
/**
 * Backoffice login: (1) the page told AngularJS "login success" on every load, even without a session, so a logged out
 * visitor saw the menu and an empty view before the login form replaced them; (2) the loader sat next to the button
 * and squeezed it, it is now a spinner inside the button.
 */
$backoffice = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice';
$layout = $backoffice . '/template/page/layout.phtml';
$login = $backoffice . '/template/backoffice/user/login.phtml';
$theme = $backoffice . '/css/theme-default/styles.css';
$icons = $backoffice . '/css/font-awesome.min.css';

return [
    'login success is announced only for a real session' => function () use ($layout) {
        $html = file_get_contents($layout);
        t_true(preg_match('/if \(Auth\.user\.id\) \{\s*\$rootScope\.\$broadcast\(AUTH_EVENTS\.loginSuccess\);\s*\}/', $html) === 1, 'broadcast guarded by the session user');
        t_equal(1, substr_count($html, 'AUTH_EVENTS.loginSuccess'), 'announced once');
    },

    'the login form has no loader beside its buttons, a spinner is inside them' => function () use ($login) {
        $html = file_get_contents($login);
        t_equal(0, substr_count($html, '<sb-loader'), 'no sb-loader');
        t_equal(2, substr_count($html, 'fa fa-spinner fa-spin'), 'a spinner in each form');
        t_equal(2, substr_count($html, 'ng-disabled="form_loader_is_visible"'), 'buttons are disabled while loading');
        foreach (['Log in', 'Send'] as $label) {
            t_true(preg_match('/<button[^>]*>\s*<i class="fa fa-spinner fa-spin"[^>]*><\/i>\s*<\?php echo __\(\'' . $label . '\'\); \?>\s*<\/button>/', $html) === 1, "$label button has the spinner inside");
        }
    },

    'the spinner is in the backoffice icon font and the button centers it' => function () use ($theme, $icons) {
        $font = file_get_contents($icons);
        t_true(strpos($font, '.fa-spinner:before') !== false, 'fa-spinner');
        t_true(strpos($font, '.fa-spin') !== false, 'fa-spin');
        t_true(preg_match('/\.login-actions \.btn-primary\s*\{[^}]*display:\s*flex[^}]*justify-content:\s*center/s', file_get_contents($theme)) === 1, 'button content centered');
    },
];
