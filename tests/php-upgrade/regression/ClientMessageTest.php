<?php
/**
 * The mobile controllers answer ['error' => true, 'message' => $e->getMessage()]. A database error there reached the
 * app user ("SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens" on
 * the registration form of an Android app). The two mobile _sendJson() replace it by a plain sentence and log the text.
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/ClientMessage.php';

use Siberian\ClientMessage;

return [
    'the messages of the database layer are technical' => function () {
        foreach ([
            'SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens',
            'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'x\' in \'field list\'',
            'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'a@b.c\' for key \'email\'',
            'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away',
            'You have an error in your SQL syntax; check the manual',
            'Fetching the rows of Customer_Model_Db_Table_Customer failed (00000 )',
            'Fetching the many-to-many rows of Foo_Model_Db_Table_Bar failed (HY000 )',
            'Zend_Db_Statement_Exception: whatever',
        ] as $message) {
            t_true(ClientMessage::isTechnical($message), $message);
        }
    },

    'our own messages are kept' => function () {
        foreach ([
            'This e-mail address is already in use, maybe you want to retrieve your password?',
            'The following fields are required:<br />- Firstname',
            'Thanks for your registration!',
            'Ho letto e accetto l\'informativa',
            '',
        ] as $message) {
            t_true(!ClientMessage::isTechnical($message), "kept: $message");
        }
        t_true(!ClientMessage::isTechnical(null), 'null is kept');
        t_true(!ClientMessage::isTechnical(['SQLSTATE[HY093]']), 'an array is not a message');
    },

    'a technical error message is replaced and logged' => function () {
        $logged = [];
        $payload = ClientMessage::clean(
            ['error' => true, 'message' => 'SQLSTATE[HY093]: Invalid parameter number'],
            'Something went wrong',
            function ($message) use (&$logged) {
                $logged[] = $message;
            });

        t_equal('Something went wrong', $payload['message'], 'the app reads the plain sentence');
        t_equal(['SQLSTATE[HY093]: Invalid parameter number'], $logged, 'the original goes to the log');
        t_true($payload['error'] === true, 'still an error answer');
    },

    'a normal error and a success are untouched' => function () {
        $logged = [];
        $log = function ($m) use (&$logged) {
            $logged[] = $m;
        };

        $error = ['error' => true, 'message' => 'This e-mail address is already in use'];
        t_equal($error, ClientMessage::clean($error, 'x', $log), 'own message');

        // not an error answer: nothing to hide, a customer may legitimately have this text in its data
        $ok = ['success' => true, 'message' => 'SQLSTATE[HY093] is what the user typed'];
        t_equal($ok, ClientMessage::clean($ok, 'x', $log), 'success answer');

        t_equal([], $logged, 'nothing logged');
    },

    'both mobile _sendJson use it, outside the development mode' => function () {
        $root = dirname(__DIR__, 3) . '/siberian/app/sae/modules';
        foreach (['Application/Controller/Mobile/Default.php', 'Front/Controller/App/Default.php'] as $file) {
            $source = file_get_contents("$root/$file");
            t_true(strpos($source, '\\Siberian\\ClientMessage::clean(') !== false, "$file cleans the answer");
            t_true(strpos($source, '!Siberian_Debug::isDevelopment()') !== false, "$file keeps the development answers");
        }
    },
];
