<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Config>
 */
class ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }

    /**
     * @return Config[] Returns an array of Config objects
     */
    public function getAll() {
        $entityManager = $this->getEntityManager();
        $entities = $entityManager->getRepository(Config::class)->findAll();

        $config = [];
        foreach ($entities as $elem) {
            $config[$elem->getName()] = $elem->getValue();
        }

        return $config;
    }
}
