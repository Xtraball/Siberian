<?php

namespace App\Next\Plugin\Controller;

use App\Next\Core\Controller\AbstractCoreController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

abstract class AbstractPluginController extends AbstractCoreController
{
    public string $templateNamespace = 'next/plugin';

    public function __construct(Environment $twig)
    {
        //
    }

    /**
     * Set the container for this controller.
     * This ensures the controller behaves like native Symfony controllers.
     *
     * @param ContainerInterface $container
     * @return ContainerInterface|null
     */
    #[Required]
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        return parent::setContainer($container);
    }
}
