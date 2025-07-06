<?php

declare(strict_types=1);

namespace App\Next\Plugin\Repository;

use App\Next\Plugin\Entity\Plugin;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

// extend doctrine repository
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class PluginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, protected EntityManagerInterface $entityManager)
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
        return $this->createQueryBuilder('p')
            ->where('p.enabled = true')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Plugin $plugin, bool $andFlush = true): void
    {
        $this->entityManager->persist($plugin);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }
}