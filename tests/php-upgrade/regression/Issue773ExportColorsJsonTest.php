<?php
/**
 * #773: the Design > Colors editor showed every "ionic colors" SCSS variable only as a colored swatch - no way to
 * see its actual value as text, let alone copy the whole set to paste into a custom template definition file. Add
 * a JSON export (same download pattern the module's existing, unused YAML exportAction already uses) built on
 * Template_Model_Design::getVariables(), which already computes the exact name => rgba/hex map used to render the
 * swatches. Also print the value as text next to each swatch, answering the "how do I even know this variable
 * exists/what its value is" half of the request without needing to export anything.
 *
 * Importing a pasted scheme back is a separate, larger change (parsing, validating variable names/types against
 * the current template pack, writing back to Template_Model_Block/Design_Block) and is intentionally not part of
 * this fix.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

return [
    '773 a JSON export action exists and uses the same variable map as the swatches' => function () use ($siberian) {
        $controller = (string) file_get_contents(
            $siberian . '/app/sae/modules/Application/controllers/Customization/Design/ColorsController.php'
        );
        t_true(strpos($controller, 'function exportJsonAction') !== false, 'exportJsonAction exists');
        $pos = strpos($controller, 'function exportJsonAction');
        $body = substr($controller, $pos, 1200);
        t_true(
            strpos($body, 'Template_Model_Design::getVariables($application)') !== false,
            'it reuses the exact variable map Design::getVariables() computes (same data the swatches render)'
        );
        t_true(strpos($body, 'Json::encode') !== false, "it's JSON, not the existing YAML export");
        t_true(strpos($body, "'application/json'") !== false, 'served with the correct content type');
    },
    '773 the editor links to the export action and shows each value as text' => function () use ($siberian) {
        $template = (string) file_get_contents(
            $siberian . '/app/sae/modules/Application/resources/design/desktop/flat/template/application/'
            . 'customization/design/colors/ionic/edit.phtml'
        );
        t_true(
            strpos($template, "'application/customization_design_colors/export-json'") !== false,
            'a link points at the new export action'
        );
        $swatchLoop = strpos($template, 'foreach ($variables as $name => $value)');
        t_true($swatchLoop !== false, 'the swatch loop is still there');
        $loopBody = substr($template, $swatchLoop, 900);
        t_true(
            strpos($loopBody, '<?php echo $value ?>') !== false,
            'the raw value is now printed as text next to the swatch, not just used as a CSS background-color'
        );
    },
    '773 the new button label is translated' => function () use ($siberian) {
        $catalog = (string) file_get_contents($siberian . '/languages/base/c_application.po');
        t_true(strpos($catalog, 'msgid "Export as JSON"') !== false, '"Export as JSON" is in the application translation catalog');
    },
];
