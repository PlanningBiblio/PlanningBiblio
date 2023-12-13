<?php

namespace App\Trait;

use App\Model\Model;
use App\Model\PlanningPositionTab;

trait FrameworkTrait
{

    protected function getAllFrameworks(String $site = 'all', $deleted = false)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('f')
            ->from(PlanningPositionTab::class, 'f')
            ->where('f.updated is NULL')
            ->orderBy('f.nom', 'ASC');

        if ($site != 'all') {
            $site = (int) $site;
            $qb->andWhere('f.site = :site')
                ->setParameter('site', $site);
        }

        if ($deleted) {
            $deleted = date('Y-m-d H:i:s', strtotime('- 1 year'));
            $qb->andWhere('f.supprime >= :deleted')
                ->setParameter('deleted', $deleted);
        } else {
            $qb->andWhere('f.supprime is NULL');
        }

        $frameworks = $qb->getQuery()->getResult();

        return $frameworks;
    }

    protected function getLatestFrameworkCopy(int $modelId)
    {
        $copiesExist = false;
        $lastCopies = array();

        $modelElements = $this->entityManager->getRepository(Model::class)
            ->findBy(
                array('model_id' => $modelId),
                array('jour' => 'asc')
            );

        foreach($modelElements as $elem) {
            $em = $this->entityManager->getRepository(PlanningPositionTab::class)
                ->findOneBy(
                    array('origin' => $elem->tableau(), 'supprime' => null),
                    array('updated_at' => 'desc')
                );
            $lastCopies[] = $em ? $em->tableau() : 0;
            if ($em) {
                $copiesExist = true;
            }
        }

        return (object) array(
            'copies' =>$lastCopies,
            'copiesExist' => $copiesExist,
        );
    }
}
