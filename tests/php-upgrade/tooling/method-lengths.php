<?php
/**
 * Prints the length in lines of every method or function of the given PHP files, longest first, as TSV:
 *   <lines>\t<name>\t<file>
 * Used by check-pr.sh (a new or grown method over the limit fails) and for reviews. Usage:
 *   php tests/php-upgrade/tooling/method-lengths.php [--min=N] file...
 * A name that appears twice in a file (an anonymous class, a closure named by its variable) is reported per occurrence.
 */
$min = 0;
$files = [];
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--min=') === 0) {
        $min = (int) substr($arg, 6);
    } else {
        $files[] = $arg;
    }
}

$rows = [];
foreach ($files as $file) {
    if (!is_file($file)) {
        continue;
    }
    $tokens = token_get_all((string) file_get_contents($file));
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        $t = $tokens[$i];
        if (!is_array($t) || $t[0] !== T_FUNCTION) {
            continue;
        }
        $j = $i + 1;
        while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }
        if (isset($tokens[$j]) && $tokens[$j] === '&') {
            $j++;
        }
        while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }
        if (!isset($tokens[$j]) || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) {
            continue; // a closure
        }
        $name = $tokens[$j][1];
        $depth = 0;
        $opened = false;
        for ($k = $j; $k < $count; $k++) {
            $x = $tokens[$k];
            if ($x === ';' && !$opened) {
                break; // abstract or interface method
            }
            if ($x === '{' || (is_array($x) && in_array($x[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $depth++;
                $opened = true;
            } elseif ($x === '}') {
                $depth--;
                if ($opened && $depth === 0) {
                    break;
                }
            }
        }
        if (!$opened) {
            continue;
        }
        $end = $t[2];
        for ($m = $k; $m >= $j; $m--) {
            if (is_array($tokens[$m])) {
                $end = $tokens[$m][2] + substr_count($tokens[$m][1], "\n");
                break;
            }
        }
        $length = max(1, $end - $t[2] + 1);
        if ($length >= $min) {
            $rows[] = [$length, $name, $file];
        }
    }
}

usort($rows, static function ($a, $b) {
    return $b[0] <=> $a[0];
});
foreach ($rows as $row) {
    echo $row[0], "\t", $row[1], "\t", $row[2], "\n";
}
