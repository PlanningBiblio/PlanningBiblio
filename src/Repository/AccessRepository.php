<?php

namespace App\Repository;

use App\Entity\Access;
use Doctrine\ORM\EntityRepository;

class AccessRepository extends EntityRepository
{
    public function findGroupesAcces(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.groupe_id, a.groupe, a.categorie, a.ordre')
            ->where('a.groupe_id NOT IN (:excluded)')
            ->setParameter('excluded', [99, 100])
            ->groupBy('a.groupe')
            ->getQuery()
            ->getArrayResult();
    }
}
