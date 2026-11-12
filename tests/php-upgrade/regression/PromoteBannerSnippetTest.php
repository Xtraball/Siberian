<?php
/**
 * Smart banner snippet (Promote page): the application title, author and labels are typed by the owner and end up
 * inside a JavaScript string. A quote must not break the snippet, "</script>" must not close it, and a plain
 * banner must come out exactly as before.
 */
$module = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Application';

if (!class_exists('Application_Model_Promote_Banner', false)) {
    require_once $module . '/Model/Promote/Banner.php';
}

return [
    'banner a plain banner gives the snippet it always gave' => function () {
        $expected = <<<'JS'
<script src="https://example.com/external/smart-banner/smart-app-banner.js"></script>
<script type="text/javascript">
    new SmartBanner({
        daysHidden: 15,
        daysReminder: 90,
        title: "My App",
        author: "Me & Co",
        button: "View",
        store: {
            ios: "On the App Store",
            android: "On Google Play"
        },
        price: {
            ios: "FREE",
            android: "FREE"
        }
    });
</script>
JS;
        $script = Application_Model_Promote_Banner::script('https://example.com/external/smart-banner/smart-app-banner.js', [
            'title' => 'My App',
            'author' => 'Me & Co',
            'button' => 'View',
            'store' => ['ios' => 'On the App Store', 'android' => 'On Google Play'],
            'price' => ['ios' => 'FREE', 'android' => 'FREE'],
        ]);
        t_equal($expected, $script, 'snippet');
    },

    'banner a single platform label has no trailing comma' => function () {
        $script = Application_Model_Promote_Banner::script('u.js', [
            'title' => 'T', 'author' => 'A', 'button' => 'B',
            'store' => ['ios' => 'iOS only', 'android' => ''],
        ]);
        t_true(strpos($script, "store: {\n            ios: \"iOS only\"\n        }\n    });") !== false, 'no comma');
        t_true(strpos($script, 'price') === false, 'no price block');
    },

    'banner quotes, backslashes and new lines in a title stay inside the string' => function () {
        $title = "Joe's \"Best\" App\\ \n2";
        $script = Application_Model_Promote_Banner::script('u.js', ['title' => $title, 'author' => 'A', 'button' => 'B']);
        t_true(strpos($script, 'title: "Joe\'s \\"Best\\" App\\\\ \\n2",') !== false, 'escaped literal');
        // the literal is valid JSON for the value we typed
        preg_match('/title: (".*"),\n/', $script, $m);
        t_equal($title, json_decode($m[1]), 'round trip');
    },

    'banner a title cannot close the pasted script tag' => function () {
        $script = Application_Model_Promote_Banner::script('u.js', [
            'title' => '</script><script>alert(1)</script>', 'author' => 'A', 'button' => 'B',
        ]);
        t_equal(2, substr_count($script, '</script>'), 'only the two real closing tags');
        t_true(strpos($script, '</script>') !== false, 'tags escaped in the literal');
    },

    'banner the view builds the snippet through the helper' => function () use ($module) {
        $code = file_get_contents($module . '/View/Promote/Index.php');
        t_true(strpos($code, 'Application_Model_Promote_Banner::script(') !== false, 'helper used');
        t_true(strpos($code, "title: \"'.") === false, 'no value glued into the script');
    },
];
