<?php
/**
 * #948: "This code is already used" on every request.
 *  1. Bootstrap runs each module init.php through the global $init; a module that does not define it ran the closure
 *     of the previous module again, which registered the same payment gateway twice
 *  2. the exception said nothing about the module it came from
 *  3. registering the same gateway class under the same code again is now a no-op
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** Gateway extends the application base model, which needs the whole app: a bare stand-in is enough here. */
$loadGateway = function () use ($siberian) {
    if (!class_exists('Core\Model\Base', false)) {
        eval('namespace Core\Model; class Base {}');
    }
    if (!class_exists('Siberian\Exception', false)) {
        eval('namespace Siberian; class Exception extends \Exception {}');
    }
    require_once $siberian . '/app/sae/modules/PaymentMethod/Model/Gateway.php';
};

/** Source of the init loop in app/Bootstrap.php. */
$initLoop = function () use ($siberian) {
    $source = file_get_contents($siberian . '/app/Bootstrap.php');
    $start = strpos($source, 'global $init;');

    return substr($source, $start, strpos($source, '} else if (is_readable($path))', $start) - $start);
};

return [
    '948 the global $init is cleared before each module init.php is loaded' => function () use ($initLoop) {
        $loop = $initLoop();
        $clear = strpos($loop, '$init = null;');
        $load = strpos($loop, 'require_once $pathInit;');
        t_true($clear !== false && $load !== false && $clear < $load, 'cleared first, loaded after');
    },

    '948 a failing init says which module it was' => function () use ($initLoop) {
        t_true(strpos($initLoop(), "'Module %s init: %s', \$module") !== false, 'module name in the message');
    },

    '948 the same gateway registered twice is accepted once' => function () use ($loadGateway) {
        $loadGateway();

        $before = PaymentMethod\Model\Gateway::$gateways;
        PaymentMethod\Model\Gateway::$gateways = [];
        try {
            PaymentMethod\Model\Gateway::register('t948', ['class' => 'Some_Class']);
            PaymentMethod\Model\Gateway::register('t948', ['class' => 'Some_Class', 'label' => 'again']);
            t_equal(['t948'], array_keys(PaymentMethod\Model\Gateway::$gateways), 'one entry');
            t_equal(
                ['class' => 'Some_Class'],
                PaymentMethod\Model\Gateway::$gateways['t948'],
                'the first registration stands'
            );
        } finally {
            PaymentMethod\Model\Gateway::$gateways = $before;
        }
    },

    '948 another class under a taken code is still refused' => function () use ($loadGateway) {
        $loadGateway();

        $before = PaymentMethod\Model\Gateway::$gateways;
        PaymentMethod\Model\Gateway::$gateways = [];
        $message = null;
        try {
            PaymentMethod\Model\Gateway::register('t948', ['class' => 'Some_Class']);
            try {
                PaymentMethod\Model\Gateway::register('t948', ['class' => 'Other_Class']);
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        } finally {
            PaymentMethod\Model\Gateway::$gateways = $before;
        }
        t_true($message !== null, 'refused');
    },
];
