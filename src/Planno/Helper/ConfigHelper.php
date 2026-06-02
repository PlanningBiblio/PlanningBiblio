<?php

namespace App\Planno\Helper;

use App\Entity\ConfigNetwork;
use App\Entity\ConfigTechnical;
use Exception;

class ConfigHelper extends BaseHelper
{
    private function getContextNetworkId(): ?int
    {
        return 1;
    }

    public function saveConfig($params): string
    {
        $technical = $params['technical'];
        $error = '';

        $configParams = $this->findByType($technical);

        foreach ($configParams as $cp) {
            $type = $cp->getConfig()->getType();
            if (in_array($type, ['hidden', 'info'])) {
                continue;
            }

            if (!isset($params[$cp->getConfig()->getName()])) {
                if ($type == 'boolean') {
                    $params[$cp->getConfig()->getName()] = '0';
                } elseif ($type == 'checkboxes') {
                    $params[$cp->getConfig()->getName()] = array();
                } else {
                    continue;
                }
            }
            $value = $params[$cp->getConfig()->getName()];

            if (is_string($value)) {
                $value = trim($value);
            }

            // Passwords
            if ($cp->getConfig()->getType() == 'password') {
                if ($value == '') {
                    continue;
                }
                $value = encrypt($value);
            }
            // Checkboxes
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if ($cp->getConfig()->getType() == 'color') {
                $value = filter_var($value, FILTER_CALLBACK, ['options' => 'sanitize_color']) ?: '';
            }

            try {
                $cp->setValue($value);
                $this->entityManager->persist($cp);
            }
            catch (Exception) {
                $error = 'Une erreur est survenue pendant la modification de la configuration !';
            }
        }
        $this->entityManager->flush();

        return $error;
    }
    public function setParam($name, $value): void
    {
        $GLOBALS['config'][$name] = $value;

        $param = $this->findOneByName($name);

        if (!$param) {
            # error
        } else {
            $param->setValue($value);
            $this->entityManager->persist($param);
            $this->entityManager->flush();
        }
    }

    public function getAll(): array
    {
        $entities = $this->__findBy();
        $networkId = $this->getContextNetworkId();
        if ($networkId) {
            $entities = array_merge($entities, $this->__findBy($networkId));
        }

        $config = [];
        foreach ($entities as $elem) {
            $config[$elem->getConfig()->getName()] = $elem->getValue();
        }

        $config['dbprefix'] = $_ENV['DATABASE_PREFIX'];
        $config['secret'] = $_ENV['APP_SECRET'];

        return $config;
    }

    public function findOneByName(string $name): ConfigNetwork|ConfigTechnical|null
    {
        $networkId = $this->getContextNetworkId();

        if ($networkId) {
            $res = $this->__findOne($name, $networkId);
            if ($res) return $res;
        }

        return $this->__findOne($name);
    }

    public function findBy(?array $where = null): array
    {
        $technical = $this->__findBy(null, $where);
        $networkId = $this->getContextNetworkId();

        if (!$networkId) {
            return $technical;
        }

        $network = $this->__findBy($networkId, $where);

        $indexed = [];
        foreach (array_merge($technical, $network) as $entity) {
            $indexed[$entity->getConfig()->getName()] = $entity;
        }

        return array_values($indexed);
    }

    public function findByType(bool $technical): array
    {
        $networkId = $technical ? null :$this->getContextNetworkId();
        return $this->__findBy($networkId);
    }


    private function __findBy(?int $networkId = null, ?array $where = null): array
    {
        $entityClass = ($networkId !== null) ? ConfigNetwork::class : ConfigTechnical::class;
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->innerJoin('e.config', 'c')
            ->orderBy('c.categorie', 'ASC')
            ->addOrderBy('c.ordre', 'ASC');

        if ($networkId !== null) {
            $qb->andWhere('e.network_id = :networkId')->setParameter('networkId', $networkId);
        }

        if ($where) {
            foreach ($where as $key => $value) {
                $qb->andWhere("c.$key = :$key")->setParameter($key, $value);
            }
        }

        return $qb->getQuery()->getResult();
    }

    private function __findOne(string $name, ?int $networkId = null): ConfigNetwork|ConfigTechnical|null
    {
        $entityClass = ($networkId !== null) ? ConfigNetwork::class : ConfigTechnical::class;
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->innerJoin('e.config', 'c')
            ->andWhere('c.nom = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);

        if ($networkId !== null) {
            $qb->andWhere('e.network_id = :networkId')->setParameter('networkId', $networkId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}