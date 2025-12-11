<?php

namespace App\Repository;

use App\Entity\SaturdayWorkingHours;
use DateTime;
use Doctrine\ORM\EntityRepository;

class SaturdayWorkingHoursRepository extends EntityRepository
{
    public function update(array $workingHours, string $start, string $end, int $user) :void
    {
        self::deleteBetweenWeeks($start, $end, $user);
        self::insert($workingHours, $user);
    }

    private function deleteBetweenWeeks($start, $end, $user)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->delete(SaturdayWorkingHours::class, 's')
            ->where('s.semaine >= :debut')
            ->andWhere('s.semaine <= :fin')
            ->andWhere('s.perso_id = :perso_id')
            ->setParameter('debut', $start)
            ->setParameter('fin', $end)
            ->setParameter('perso_id', $user)
            ->getQuery();

        $query->execute();
    }

    private function insert(array $workingHours, int $user)
    {
        $entityManager = $this->getEntityManager();

        foreach ($workingHours as $elem) {

            $entry = new SaturdayWorkingHours();

            if (!is_array($elem)) {
                // Si config['EDTSamedi'] == 1 (Emploi du temps différent les semaines avec samedi travaillé)
                $entry->setUserId($user);
                $date = new DateTime($elem);
                $entry->setWeek($date);
                $entry->setTable(2);
            } else {
                // Si config['EDTSamedi'] == 2 (Emploi du temps différent les semaines avec samedi travaillé et en ouverture restreinte)
                $entry->setUserId($user);
                $date = new DateTime($elem[0]);
                $entry->setWeek($date);
                $entry->setTable($elem[1]);
            }

            $entityManager->persist($entry);
        }

        $entityManager->flush();
    }

}
