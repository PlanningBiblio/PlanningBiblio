<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Model\Position;
use App\Model\PlanningPositionLines;

class PositionRepository extends EntityRepository
{
    public $site=null;

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

    public function all($sort="nom", $name=null, $group=null)
    {
        // Floors
        $floors = array();
        $db=new \db();
        $db->sanitize_string = false;
        $db->select("select_etages");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $floors[$elem['id']] = $elem['valeur'];
            }
        }

        $where=array("supprime"=>null);

        if ($this->site) {
            $where["site"]=$this->site;
        }

        //	Select All
        $db=new \db();
        $db->select2("postes", null, $where, "ORDER BY $sort");

        $all=array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $all[$elem['id']]=$elem;
                $all[$elem['id']]['etage'] = $floors[$elem['etage']] ?? '';
            }
        }

        //	By default $result=$all
        $result=$all;

        //	If name, keep only matching results
        if (!empty($all) and $name) {
            $result=array();
            foreach ($all as $elem) {
                if (pl_stristr($elem['nom'], $name)) {
                    $result[$elem['id']]=$elem;
                }
            }
        }

        return $result;
    }
}
