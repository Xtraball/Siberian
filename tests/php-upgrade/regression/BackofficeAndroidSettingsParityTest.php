<?php
/**
 * The advanced Android settings of an app can be changed in the editor and in the back office. The back office page
 * has the same options as the editor.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Application';
$editor = file_get_contents($root . '/Form/Android.php');
$template = file_get_contents($root . '/resources/design/desktop/backoffice/template/application/view/source.phtml');
$controller = file_get_contents($root . '/Controller/View/Abstract.php');

// every option of the editor form: the checkbox names
preg_match_all('/addSimpleCheckbox\("([a-z_]+)"/', $editor, $m);
$options = $m[1];

return [
    'the editor form has the two options' => function () use ($options) {
        t_equal(['disable_battery_optimization', 'disable_location'], $options, 'editor options');
    },

    'the back office page has a checkbox for every option of the editor' => function () use ($options, $template) {
        foreach ($options as $name) {
            t_true(strpos($template, 'ng-model="tmp_application.' . $name . '"') !== false, "$name is in the back office page");
            t_true(strpos($template, 'id="' . $name . '"') !== false, "$name has its id");
        }
    },

    'the back office loads every option as a boolean' => function () use ($options, $controller) {
        foreach ($options as $name) {
            t_true(strpos($controller, "\$data['application']['$name'] = (bool)") !== false, "$name is cast on load");
        }
    },

    'the back office saves every option' => function () use ($options, $controller) {
        foreach ($options as $name) {
            t_true(strpos($controller, "array_key_exists('$name', \$data)") !== false, "$name is read on save");
            t_true(strpos($controller, "unset(\$data['$name'])") !== false, "$name is not saved as a raw column");
        }
    },

    'the editor lays a checkbox, its label and its help out in a row, whatever the label length' => function () {
        $phtml = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Application/resources/design/desktop/flat/template/application/customization/publication/sources.phtml');
        t_true(strpos($phtml, '#form-general-information-android .form-group-checkbox {') !== false, 'the row rule is there');
        t_true(strpos($phtml, 'flex-direction: row-reverse') !== false, 'the checkbox comes first');
    },
];
