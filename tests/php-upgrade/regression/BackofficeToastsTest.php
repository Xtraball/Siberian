<?php
/**
 * Backoffice: the stock logo is white and sat on the white bar of the default theme (a colored copy is used there),
 * and the messages at the top (saved, error...) get a type with a color and an icon.
 */
$backoffice = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice';
$theme = $backoffice . '/css/theme-default/styles.css';
$icons = $backoffice . '/css/font-awesome.min.css';

/**
 * @param string $css
 * @param string $selector the exact selector, written as in the stylesheet
 * @return string the declarations of the rule, or '' when it is missing
 */
$rule = function ($css, $selector) {
    return preg_match('/(?:^|\}|\*\/)\s*' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/', $css, $m) === 1 ? $m[1] : '';
};

return [
    'a colored copy of the logo exists, same size and shape, no white pixel' => function () use ($backoffice) {
        $white = getimagesize($backoffice . '/images/header/logo.png');
        $color = getimagesize($backoffice . '/images/header/logo-color.png');
        t_equal($white[0] . 'x' . $white[1], $color[0] . 'x' . $color[1], 'same size');
        t_equal(IMAGETYPE_PNG, $color[2], 'png');
        if (!function_exists('imagecreatefrompng')) {
            return;
        }
        $image = imagecreatefrompng($backoffice . '/images/header/logo-color.png');
        $opaque = 0;
        for ($y = 0; $y < imagesy($image); $y += 2) {
            for ($x = 0; $x < imagesx($image); $x += 2) {
                $pixel = imagecolorat($image, $x, $y);
                if ((($pixel >> 24) & 0x7F) < 64) {
                    $opaque++;
                    t_true((($pixel >> 16) & 0xFF) < 0xC0, 'not white at ' . $x . ',' . $y);
                }
            }
        }
        t_true($opaque > 100, 'the logo has visible pixels');
    },

    'the default theme uses the colored logo, a custom logo is left alone' => function () use ($backoffice) {
        $html = file_get_contents($backoffice . '/template/page/layout/header.phtml');
        t_true(strpos($html, "in_array(\$backofficeTheme, [null, '', 'default'], true)") !== false, 'default theme only');
        t_true(strpos($html, "strpos(\$backofficeLogo, '/images/header/logo.png') !== false") !== false, 'stock logo only');
        t_true(strpos($html, "'/images/header/logo-color.png'") !== false, 'colored file');
    },

    'a message has a type and an icon for it' => function () use ($backoffice, $icons) {
        $js = file_get_contents($backoffice . '/js/services/message.js');
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            t_true(strpos($js, $type . ': "fa-') !== false, 'icon for ' . $type);
        }
        t_true(strpos($js, 'this.type = is_error ? "error" : "success"') !== false, 'isError sets the type');
        t_true(strpos($js, 'this.setText(message).isInfo().show()') !== false, 'information() is info');
        t_true(strpos($js, 'this.warning = function') !== false, 'warning() exists');
        $css = file_get_contents($icons);
        foreach (['fa-check-circle', 'fa-times-circle', 'fa-exclamation-triangle', 'fa-info-circle'] as $icon) {
            t_true(strpos($css, '.' . $icon . ':before') !== false, $icon . ' in the font');
        }
    },

    'the layout shows the icon and the type of the message, keeps the error class' => function () use ($backoffice) {
        $html = file_get_contents($backoffice . '/template/page/layout.phtml');
        t_true(strpos($html, 'message-{{ message.type }}') !== false, 'type class');
        t_true(strpos($html, 'ng-class="{error: message.is_error}"') !== false, 'error class kept for the other themes');
        t_true(strpos($html, 'ng-class="message.getIcon()"') !== false, 'icon');
        t_true(strpos($html, '<span ng-bind-html="message.text"></span>') !== false, 'text');
    },

    'each type has its own color in the default theme' => function () use ($theme, $rule) {
        $css = file_get_contents($theme);
        foreach (['success', 'warning', 'info'] as $type) {
            $declarations = $rule($css, '.top div.message.message-' . $type . ' p');
            t_true(strpos($declarations, 'var(--sb-' . ($type === 'info' ? 'accent' : $type) . ')') !== false, $type . ' color');
        }
        t_true(strpos($css, '.top div.message.message-error p {') !== false, 'error color');
    },
];
