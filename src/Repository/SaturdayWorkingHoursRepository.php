<?php

namespace App\Repository;

use App\Entity\SaturdayWorkingHours;
use Doctrine\ORM\EntityRepository;

class SaturdayWorkingHoursRepository extends EntityRepository
{
    public function deleteEdtSamediBetweenWeeks($debut, $fin, $perso_id)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->delete(SaturdayWorkingHours::class, 's')
            ->where('s.semaine >= :debut')
            ->andWhere('s.semaine <= :fin')
            ->andWhere('s.perso_id = :perso_id')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('perso_id', $perso_id)
            ->getQuery();

        $query->execute();
    }

    public function insertEdtSamedi(array $eDTSamedi, int $perso_id)
    {
        $entityManager = $this->getEntityManager();

        foreach ($eDTSamedi as $elem) {

            $entry = new SaturdayWorkingHours();

            if (!is_array($elem)) {
                // Si config['EDTSamedi'] == 1 (Emploi du temps différent les semaines avec samedi travaillé)
                $entry->setUserId($perso_id);
                $entry->setWeek($elem);
                $entry->setTable(2);
            } else {
                // Si config['EDTSamedi'] == 2 (Emploi du temps différent les semaines avec samedi travaillé et en ouverture restreinte)
                $entry->setUserId($perso_id);
                $entry->setWeek($elem[0]);
                $entry->setTable($elem[1]);
            }

            $entityManager->persist($entry);
        }

        $entityManager->flush();
    }

}
