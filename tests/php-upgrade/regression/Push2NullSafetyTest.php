<?php
/**
 * Push2 errors reported to the downloader: Push2.registerPlayer read player_id, push_token and external_user_id without
 * checking they were sent ("Undefined index: push_token", hundreds of times from one editor), and the message getters
 * called trim() on a null column (PHP 8.1+ deprecation). The body is read through a pure helper, an empty player id is
 * answered with an error, and the getters cast to string.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$push2 = $siberian . '/app/sae/modules/Push2';
require_once $push2 . '/Model/Onesignal/Registration.php';

use Push2\Model\Onesignal\Registration;

return [
    'a full registration body is read as sent' => function () {
        t_equal(
            ['player_id' => 'p-1', 'push_token' => 'tok', 'external_user_id' => 'u-9'],
            Registration::fromBody(['player_id' => 'p-1', 'push_token' => 'tok', 'external_user_id' => 'u-9']),
            'full body'
        );
    },

    'a missing key is an empty value and raises no notice' => function () {
        // the runner turns any notice or warning into a failure
        t_equal(
            ['player_id' => 'p-1', 'push_token' => '', 'external_user_id' => ''],
            Registration::fromBody(['player_id' => 'p-1']),
            'only the player id'
        );
        t_equal('', Registration::fromBody([])['player_id'], 'empty body');
    },

    'a body that is not an array or holds arrays is harmless' => function () {
        t_equal('', Registration::fromBody(null)['player_id'], 'null body');
        t_equal('', Registration::fromBody('x')['push_token'], 'string body');
        t_equal('', Registration::fromBody(['push_token' => ['a']])['push_token'], 'array value');
        t_equal('12', Registration::fromBody(['player_id' => 12])['player_id'], 'number is kept as text');
    },

    'the controller reads the body through the helper and refuses an empty player id' => function () use ($push2) {
        $code = file_get_contents($push2 . '/controllers/Mobile/PlayerController.php');
        t_true(strpos($code, 'Registration::fromBody(') !== false, 'helper used');
        t_equal(0, preg_match('/\$data\[\'(player_id|push_token|external_user_id)\'\]/', $code), 'no direct read of the body');
        t_true(strpos($code, "=== ''") !== false, 'empty player id is checked');
        t_true(strpos($code, 'p__(\'push2\', \'The player id is missing.\')') !== false, 'with a clear message');
    },

    'the message getters do not pass null to trim()' => function () use ($push2) {
        $code = file_get_contents($push2 . '/Model/Onesignal/Message.php');
        t_equal(0, preg_match('/trim\(\$this->getData\(/', $code), 'no bare trim of a column');
        t_equal(4, substr_count($code, 'trim((string) $this->getData('), 'the four columns are cast first');
    },

    'the list controller has no non-canonical cast' => function () use ($push2) {
        $code = file_get_contents($push2 . '/controllers/Mobile/ListController.php');
        t_equal(0, preg_match('/\((integer|boolean|double)\)/', $code), 'only (int) (bool) (float)');
    },

    'the new text is in the catalog' => function () use ($siberian) {
        $po = file_get_contents($siberian . '/languages/base/c_push2.po');
        t_true(strpos($po, "msgctxt \"push2\"\nmsgid \"The player id is missing.\"\n") !== false, 'c_push2.po');
    },
];
