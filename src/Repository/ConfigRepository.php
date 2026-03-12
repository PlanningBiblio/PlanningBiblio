<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConfigRepository extends ServiceEntityRepository
{

   public function __construct(ManagerRegistry $registry)
   {
       parent::__construct($registry, Config::class);
   }

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

    public function getParam($name) {
        $configOption = $this->findOneBy(['nom' => $name]);
        return $configOption->getValue();
    }

    public function setParam($name, $value, $technical = 0)
    {
        $GLOBALS['config'][$name] = $value;
        $param = $this->findOneBy(['nom' => $name]);

        if (!$param) {
            # error
        } else {
            $param->setValue($value);
            $param->setTechnical($technical);
        }
    }

}
