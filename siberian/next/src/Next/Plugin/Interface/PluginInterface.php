<?php

namespace App\Next\Plugin\Interface;

use App\Next\Plugin\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag('app.plugin')]
#[AutoconfigureTag('controller.service_arguments')]
interface PluginInterface
{
    public static function getName(): string;

    public static function getVersion(): string;

    public static function getAuthor(): string;

    public static function getDescription(): string;

    public function install(): void;

    public function uninstall(): void;

    public function enable(): void;

    public function disable(): void;

    public function upgrade(): void;

    public function manage(Request $request, Plugin $plugin): ?Response;

    public function boot(): void;
}