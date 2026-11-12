<?php
/**
 * #549: the PadLock "Choose the features to lock:" checklist had no "select all / deselect all" control - with
 * many features, locking (or unlocking) all of them meant clicking every checkbox by hand. The admin template is
 * plain PHP/jQuery with no shared form abstraction (unlike Media's gallery lists, which already have this pattern),
 * so the fix is template-local: a select_all checkbox plus a jQuery handler that toggles every .select_option_value
 * checkbox, wired into the existing didappear/willdisappear page lifecycle callbacks.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$template = (string) file_get_contents(
    $siberian . '/app/sae/modules/Padlock/resources/design/desktop/flat/template/padlock/application/edit.phtml'
);

return [
    '549 a select-all checkbox is rendered above the feature list' => function () use ($template) {
        $rowPos = strpos($template, 'class="row"');
        $selectAllPos = strpos($template, 'id="select_all_features_to_lock"');
        t_true($rowPos !== false, 'the feature checkbox row is still there');
        t_true($selectAllPos !== false, 'the select-all checkbox exists');
        t_true($selectAllPos < $rowPos, 'the select-all control is placed before (above) the row of feature checkboxes');
    },
    '549 toggling it toggles every feature checkbox, bound in the page lifecycle' => function () use ($template) {
        $didappear = strpos($template, "page.setCallback('didappear'");
        t_true($didappear !== false, "didappear callback still exists");
        $lifecycle = substr($template, $didappear, 3000);
        t_true(
            strpos($lifecycle, '$("#select_all_features_to_lock").change(') !== false,
            'a change handler is bound on the select-all checkbox inside didappear'
        );
        t_true(
            strpos($lifecycle, '.select_option_value') !== false
            && strpos($lifecycle, '.prop("checked"') !== false,
            'the handler drives .prop("checked") on the .select_option_value checkboxes'
        );
        $willdisappear = strpos($template, "page.setCallback('willdisappear'");
        $teardown = substr($template, $willdisappear, 500);
        t_true(
            strpos($teardown, '#select_all_features_to_lock') !== false
            && strpos($teardown, 'unbind') !== false,
            'the handler is unbound again in willdisappear, like the form\'s other handlers'
        );
    },
    '549 the new label is a translated padlock-context string' => function () use ($template) {
        $siberian = dirname(__DIR__, 3) . '/siberian';
        t_true(
            strpos($template, "p__('padlock', 'Select all')") !== false,
            'the label goes through p__ with the padlock context'
        );
        $catalog = (string) file_get_contents($siberian . '/languages/base/c_padlock.po');
        t_true(strpos($catalog, 'msgid "Select all"') !== false, '"Select all" is in the padlock translation catalog');
    },
];
