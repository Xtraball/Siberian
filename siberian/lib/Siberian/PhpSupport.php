<?php

namespace Siberian;

/**
 * Class \Siberian\PhpSupport
 *
 * The PHP versions Siberian runs on. One place for the installer and the backoffice.
 */
class PhpSupport
{
    /**
     * The lowest supported PHP version: the first release line that still receives the security fixes.
     */
    public const MINIMUM = '8.2.0';

    /**
     * The PHP version we advise to run.
     */
    public const RECOMMENDED = '8.5.0';

    /**
     * @param string|null $version
     * @return bool
     */
    public static function isSupported(?string $version = null): bool
    {
        return version_compare($version ?? PHP_VERSION, self::MINIMUM, '>=');
    }

    /**
     * @param string|null $version
     * @return bool
     */
    public static function isRecommended(?string $version = null): bool
    {
        return version_compare($version ?? PHP_VERSION, self::RECOMMENDED, '>=');
    }
}
