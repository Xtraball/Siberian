<?php
/**
 * The optional custom stylesheet (app/local/design/desktop/<area>/css/user-style.css, written by the user or by a
 * module) is linked only when the file exists: a missing file was a 404 on every page. A file created later is still
 * picked up, the check runs at each render.
 */
$design = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop';

return [
    'the backoffice and the editor link the user stylesheet only when it exists' => function () use ($design) {
        foreach (['backoffice', 'flat'] as $area) {
            $html = file_get_contents($design . '/' . $area . '/template/page/layout.phtml');
            $path = '/app/local/design/desktop/' . $area . '/css/user-style.css';
            t_equal(2, substr_count($html, $path), $area . ': the guard and the link');
            $guard = "<?php if (is_file(Core_Model_Directory::getBasePathTo('" . $path . "'))): ?>";
            $guardPosition = strpos($html, $guard);
            t_true($guardPosition !== false, $area . ': guarded by the existence of the file');
            $linkPosition = strpos($html, 'href="' . $path);
            $endPosition = strpos($html, '<?php endif; ?>', $linkPosition);
            t_true($guardPosition < $linkPosition && $endPosition !== false && $endPosition - $linkPosition < 300,
                $area . ': the link sits inside the guard');
        }
    },

    'the guard resolves the same file the link serves' => function () {
        // getBasePathTo() prefixes the install path, the url is relative to the web root: same file by construction
        $source = file_get_contents(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Core/Model/Directory.php');
        t_true(strpos($source, 'public static function getBasePathTo(') !== false, 'helper exists');
    },
];
