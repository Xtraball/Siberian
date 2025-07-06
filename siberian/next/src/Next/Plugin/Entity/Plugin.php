<?php

declare(strict_types=1);

namespace App\Next\Plugin\Entity;

use App\Next\Plugin\Repository\PluginRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PluginRepository::class)]
#[ORM\Table(name: 'next_plugin')]
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

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): void
    {
        $this->settings = $settings;
    }

    public function hasSettings(): bool
    {
        return !empty($this->settings);
    }

    public function getLatestVersion(): string
    {
        return $this->latestVersion;
    }

    public function setLatestVersion(string $latestVersion): void
    {
        $this->latestVersion = $latestVersion;
    }

    public function isUpgradable(): bool
    {
        return $this->upgradable;
    }

    public function setUpgradable(bool $upgradable): void
    {
        $this->upgradable = $upgradable;
    }

    public function getHandle(): ?string
    {
        return $this->handle;
    }

    public function setHandle(?string $handle): void
    {
        $this->handle = $handle;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isInstalled(): ?bool
    {
        return $this->installed;
    }

    public function setInstalled(?bool $installed): void
    {
        $this->installed = $installed;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}