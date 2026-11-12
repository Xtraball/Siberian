<?php
/**
 * #855: Google Play App Signing. Google keeps the app signing key, the keystore of the application is only the upload
 * key. Enabling the option (per application, off by default) keeps the current keystore as a backup, and the keystore
 * zip of the editor (keystore.pks + passwords.txt) can be uploaded, again with a backup of the previous one.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Siberian/Android/Keystore.php';

use Siberian\Android\Keystore;

/** A scratch directory, removed by the caller */
function ks_dir(): string
{
    $dir = sys_get_temp_dir() . '/ks855-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);

    return $dir;
}

function ks_rm(string $dir): void
{
    foreach (glob($dir . '/*') as $entry) {
        unlink($entry);
    }
    rmdir($dir);
}

/** The first bytes of a JKS, then filler: enough for the checks which only look at the magic number */
function ks_jks(string $payload = 'x'): string
{
    return "\xFE\xED\xFE\xED" . str_repeat($payload, 64);
}

function ks_zip(string $dir, array $entries): string
{
    $path = $dir . '/upload-' . bin2hex(random_bytes(3)) . '.zip';
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE);
    foreach ($entries as $name => $content) {
        $zip->addFromString($name, $content);
    }
    $zip->close();

    return $path;
}

function ks_fails(callable $call, string $needle): bool
{
    try {
        $call();
    } catch (\Throwable $e) {
        return strpos($e->getMessage(), $needle) !== false;
    }

    return false;
}

