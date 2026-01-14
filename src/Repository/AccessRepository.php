<?php

namespace App\Repository;

use App\Entity\Access;
use Doctrine\ORM\EntityRepository;

class AccessRepository extends EntityRepository
{
    /**
     * Find access filtered by group id.
     *
     * This method returns access whose "groupe_id" value donesn't equal 99 or 100
     * grouped by its label(column "groupe").
     * 
     * @return array List of matching access
     */
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
