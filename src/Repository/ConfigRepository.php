<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<Config>
 */
class ConfigRepository extends EntityRepository
{
    /**
     * @return Config[] Returns an array of Config objects
     */
    public function getAll(): array {
        $entityManager = $this->getEntityManager();
        $entities = $entityManager->getRepository(Config::class)->findAll();

        $config = [];
        foreach ($entities as $elem) {
            $config[$elem->getName()] = $elem->getValue();
        }

        $config['dbprefix'] = $_ENV['DATABASE_PREFIX'];
        $config['secret'] = $_ENV['APP_SECRET'];

        return $config;
    }
}
