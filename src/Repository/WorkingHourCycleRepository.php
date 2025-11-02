<?php

namespace App\Repository;

use App\Entity\WorkingHourCycle;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends EntityRepository<WorkingHourCycle>
 */
class WorkingHourCycleRepository extends EntityRepository
{

    /**
    * @return $result Returns an array of WorkingHourCycle
    */
    public function findBetween($start, $end): array
    {
        $start = $start ? preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3-$2-$1", $start) : date('Y-01-01', strtotime('2 years ago'));
        $end = $end ? preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3-$2-$1", $end) : '';

        $query = $this->createQueryBuilder('w')
            ->andWhere('w.date >= :start')
            ->setParameter('start', $start);

        if ($end) {
            $query->andWhere('w.date <= :end')
                ->setParameter('end', $end);
        }

        $query->orderBy('w.date', 'ASC');
        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
    * @return $firstWeekDate Returns the first week of a cycle.
    */
    public function findFirstWeek($date): ?string
    {
        $result = $this->createQueryBuilder('w')
            ->andWhere('w.date <= :date')
            ->setParameter('date', $date)
            ->orderBy('w.date', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        if (!$result) {
            return null;
        }

        $offset = $result[0]->getWeek() - 1;
        $firstWeekDate = date('Y-m-d', strtotime($result[0]->getDate()->format('Y-m-d') . " - $offset week"));

        return $firstWeekDate;
    }
}
