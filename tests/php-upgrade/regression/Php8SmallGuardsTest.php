<?php
/**
 * Small places where PHP 8 turns a warning into an error that "catch (Exception)" does not catch:
 *  - file_get_contents('') is a ValueError (a certificate without a chain has an empty path): Siberian\File::readOrEmpty()
 *  - in_array(x, "a string") is a TypeError: the demo-mode check of the SSL delete compared with a string column
 *  - imagepng(null) in the colorize action, when the file is not an image; non-numeric width/height of blankimage
 *  - array_key_exists() / count() / array_filter() on a JSON body that is not an array (5, "x", true)
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/File.php';

function guard_source(string $path): string
{
    return file_get_contents(dirname(__DIR__, 3) . '/siberian/' . $path);
}

return [
    'readOrEmpty() reads a file and gives an empty string for anything else' => function () {
        $file = tempnam(sys_get_temp_dir(), 'guard');
        file_put_contents($file, "PEM\n");

        t_equal("PEM\n", \Siberian\File::readOrEmpty($file), 'a file');
        foreach (['', null, false, [], '/nonexistent/chain.pem', sys_get_temp_dir()] as $path) {
            t_equal('', \Siberian\File::readOrEmpty($path), 'nothing for ' . var_export($path, true));
        }
        unlink($file);
    },

    'the certificate uploads to the panels use it' => function () {
        foreach (['Plesk', 'Cpanel', 'DirectAdmin'] as $panel) {
            $source = guard_source("lib/Siberian/$panel.php");

            t_true(preg_match('/file_get_contents\(\$\w+->get(Certificate|Private|Chain)\(\)\)/', $source) === 0, "$panel: no raw read");
            t_true(strpos($source, '\Siberian\File::readOrEmpty(') !== false, "$panel: guarded read");
        }
    },

    'the demo protection of a certificate compares two strings' => function () {
        $source = guard_source('app/sae/modules/Backoffice/controllers/Advanced/ConfigurationController.php');

        t_true(strpos($source, "__getConfig('hostname') === \$cert->getHostname()") !== false, 'strict string comparison');
        t_true(strpos($source, "in_array(__getConfig('hostname')") === false, 'no in_array on the string');
    },

    'the colorize action stops when there is no image and blankimage casts its size' => function () {
        $source = guard_source('app/sae/modules/Template/controllers/BlockController.php');

        t_true(strpos($source, 'if (empty($resources))') !== false, 'no imagepng(null)');
        t_true(strpos($source, "max(1, (int) \$this->getRequest()->getParam('width', 320))") !== false, 'width');
        t_true(strpos($source, "max(1, (int) \$this->getRequest()->getParam('height', 75))") !== false, 'height');
    },

    'a JSON body that is not an array is treated as empty' => function () {
        t_true(strpos(guard_source('app/sae/modules/Front/Controller/App/Default.php'), '$rawBody = is_array($rawBody) ? $rawBody : [];') !== false, 'app controllers');
        t_true(strpos(guard_source('app/sae/modules/System/Controller/Backoffice/Default.php'), 'if (!is_array($data) || count($data) !== 2)') !== false, 'analytics period');
        foreach (['Backoffice/controllers/Account/ViewController.php', 'Admin/controllers/Backoffice/EditController.php'] as $file) {
            t_true(strpos(guard_source("app/sae/modules/$file"), 'array_filter(is_array($data) ? $data : [])') !== false, $file);
        }
    },
];
