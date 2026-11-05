<?php
/**
 * The advanced Android settings of an app can be changed in the editor and in the back office. The back office page
 * had the battery and location options, not the one that removes the photos & videos permissions.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Application';
$editor = file_get_contents($root . '/Form/Android.php');
$template = file_get_contents($root . '/resources/design/desktop/backoffice/template/application/view/source.phtml');
$controller = file_get_contents($root . '/Controller/View/Abstract.php');

// every option of the editor form: the checkbox names
preg_match_all('/addSimpleCheckbox\("([a-z_]+)"/', $editor, $m);
$options = $m[1];

return [
    'the editor form has the three options' => function () use ($options) {
        t_equal(['disable_battery_optimization', 'disable_location', 'disable_media_permissions'], $options, 'editor options');
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
        t_true(strpos($controller, 'setDisableMediaPermissions(') !== false, 'the media option is stored');
    },

    'the editor and the back office use the same texts' => function () use ($template) {
        foreach ([
            'Remove the photos & videos permissions (READ_MEDIA_IMAGES, READ_MEDIA_VIDEO).',
            'Google Play only accepts these permissions for apps whose core purpose is photo or video access, and rejects the others. Picking a photo with the system photo picker keeps working without them.',
        ] as $text) {
            t_true(strpos($template, $text) !== false, 'back office text: ' . substr($text, 0, 40));
        }
    },

    'the editor lays a checkbox, its label and its help out in a row, whatever the label length' => function () {
        $phtml = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Application/resources/design/desktop/flat/template/application/customization/publication/sources.phtml');
        t_true(strpos($phtml, '#form-general-information-android .form-group-checkbox {') !== false, 'the row rule is there');
        t_true(strpos($phtml, 'flex-direction: row-reverse') !== false, 'the checkbox comes first');
    },
];
