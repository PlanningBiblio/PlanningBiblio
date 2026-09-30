<?php

namespace App\Repository;

use App\Entity\PublicHoliday;
use Doctrine\ORM\EntityRepository;

class PublicHolidayRepository extends EntityRepository
{
    public function findYears(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('p.annee')
            ->groupBy('p.annee')
            ->getQuery()
            ->getResult();

        $years = [];
        foreach ($result as $elem) {
            $years[] = $elem['annee'];
        }

        return $years;
    }
}
