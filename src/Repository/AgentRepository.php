<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Model\HiddenTables;
use App\Model\PlanningPositionCells;
use App\Model\PlanningPositionLines;
use App\Model\PlanningPositionHours;
use App\Model\PlanningPositionTabGroup;

class AgentRepository extends EntityRepository
{
    public function purge()
    {
        $agents = $this->findBy(['supprime'] => 2);
        $entityManager = $this->getEntityManager();

        foreach ($agents as $agent) {
            $delete = true;
            $perso_id = $agent->perso_id();
        =   $classes = [Absence::class, 
                        CompTime::class,
                        Detached::class,
                        HiddenTables::class,
                        Holiday::class, 
                        HolidayCET::class,
                        PlanningNote::class,
                        PlanningPosition::class,
                        PlanningPositionModel::class,
                        RecurringAbsence::class, 
                        SaturdayWorkingHours::class,
                        WeekPlanning::class
                       ];
                        
                        
            foreach ($classes as $class) {
                $objects = $entityManager->getRepository($class)->findBy(['perso_id' => $perso_id]);
                if ($objects) { $delete = false; break; }
            }

            // Special cases:
            // PlanningPositionLock::class,
            // Responsables

        }


        $builder = $entityManager->createQueryBuilder();
        $builder->delete()
                ->from(PlanningPositionTabGroup::class, 'a')
                ->where('a.lundi = :tableau')
                ->OrWhere('a.mardi = :tableau')
                ->OrWhere('a.mercredi = :tableau')
                ->OrWhere('a.jeudi = :tableau')
                ->OrWhere('a.vendredi = :tableau')
                ->OrWhere('a.samedi = :tableau')
                ->OrWhere('a.dimanche = :tableau')
                ->setParameter('tableau', $tableau);
        $results = $builder->getQuery()->getResult();

        $entityManager->remove($planningPositionTab);
    }

}
