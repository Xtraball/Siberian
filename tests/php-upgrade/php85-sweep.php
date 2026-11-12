<?php
/**
 * PHP 8.5 deprecation sweep, token level (strings and comments are never touched).
 *
 *   php tests/php-upgrade/php85-sweep.php --check          list what is left in the first-party tree (exit 1 if any)
 *   php tests/php-upgrade/php85-sweep.php --write          rewrite it in place
 *   php tests/php-upgrade/php85-sweep.php --check FILE...  only these files (paths relative to the repository root)
 *
 * It handles the three kinds that are safe to rewrite mechanically:
 *  - (boolean) (integer) (double) casts become (bool) (int) (float)
 *  - implicitly nullable parameters (`Type $x = null`) become `?Type $x = null` (`A|B $x = null` becomes `A|B|null`)
 *  - `case X;` becomes `case X:`
 * Vendor trees are not touched: libraries are updated, not patched (lib/vendor, lib/onesignal/vendor), except
 * lib/vendor/gettext which this project already carries patches for.
 */

/**
 * @return array [new source, ['cast' => n, 'nullable' => n, 'case' => n]]
 */
function php85_sweep_source(string $source): array
{
    $tokens = token_get_all($source);
    $n = count($tokens);
    $text = array_map(fn($t) => is_array($t) ? $t[1] : $t, $tokens);
    $id = array_map(fn($t) => is_array($t) ? $t[0] : $t, $tokens);
    $count = ['cast' => 0, 'nullable' => 0, 'case' => 0];
    $ws = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];
    $modifiers = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_READONLY];

    // 1. casts
    $map = ['boolean' => 'bool', 'integer' => 'int', 'double' => 'float'];
    for ($i = 0; $i < $n; $i++) {
        if (in_array($id[$i], [T_BOOL_CAST, T_INT_CAST, T_DOUBLE_CAST], true)) {
            $inner = strtolower(trim($text[$i], "() \t"));
            if (isset($map[$inner])) {
                $text[$i] = '(' . $map[$inner] . ')';
                $count['cast']++;
            }
        }
    }

    // 2. implicit nullable parameters
    for ($i = 0; $i < $n; $i++) {
        if ($id[$i] !== T_FUNCTION && $id[$i] !== T_FN) {
            continue;
        }
        $j = $i + 1;
        while ($j < $n && $text[$j] !== '(') {
            if ($text[$j] === ';' || $text[$j] === '{') {
                break;
            }
            $j++;
        }
        if ($j >= $n || $text[$j] !== '(') {
            continue;
        }
        // parameters from $j+1 to the matching ')'
        $depth = 1;
        $k = $j + 1;
        $start = $k;
        $params = [];
        while ($k < $n && $depth > 0) {
            $t = $text[$k];
            if ($t === '(' || $t === '[' || $t === '{' || $id[$k] === T_CURLY_OPEN || $id[$k] === T_DOLLAR_OPEN_CURLY_BRACES) {
                $depth++;
            } elseif ($t === ')' || $t === ']' || $t === '}') {
                $depth--;
                if ($depth === 0) {
                    $params[] = [$start, $k];
                    break;
                }
            } elseif ($t === ',' && $depth === 1) {
                $params[] = [$start, $k];
                $start = $k + 1;
            }
            $k++;
        }
        foreach ($params as [$from, $to]) {
            $parts = [];
            for ($p = $from; $p < $to; $p++) {
                if (!in_array($id[$p], $ws, true)) {
                    $parts[] = $p;
                }
            }
            $varAt = null;
            foreach ($parts as $idx => $p) {
                if ($id[$p] === T_VARIABLE) {
                    $varAt = $idx;
                    break;
                }
            }
            if ($varAt === null) {
                continue;
            }
            // the default must be exactly `= null`
            $after = array_slice($parts, $varAt + 1);
            if (count($after) !== 2 || $text[$after[0]] !== '=' || strtolower($text[$after[1]]) !== 'null') {
                continue;
            }
            $before = array_slice($parts, 0, $varAt);
            // strip a trailing by-ref/variadic marker and leading modifiers
            while ($before && ($text[end($before)] === '&' || $text[end($before)] === '...'
                    || $id[end($before)] === T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG
                    || $id[end($before)] === T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG
                    || $id[end($before)] === T_ELLIPSIS)) {
                array_pop($before);
            }
            while ($before && in_array($id[$before[0]], $modifiers, true)) {
                array_shift($before);
            }
            if (!$before) {
                continue; // untyped: nothing implicit about it
            }
            $typeText = strtolower(implode('', array_map(fn($p) => $text[$p], $before)));
            if ($typeText[0] === '?' || strpos($typeText, '#[') !== false || strpos($typeText, '&') !== false
                || strpos($typeText, '(') !== false || preg_match('/(^|\|)(null|mixed)(\||$)/', $typeText)) {
                continue;
            }
            if (strpos($typeText, '|') === false) {
                $text[$before[0]] = '?' . $text[$before[0]];
            } else {
                $text[end($before)] .= '|null';
            }
            $count['nullable']++;
        }
    }

    // 3. case X; (an enum's `case Foo;` is valid and stays)
    $hasEnum = defined('T_ENUM') && in_array(T_ENUM, $id, true);
    for ($i = 0; $i < $n; $i++) {
        if ($hasEnum || $id[$i] !== T_CASE) {
            continue;
        }
        $depth = 0;
        $ternary = 0;
        for ($k = $i + 1; $k < $n; $k++) {
            $t = $text[$k];
            if ($t === '(' || $t === '[' || $t === '{' || $id[$k] === T_CURLY_OPEN) {
                $depth++;
            } elseif ($t === ')' || $t === ']' || $t === '}') {
                $depth--;
            } elseif ($depth === 0) {
                if ($t === '?') {
                    $ternary++;
                } elseif ($t === ':') {
                    if ($ternary > 0) {
                        $ternary--;
                    } else {
                        break;
                    }
                } elseif ($t === ';') {
                    $text[$k] = ':';
                    $count['case']++;
                    break;
                }
            }
        }
    }

    return [implode('', $text), $count];
}

