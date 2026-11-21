<?php

/**
 * Class Core_Model_AlertCenter
 *
 * Glue between the application (extensions, PHP version, module config, quarantine, error reports) and the
 * framework-free Siberian\AlertCenter. See Siberian\AlertCenter's own docblock for the phased plan and for why
 * the SSL and "unstable version" checks are not gathered here yet.
 */
class Core_Model_AlertCenter
{
    /**
     * @return array {counts: {danger, warning, info}, items: array[]}
     */
    public static function collect(): array
    {
        $signals = [
            'iconv_missing' => !extension_loaded('iconv'),
            'php_must_upgrade' => !\Siberian\PhpSupport::isSupported(),
            'php_should_upgrade' => \Siberian\PhpSupport::isSupported() && !\Siberian\PhpSupport::isRecommended(),
            'php_version' => PHP_VERSION,
            'mobilcart_warning' => __get('mobilcart_warning') === 'show',
            'quarantine' => \Siberian\Module\Quarantine::summary(),
            'app_errors' => Core_Model_ErrorReports::appErrors(),
        ];

        return \Siberian\AlertCenter::build($signals);
    }
}
