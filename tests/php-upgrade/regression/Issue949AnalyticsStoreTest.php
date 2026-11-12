<?php
/**
 * #949: Analytics_Model_Store built its INSERT values with addslashes($metric[$field]); a metric with a null (or
 * missing) field handed null to addslashes(), deprecated since PHP 8.1.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

/** Runs $fn and returns [result, what PHP complained about while it ran], without failing the test. */
$complaints = function (callable $fn) {
    $seen = [];
    set_error_handler(function ($no, $msg) use (&$seen) {
        if (!(error_reporting() & $no)) {
            return false; // @-suppressed
        }
        $seen[] = $msg;

        return true;
    });
    try {
        $result = $fn();
    } finally {
        restore_error_handler();
    }

    return [$result, $seen];
};

/** A store whose SQLite adapter only records the queries it is given. */
$store = function () use ($siberian) {
    require_once $siberian . '/app/sae/modules/Analytics/Model/Store.php';

    $adapter = new class {
        public $queries = [];

        public function query($sql)
        {
            $this->queries[] = $sql;
            return true;
        }
    };
    $reflection = new ReflectionClass('Analytics_Model_Store');
    $instance = $reflection->newInstanceWithoutConstructor();
    $property = $reflection->getProperty('_sqliteAdapter');
    $property->setAccessible(true);
    $property->setValue($instance, $adapter);

    return [$instance, $adapter];
};

return [
    '949 metrics with null and missing fields are stored without complaint' => function () use ($store, $complaints) {
        [$instance, $adapter] = $store();

        [$results, $seen] = $complaints(function () use ($instance) {
            $metric = ['appId' => 12, 'latitude' => null, 'deviceUUID' => 'abc'];

            return [
                $instance->addAppInstallationMetric($metric),
                $instance->addAppLoadedMetric($metric),
                $instance->addAppPageNavigationMetric($metric),
                $instance->addAppMcommerceProductNavigationMetric($metric),
                $instance->addAppMcommerceProductSoldMetric($metric),
            ];
        });
        t_equal([], $seen, 'nothing is reported');
        t_equal(5, count($adapter->queries), 'five inserts');
        t_true(strpos($adapter->queries[0], "VALUES ('12','','','','','','','','abc')") !== false, 'null and missing are empty strings');
    },

    '949 values are still escaped' => function () use ($store) {
        [$instance, $adapter] = $store();

        $instance->addAppInstallationMetric(['appId' => "o'brien", 'deviceUUID' => 'a\\b']);
        t_true(strpos($adapter->queries[0], "'o\\'brien'") !== false, 'quote escaped');
        t_true(strpos($adapter->queries[0], "'a\\\\b'") !== false, 'backslash escaped');
    },
];
