<?php
/**
 * Siberian\AlertCenter::build() is the Error/Take-Care control center (P3, phase 1): it turns the dashboard's
 * separate "needs attention" signals (iconv, PHP support, a known-bad module, module quarantine, errors on app
 * calls) into one ordered list with counts by severity, so a single card can show them instead of a stack of
 * independent banners. It is framework-free and never translates anything: callers get an `id` + raw data per
 * item, translation happens in the view (the same way the existing quarantine banner already switches on
 * `item.reason`).
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
require_once $siberian . '/lib/Siberian/AlertCenter.php';

use Siberian\AlertCenter;

function ac_ids(array $result): array
{
    return array_map(static function (array $item) {
        return $item['id'];
    }, $result['items']);
}

return [
    'nothing to report gives an empty list and all counts at zero' => function () {
        $result = AlertCenter::build([]);
        t_equal([], $result['items']);
        t_equal(['danger' => 0, 'warning' => 0, 'info' => 0], $result['counts']);
    },

    'a missing iconv extension is a warning' => function () {
        $result = AlertCenter::build(['iconv_missing' => true]);
        t_equal(['iconv'], ac_ids($result));
        t_equal('warning', $result['items'][0]['severity']);
        t_equal(1, $result['counts']['warning']);
    },

    'an unsupported PHP version is a danger, and takes priority over the recommended-version notice' => function () {
        $result = AlertCenter::build([
            'php_must_upgrade' => true,
            'php_should_upgrade' => true, // a caller should never send both true, but the danger case must still win
            'php_version' => '8.1.2',
        ]);
        t_equal(['php_support'], ac_ids($result));
        t_equal('danger', $result['items'][0]['severity']);
        t_equal('8.1.2', $result['items'][0]['version']);
    },

    'a merely outdated but still supported PHP version is only an info' => function () {
        $result = AlertCenter::build([
            'php_must_upgrade' => false,
            'php_should_upgrade' => true,
            'php_version' => '8.2.9',
        ]);
        t_equal(['php_support'], ac_ids($result));
        t_equal('info', $result['items'][0]['severity']);
        t_equal(1, $result['counts']['info']);
    },

    'a supported and recommended PHP version raises nothing' => function () {
        $result = AlertCenter::build([
            'php_must_upgrade' => false,
            'php_should_upgrade' => false,
            'php_version' => '8.5.1',
        ]);
        t_equal([], ac_ids($result));
    },

    'the known-bad module warning passes through as a warning' => function () {
        $result = AlertCenter::build(['mobilcart_warning' => true]);
        t_equal(['mobilcart'], ac_ids($result));
        t_equal('warning', $result['items'][0]['severity']);
    },

    'quarantined modules are a danger and carry the module list through untranslated' => function () {
        $quarantine = [
            'total' => 5,
            'quarantined' => 2,
            'items' => [
                ['name' => 'Broken1', 'reason' => 'fatal', 'message' => 'boom'],
                ['name' => 'Broken2', 'reason' => 'version', 'message' => 'needs a newer core'],
            ],
            'scan_outdated' => false,
        ];
        $result = AlertCenter::build(['quarantine' => $quarantine]);
        t_equal(['quarantine'], ac_ids($result));
        t_equal('danger', $result['items'][0]['severity']);
        t_equal(2, $result['items'][0]['count']);
        t_equal(5, $result['items'][0]['total']);
        t_equal($quarantine['items'], $result['items'][0]['modules']);
        t_equal(1, $result['counts']['danger']);
    },

    'nothing is quarantined but a scan is outdated is only an info, not a danger' => function () {
        $result = AlertCenter::build([
            'quarantine' => ['total' => 3, 'quarantined' => 0, 'items' => [], 'scan_outdated' => true],
        ]);
        t_equal(['quarantine_scan'], ac_ids($result));
        t_equal('info', $result['items'][0]['severity']);
    },

    'nothing quarantined and no outdated scan raises nothing' => function () {
        $result = AlertCenter::build([
            'quarantine' => ['total' => 3, 'quarantined' => 0, 'items' => [], 'scan_outdated' => false],
        ]);
        t_equal([], ac_ids($result));
    },

    'app errors are a warning normally, a danger once a module crosses the warning threshold' => function () {
        $warning = AlertCenter::build([
            'app_errors' => [
                'enabled' => true,
                'modules' => [['name' => 'SomeModule', 'fatals' => 1, 'errors' => 2, 'warning' => false]],
            ],
        ]);
        t_equal(['app_errors'], ac_ids($warning));
        t_equal('warning', $warning['items'][0]['severity']);
        t_equal(1, $warning['items'][0]['count']);

        $danger = AlertCenter::build([
            'app_errors' => [
                'enabled' => true,
                'modules' => [
                    ['name' => 'QuietModule', 'fatals' => 0, 'errors' => 1, 'warning' => false],
                    ['name' => 'LoudModule', 'fatals' => 9, 'errors' => 0, 'warning' => true],
                ],
            ],
        ]);
        t_equal('danger', $danger['items'][0]['severity']);
        t_equal(2, $danger['items'][0]['count']);
    },

    'app errors disabled, or enabled with nothing recorded, raise nothing' => function () {
        $disabled = AlertCenter::build([
            'app_errors' => ['enabled' => false, 'modules' => [['name' => 'X', 'warning' => true]]],
        ]);
        t_equal([], ac_ids($disabled));

        $empty = AlertCenter::build([
            'app_errors' => ['enabled' => true, 'modules' => []],
        ]);
        t_equal([], ac_ids($empty));
    },

    'every signal at once orders items the same way the dashboard lists them and sums the counts correctly' => function () {
        $result = AlertCenter::build([
            'iconv_missing' => true,
            'php_must_upgrade' => true,
            'php_version' => '8.0.0',
            'mobilcart_warning' => true,
            'quarantine' => ['total' => 2, 'quarantined' => 1, 'items' => [['name' => 'B', 'message' => 'x']], 'scan_outdated' => false],
            'app_errors' => ['enabled' => true, 'modules' => [['name' => 'M', 'warning' => false]]],
        ]);
        t_equal(['iconv', 'php_support', 'mobilcart', 'quarantine', 'app_errors'], ac_ids($result));
        // iconv + mobilcart + app_errors (no module crossed the warning threshold) are warnings; php_support
        // (unsupported) + quarantine are dangers.
        t_equal(['danger' => 2, 'warning' => 3, 'info' => 0], $result['counts']);
    },
];
