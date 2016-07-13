<?php

class Siberian_Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.1.12';

    static function is($type) {
        return self::TYPE == strtoupper($type);
    }
}
