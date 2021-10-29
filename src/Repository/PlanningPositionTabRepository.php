<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Model\HiddenTables;
use App\Model\PlanningPositionCells;
use App\Model\PlanningPositionLines;
use App\Model\PlanningPositionHours;
use App\Model\PlanningPositionTabGroup;

class PlanningPositionTabRepository extends EntityRepository
{
    public function purge($id)
    {
        $planningPositionTab = $this->find($id);
        $tableau = $planningPositionTab->tableau();
        $entityManager = $this->getEntityManager();

        $this->removeObjects(HiddenTables::class,          'tableau', $tableau);
        $this->removeObjects(PlanningPositionCells::class, 'numero',  $tableau);
        $this->removeObjects(PlanningPositionHours::class, 'numero',  $tableau);
        $this->removeObjects(PlanningPositionLines::class, 'numero',  $tableau);
        
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

    private function removeObjects($class, $field, $value) {
        $entityManager = $this->getEntityManager();
        $objects = $entityManager->getRepository($class)->findBy([$field => $value]);
        foreach ($objects as $object) {
            $entityManager->remove($object);
        }
    }
}
