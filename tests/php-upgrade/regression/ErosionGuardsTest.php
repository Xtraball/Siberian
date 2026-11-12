<?php
/**
 * The erosion guards of check-pr.sh (AGENTS.md rule 10): the method length tool measures what it says, and the list of
 * big files names files that exist and are not already above their limit.
 */
$root = dirname(__DIR__, 3);
$tool = $root . '/tests/php-upgrade/tooling/method-lengths.php';

$measure = static function ($code, $min = 0) use ($tool) {
    $file = sys_get_temp_dir() . '/ml-' . bin2hex(random_bytes(4)) . '.php';
    file_put_contents($file, $code);
    $out = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tool) . ' --min=' . (int) $min . ' ' . escapeshellarg($file));
    unlink($file);
    $rows = [];
    foreach (array_filter(explode("\n", (string) $out)) as $line) {
        [$len, $name] = explode("\t", $line);
        $rows[$name] = (int) $len;
    }

    return $rows;
};

return [
    'the tool counts the lines of each method, braces included' => function () use ($measure) {
        $code = "<?php\nclass A {\n    public function one() {\n        return 1;\n    }\n\n    private static function two(\$x)\n    {\n        \$a = 1;\n        \$b = 2;\n        return \$a + \$b + \$x;\n    }\n}\n";
        t_equal(['two' => 6, 'one' => 3], $measure($code));
    },
    'abstract methods and closures are not methods, braces in strings do not confuse it' => function () use ($measure) {
        $code = "<?php\nabstract class A {\n    abstract public function none();\n    public function s() {\n        \$f = function () { return 1; };\n        return \"{ not a block } {\$f()}\";\n    }\n}\n";
        t_equal(['s' => 4], $measure($code));
    },
    'min leaves the short ones out, a long one is found' => function () use ($measure) {
        $body = str_repeat("        \$a = 1;\n", 200);
        $code = "<?php\nclass A {\n    public function big() {\n{$body}    }\n    public function small() { return 1; }\n}\n";
        $rows = $measure($code, 151);
        t_equal(['big'], array_keys($rows));
        t_equal(202, $rows['big']);
    },
    'the big files listed in size-limits.txt exist and are within their limit' => function () use ($root) {
        $lines = array_filter(explode("\n", (string) file_get_contents($root . '/tests/php-upgrade/size-limits.txt')));
        t_true(count($lines) >= 5, 'the list is not empty');
        foreach ($lines as $line) {
            [$path, $cap] = explode("\t", $line);
            t_true(is_file($root . '/' . $path), "$path exists");
            $now = substr_count((string) file_get_contents($root . '/' . $path), "\n");
            t_true($now <= (int) $cap, "$path has $now lines, the limit is $cap");
        }
    },
];
