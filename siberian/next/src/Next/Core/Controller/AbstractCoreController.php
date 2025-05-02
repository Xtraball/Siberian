<?php

namespace App\Next\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCoreController extends AbstractController
{
    public string $templateNamespace = 'next/core';

    # Override render() to automatically prefix the template with the namespace handle
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $view = $this->templateNamespace . '/' . $view;
        return parent::render($view, $parameters, $response);
    }
}