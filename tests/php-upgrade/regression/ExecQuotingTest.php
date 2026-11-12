<?php
/** Values that reach the shell through exec() are escaped with escapeshellarg(). */

function t_exec_src($path)
{
    return file_get_contents(dirname(__DIR__, 3) . '/siberian/' . $path);
}

return [
    'unzip of an uploaded module quotes both paths' => function () {
        $src = t_exec_src('app/sae/modules/Installer/Model/Installer/Module/Parser.php');
        t_true(strpos($src, "exec('unzip ' . escapeshellarg(") !== false);
    },
    'sqlite cli query is escaped' => function () {
        $src = t_exec_src('lib/Siberian/Wrapper/Sqlite.php');
        t_true(strpos($src, '\'"\' . $query') === false);
        t_true(strpos($src, 'escapeshellarg($query)') !== false);
    },
    'app image folder removal is escaped' => function () {
        $src = t_exec_src('app/sae/modules/Application/Model/Application/Abstract.php');
        t_true(strpos($src, "exec('rm -rf ' . escapeshellarg(") !== false);
    },
    'escapeshellarg keeps a hostile query one argument' => function () {
        $q = 'select "a"; $(touch /tmp/sib-pwn-' . getmypid() . ')';
        exec('printf %s ' . escapeshellarg($q), $out);
        t_equal($q, $out[0]);
        t_true(!is_file('/tmp/sib-pwn-' . getmypid()));
    },
];