return [
    '855 the keystore is kept as a timestamped backup, never overwritten' => function () {
        $dir = ks_dir();
        $path = $dir . '/7.pks';
        $now = 1700000000;

        t_equal(null, Keystore::backup($path, $now), 'nothing to keep when there is no keystore');

        file_put_contents($path, 'first');
        $first = Keystore::backup($path, $now);
        t_true($first !== null && is_file($first) && !is_file($path), 'renamed, not copied');
        t_equal('first', file_get_contents($first), 'the content is preserved');
        t_true(preg_match('/^7-backup-\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.pks$/', basename($first)) === 1, 'name: ' . basename($first));

        file_put_contents($path, 'second');
        $second = Keystore::backup($path, $now);
        t_true($second !== $first && file_get_contents($first) === 'first', 'a backup of the same second does not replace the previous one');
        t_equal('second', file_get_contents($second), 'both are kept');

        ks_rm($dir);
    },

    '855 only java keystores are accepted' => function () {
        t_equal('jks', Keystore::kind(ks_jks()), 'jks');
        t_equal('jceks', Keystore::kind("\xCE\xCE\xCE\xCE" . str_repeat('x', 64)), 'jceks');
        t_equal('pkcs12', Keystore::kind("\x30\x82" . str_repeat('x', 100)), 'pkcs12');
        t_equal(null, Keystore::kind('hello world'), 'text');
        t_equal(null, Keystore::kind(''), 'empty');
        t_equal(null, Keystore::kind("\x30"), 'a lone byte is not a PKCS#12');
    },

    '855 passwords.txt is parsed and checked' => function () {
        $ok = Keystore::parsePasswords("key_pass:appkeypassword\r\nstore_pass: appstorepassword \nalias:aliasofapp\n");
        t_equal(['alias' => 'aliasofapp', 'store_pass' => 'appstorepassword', 'key_pass' => 'appkeypassword'], $ok, 'the example archive format, CRLF and spaces tolerated');

        $colon = Keystore::parsePasswords("key_pass:a:b:c:d\nstore_pass:secret1\nalias:up");
        t_equal('a:b:c:d', $colon['key_pass'], 'a password may contain a colon');

        t_true(ks_fails(function () { Keystore::parsePasswords("store_pass:abcdefg\nalias:a"); }, 'key_pass'), 'missing key_pass');
        t_true(ks_fails(function () { Keystore::parsePasswords("key_pass:abc\nstore_pass:abcdefg\nalias:a"); }, 'at least 6'), 'too short for Java');
        t_true(ks_fails(function () { Keystore::parsePasswords("key_pass:abcdefg\nstore_pass:abcdefg\nalias:" . str_repeat('a', 51)); }, 'too long'), 'alias longer than the column');
        t_true(ks_fails(function () { Keystore::parsePasswords(''); }, 'alias'), 'empty file');
    },

    '855 the keystore archive is read in memory and checked' => function () {
        $dir = ks_dir();
        $passwords = "key_pass:appkeypassword\nstore_pass:appstorepassword\nalias:aliasofapp\n";

        $read = Keystore::readArchive(ks_zip($dir, ['keystore.pks' => ks_jks(), 'passwords.txt' => $passwords]));
        t_equal(ks_jks(), $read['pks'], 'the keystore bytes');
        t_equal('aliasofapp', $read['alias'], 'alias');

        $nested = Keystore::readArchive(ks_zip($dir, [
            'upload/my.pks' => ks_jks(), 'upload/passwords.txt' => $passwords,
            '__MACOSX/upload/._my.pks' => 'resource fork', '__MACOSX/upload/._passwords.txt' => 'resource fork',
        ]));
        t_equal(ks_jks(), $nested['pks'], 'a folder and the macOS resource forks are fine');

        t_true(ks_fails(function () use ($dir, $passwords) { Keystore::readArchive(ks_zip($dir, ['passwords.txt' => $passwords])); }, 'No keystore'), 'no keystore');
        t_true(ks_fails(function () use ($dir) { Keystore::readArchive(ks_zip($dir, ['a.pks' => ks_jks()])); }, 'No passwords.txt'), 'no passwords');
        t_true(ks_fails(function () use ($dir, $passwords) { Keystore::readArchive(ks_zip($dir, ['a.pks' => ks_jks(), 'b.pks' => ks_jks('y'), 'passwords.txt' => $passwords])); }, 'only one keystore'), 'two keystores');
        t_true(ks_fails(function () use ($dir, $passwords) { Keystore::readArchive(ks_zip($dir, ['a.pks' => 'not a keystore', 'passwords.txt' => $passwords])); }, 'not a Java keystore'), 'not a keystore');

        $notZip = $dir . '/plain.zip';
        file_put_contents($notZip, 'plain text');
        t_true(ks_fails(function () use ($notZip) { Keystore::readArchive($notZip); }, 'not a zip'), 'not an archive');

        ks_rm($dir);
    },

    '855 the option is per application, off by default, and the wiring keeps a backup' => function () use ($siberian) {
        $module = $siberian . '/app/sae/modules/Application';

        $schemas = [];
        require $module . '/resources/db/schema/application_device.php';
        $column = $schemas['application_device']['google_signing'] ?? null;
        t_true($column !== null && $column['default'] === '0', 'google_signing exists and is off by default');
        t_equal('varchar(255)', $schemas['application_device']['store_pass']['type'], 'a Google upload key password is not limited to 10 characters');
        t_equal('varchar(255)', $schemas['application_device']['key_pass']['type'], 'same for the key password');

        $controller = file_get_contents($module . '/Controller/View/Abstract.php');
        t_true(strpos($controller, '!(int) $device->getGoogleSigning()') !== false, 'only the switch from off to on replaces the keystore');
        t_true(strpos($controller, 'Keystore::backup($keystorePath)') !== false, 'the keystore is backed up when enabling');
        t_true(strpos($controller, 'rename($keystoreBackup, $keystorePath)') !== false, 'and put back when the save fails');
        t_true(strpos($controller, 'function uploadkeystoreAction') !== false, 'the upload of the editor has a handler');
        t_equal(1, substr_count($controller, 'Keystore::readArchive('), 'the archive is read once');
        t_true(strpos($controller, '$previous = [$device->getAlias(), $device->getStorePass(), $device->getKeyPass()];') !== false
            && strpos($controller, '...$previous') !== false, 'the passwords of the backup are given before they are replaced');

        $template = file_get_contents($module . '/resources/design/desktop/backoffice/template/application/view/device.phtml');
        t_true(strpos($template, 'ng-model="device.google_signing"') !== false, 'the option is in the device form');

        $android = file_get_contents($module . '/Model/Device/Ionic/Android.php');
        t_true(strpos($android, 'google_signing') === false && strpos($android, 'GoogleSigning') === false, 'the build itself is unchanged');
    },
];
