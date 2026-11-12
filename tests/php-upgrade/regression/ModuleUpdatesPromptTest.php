<?php
/**
 * Module updates, back-office screen: "Please enter the licence key of X." must open the licence prompt.
 *
 * The server answers an error of the module update endpoints with HTTP 400 and the JSON payload
 * (Core_Controller_Default_Abstract::_sendJson). AngularJS gives such an answer to the error callback of $http, never
 * to .success(), so the branches written for `data.code === 'license_required'` were never reached: the message was
 * shown as an error banner and the prompt for the key never opened, for every module without a saved key. The same
 * went for the terms that changed meanwhile and for the message of an unreachable service.
 *
 * Source checks here; tests/php-upgrade/tooling/module_updates_prompt.test.js drives the real controller in jsdom
 * (14 of its 20 checks fail on the controller as it was, none with this fix; the last ones use the real sweetalert).
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae';
$controller = $root . '/design/desktop/backoffice/js/controllers/installer.js';

return [
    'a module update call is never left to .success() alone' => function () use ($controller) {
        $js = file_get_contents($controller);
        foreach (['checkModuleUpdates', 'moduleUpdatesTerms', 'acceptModuleUpdatesTerms', 'updateModule'] as $call) {
            $count = preg_match_all('/Installer\.' . $call . '\(/', $js, $matches, PREG_OFFSET_CAPTURE);
            t_true($count >= 1, "Installer.$call() is called");
            foreach ($matches[0] as $match) {
                $before = substr($js, max(0, $match[1] - 25), 25);
                t_true(
                    strpos($before, 'moduleUpdatesRequest(') !== false,
                    "Installer.$call() goes through moduleUpdatesRequest(), which also reads the HTTP 400 payload"
                );
            }
        }
        t_equal(0, preg_match('/Installer\.(checkModuleUpdates|moduleUpdatesTerms|acceptModuleUpdatesTerms|updateModule)\([^)]*\)\s*\.success/', $js), 'no .success() chained on them');
    },

    'moduleUpdatesRequest hands the payload of a 400 answer to the same handler' => function () use ($controller) {
        $js = file_get_contents($controller);
        $start = strpos($js, 'var moduleUpdatesRequest = function');
        t_true($start !== false, 'the helper exists');
        $helper = substr($js, $start, 900);
        t_true(strpos($helper, 'request.then(') !== false, 'it uses then() with both callbacks');
        t_true(strpos($helper, 'handler(response.data)') !== false, 'the 2xx answer goes to the handler');
        t_true(strpos($helper, 'angular.isObject(data) && data.error') !== false, 'the 400 payload goes to the handler');
        t_true(strpos($helper, "code: 'general'") !== false && strpos($helper, 'Label.uploader.error.general') !== false, 'anything else is the general error');
    },

    'the codes that open a prompt are the ones the server sends, and the server sends them with a 400' => function () use ($root, $controller) {
        $js = file_get_contents($controller);
        $model = file_get_contents($root . '/modules/Installer/Model/ModuleUpdates.php');
        foreach (['license_required', 'module_license'] as $code) {
            t_true(strpos($model, "'$code'") !== false, "the server sends $code");
            t_true(strpos($js, "data.code === '$code'") !== false, "the screen opens the prompt on $code");
        }
        t_true(strpos($js, 'data.error && data.terms') !== false, 'the screen shows changed terms');

        $abstract = file_get_contents($root . '/modules/Core/Controller/Default/Abstract.php');
        t_true(
            preg_match('/array_key_exists\("error", \$payload\) &&\s*\$payload\["error"\] == true\) \{\s*try \{\s*\$this->getResponse\(\)->setHttpResponseCode\(400\)/', $abstract) === 1,
            '_sendJson answers an error payload with HTTP 400 (if that ever changes, the screen still works: both are handled)'
        );
    },
];
