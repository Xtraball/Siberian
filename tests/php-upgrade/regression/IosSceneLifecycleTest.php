<?php
/**
 * iOS 27: apps built with the iOS 27 SDK must adopt the UIScene life cycle or they stop at launch in UIApplicationMain
 * (EXC_BREAKPOINT, ___UIApplicationEvaluateRuntimeIssueForNoSceneLifecycleAdoption, issue #995). The iOS shells now
 * declare a scene manifest and create the window in a SceneDelegate. Objective-C cannot run here, so the three copies of
 * the shell sources are checked.
 */
$root = dirname(__DIR__, 3);
$trees = [
    'siberian/var/apps/ionic/ios',
    'siberian/var/apps/ionic/ios-noads',
    'ionic/platforms/ios',
];
$read = function ($path) use ($root) {
    return file_get_contents("$root/$path");
};

return [
    'the Info.plist declares a single-window scene manifest with the SceneDelegate' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            $plist = $read("$tree/AppsMobileCompany/AppsMobileCompany-Info.plist");
            t_equal(1, substr_count($plist, '<key>UIApplicationSceneManifest</key>'), "$tree: one scene manifest");
            t_equal(1, preg_match('#<key>UIApplicationSupportsMultipleScenes</key>\s*<false/>#', $plist), "$tree: single window");
            t_equal(1, preg_match('#<key>UIWindowSceneSessionRoleApplication</key>\s*<array>\s*<dict>\s*<key>UISceneConfigurationName</key>\s*<string>Default Configuration</string>\s*<key>UISceneDelegateClassName</key>\s*<string>SceneDelegate</string>#', $plist), "$tree: scene configuration names SceneDelegate");
            // The plist must still parse as a plist.
            $xml = simplexml_load_string($plist);
            t_true($xml !== false, "$tree: well-formed XML");
        }
    },

    'the window is created by the SceneDelegate and handed to the AppDelegate' => function () use ($trees, $read) {
        foreach ($trees as $tree) {
            $m = $read("$tree/AppsMobileCompany/Classes/AppDelegate.m");
            $code = preg_replace('#//[^\n]*#', '', $m);
            t_equal(0, preg_match('/\[super application:application didFinishLaunchingWithOptions/', $code), "$tree: CDVAppDelegate window set up is not called");
            t_true(strpos($m, 'self.viewController = [[MainViewController alloc] init];') !== false, "$tree: view controller created at launch");
            t_true(strpos($m, '@implementation SceneDelegate') !== false, "$tree: SceneDelegate implemented");
            t_true(strpos($m, 'initWithWindowScene:(UIWindowScene*)scene') !== false, "$tree: window created on the scene");
            t_true(strpos($m, 'appDelegate.window = self.window;') !== false, "$tree: window handed to the app delegate");
            t_true(strpos($m, '[self.window makeKeyAndVisible];') !== false, "$tree: window shown");
            t_true(strpos($m, 'scene:(UIScene*)scene openURLContexts:') !== false, "$tree: URLs opened while running are forwarded");
            t_true(strpos($m, 'forwardURLContexts:connectionOptions.URLContexts') !== false, "$tree: the launch URL is forwarded");
            $h = $read("$tree/AppsMobileCompany/Classes/AppDelegate.h");
            t_true(strpos($h, '@interface SceneDelegate : UIResponder <UIWindowSceneDelegate>') !== false, "$tree: SceneDelegate declared");
        }
    },

    'the three shells carry identical sources' => function () use ($trees, $read) {
        foreach (['AppDelegate.h', 'AppDelegate.m'] as $file) {
            $first = $read("{$trees[0]}/AppsMobileCompany/Classes/$file");
            foreach (array_slice($trees, 1) as $tree) {
                t_equal(md5($first), md5($read("$tree/AppsMobileCompany/Classes/$file")), "$tree $file same as {$trees[0]}");
            }
        }
    },
];
