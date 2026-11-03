<?php
/**
 * A translation echoed inside a JavaScript string has to be escaped for that string: p__js($context, $text, "'"),
 * __js($text) for a double quoted string, __jss($text) for a single quoted one. The plain p__ / __ leaves the quote
 * of the translation in the string ("Vuoi davvero reimpostare lo stato dell'utente?"), which ends the string and
 * breaks the whole script of the page. Careful: the third argument of p__ is a sprintf argument, not an escape
 * character, so p__('ctx', 'text', "'") escapes nothing.
 *
 * The rule is checked on the templates of SAE (a line at a time: a string that spans lines is not seen).
 */
if (!function_exists('jste_find')) {
    /** [[offset of the name, name, 'single'|'double'], ...] for the echoes of __ / p__ / P__ inside a JS string */
    function jste_find(string $line): array
    {
        $found = [];
        if (strpos($line, '__(') === false
            || !preg_match_all('/<\?(?:php|=)\s*(?:echo\s+)?(?<![\w])([pP]?__)\(/', $line, $mm, PREG_OFFSET_CAPTURE)) {
            return $found;
        }
        foreach ($mm[0] as $k => $m) {
            $prefix = preg_replace('/<\?.*?\?>/', '', substr($line, 0, $m[1]));
            $open = null;
            $at = 0;
            for ($i = 0, $len = strlen($prefix); $i < $len; $i++) {
                $c = $prefix[$i];
                if ($c === '\\') {
                    $i++;
                    continue;
                }
                if ($open === null && ($c === '"' || $c === "'")) {
                    $open = $c;
                    $at = $i;
                } elseif ($open !== null && $c === $open) {
                    $open = null;
                }
            }
            if ($open === null) {
                continue;
            }
            if (preg_match('/[\w:@.#-]=$/', substr($prefix, 0, $at)) === 1) {
                // an html attribute is not JavaScript, unless it holds a single quoted JS string (angular expression)
                if ($open === '"' && substr_count(substr($prefix, $at + 1), "'") % 2 === 1) {
                    $found[] = [$mm[1][$k][1], $mm[1][$k][0], 'single'];
                }
                continue;
            }
            $found[] = [$mm[1][$k][1], $mm[1][$k][0], $open === "'" ? 'single' : 'double'];
        }
        return $found;
    }
}

return [
    'the detector sees a translation echoed unescaped inside a JS string' => function () {
        t_equal(1, count(jste_find("text: 'Hello <?php echo __('x') ?>',")), 'single quoted string');
        t_equal(1, count(jste_find('error: "<?php echo p__("ctx", "x"); ?>"')), 'double quoted string');
        t_equal(1, count(jste_find('title="\'<?php echo __("x"); ?>\'"')), 'angular string inside an attribute');
        t_equal(1, count(jste_find("text: '<?php echo p__('ctx', 'x', \"'\") ?>'")), 'a quote as third argument of p__ escapes nothing');
    },

    'the detector leaves the escaped forms and html attributes alone' => function () {
        t_equal(0, count(jste_find("text: '<?php echo __jss('x') ?>',")), '__jss');
        t_equal(0, count(jste_find("text: '<?php echo p__js('ctx', 'x', \"'\") ?>',")), 'p__js');
        t_equal(0, count(jste_find('error: "<?php echo __js("x"); ?>"')), '__js');
        t_equal(0, count(jste_find('<input title="<?php echo __("x") ?>" />')), 'html attribute');
        t_equal(0, count(jste_find('<p><?php echo __("x") ?></p>')), 'html text');
    },

    'no translation of SAE is echoed unescaped inside a JS string' => function () {
        $root = dirname(__DIR__, 3) . '/siberian/app/sae';
        $bad = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            $path = $file->getPathname();
            if (!preg_match('/\.(phtml|php)$/', $path) || strpos($path, '/vendor/') !== false || strpos($path, '/node_modules/') !== false) {
                continue;
            }
            foreach (file($path) as $i => $line) {
                if (jste_find($line)) {
                    $bad[] = substr($path, strlen($root) + 1) . ':' . ($i + 1);
                }
            }
        }
        t_true($bad === [], 'use p__js / __js / __jss inside a JS string: ' . implode(', ', array_slice($bad, 0, 12)) . (count($bad) > 12 ? ' ... (' . count($bad) . ' in all)' : ''));
    },
];
