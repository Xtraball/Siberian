<?php
/**
 * Access matrix: how every controller of the application is guarded.
 *
 * Access is decided in the controller family (the base class), not by a route pattern. Four families check the
 * caller in `init()`: Backoffice, Admin, Application (editor) and Api. Everything else answers without a login:
 * the mobile app endpoints (identified by the application key), the installer, and a short list of public
 * controllers. This test reads every controller with the PHP tokenizer and fails when:
 *
 *  - a family guard loses the check it is there for,
 *  - a controller overrides `init()` or `preDispatch()` and does not call the parent, which switches the guard off,
 *  - a controller or an action that answers without a login appears (or disappears) without the baseline being
 *    updated on purpose,
 *  - the list of guest routes or open actions grows.
 *
 * The baseline is tests/php-upgrade/access/baseline.json. To accept a change after review:
 *   SIB_UPDATE_ACCESS_BASELINE=1 php tests/php-upgrade/run.php ControllerAccessMatrix
 * and describe in the pull request why each new unauthenticated action is safe.
 *
 * This does not prove that an action checks ownership of the object it touches. It proves nobody adds an
 * unauthenticated door, or removes the login of a family, by accident.
 */
require_once dirname(__DIR__) . '/access/ControllerMap.php';
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Security.php';

$modules = dirname(__DIR__, 3) . '/siberian/app/sae/modules';
$baselineFile = dirname(__DIR__) . '/access/baseline.json';

/** Families, most specific first: the first one found in the parent chain names the guard of a controller. */
$families = [
    'Backoffice_Controller_Default' => 'backoffice login',
    'Application_Controller_Default' => 'editor login + option ACL',
    'Admin_Controller_Default' => 'admin login',
    'Api_Controller_Default' => 'API credentials',
    'Installer_Controller_Installation_Default' => 'installer',
    'Application_Controller_Mobile_Default' => 'mobile app (application key)',
];
$loginFamilies = ['Backoffice_Controller_Default', 'Application_Controller_Default', 'Admin_Controller_Default', 'Api_Controller_Default'];

$familyOf = static function (ControllerMap $map, string $class) use ($families): string {
    foreach ($map->chain($class) as $ancestor) {
        if (isset($families[$ancestor])) {
            return $ancestor;
        }
    }

    return 'public';
};

/** @return array current state, same shape as baseline.json */
$snapshot = static function (ControllerMap $map) use ($familyOf, $loginFamilies): array {
    $noLogin = [];
    $open = [];
    foreach ($map->classes() as $class => $info) {
        $family = $familyOf($map, $class);
        if (!in_array($family, $loginFamilies, true) && !$info['abstract']) {
            $actions = $info['actions'];
            sort($actions);
            $noLogin[$class] = ['family' => $family, 'actions' => $actions];
        }
        foreach ($map->openActions($class) as $triple) {
            $open[] = $class . ' => ' . $triple;
        }
    }
    ksort($noLogin);
    sort($open);
    $guest = \Siberian\Security::$routesGuest;
    sort($guest);

    return ['guestRoutes' => $guest, 'openActions' => $open, 'withoutLogin' => $noLogin];
};

$map = new ControllerMap($modules);

return [
    'the controllers are found (a broken scan must not pass for a clean one)' => function () use ($map) {
        t_true(count($map->classes()) > 300, 'controllers found: ' . count($map->classes()));
        t_true(isset($map->classes()['Backoffice_Controller_Default'], $map->classes()['Application_Controller_Mobile_Default']), 'the family bases are found');
    },

    'each login family still checks the caller in init()' => function () use ($map) {
        $must = [
            ['Backoffice_Controller_Default', 'isLoggedIn'],
            ['Admin_Controller_Default', 'isLoggedIn'],
            ['Admin_Controller_Default', '_canAccessCurrentPage'],
            ['Application_Controller_Default', '_canAccessCurrentPage'],
            ['Api_Controller_Default', 'notauthorized'],
        ];
        foreach ($must as [$class, $identifier]) {
            t_true($map->bodyMentions($class, 'init', $identifier), "$class::init() no longer calls $identifier");
        }
        foreach (['Backoffice_Controller_Default', 'Admin_Controller_Default', 'Application_Controller_Default', 'Api_Controller_Default'] as $class) {
            t_true(!$map->breaksChain($class, 'init'), "$class::init() must call parent::init()");
        }
    },

    'no controller switches a guard off by overriding init() or preDispatch() without calling the parent' => function () use ($map) {
        // Reviewed exceptions: the root of the tree, and an XHR-only gate that only ever forwards to "noroute"
        $reviewed = [
            'Core_Controller_Default_Abstract::init' => 'root of the controller tree',
            'Application_Controller_Default_Ajax::preDispatch' => 'only refuses non-XHR requests; init() of the editor guard still runs',
        ];
        $broken = [];
        foreach ($map->classes() as $class => $info) {
            foreach (['init', 'preDispatch'] as $method) {
                if (isset($info['methods'][$method]) && !$info['methods'][$method]['callsParent'] && !isset($reviewed["$class::$method"])) {
                    $broken[] = "$class::$method ({$info['file']})";
                }
            }
        }
        t_equal([], $broken, 'overrides without parent::init()/parent::preDispatch()');
    },

    'every controller is either behind a login family or listed in the baseline of the ones that are not' => function () use ($map, $snapshot, $baselineFile) {
        $current = $snapshot($map);
        if (getenv('SIB_UPDATE_ACCESS_BASELINE')) {
            file_put_contents($baselineFile, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        }
        $baseline = json_decode((string) file_get_contents($baselineFile), true);
        t_true(is_array($baseline), 'baseline.json is readable');

        $added = array_values(array_diff(array_keys($current['withoutLogin']), array_keys($baseline['withoutLogin'])));
        $removed = array_values(array_diff(array_keys($baseline['withoutLogin']), array_keys($current['withoutLogin'])));
        t_equal([], $added, 'controllers that answer without a login and are not in the baseline (review them, then update it)');
        t_equal([], $removed, 'controllers in the baseline that no longer exist');
    },

    'no action appears or disappears on a controller that answers without a login' => function () use ($map, $snapshot, $baselineFile) {
        $current = $snapshot($map)['withoutLogin'];
        $baseline = json_decode((string) file_get_contents($baselineFile), true)['withoutLogin'];
        $changes = [];
        foreach ($current as $class => $info) {
            if (!isset($baseline[$class])) {
                continue;
            }
            foreach (array_diff($info['actions'], $baseline[$class]['actions']) as $action) {
                $changes[] = "+ $class::$action";
            }
            foreach (array_diff($baseline[$class]['actions'], $info['actions']) as $action) {
                $changes[] = "- $class::$action";
            }
            if ($info['family'] !== $baseline[$class]['family']) {
                $changes[] = "$class moved from {$baseline[$class]['family']} to {$info['family']}";
            }
        }
        t_equal([], $changes, 'unauthenticated actions changed (a new one needs a reviewed reason in the pull request)');
    },

    'the guest routes and the open actions of the login families are the reviewed ones' => function () use ($map, $snapshot, $baselineFile) {
        $current = $snapshot($map);
        $baseline = json_decode((string) file_get_contents($baselineFile), true);
        t_equal($baseline['guestRoutes'], $current['guestRoutes'], 'Security::$routesGuest (routes that skip the backoffice/admin/editor login)');
        t_equal($baseline['openActions'], $current['openActions'], '$openActions (actions that skip the admin/editor login)');
    },
];
