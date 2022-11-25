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

    public function delete()
    {
        $db=new \db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("activites", array("supprime"=>"SYSDATE"), array("id"=>$this->id));
    }

    public function all()
    {
        $activites=array();
        $db=new \db();
        $db->sanitize_string = false;
        if ($this->deleted) {
            $db->select2("activites");
        } else {
            $db->select2("activites", null, array("supprime"=>null));
        }
      
        if ($db->result) {
            $activites=$db->result;
        }
    
        usort($activites, "cmp_nom");
    
        $tmp=array();
        foreach ($activites as $elem) {
            $tmp[$elem['id']]=$elem;
        }
        $activites=$tmp;
        $this->elements=$activites;
    }

}
