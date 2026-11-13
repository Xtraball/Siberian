<?php
/**
 * Places asked Location.getLocation() with the second argument set, which forces a new high-accuracy fix
 * (maximumAge 0) and skips the 42 second cache of the Location service. Opening the feature asked for two forced
 * fixes in a row (the init, then searchPlaces), and every category opened asked for another one: indoors each fix
 * takes up to the 10 second timeout, and the list request only starts after it. Places now uses the cached position
 * when it is less than 42 seconds old (the service still refreshes it in the background).
 * JavaScript cannot run here, so the sources are checked.
 */
$root = dirname(__DIR__, 3);
$controller = "$root/siberian/app/sae/modules/Places/features/places/js/controllers/places-list-controller.js";
$service = "$root/ionic/www/js/services/location.js";

return [
    'Places no longer forces a new location fix' => function () use ($controller) {
        $js = file_get_contents($controller);
        t_equal(0, preg_match('/getLocation\(\s*\{[^}]*\}\s*,\s*true\s*\)/', $js), 'no getLocation({...}, true) in places-list-controller.js');
        t_equal(2, preg_match_all('/getLocation\(\s*\{timeout:\s*10000\}\s*\)/', $js), 'both calls keep the 10 second timeout');
    },

    'the Location service only skips its cache when the second argument is given' => function () use ($service) {
        $js = file_get_contents($service);
        t_true(strpos($js, 'var localForce = (force !== undefined);') !== false, 'force is any second argument');
        t_true(strpos($js, '(service.lastFetch + 42000) > Date.now()') !== false, 'a fix younger than 42 seconds is reused');
    },
];
