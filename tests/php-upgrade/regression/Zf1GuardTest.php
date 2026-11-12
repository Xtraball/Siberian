<?php
/**
 * check-pr.sh fails a branch that adds Zend Framework 1 (Zend_*) use, see docs/decisions/0001-remove-zf1.md.
 *
 * The real script runs against a throwaway git repository: a trunk branch with a legacy file (listed in
 * zf1-allowlist.txt) and a clean file, then one branch per case.
 */
$realScript = dirname(__DIR__) . '/check-pr.sh';

$sh = function (string $dir, string $command): array {
    $out = [];
    exec('cd ' . escapeshellarg($dir) . ' && ' . $command . ' 2>&1', $out, $code);
    return [$code, implode("\n", $out)];
};

$git = 'git -c user.name=test -c user.email=test@example.invalid -c commit.gpgsign=false -c core.autocrlf=false ';

$put = function (string $dir, string $path, string $content) {
    $full = $dir . '/' . $path;
    if (!is_dir(dirname($full))) {
        mkdir(dirname($full), 0777, true);
    }
    file_put_contents($full, $content);
};

$legacyPath = 'siberian/app/sae/modules/Old/Model/Old.php';
$cleanPath = 'siberian/lib/Siberian/Clean.php';
$legacy = "<?php\nclass Old_Model_Old extends Zend_Db_Table\n{\n    public function a() { return new Zend_Date(); }\n    public function b() { return 1; }\n}\n";

// A repository whose trunk has the script, the allowlist, a legacy file and a clean file.
$repo = sys_get_temp_dir() . '/zf1guard-' . getmypid() . '-' . mt_rand();
mkdir($repo . '/tests/php-upgrade', 0777, true);
copy($realScript, $repo . '/tests/php-upgrade/check-pr.sh');
$put($repo, 'tests/php-upgrade/zf1-allowlist.txt', $legacyPath . "\n");
$put($repo, $legacyPath, $legacy);
$put($repo, $cleanPath, "<?php\nnamespace Siberian;\nclass Clean {}\n");
$put($repo, 'siberian/lib/Zend/Date.php', "<?php\nclass Zend_Date {}\n");
$sh($repo, 'git init -q -b trunk && git add -A && ' . $git . 'commit -q -m base');
register_shutdown_function(function () use ($repo) {
    exec('rm -rf ' . escapeshellarg($repo));
});

// Runs one case on its own branch from trunk: $change edits the tree, the result is [exit code, output].
$run = function (callable $change) use ($repo, $sh, $git, $put) {
    $sh($repo, 'git checkout -q -B case trunk');
    $change($repo, $put);
    $sh($repo, 'git add -A && ' . $git . 'commit -q -m case');
    return $sh($repo, 'sh tests/php-upgrade/check-pr.sh trunk');
};

