<?php
/**
 * check-pr.sh fails a branch that changes a module's resources/db/schema or resources/db/data files without raising
 * the "version" of that module's package.json (AGENTS.md rule 9): the installer plays those files only on an
 * installation whose database has an older module version, so without the bump an updated installation never gets
 * them (5.1.14: application.android_edge_to_edge, Application stayed at 5.0.15).
 *
 * The real script runs against a throwaway git repository with a trunk that has one module, then one branch per case.
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

$module = 'siberian/app/sae/modules/Widget';
$package = function (string $version) {
    return "{\n  \"name\": \"Widget\",\n  \"description\": \"\",\n  \"version\": \"$version\",\n  \"dependencies\": {}\n}\n";
};
$schema = "<?php\n\$schemas = [];\n\$schemas['widget'] = [\n    'widget_id' => ['type' => 'int(11)'],\n];\n";
$data = "<?php\n\$x = 1;\n";

$repo = sys_get_temp_dir() . '/schemaguard-' . getmypid() . '-' . mt_rand();
mkdir($repo . '/tests/php-upgrade', 0777, true);
copy($realScript, $repo . '/tests/php-upgrade/check-pr.sh');
$put($repo, "$module/package.json", $package('1.0.0'));
$put($repo, "$module/resources/db/schema/widget.php", $schema);
$put($repo, "$module/resources/db/data/widget.php", $data);
$put($repo, "$module/Model/Widget.php", "<?php\nclass Widget_Model_Widget {}\n");
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
    'a new column without a version bump fails and names the module' => function () use ($run, $module, $schema) {
        [$code, $out] = $run(function ($r, $put) use ($module, $schema) {
            $put($r, "$module/resources/db/schema/widget.php", str_replace("];\n", "    'extra' => ['type' => 'tinyint(1)'],\n];\n", $schema));
        });
        t_equal(1, $code, "exit code\n$out");
        t_true(strpos($out, "FAIL  $module:") !== false, "names the module\n$out");
        t_true(strpos($out, 'AGENTS.md rule 9') !== false, 'points to the rule');
    },

    'a changed data file without a version bump fails' => function () use ($run, $module) {
        [$code, $out] = $run(function ($r, $put) use ($module) {
            $put($r, "$module/resources/db/data/widget.php", "<?php\n\$x = 2;\n");
        });
        t_equal(1, $code, "exit code\n$out");
        t_true(strpos($out, "FAIL  $module:") !== false, "names the module\n$out");
    },

    'the same change with a version bump passes' => function () use ($run, $module, $schema, $package) {
        [$code, $out] = $run(function ($r, $put) use ($module, $schema, $package) {
            $put($r, "$module/resources/db/schema/widget.php", str_replace("];\n", "    'extra' => ['type' => 'tinyint(1)'],\n];\n", $schema));
            $put($r, "$module/package.json", $package('1.0.1'));
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'a change outside schema and data needs no bump' => function () use ($run, $module) {
        [$code, $out] = $run(function ($r, $put) use ($module) {
            $put($r, "$module/Model/Widget.php", "<?php\nclass Widget_Model_Widget { public function a() { return 1; } }\n");
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'a new module with its package.json passes' => function () use ($run, $package) {
        [$code, $out] = $run(function ($r, $put) use ($package) {
            $put($r, 'siberian/app/sae/modules/Gadget/package.json', $package('1.0.0'));
            $put($r, 'siberian/app/sae/modules/Gadget/resources/db/schema/gadget.php', "<?php\n\$schemas = [];\n");
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'a deleted data file needs no bump' => function () use ($run, $module) {
        [$code, $out] = $run(function ($r, $put) use ($module) {
            unlink("$r/$module/resources/db/data/widget.php");
        });
        t_equal(0, $code, "exit code\n$out");
    },

    'a schema file in a module without a version fails' => function () use ($run, $module) {
        [$code, $out] = $run(function ($r, $put) use ($module) {
            $put($r, 'siberian/app/sae/modules/Gadget/package.json', "{\n  \"name\": \"Gadget\"\n}\n");
            $put($r, 'siberian/app/sae/modules/Gadget/resources/db/schema/gadget.php', "<?php\n\$schemas = [];\n");
        });
        t_equal(1, $code, "exit code\n$out");
        t_true(strpos($out, 'siberian/app/sae/modules/Gadget:') !== false, "names the module\n$out");
    },
];
