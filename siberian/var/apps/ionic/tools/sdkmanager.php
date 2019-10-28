<?php

/**
 * @param $command
 * @return mixed
 */
function lexec ($command) {
    exec($command, $result);

    return $result;
}

$linux = 'https://dl.google.com/android/repository/sdk-tools-linux-3859397.zip';
$darwin = 'https://dl.google.com/android/repository/sdk-tools-darwin-3859397.zip';

$result = lexec("uname -s");
switch (strtolower($result[0])) {
    case 'darwin':
        $file = $darwin;
        break;
    case 'linux':
    default:
        $file = $linux;
        break;
}

// Disabling Android-SDK check on APK Build!
$run = true;
if (is_file(__DIR__ . '/../../../../config.php')) {
    require __DIR__ . '/../../../../config.php';

    if (isset($config) && array_key_exists('disabled', $config)) {
        echo 'Android SDK Updater is disabled in `config.php` !' . PHP_EOL;
        $run = false;
    }
}

if ($run) {
    $toolsPath = dirname(__FILE__);
    chmod($toolsPath, 0777);
    $androidSdkPath = $toolsPath . '/android-sdk';
    if (!@file_exists($androidSdkPath)) {
        mkdir($androidSdkPath, 0777, true);
    }

    // Ensure we have the latest tools!
    if (!is_dir($androidSdkPath . '/tools') ||
        !is_file($androidSdkPath . '/tools/bin/sdkmanager')) {
        lexec("rm -Rf '" . $androidSdkPath . "'");
        if (!@file_exists($androidSdkPath)) {
            mkdir($androidSdkPath, 0777, true);
        }
        lexec("wget '" . $file . "' -O " .
            $androidSdkPath . "/tools.zip");
        chdir($androidSdkPath);
        lexec("unzip tools.zip");
    }

    if (!is_dir($androidSdkPath . '/licenses')) {
        lexec("mkdir -p '" . $androidSdkPath . "/licenses'");
    }

    // Manual licenses
    $licenses = [
        "android-googletv-license" => "\n601085b94cd77f0b54ff86406957099ebe79c4d6",
        "android-sdk-license" => "\nd56f5187479451eabf01fb78af6dfcb131a6481e\n24333f8a63b6825ea9c5514f83c2829b004d1fee",
        "android-sdk-preview-license" => "\n84831b9409646a918e30573bab4c9c91346d8abd",
        "google-gdk-license" => "\n33b6a2b64607f11b759f320ef9dff4ae5c47d97a",
        "mips-android-sysimage-license" => "\ne9acab5b5fbb560a72cfaecce8946896ff6aab9d",
    ];
    foreach ($licenses as $filename => $license) {
        $_path = "{$androidSdkPath}/licenses/{$filename}";
        if (!is_file($_path)) {
            file_put_contents($_path, $license);
        }
    }

    file_put_contents($androidSdkPath . "/y.txt",
        implode("\n", array_fill(0, 100, 'y')));
    lexec($androidSdkPath . '/tools/bin/sdkmanager ' .
        '"build-tools;27.0.3" ' .
        '"build-tools;28.0.3" ' .
        '"platform-tools" ' .
        '"tools" ' .
        '"platforms;android-27" ' .
        '"platforms;android-28" ' .
        '"extras;android;m2repository" ' .
        '"extras;google;m2repository" ' .
        '"extras;google;google_play_services" ' .
        '"patcher;v4" < ' . $androidSdkPath . '/y.txt');

    // Clean-up!
    if (is_file($androidSdkPath . "/y.txt")) {
        unlink($androidSdkPath . "/y.txt");
    }
    if (is_file($androidSdkPath . "/tools.zip")) {
        unlink($androidSdkPath . "/tools.zip");
    }

    lexec("chmod -R 777 '" . $androidSdkPath . "'");
    echo 'android-sdk is up to date.' . PHP_EOL;
}

