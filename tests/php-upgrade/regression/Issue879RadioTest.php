<?php
/**
 * #879: the "keep_url" option leaves the stream link as typed. Off by default, the Shoutcast "/;" fix that
 * shipped before is unchanged.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/app/sae/modules/Radio/Model/Stream.php';

return [
    '879 default keeps the Shoutcast fix: a non audio answer gets /; appended' => function () {
        t_equal('http://h:8000/;', Radio_Model_Stream::link('http://h:8000', 'text/html'), 'html answer');
        t_equal('http://h:8000/;', Radio_Model_Stream::link('http://h:8000', ''), 'no content type');
        t_equal('http://h:8000/;', Radio_Model_Stream::link('http://h:8000', null), 'not tested');
        t_equal('http://h:8000/;', Radio_Model_Stream::link('http://h:8000', 'text/html', false), 'option explicitly off');
    },

    '879 default leaves audio streams and already forced links alone' => function () {
        t_equal('http://h/live', Radio_Model_Stream::link('http://h/live', 'audio/mpeg'), 'audio/mpeg');
        t_equal('http://h/live', Radio_Model_Stream::link('http://h/live', 'audio/aacp'), 'audio/aacp');
        t_equal('http://h/live', Radio_Model_Stream::link('http://h/live', 'application/ogg'), 'ogg');
        t_equal('http://h:8000/;', Radio_Model_Stream::link('http://h:8000/;', 'text/html'), 'already has ;');
    },

    '879 with the option on the link is saved exactly as typed' => function () {
        foreach ([null, '', 'text/html', 'audio/mpeg', 'application/ogg'] as $type) {
            t_equal('http://h:8000', Radio_Model_Stream::link('http://h:8000', $type, true), 'type ' . var_export($type, true));
        }
    },

    '879 the option is stored off by default' => function () use ($siberian) {
        $schemas = [];
        require $siberian . '/app/sae/modules/Radio/resources/db/schema/radio.php';
        t_equal('tinyint(1) unsigned', $schemas['radio']['keep_url']['type'], 'type');
        t_equal(0, $schemas['radio']['keep_url']['default'], 'default off');
    },

    '879 the controller only skips the stream test when the option is on' => function () use ($siberian) {
        $code = file_get_contents($siberian . '/app/sae/modules/Radio/controllers/ApplicationController.php');
        t_true(strpos($code, '$keepUrl ? null : Request::testStream($radio->getLink())') !== false, 'test skipped only with the option');
        t_true(strpos($code, 'Radio_Model_Stream::link(') !== false, 'the helper is used');
    },
];
