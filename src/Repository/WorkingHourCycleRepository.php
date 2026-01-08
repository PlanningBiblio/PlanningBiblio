<?php

namespace App\Repository;

use App\Entity\Config;
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
    public function findFirstWeek($date, $site): ?string
    {
        $entityManager = $this->getEntityManager();
        $configResetCycles = $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'PlanningHebdo-resetCycles'])->getValue();

        if ($configResetCycles == 0) {
            return null;
        }

        $results = $this->createQueryBuilder('w')
            ->andWhere('w.date <= :date')
            ->setParameter('date', $date)
            ->orderBy('w.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        foreach ($results as $elem) {
            if (in_array($site, $elem->getSites())
                or empty($elem->getSites())
                or $configResetCycles == 1
            ) {
                $result = $elem;
                break;
            }
        }

        if (empty($result)) {
            return null;
        }

        $offset = $result->getWeek() - 1;
        $firstWeekDate = date('Y-m-d', strtotime($result->getDate()->format('Y-m-d') . " - $offset week"));

        return $firstWeekDate;
    }
}
