<?php
/**
 * Translation editor: the "suggest to the community" feature (a Weblate service that is gone, with an API token in the
 * source) is removed, the machine translation is kept and reads the answers of its services defensively.
 */
$module = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Translation';
$design = $module . '/resources/design/desktop/backoffice';

require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/TranslateResponse.php';

/**
 * @param string $dir
 * @return string[] every file of the module that is code or a template
 */
$files = function ($dir) {
    $found = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (preg_match('/\.(php|phtml|js|xml)$/', $file->getFilename()) === 1) {
            $found[] = $file->getPathname();
        }
    }
    return $found;
};

return [
    'nothing in the module talks about suggestions, Weblate or the community any more' => function () use ($module, $files) {
        foreach ($files($module) as $file) {
            $source = file_get_contents($file);
            foreach (['suggest', 'SUGGEST', 'weblate', 'translate.siberiancms.com', 'Authorization: Token', 'community'] as $word) {
                t_true(strpos($source, $word) === false, $word . ' in ' . substr($file, strlen($module)));
            }
        }
    },

    'the editor has no suggest button, column or action' => function () use ($module, $design) {
        $html = file_get_contents($design . '/template/translation/edit.phtml');
        t_equal(5, substr_count($html, '<col width='), 'five columns left');
        $table = substr($html, strpos($html, 'id="file_datas"'));
        $table = substr($table, 0, strpos($table, '</thead>'));
        t_equal(5, preg_match_all('/<th[\s>]/', $table), 'five column titles in the translations table');
        t_true(strpos(file_get_contents($module . '/controllers/Backoffice/EditController.php'), 'suggestAction') === false, 'no action');
        t_true(strpos(file_get_contents($design . '/js/factories/translation.js'), 'backoffice_edit/suggest') === false, 'no factory call');
    },

    'the machine translation is kept' => function () use ($module, $design) {
        $php = file_get_contents($module . '/controllers/Backoffice/EditController.php');
        t_true(strpos($php, 'public function translateAction()') !== false, 'translate action');
        t_true(strpos($php, 'translate.googleapis.com/translate_a/single') !== false, 'google endpoint');
        t_true(strpos($php, '!empty($yandexKey)') !== false, 'yandex only when a key is set: no key is no longer a dead end');
        $js = file_get_contents($design . '/js/controllers/translation.js');
        t_equal(2, substr_count($js, 'response.data.error'), 'both buttons show the error of the service');
        t_true(strpos($js, 'if (size > 0) {' . "\n" . '            size = translateQueue.size();') === false, 'translate all reads its size before testing it');
    },

    'google: one entry per sentence, all of them are joined' => function () {
        $body = '[[["Bonjour. ","Hello. ",null,null,10],["Le monde","The world",null,null,10]],null,"en"]';
        t_equal('Bonjour. Le monde', \Siberian\TranslateResponse::fromGoogle($body), 'two sentences');
        t_equal('Bonjour le monde', \Siberian\TranslateResponse::fromGoogle('[[["Bonjour le monde","Hello world",null,null,10]],null,"en"]'), 'one');
    },

    'google: a blocked address, an empty or an odd answer is not a translation' => function () {
        foreach (['<html><head><title>Sorry...</title></head></html>', '', null, '[]', '[[]]', '{"a":1}', '[[[null]]]', 'null'] as $body) {
            t_equal(null, \Siberian\TranslateResponse::fromGoogle($body), var_export($body, true));
        }
    },

    'yandex: only a 200 with a text is a translation' => function () {
        t_equal('Bonjour', \Siberian\TranslateResponse::fromYandex('{"code":200,"lang":"en-fr","text":["Bonjour"]}'), 'ok');
        foreach (['{"code":401,"message":"API key is invalid"}', '{"code":200,"text":[]}', '{"code":200,"text":[""]}', '', null, '<html></html>', '[]'] as $body) {
            t_equal(null, \Siberian\TranslateResponse::fromYandex($body), var_export($body, true));
        }
    },
];
