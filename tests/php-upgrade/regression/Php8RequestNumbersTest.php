<?php
/**
 * Numbers that come from the request were used in arithmetic as they are. PHP 7 warned and used 0; PHP 8 throws a
 * TypeError for "" and "abc" ("abc" * 1, "abc" % 20, array_slice(..., "abc")) that "catch (Exception)" does not catch,
 * so the app got a 500 instead of its fallback. The controllers and models that read them are source-checked here (they
 * need the full application to load); the PHP behaviour itself is asserted with the real operators.
 */
function request_source(string $path): string
{
    return file_get_contents(dirname(__DIR__, 3) . '/siberian/' . $path);
}

return [
    'the PHP 8 behaviour this relies on' => function () {
        foreach (['', 'abc'] as $text) {
            try {
                $r = $text * 1;
                t_true(false, 'no error for ' . var_export($text, true));
            } catch (TypeError $e) {
                t_true(true, 'TypeError');
            }
            t_equal(0.0, (float) $text, 'the cast is safe');
            t_equal(0, (int) $text, 'the int cast is safe');
        }
    },

    'the image sizes of the app are cast before they are compared' => function () {
        $source = request_source('lib/Siberian/Image.php');

        t_true(strpos($source, '$device_width * 1') === false, 'no arithmetic on the raw device width');
        t_equal(2, substr_count($source, 'max((float) $device_width, (float) $device_height)'), 'both lookups');
    },

    'the crop action validates the requested size and the real size' => function () {
        $source = request_source('app/sae/modules/Front/controllers/ImageController.php');

        t_true(strpos($source, "\$expected_width = (int) \$datas['width'];") !== false, 'width cast');
        t_true(strpos($source, 'if ($expected_width < 1 OR $expected_height < 1)') !== false, 'positive sizes');
        t_true(strpos($source, 'if (empty($width) OR empty($height))') !== false, 'readable image');
    },

    'latitude and longitude must be numbers to sort by distance' => function () {
        $source = request_source('app/sae/modules/Places/controllers/Mobile/Api/PlacesController.php');

        t_true(strpos($source, 'if (is_numeric($latitude) && is_numeric($longitude) && $latitude && $longitude)') !== false, 'is_numeric guard');
    },

    'every offset of the app is an int before it is used' => function () {
        $expected = [
            'app/sae/modules/Media/controllers/Mobile/Gallery/ImageController.php' => "(int) \$datas['offset']",
            'app/sae/modules/Media/controllers/Mobile/Gallery/Image/ViewController.php' => "\\Siberian\\Input::int(\$request->getParam('offset'), 0, 0)",
            'app/sae/modules/Media/controllers/Mobile/Gallery/Video/ViewController.php' => "(int) \$this->getRequest()->getParam('offset', 1)",
            'app/sae/modules/Wordpress/controllers/Mobile/ListController.php' => "\\Siberian\\Input::int(\$this->getRequest()->getParam('offset'), 0, 0)",
            'app/sae/modules/Comment/controllers/Mobile/ListController.php' => "\\Siberian\\Input::int(\$this->getRequest()->getParam('offset'), 0, 0)",
            'app/sae/modules/Event/Model/Event.php' => '$offset = (int) $offset;',
            'app/sae/modules/Wordpress/Model/Wordpress.php' => '$offset = (int) $offset;',
            'app/sae/modules/Media/Model/Gallery/Video/Vimeo.php' => '$offset = (int) $offset;',
            'app/sae/modules/Social/Model/Facebook.php' => '$offset = (int) $offset;',
        ];
        foreach ($expected as $file => $needle) {
            t_true(strpos(request_source($file), $needle) !== false, "$file casts the offset");
        }
    },
];
