<?php

namespace App\Next\Plugin\Controller;

use App\Next\Core\Controller\AbstractCoreController;
use App\Next\Plugin\Service\PluginManager;

abstract class AbstractPluginController extends AbstractCoreController
{
    public string $templateNamespace = 'next/plugin';

    public function __construct(
        protected PluginManager $pluginManager,
    )
    {
    }
}