<?php
/**
 * #875: _() is undefined when the gettext extension is not loaded, the translation helper is __().
 * Static check on the source, so it runs without the application.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** Lines on which the bare gettext alias _() is called (not a method, not a definition). */
$bareGettextCalls = function (string $code) {
    $tokens = token_get_all($code);
    $lines = [];
    foreach ($tokens as $i => $token) {
        if (!is_array($token) || $token[0] !== T_STRING || $token[1] !== '_') {
            continue;
        }
        $prev = null;
        for ($j = $i - 1; $j >= 0; $j--) {
            if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT], true)) {
                continue;
            }
            $prev = $tokens[$j];
            break;
        }
        $next = $tokens[$i + 1] ?? null;
        $isMember = is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true);
        if ($next === '(' && !$isMember) {
            $lines[] = $token[2];
        }
    }
    return $lines;
};

return [
    '875 detector finds a bare _() call and ignores __() and methods' => function () use ($bareGettextCalls) {
        t_equal([1], $bareGettextCalls('<?php $a = _("x");'), 'bare call found');
        t_equal([], $bareGettextCalls('<?php $a = __("x");'), '__ is fine');
        t_equal([], $bareGettextCalls('<?php $a = $o->_("x"); $b = Foo::_("y");'), 'methods are fine');
    },

    '875 the publication form translates with __()' => function () use ($siberian, $bareGettextCalls) {
        $file = $siberian . '/app/sae/modules/Application/Form/Customization/Publication/App.php';
        $code = file_get_contents($file);
        t_equal([], $bareGettextCalls($code), 'no bare _() call');
        t_true(strpos($code, '__("Download template as")') !== false, 'the hint is still translated');
    },
];
