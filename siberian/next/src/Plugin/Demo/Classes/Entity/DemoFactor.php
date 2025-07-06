<?php

declare(strict_types=1);

namespace App\Plugin\Demo\Classes\Entity;

use App\Plugin\Demo\Classes\Repository\DemoFactorRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: DemoFactorRepository::class)]
#[ORM\Table(name: 'demo_factor')]
class DemoFactor
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', nullable: true)]
//    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
//    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $uuid = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private float $factor = 0.00;

    #[ORM\Column(type: 'bigint', nullable: false)]
    private int $power = 0;

    #[ORM\Column(type: 'timestamp', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'timestamp', nullable: false)]
    private \DateTime $updatedAt;

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(?Uuid $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    public function setFactor(float $factor): self
    {
        $this->factor = $factor;
        return $this;
    }

    public function getPower(): int
    {
        return $this->power;
    }

    public function setPower(int $power): self
    {
        $this->power = $power;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}