return [
    'a branch without Zend_ passes' => function () use ($run, $cleanPath) {
        [$code, $out] = $run(function ($r, $put) use ($cleanPath) {
            $put($r, $cleanPath, "<?php\nnamespace Siberian;\nclass Clean { public function ok() { return 1; } }\n");
        });
        t_equal(0, $code, "exit code\n$out");
        t_true(strpos($out, 'Zend_') === false, 'nothing about Zend_');
    },

    'a new file that uses Zend_ fails and points to the decision' => function () use ($run) {
        [$code, $out] = $run(function ($r, $put) {
            $put($r, 'siberian/app/sae/modules/New/Model/New.php', "<?php\nclass New_Model_New extends Zend_Db_Table {}\n");
        });
        t_equal(1, $code, "exit code\n$out");
        t_true(strpos($out, 'siberian/app/sae/modules/New/Model/New.php') !== false, 'names the file');
        t_true(strpos($out, 'docs/decisions/0001-remove-zf1.md') !== false, 'points to the decision');
    },

    'the first Zend_ in a clean file fails' => function () use ($run, $cleanPath) {
        [$code, $out] = $run(function ($r, $put) use ($cleanPath) {
            $put($r, $cleanPath, "<?php\nnamespace Siberian;\nclass Clean { public function d() { return new \\Zend_Date(); } }\n");
        });
        t_equal(1, $code, "exit code\n$out");
        t_true(strpos($out, $cleanPath) !== false, 'names the file');
    },

    'a legacy file may not get one more Zend_' => function () use ($run, $legacyPath, $legacy) {
        [$code, $out] = $run(function ($r, $put) use ($legacyPath, $legacy) {
            $put($r, $legacyPath, str_replace('return 1;', 'return new Zend_Locale();', $legacy));
        });
        t_equal(1, $code, "exit code\n$out");
        t_true(strpos($out, '1 new Zend_ reference') !== false, 'counts the new reference');
    },

    'a legacy file can change without adding Zend_' => function () use ($run, $legacyPath, $legacy) {
        [$code, $out] = $run(function ($r, $put) use ($legacyPath, $legacy) {
            $put($r, $legacyPath, str_replace('return 1;', 'return 2;', $legacy));
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'a legacy file can lose Zend_' => function () use ($run, $legacyPath, $legacy) {
        [$code, $out] = $run(function ($r, $put) use ($legacyPath, $legacy) {
            $put($r, $legacyPath, str_replace('new Zend_Date()', 'new \\DateTimeImmutable()', $legacy));
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'a legacy file with no Zend_ left is reported, not failed' => function () use ($run, $legacyPath) {
        [$code, $out] = $run(function ($r, $put) use ($legacyPath) {
            $put($r, $legacyPath, "<?php\nclass Old_Model_Old {}\n");
        });
        t_equal(0, $code, "exit code\n$out");
        t_true(strpos($out, 'WARN  ' . $legacyPath . ' has no Zend_ left') !== false, 'asks to remove it from the list');
    },

    'a line marked ZF1-OK is not counted' => function () use ($run) {
        [$code, $out] = $run(function ($r, $put) {
            $put($r, 'siberian/lib/Siberian/Db/Facade.php', "<?php\nnamespace Siberian\\Db;\nclass Facade { /* ZF1-OK: facade over the old adapter */ private \$a = 'Zend_Db_Adapter_Pdo_Mysql'; }\n");
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'a renamed legacy file keeps its place on the list' => function () use ($run, $sh, $legacyPath) {
        [$code, $out] = $run(function ($r, $put) use ($sh, $legacyPath) {
            $sh($r, 'mkdir -p siberian/app/sae/modules/Old/Moved && git mv ' . $legacyPath . ' siberian/app/sae/modules/Old/Moved/Old.php');
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'the bundled framework, vendor code and tests are not checked' => function () use ($run) {
        [$code, $out] = $run(function ($r, $put) {
            $put($r, 'siberian/lib/Zend/Date.php', "<?php\nclass Zend_Date { public function more() { return new Zend_Locale(); } }\n");
            $put($r, 'siberian/lib/vendor/some/Lib.php', "<?php\nclass Lib extends Zend_Db_Table {}\n");
            $put($r, 'tests/php-upgrade/regression/SomeTest.php', "<?php\nreturn ['x' => function () { new Zend_Date(); }];\n");
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'the real allowlist is sound and the script can remake it' => function () use ($realScript) {
        $list = file(dirname($realScript) . '/zf1-allowlist.txt', FILE_IGNORE_NEW_LINES);
        t_true(count($list) > 100, 'the allowlist is not empty');
        t_equal(count($list), count(array_unique($list)), 'no duplicate in the allowlist');
        t_equal($list, array_values(array_filter($list, function ($f) { return strpos($f, 'siberian/lib/Zend/') !== 0; })), 'the bundled framework is not listed');
        t_true(strpos(file_get_contents($realScript), '--zf1-list') !== false, 'the script can remake the list');
    },
];
