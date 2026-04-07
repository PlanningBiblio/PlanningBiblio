<?php

namespace App\Planno;

use App\Entity\NetworkConfig;
use App\Entity\TechnicalConfig;
use Doctrine\ORM\EntityManagerInterface;

class ConfigFinder
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function getAll(string $entityClass, ?int $networkId = null): array
    {
        $entities = $this->findByType($entityClass, $networkId);

        $config = [];
        foreach ($entities as $elem) {
            $config[$elem->getConfig()->getName()] = $elem->getValue();
        }
        $config['dbprefix'] = $_ENV['DATABASE_PREFIX'];
        $config['secret'] = $_ENV['APP_SECRET'];

        return $config;
    }

    public function findByType(string $entityClass, ?int $networkId = null): array
    {
        if (!is_a($entityClass, NetworkConfig::class, true) && !is_a($entityClass, TechnicalConfig::class, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported entity class: %s', $entityClass));
        }

        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->innerJoin('e.config', 'c')
            ->orderBy('c.categorie', 'ASC')
            ->addOrderBy('c.ordre', 'ASC')
            ->addOrderBy('c.id', 'ASC');

        if ($networkId !== null && is_a($entityClass, NetworkConfig::class, true)) {
            $qb->andWhere('IDENTITY(e.network) = :networkId')
                ->setParameter('networkId', $networkId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneByConfigName(string $entityClass, string $name, ?int $networkId = null) : NetworkConfig|TechnicalConfig
    {
        if (!is_a($entityClass, NetworkConfig::class, true) && !is_a($entityClass, TechnicalConfig::class, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported entity class: %s', $entityClass));
        }

        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->innerJoin('e.config', 'c')
            ->andWhere('c.nom = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);

        if ($networkId !== null && is_a($entityClass, NetworkConfig::class, true)) {
            $qb->andWhere('IDENTITY(e.network) = :networkId')
                ->setParameter('networkId', $networkId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}