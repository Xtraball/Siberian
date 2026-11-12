<?php
/**
 * #933: four fatal errors of the core on PHP 8.5.
 *  1. uploadiconAction answered with the bare constant `a` instead of $CanBeColorized
 *  2. an empty Source code feature: DOMDocument::loadHTML('') throws a ValueError since PHP 8
 *  3. Core_Model_Url::current() cast every request param to a string, an ArrayObject cannot be
 *  4. Backoffice translations: a language with the same file as .csv and .po merged null with an array
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

return [
    '933 uploadicon answers with the colorizable flag, not a bare constant' => function () use ($siberian) {
        $file = $siberian . '/app/sae/modules/Application/controllers/Customization/FeaturesController.php';
        $code = file_get_contents($file);
        t_true(preg_match("/'colorizable'\s*=>\s*\\\$CanBeColorized\s*,/", $code) === 1, 'colorizable uses $CanBeColorized');
        t_true(preg_match("/=>\s*a\s*,/", $code) === 0, 'no bare constant a');
    },

    '933 loading an empty html source does not throw' => function () use ($siberian) {
        require_once $siberian . '/lib/Dom/SmartDOMDocument.php';
        $dom = new Dom_SmartDOMDocument();
        foreach (['', '   ', null] as $empty) {
            $dom->loadHTML($empty);
            t_true(is_string($dom->saveHTMLExact()), 'an empty source gives a document');
        }
        $dom->loadHTML('<p>Hello</p>');
        t_equal('<p>Hello</p>', trim($dom->saveHTMLExact()), 'a real source is still loaded');
    },

    '933 Url::current drops array and object params instead of casting them' => function () use ($siberian) {
        $code = file_get_contents($siberian . '/app/sae/modules/Core/Model/Url.php');
        t_true(strpos($code, 'is_scalar($param) || $param === null') !== false, 'only scalars are sanitized');
        t_true(strpos($code, 'unset($params[$paramKey])') !== false, 'the others are dropped');
        t_true(preg_match('/foreach\s*\(\s*\$params\s+as\s+&\$param\s*\)/', $code) === 0, 'no by-reference loop casting every param');
    },

    '933 backoffice translations merge a csv entry with its po entry' => function () use ($siberian) {
        require_once $siberian . '/app/sae/modules/Core/Model/Translator.php';

        $dir = sys_get_temp_dir() . '/t933_' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/c_front.csv', "\"Hello\";\"Ahoj\"\n");
        file_put_contents($dir . '/c_front.po', "msgid \"\"\nmsgstr \"\"\n\"Content-Type: text/plain; charset=UTF-8\\n\"\n\n#. note\n#, fuzzy\nmsgid \"Hello\"\nmsgstr \"Ahoj\"\n");

        try {
            $data = [];
            $data = Core_Model_Translator::parseTypeForBackoffice($data, 'c_front', $dir . '/c_front.csv', 'csv');
            $data = Core_Model_Translator::parseTypeForBackoffice($data, 'c_front', $dir . '/c_front.po', 'po');
        } finally {
            @unlink($dir . '/c_front.csv');
            @unlink($dir . '/c_front.po');
            @rmdir($dir);
        }

        t_true(isset($data['c_front']['Hello']), 'the entry exists');
        t_true(is_array($data['c_front']['Hello']['comments']), 'comments are an array');
        t_true(is_array($data['c_front']['Hello']['flags']), 'flags are an array');
        t_equal('Ahoj', $data['c_front']['Hello']['user'], 'the translation is kept');
    },
];
