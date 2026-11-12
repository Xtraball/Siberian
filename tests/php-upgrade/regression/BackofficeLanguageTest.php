<?php
/**
 * Backoffice: the language of the session can be chosen from the navbar, like in the editor.
 *
 * The real validCode() is loaded out of the controller source (the controller needs the whole framework).
 */
$root = dirname(__DIR__, 3) . '/siberian/';

$probe = function () use ($root) {
    if (!class_exists('BackofficeLanguageProbe', false)) {
        $controller = file_get_contents($root . 'app/sae/modules/Backoffice/controllers/LanguageController.php');
        $start = strpos($controller, 'public static function validCode(');
        if ($start === false) {
            return null;
        }
        $open = strpos($controller, '{', $start);
        $depth = 0;
        for ($i = $open, $n = strlen($controller); $i < $n; $i++) {
            if ($controller[$i] === '{') {
                $depth++;
            } elseif ($controller[$i] === '}' && --$depth === 0) {
                break;
            }
        }
        eval('class BackofficeLanguageProbe { ' . substr($controller, $start, $i - $start + 1) . ' }');
    }
    return 'BackofficeLanguageProbe';
};

return [
    'only an enabled language can be set' => function () use ($probe) {
        $class = $probe();
        t_true($class !== null, 'the controller has a validCode() rule');
        $available = ['fr', 'pt_BR', 'zh_Hans', 'en'];
        t_equal('fr', $class::validCode('fr', $available), 'an available language is accepted');
        t_equal('zh_Hans', $class::validCode('zh_Hans', $available), 'a code with a script is accepted');
        t_equal(null, $class::validCode('de', $available), 'a language that is not enabled is refused');
        t_equal(null, $class::validCode('../fr', $available), 'a path is refused');
        t_equal(null, $class::validCode(['fr'], $available), 'an array is refused');
        t_equal(null, $class::validCode(null, $available), 'nothing is refused');
        t_equal(null, $class::validCode('', $available), 'an empty code is refused');
    },

    'the menu is in the header on the left of the user menu and wired to the action' => function () use ($root) {
        $header = file_get_contents($root . 'app/sae/design/desktop/backoffice/template/page/layout/header.phtml');
        t_true(strpos($header, 'setLanguage(') !== false, 'the header menu calls setLanguage()');
        t_true(strpos($header, 'id="backoffice_language"') > strpos($header, 'id="backoffice_user"'),
            'it follows the user menu in the markup: it floats right, so it shows on its left');
        t_true(strpos($header, 'count($backofficeLanguages) > 1') !== false, 'it shows only when there is a choice');

        $app = file_get_contents($root . 'app/sae/design/desktop/backoffice/js/app.js');
        t_true(strpos($app, 'backoffice/language/set') !== false, 'setLanguage() posts to backoffice/language/set');
    },

    'the new texts are in the catalog' => function () use ($root) {
        $catalog = file_get_contents($root . 'languages/base/c_backoffice.po');
        foreach (['Language', 'This language is not available.'] as $text) {
            t_true(strpos($catalog, "msgctxt \"backoffice\"\nmsgid \"$text\"\n") !== false, "c_backoffice.po has: $text");
        }
    },
];
