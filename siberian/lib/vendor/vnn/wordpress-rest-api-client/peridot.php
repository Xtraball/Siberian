<?php

use Evenement\EventEmitterInterface;
use Peridot\Plugin\Prophecy\ProphecyPlugin;

return function (EventEmitterInterface $eventEmitter) {
    $eventEmitter->on('peridot.start', function (\Peridot\Console\Environment $environment) {
        $environment->getDefinition()->getArgument('path')->setDefault('specs');
    });

    new ProphecyPlugin($eventEmitter);
};

