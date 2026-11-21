<?php

namespace Siberian;

/**
 * Class \Siberian\AlertCenter
 *
 * Turns the backoffice dashboard's separate "something needs attention" signals into one ordered list, so a
 * single card can show them instead of a stack of independent banners scattered across the page.
 *
 * Framework-free on purpose, like ErrorReporter and Module\Quarantine: it only combines values it is given, it
 * never reads a file, a database, the registry or the network itself. It also never translates anything: an item
 * carries an `id` (and whatever raw data the id needs) and the view picks the translated title/message for that
 * id, the same way the existing quarantine banner already switches on `item.reason` instead of receiving
 * pre-translated prose from the model. Core_Model_AlertCenter (app/sae/modules/Core/Model/AlertCenter.php) is the
 * glue that gathers the signals from the framework and calls build().
 *
 * This is phase 1 of 2: building the card first, without removing the equivalent banners from the top of the
 * dashboard yet (that migration is a small follow-up once the card has been seen live). Signals with a per-request
 * network cost of their own are deliberately left out of this phase, so the new card never doubles an existing
 * live call:
 *  - the SSL certificate expiry check (a live TLS handshake against the current host), today a separate async
 *    call from the dashboard already, kept that way;
 *  - the "unstable version" notice (a call to the update provider), today computed inline once per dashboard load;
 *    folding it in here would make it run a second time until the inline copy is removed.
 * Both are natural additions to make in that follow-up, once there is only one place left asking for them.
 */
class AlertCenter
{
    /**
     * @param array $signals {
     *     @var bool        $iconv_missing      extension_loaded('iconv') is false
     *     @var bool        $php_must_upgrade    !PhpSupport::isSupported()
     *     @var bool        $php_should_upgrade  PhpSupport::isSupported() && !PhpSupport::isRecommended()
     *     @var string      $php_version
     *     @var bool        $mobilcart_warning   __get('mobilcart_warning') === 'show'
     *     @var array       $quarantine          Siberian\Module\Quarantine::summary()
     *     @var array       $app_errors          Core_Model_ErrorReports::appErrors()
     * }
     * @return array {counts: {danger: int, warning: int, info: int}, items: array[]}
     */
    public static function build(array $signals): array
    {
        $items = [];

        if (!empty($signals['iconv_missing'])) {
            $items[] = [
                'id' => 'iconv',
                'severity' => 'warning',
            ];
        }

        if (!empty($signals['php_must_upgrade'])) {
            $items[] = [
                'id' => 'php_support',
                'severity' => 'danger',
                'version' => $signals['php_version'] ?? PHP_VERSION,
            ];
        } elseif (!empty($signals['php_should_upgrade'])) {
            $items[] = [
                'id' => 'php_support',
                'severity' => 'info',
                'version' => $signals['php_version'] ?? PHP_VERSION,
            ];
        }

        if (!empty($signals['mobilcart_warning'])) {
            $items[] = [
                'id' => 'mobilcart',
                'severity' => 'warning',
            ];
        }

        $quarantine = $signals['quarantine'] ?? [];
        $quarantined = (int) ($quarantine['quarantined'] ?? 0);
        if ($quarantined > 0) {
            $items[] = [
                'id' => 'quarantine',
                'severity' => 'danger',
                'count' => $quarantined,
                'total' => (int) ($quarantine['total'] ?? 0),
                'modules' => $quarantine['items'] ?? [],
            ];
        } elseif (!empty($quarantine['scan_outdated'])) {
            $items[] = [
                'id' => 'quarantine_scan',
                'severity' => 'info',
            ];
        }

        $appErrors = $signals['app_errors'] ?? [];
        $modules = $appErrors['modules'] ?? [];
        if (!empty($appErrors['enabled']) && count($modules) > 0) {
            $hasWarning = false;
            foreach ($modules as $module) {
                if (!empty($module['warning'])) {
                    $hasWarning = true;
                    break;
                }
            }
            $items[] = [
                'id' => 'app_errors',
                'severity' => $hasWarning ? 'danger' : 'warning',
                'count' => count($modules),
                'modules' => $modules,
            ];
        }

        $counts = ['danger' => 0, 'warning' => 0, 'info' => 0];
        foreach ($items as $item) {
            if (isset($counts[$item['severity']])) {
                $counts[$item['severity']]++;
            }
        }

        return [
            'counts' => $counts,
            'items' => $items,
        ];
    }
}
