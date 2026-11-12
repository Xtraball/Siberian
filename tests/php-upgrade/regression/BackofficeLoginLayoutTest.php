<?php
/**
 * Backoffice login page, default theme: the form fields sat in Bootstrap thirds of a 460px card (117px wide fields),
 * the title box kept the 1100px of the base stylesheet and landed outside the card, and the forgotten password form
 * stacked a red back button on top of the main action. The theme now gives the form the width of the card.
 */
$backoffice = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice';
$theme = $backoffice . '/css/theme-default/styles.css';
$template = $backoffice . '/template/backoffice/user/login.phtml';

/**
 * @param string $css
 * @param string $selector the exact selector, written as in the stylesheet
 * @return string the declarations of the rule, or '' when it is missing
 */
$rule = function ($css, $selector) {
    return preg_match('/(?:^|\}|\*\/)\s*' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/', $css, $m) === 1 ? $m[1] : '';
};

return [
    'the title box no longer keeps the 1100px of the base stylesheet' => function () use ($theme, $rule) {
        $css = file_get_contents($theme);
        $declarations = $rule($css, '.login-form .wrapper .title div.title_content');
        t_true(preg_match('/width:\s*auto/', $declarations) === 1, 'title box width auto');
        t_true(preg_match('/text-align:\s*center/', $declarations) === 1, 'title centered');
    },

    'the form columns take the width of the card' => function () use ($theme, $rule) {
        $declarations = $rule(file_get_contents($theme), '.login-form .wrapper .content [class*="col-md-"]');
        t_true(preg_match('/float:\s*none/', $declarations) === 1, 'no float');
        t_true(preg_match('/width:\s*auto/', $declarations) === 1, 'no third of the card');
        t_true(preg_match('/margin-left:\s*0/', $declarations) === 1, 'no offset');
    },

    'the actions are one row: back button next to the main action' => function () use ($theme, $rule, $template) {
        $css = file_get_contents($theme);
        t_true(preg_match('/display:\s*flex/', $rule($css, '.login-form .wrapper .content .login-actions')) === 1, 'flex row');
        t_true(preg_match('/flex:\s*1 1 auto/', $rule($css, '.login-form .wrapper .content .login-actions .btn-primary')) === 1, 'main action grows');
        $html = file_get_contents($template);
        t_equal(2, substr_count($html, 'login-actions'), 'both forms have an action row');
        t_equal(1, substr_count($html, 'login-forgot'), 'forgot link has its own style hook');
        t_equal(2, substr_count($html, '<form '), 'login and forgotten password forms are kept');
    },

    'the back button is not styled as an alarm' => function () use ($theme, $rule) {
        $declarations = $rule(file_get_contents($theme), '.login-form .wrapper .content .login-actions .btn-danger');
        t_true(strpos($declarations, 'var(--sb-surface)') !== false, 'neutral background');
        t_true(strpos($declarations, 'var(--sb-border)') !== false, 'neutral border');
    },
];
