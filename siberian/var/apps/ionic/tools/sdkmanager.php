<?php

$linux = 'https://dl.google.com/android/repository/sdk-tools-linux-3859397.zip';
$darwin = 'https://dl.google.com/android/repository/sdk-tools-darwin-3859397.zip';

$download = exec("uname -s", $result);
switch (strtolower($result[0])) {
    case 'darwin':
        $file = $darwin;
        break;
    case 'linux':
    default:
        $file = $linux;
        break;
}

if (is_file(__DIR__ . '/../../../../config.php')) {
    require __DIR__ . '/../../../../config.php';

    if (isset($config) && array_key_exists('disabled', $config)) {
        die('Android SDK Updater is disabled in `config.php` !' . PHP_EOL);
    }
}

/**
 * Alias for exec or passthru (for debug purposes)
 *
 * @param $command
 */
function lexec ($command) {
    exec($command);
}

$toolsPath = dirname(__FILE__);
chmod($toolsPath, 0777);
$androidSdkPath = $toolsPath . '/android-sdk';
if (!@file_exists($androidSdkPath)) {
    mkdir($androidSdkPath, 0777, true);
}

/** Clean-up old SDK only if new platform doesn't fit! */
if (!is_dir($androidSdkPath . '/platforms/android-27')) {
    lexec("rm -Rf ./android-sdk");
    if (!@file_exists($androidSdkPath)) {
        mkdir($androidSdkPath, 0777, true);
    }
    lexec("wget '" . $file . "' -O " .
        $androidSdkPath . "/tools.zip");
    chdir($androidSdkPath);
    lexec("unzip /tools.zip");
}

lexec("mkdir -p " . $androidSdkPath . "/licenses");
file_put_contents($androidSdkPath . "/licenses/android-sdk-license",
    "\nd56f5187479451eabf01fb78af6dfcb131a6481e");
file_put_contents($androidSdkPath . "/y.txt",
    implode("\n", array_fill(0, 100, 'y')));
lexec($androidSdkPath . '/tools/bin/sdkmanager ' .
    '"build-tools;27.0.3" ' .
    '"platform-tools" ' .
    '"platforms;android-27" ' .
    '"extras;android;m2repository" ' .
    '"extras;google;m2repository" ' .
    '"extras;google;google_play_services" ' .
    '"patcher;v4" < ' . $androidSdkPath . '/y.txt');

/** Clean-up! */
if (is_file($androidSdkPath . "/y.txt")) {
    unlink($androidSdkPath . "/y.txt");
}
if (is_file($androidSdkPath . "/tools.zip")) {
    unlink($androidSdkPath . "/tools.zip");
}

lexec('chmod -R 777 ' . $androidSdkPath);
exit('android-sdk is up to date.');