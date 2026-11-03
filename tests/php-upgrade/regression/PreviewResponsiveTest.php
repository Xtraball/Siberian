<?php
/**
 * The preview of the app (the phone on the right of the editor) takes the width of its column instead of a fixed size
 * in vh, so it never overlaps the editor, and on small screens a button opens it over the page.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$css = file_get_contents($siberian . '/app/sae/design/desktop/flat/css/style.css');
$tpl = $siberian . '/app/sae/modules/Application/resources/design/desktop/flat/template/application/customization/';
$index = file_get_contents($tpl . 'index.phtml');
$publication = file_get_contents($tpl . 'index/overview/publication.phtml');

return [
    'the phone is sized by its column, with its proportions kept' => function () use ($css) {
        t_true(preg_match('/div#iphone \{[^}]*width: min\(46vh, calc\(100% - 20px\)\);/s', $css) === 1, 'width follows the column');
        t_true(preg_match('/\.iphone_content \{[^}]*aspect-ratio: 48\.88 \/ 87;/s', $css) === 1, 'proportions');
        t_equal(0, preg_match('/div#iphone \{[^}]*height: 87vh/s', $css), 'no fixed height in vh');
    },

    'the loader and the status bar are laid over the phone, not placed by vh' => function () use ($css, $publication) {
        t_true(preg_match('/div#mask-overview\.mask \{[^}]*position: absolute;/s', $css) === 1, 'mask over the screen');
        t_true(preg_match('/div#phone-bar \{[^}]*position: absolute;/s', $css) === 1, 'status bar over the screen');
        t_equal(0, preg_match('/top: calc\(-1 \* 87vh/', $css . $publication), 'no negative vh offsets');
    },

    'small screens get a button that opens the preview over the page' => function () use ($css, $index) {
        t_true(strpos($index, 'id="overview-toggle"') !== false, 'the button');
        t_true(strpos($index, 'p__("application", "Preview")') !== false, 'a translated label');
        t_true(strpos($index, 'toggleClass("overview-open")') !== false, 'it toggles the overlay');
        t_true(preg_match('/@media \(max-width: 1200px\) \{\s+#overview \{\s+display: none;/', $css) === 1, 'hidden below 1200px');
        t_true(strpos($css, 'body.overview-open #overview') !== false, 'the overlay');
    },
];