/**
 * First-party PHP files under siberian/lib and siberian/app, relative to the repository root.
 *
 * @return string[]
 */
function php85_sweep_files(string $root): array
{
    $files = [];
    foreach (['siberian/lib', 'siberian/app'] as $dir) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $relative = substr($file->getPathname(), strlen($root) + 1);
            $relative = str_replace('\\', '/', $relative);
            if (strpos($relative, 'siberian/lib/vendor/') === 0 && strpos($relative, 'siberian/lib/vendor/gettext/') !== 0) {
                continue;
            }
            if (strpos($relative, '/node_modules/') !== false || strpos($relative, 'siberian/lib/onesignal/vendor/') === 0) {
                continue;
            }
            $files[] = $relative;
        }
    }
    sort($files);

    return $files;
}

/**
 * What is left to rewrite, per file: ['siberian/lib/x.php' => ['cast' => 2, ...]].
 *
 * @param string[] $files relative to $root
 */
function php85_sweep_findings(string $root, array $files): array
{
    $found = [];
    foreach ($files as $file) {
        try {
            [, $count] = php85_sweep_source((string) file_get_contents($root . '/' . $file));
        } catch (Throwable $e) {
            continue; // does not parse on this PHP: lint says so elsewhere
        }
        if (array_sum($count) > 0) {
            $found[$file] = $count;
        }
    }

    return $found;
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $root = dirname(__DIR__, 2);
    $write = in_array('--write', $argv, true);
    $named = array_values(array_filter(array_slice($argv, 1), fn($a) => strpos($a, '--') !== 0));
    $files = $named ?: php85_sweep_files($root);
    $found = php85_sweep_findings($root, $files);
    foreach ($found as $file => $count) {
        echo $file, ' ', json_encode($count), "\n";
        if ($write) {
            [$new] = php85_sweep_source((string) file_get_contents($root . '/' . $file));
            file_put_contents($root . '/' . $file, $new);
        }
    }
    fwrite(STDERR, count($found) . " file(s) " . ($write ? 'rewritten' : 'to rewrite') . "\n");
    exit($found && !$write ? 1 : 0);
}
