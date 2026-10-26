<?php
/**
 * bin/build-app-sources.sh builds the archives "Restore Apps sources" downloads. It is run here on a tiny repository
 * with the same folders: right names and roots, no .git / .gitignore / .gitattributes / .DS_Store, same bytes twice.
 */
$script = dirname(__DIR__, 3) . '/bin/build-app-sources.sh';

$run = function (string $command, &$output = null): int {
    exec($command . ' 2>&1', $output, $code);
    return $code;
};

/** @return string a throw-away repository tagged v9.8.7 with the four source folders */
$fixture = function () use ($run): string {
    $dir = sys_get_temp_dir() . '/app-sources-' . bin2hex(random_bytes(4));
    $folders = [
        'browser' => 'siberian/var/apps/browser',
        'android' => 'siberian/var/apps/ionic/android',
        'ios' => 'siberian/var/apps/ionic/ios',
        'ios-noads' => 'siberian/var/apps/ionic/ios-noads',
    ];
    foreach ($folders as $name => $path) {
        mkdir("$dir/$path/www", 0777, true);
        file_put_contents("$dir/$path/www/index.html", "<html>$name</html>");
        file_put_contents("$dir/$path/.gitignore", "*.log\n");
        file_put_contents("$dir/$path/www/.gitattributes", "* text\n");
        file_put_contents("$dir/$path/run.sh", "#!/bin/sh\n");
        chmod("$dir/$path/run.sh", 0755);
    }
    $git = 'git -C ' . escapeshellarg($dir) . ' -c user.name=t -c user.email=t@example.com';
    $steps = ['init -q', 'add -A', 'commit -q -m fixture', 'tag v9.8.7'];
    foreach ($steps as $step) {
        if ($run("$git $step", $out) !== 0) {
            throw new RuntimeException("fixture '$step' failed: " . implode("\n", $out));
        }
    }
    return $dir;
};

$rm = function (string $dir) {
    exec('rm -rf ' . escapeshellarg($dir));
};

return [
    'app sources the four archives are built with the right roots and without git files' => function () use ($script, $run, $fixture, $rm) {
        $repo = $fixture();
        $out = "$repo-out";
        try {
            t_equal(0, $run('cd ' . escapeshellarg($repo) . ' && sh ' . escapeshellarg($script) . ' 9.8.7 --out ' . escapeshellarg($out), $log), 'build: ' . implode("\n", $log));
            foreach (['browser', 'android', 'ios', 'ios-noads'] as $name) {
                t_true(is_file("$out/$name.tgz"), "$name.tgz exists");
                $run('tar -tzf ' . escapeshellarg("$out/$name.tgz"), $files);
                t_true(in_array("$name/www/index.html", $files, true), "$name/ is the root of $name.tgz");
                t_true(in_array("$name/run.sh", $files, true), "$name keeps its files");
                foreach ($files as $file) {
                    t_true(!preg_match('#(^|/)(\.git|\.gitignore|\.gitattributes|\.DS_Store)$#', $file), "$file is excluded");
                }
            }
            t_true(is_file("$out/SHA256SUMS"), 'checksums');
            // the restore extracts with tar p: the executable bit has to survive
            $run('tar -tvzf ' . escapeshellarg("$out/android.tgz") . ' android/run.sh', $listing);
            t_true(strpos($listing[0] ?? '', '-rwxr-xr-x') === 0, 'executable bit kept');
        } finally {
            $rm($repo);
            $rm($out);
        }
    },

    'app sources building twice gives the same bytes' => function () use ($script, $run, $fixture, $rm) {
        $repo = $fixture();
        try {
            foreach (['a', 'b'] as $suffix) {
                t_equal(0, $run('cd ' . escapeshellarg($repo) . ' && sh ' . escapeshellarg($script) . ' 9.8.7 --out ' . escapeshellarg("$repo-$suffix")), "build $suffix");
            }
            t_equal(file_get_contents("$repo-a/SHA256SUMS"), file_get_contents("$repo-b/SHA256SUMS"), 'same checksums');
        } finally {
            $rm($repo);
            $rm("$repo-a");
            $rm("$repo-b");
        }
    },

    'app sources a bad version, an unknown ref and a missing folder stop the build' => function () use ($script, $run, $fixture, $rm) {
        $repo = $fixture();
        $in = 'cd ' . escapeshellarg($repo) . ' && sh ' . escapeshellarg($script);
        try {
            t_true($run("$in 9.8.x --dry-run") !== 0, 'bad version');
            t_true($run("$in 1.0.0 --dry-run") !== 0, 'no tag v1.0.0');
            t_equal(0, $run("$in 9.8.7 --dry-run", $log), 'dry run on a good ref');
            t_equal(4, count($log), 'one line per archive');
            exec('git -C ' . escapeshellarg($repo) . ' rm -rq siberian/var/apps/ionic/ios && git -C ' . escapeshellarg($repo) . ' -c user.name=t -c user.email=t@example.com commit -qm drop && git -C ' . escapeshellarg($repo) . ' tag v9.8.8');
            t_true($run("$in 9.8.8 --dry-run", $log) !== 0, 'a missing folder is an error, not a partial build');
        } finally {
            $rm($repo);
        }
    },
];
