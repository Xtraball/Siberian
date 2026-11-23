<?php
/**
 * Core_Controller_Default_Abstract::_sendJson($payload) used to do
 * `array_key_exists("error", $payload) && $payload["error"] == true` directly, assuming $payload
 * is always array-shaped. It is not always one: the controller's own deprecated _sendHtml()
 * forwards a raw string into _sendJson() as $payload, and PHP 8 turns array_key_exists() on a
 * non-array into an uncaught TypeError instead of the old "always false" result (production error
 * group "Uncaught TypeError: array_key_exists(): Argument #2 ($array) must be of type array" at
 * app/sae/modules/Core/Controller/Default/Abstract.php:1043).
 *
 * Core_Controller_Default_Abstract extends Zend_Controller_Action, which needs real typed
 * Request/Response objects, a HelperBroker and init() to construct, so it is not something to
 * instantiate directly in a unit test. The guard was pulled out into Siberian\Json::isErrorResponse(),
 * a small pure helper with no bootstrap dependencies, which this test exercises directly; a second,
 * source-level check confirms _sendJson() actually calls it instead of the raw array_key_exists().
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Siberian/Json.php';

use Siberian\Json;

return [
    'non-array payload (e.g. a raw string from _sendHtml()) is not an error response' => function () {
        t_true(Json::isErrorResponse('<p>hello</p>') === false, 'string payload does not fatal, reads as no-error');
        t_true(Json::isErrorResponse(null) === false, 'null payload does not fatal, reads as no-error');
        t_true(Json::isErrorResponse(42) === false, 'scalar payload does not fatal, reads as no-error');
    },
    'array payload keeps the original error-flag behaviour, unchanged' => function () {
        t_true(Json::isErrorResponse(['error' => true, 'message' => 'nope']) === true, 'error => true is an error response');
        t_true(Json::isErrorResponse(['error' => false]) === false, 'error => false is not');
        t_true(Json::isErrorResponse(['ok' => true]) === false, 'no "error" key at all is not');
    },
    '_sendJson() is wired to the helper, not the raw array_key_exists() it used to inline' => function () {
        $source = file_get_contents(
            dirname(__DIR__, 3) . '/siberian/app/sae/modules/Core/Controller/Default/Abstract.php'
        );
        t_true(
            strpos($source, 'Json::isErrorResponse($payload)') !== false,
            '_sendJson() calls the pure helper'
        );
        t_true(
            strpos($source, 'array_key_exists("error", $payload)') === false,
            'the old, unguarded array_key_exists() call on $payload is gone'
        );
    },
];
