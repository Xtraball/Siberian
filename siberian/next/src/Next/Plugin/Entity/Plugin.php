<?php

namespace App\Next\Plugin\Entity;

use App\Next\Plugin\Repository\PluginRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PluginRepository::class)]
class Plugin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $version = '0.0.1';

    #[ORM\Column(length: 10000)]
    private string $description = '';

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $installed = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $enabled = false;

    #[ORM\Column(length: 255)]
    private ?string $handle = null;

    private bool $upgradable = false;

    private string $latestVersion = '';

    #[ORM\Column(type: 'json', nullable: true, options: ['default' => '[]'])]
    private ?array $settings = null;

    /**
    getters and setters...
     *
     */
}