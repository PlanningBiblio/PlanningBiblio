<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Model\Position;
use App\Model\PlanningPositionLines;

class PositionRepository extends EntityRepository
{
    public function getAllSkills() {
        $entityManager = $this->getEntityManager();
        $positions = $entityManager->getRepository(Position::class)->findAll();
        $all_skills = array();
        foreach ($positions as $position) {
            $activites = $position->activites();
            if (is_array($activites)) {
                foreach ($activites as $activite) {
                    array_push($all_skills, $activite);
                }
            }
        }
        $all_skills = array_unique($all_skills);
        return $all_skills;
    }

    public function purgeAll($limit_date) {
        $entityManager = $this->getEntityManager();
        $builder = $entityManager->createQueryBuilder();
        $builder->select('a')
                ->from(Position::class, 'a')
                ->andWhere('a.supprime < :limit_date')
                ->andWhere('a.supprime IS NOT NULL')
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();
        $deleted_position = 0;
        foreach ($results as $result) {
            $builder = $entityManager->createQueryBuilder();
            $builder->select('a')
                    ->from(PlanningPositionLines::class, 'a')
                    ->andWhere("a.type = 'poste'")
                    ->andWhere('a.poste = :id')
                    ->setParameter('id', $result->id());
            $lines = $builder->getQuery()->getResult();
            if (sizeof($lines) == 0) {
                $entityManager->remove($result);
                $deleted_position++;
            }
        }
        $entityManager->flush();
        return $deleted_position;
    }
}
