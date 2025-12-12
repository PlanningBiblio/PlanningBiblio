<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use App\Model\Agent;
use App\Model\Position;
use App\Model\Skill;

class SkillRepository extends EntityRepository
{
    public function purge($id)
    {
        $entityManager = $this->getEntityManager();
        $all_skills = $entityManager->getRepository(Position::class)->getAllSkills();

        if (in_array($id, $all_skills)) {
            return 0;
        }

        $all_skills = $entityManager->getRepository(Agent::class)->getAllSkills();

        if (in_array($id, $all_skills)) {
            return 0;
        }

        $skill = $entityManager->getRepository(Skill::class)->find($id);
        $entityManager->remove($skill);
        $entityManager->flush();
        return 1;
    }

    public function purgeAll($limit_date) {
        $entityManager = $this->getEntityManager();
        $builder = $entityManager->createQueryBuilder();
        $builder->select('a')
                ->from(Skill::class, 'a')
                ->andWhere('a.supprime < :limit_date')
                ->andWhere('a.supprime IS NOT NULL')
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();
        $deleted_skill = 0;
        foreach ($results as $result) {
            $deleted = $this->purge($result->id());
            if ($deleted) $deleted_skill++;
        }
        return $deleted_skill;
    }

}
