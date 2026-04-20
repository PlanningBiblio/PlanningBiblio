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

    public function getParams($technical): array
    {
        return $this->findBy(
                array('technical' => $technical),
                array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
            );
    }

    public function getParam($name) {
        return $this->findOneBy(['nom' => $name]);
    }

    public function getValue($name) {
        return $this->getParam($name)->getValue();
    }

    public function setParam($name, $value, $technical = 0): void
    {
        // FIXME: The variable `$GLOBALS['config']` is intended for unit tests, which continue to use it.
        // Remove this line when the unit tests will no longer use it.
        $GLOBALS['config'][$name] = $value;

        $param = $this->findOneBy(['nom' => $name]);

        if (!$param) {
            # error
        } else {
            $param->setValue($value);
            // FIXME: $technical is not supposed to change, but unit tests fail if it is not forced here.
            $param->setTechnical($technical);
        }
    }
}
