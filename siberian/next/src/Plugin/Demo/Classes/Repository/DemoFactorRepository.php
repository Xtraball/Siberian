<?php

declare(strict_types=1);

namespace App\Plugin\Demo\Classes\Repository;

use App\Plugin\Demo\Classes\Entity\DemoFactor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DemoFactorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemoFactor::class);
    }
}