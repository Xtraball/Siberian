<?php
/**
 * DEBUG ONLY, iOS only: the back office can generate an iOS source without OneSignal, to test on real hardware.
 * It is derived from the ios-noads template at generation time by bin/strip-onesignal-ios.sh, it is a queue type of
 * its own ("iosdebug"), and it exists only on a server that carries one very specific environment variable.
 */
$root = dirname(__DIR__, 3);
$module = "$root/siberian/app/sae/modules/Application";
$script = "$module/Model/Device/Ionic/bin/strip-onesignal-ios.sh";
$template = "$root/siberian/var/apps/ionic/ios-noads";
$read = function ($path) {
    return file_get_contents($path);
};

/**
 * A copy of the ios-noads template without the (300 MB) Pods folder, which the script removes anyway.
 */
$copyTemplate = function () use ($template) {
    $dir = sys_get_temp_dir() . '/strip-onesignal-' . getmypid() . '-' . mt_rand();
    mkdir($dir, 0777, true);
    exec('tar -C ' . escapeshellarg($template) . ' --exclude=./Pods -cf - . | tar -C ' . escapeshellarg($dir) . ' -xf -', $out, $code);
    t_equal(0, $code, 'the template is copied');
    return $dir;
};

$rrmdir = function ($dir) {
    if (strpos($dir, sys_get_temp_dir()) === 0) {
        exec('rm -rf ' . escapeshellarg($dir));
    }
};

return [
    'the script removes OneSignal from a copy of the ios-noads shell and can run twice' => function () use ($script, $copyTemplate, $rrmdir, $read) {
        $dir = $copyTemplate();
        // A generated app carries the Push2 feature, whose JavaScript names OneSignal (it skips push when the plugin is missing).
        mkdir("$dir/www/features/push2", 0777, true);
        file_put_contents("$dir/www/features/push2/push2.js", "if (!\$window.plugins.OneSignal) { return; }\n");
        foreach ([1, 2] as $run) {
            $out = [];
            exec('bash ' . escapeshellarg($script) . ' ' . escapeshellarg($dir) . ' 2>&1', $out, $code);
            t_equal(0, $code, "run $run succeeds: " . implode("\n", $out));
        }

        t_true(!file_exists("$dir/AppsMobileCompany/Plugins/Onesignal"), 'native plugin removed');
        t_true(!file_exists("$dir/www/plugins/Onesignal"), 'JavaScript plugin removed');
        t_true(!file_exists("$dir/Pods") && !file_exists("$dir/Podfile.lock"), 'pod binaries removed');
        t_true(file_exists("$dir/DEBUG-NO-ONESIGNAL.txt"), 'marker file written');

        // Nothing in the shell names OneSignal any more (the packed web bundles keep their guarded code).
        $left = [];
        exec('grep -rIil onesignal ' . escapeshellarg($dir) . ' --exclude-dir=dist --exclude-dir=features --exclude=DEBUG-NO-ONESIGNAL.txt', $left);
        t_equal([], $left, 'no file still names OneSignal');

        // What must stay.
        $pbx = $read("$dir/AppsMobileCompany.xcodeproj/project.pbxproj");
        t_true(strpos($pbx, 'CDVNavigator.m') !== false, 'other plugins stay in the project');
        t_true(strpos($pbx, 'CorePlugin.swift') !== false, 'CorePlugin stays in the project');
        $config = $read("$dir/AppsMobileCompany/config.xml");
        t_true(strpos($config, '<feature name="CorePlugin">') !== false, 'other features stay in config.xml');
        t_true(simplexml_load_string($config) !== false, 'config.xml is still well-formed');
        t_true(json_decode($read("$dir/ios.json"), true) !== null, 'ios.json is still valid JSON');
        t_true(json_decode($read("$dir/pods.json"), true) !== null, 'pods.json is still valid JSON');
        $plist = $read("$dir/AppsMobileCompany/AppsMobileCompany-Info.plist");
        t_equal(1, substr_count($plist, '[DEBUG]'), 'the display name says DEBUG once');
        t_true(simplexml_load_string($plist) !== false, 'Info.plist is still well-formed');
        $plugins = $read("$dir/www/cordova_plugins.js");
        t_true(strpos($plugins, 'plugins/CorePlugin') !== false || strpos($plugins, 'CorePlugin') !== false, 'other JavaScript plugins stay registered');

        $rrmdir($dir);
    },

    'the script refuses a folder that is not an iOS shell' => function () use ($script) {
        $out = [];
        exec('bash ' . escapeshellarg($script) . ' /tmp 2>&1', $out, $code);
        t_true($code !== 0, 'refuses /tmp');
    },

    'the queue type exists, is an iOS one and is refused without the flag' => function () use ($module, $read) {
        $queue = $read("$module/Model/SourceQueue.php");
        t_true(strpos($queue, "'iosdebug' => 1,") !== false, 'iosdebug is built as device 1 (iOS)');
        t_true(strpos($queue, "const DEBUG_NO_ONESIGNAL_VALUE = 'yes-this-build-is-not-for-the-store';") !== false, 'the flag has a very specific value');
        t_true(strpos($queue, 'getenv(self::DEBUG_NO_ONESIGNAL_ENV) === self::DEBUG_NO_ONESIGNAL_VALUE') !== false, 'only the environment can enable it');
        t_true(strpos($queue, '!self::isDebugNoOneSignalEnabled()') !== false, 'the generator refuses it without the flag');
        t_true(strpos($read("$module/Model/Queue.php"), "'iosdebug',") !== false, 'its queue position is reported');

        $ios = $read("$module/Model/Device/Ionic/Ios.php");
        t_true(strpos($ios, '-DEBUG-NO-ONESIGNAL') !== false, 'the archive is named DEBUG-NO-ONESIGNAL');
        t_true(strpos($ios, "'/ios-noads-debug'") !== false, 'it is built in a folder of its own');
        t_true(strpos($ios, 'if (!Application_Model_SourceQueue::isDebugNoOneSignalEnabled())') !== false, 'the strip step re-checks the flag');

        $controller = $read("$module/Controller/View/Abstract.php");
        t_true(strpos($controller, "getParam('no_ads') == 2 && \$device === 'ios'") !== false, 'the request is iOS only');
        t_true(strpos($controller, 'isDebugNoOneSignalEnabled()') !== false, 'the controller refuses it without the flag');
    },

    'the back office shows the row only when the flag is on, and the editor never shows it' => function () use ($module, $read) {
        $source = $read("$module/resources/design/desktop/backoffice/template/application/view/source.phtml");
        t_true(strpos($source, 'Application_Model_SourceQueue::isDebugNoOneSignalEnabled()') !== false, 'the row is behind the flag');
        t_true(strpos($source, 'generateSource(1, 2)') !== false, 'the button asks for the debug build');
        $editor = $read("$module/resources/design/desktop/flat/template/application/customization/publication/sources.phtml");
        t_true(stripos($editor, 'iosdebug') === false && stripos($editor, 'OneSignal') === false, 'the editor has no such build');
    },

    'the deliberate lack of parity is documented' => function () use ($root, $read) {
        $doc = $read("$root/docs/mobile-parity.md");
        t_true(strpos($doc, 'iOS debug build without OneSignal') !== false, 'listed in "Different on purpose"');
    },
];
