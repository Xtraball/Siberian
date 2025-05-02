<?php

namespace App\Next\Plugin\Repository;

use App\Next\Plugin\Entity\Plugin;
use Doctrine\Persistence\ManagerRegistry;

// extend doctrine repository
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class PluginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plugin::class);
    }

    public function findOneByHandle(string $handle): ?Plugin
    {
        return $this->createQueryBuilder('p')
            ->where('p.handle = :handle')
            ->setParameter('handle', $handle)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getEnabledPlugins(): array
    {
        return [];

//        return $this->createQueryBuilder('p')
//            ->where('p.enabled = true')
//            ->getQuery()
//            ->getResult()
//        ;
    }
}