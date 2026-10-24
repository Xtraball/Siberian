<?php

// Builds the downloader fixtures inside the dl container: three test modules, registry, signing key, manifest.
//   demoupd  1.1.0  the update we install (Siberian has 1.0.0)
//   newmod   2.0.0  not installed in Siberian, must not be offered
//   toonew   1.0.0  needs Siberian 9.0.0, offered but incompatible
chdir('/app');
$fixture = '/app/var/fixture';
@mkdir($fixture . '/dist', 0775, true);
@mkdir($fixture . '/src', 0775, true);

$modules = [
    ['label' => 'DemoUpd', 'key' => 'demoupd', 'version' => '1.1.0', 'item' => 900001, 'min' => '5.0.0'],
    ['label' => 'NewMod', 'key' => 'newmod', 'version' => '2.0.0', 'item' => 900002, 'min' => '5.0.0'],
    ['label' => 'TooNew', 'key' => 'toonew', 'version' => '1.0.0', 'item' => 900003, 'min' => '9.0.0'],
];
$registry = [];
foreach ($modules as $m) {
    $dir = $fixture . '/src/' . $m['key'];
    exec('rm -rf ' . escapeshellarg($dir));
    mkdir($dir . '/resources', 0775, true);
    file_put_contents($dir . '/package.json', json_encode([
        'name' => $m['label'],
        'description' => 'E2E test module',
        'type' => 'module',
        'version' => $m['version'],
        'dependencies' => ['system' => ['type' => 'SAE', 'version' => $m['min']]],
    ], JSON_PRETTY_PRINT));
    file_put_contents($dir . '/init.php', "<?php\n// e2e module\n");
    file_put_contents($dir . '/resources/hello.php', "<?php\nreturn 'hello from " . $m['label'] . ' ' . $m['version'] . "';\n");
    $zip = $fixture . '/dist/' . $m['key'] . '-' . $m['version'] . '.zip';
    @unlink($zip);
    exec('cd ' . escapeshellarg($dir) . ' && zip -q -r ' . escapeshellarg($zip) . ' ./');
    $registry[] = [
        'label' => $m['label'], 'download_key' => $m['key'], 'version' => $m['version'],
        'item_id' => $m['item'], 'archive' => '/../dist-archives/' . $m['key'] . '-' . $m['version'] . '.zip',
    ];
}
file_put_contents($fixture . '/modules.json', json_encode($registry, JSON_PRETTY_PRINT));
echo count($registry) . " fixture modules\n";
