<?php

namespace App\Repository;

use App\Entity\PublicHoliday;
use Doctrine\ORM\EntityRepository;

class PublicHolidayRepository extends EntityRepository
{
    public function findByDateRange($start, $end): array
    {
        $dBResult = $this->createQueryBuilder('p')
            ->where('p.jour BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($dBResult as $elem) {
            $result[$elem->getDay()->format('Y-m-d')] = $elem;
        }

        return $result;
    }

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
