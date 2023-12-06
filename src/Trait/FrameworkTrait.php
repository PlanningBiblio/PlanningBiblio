<?php

namespace App\Trait;

use App\Model\Model;
use App\Model\PlanningPositionTab;

trait FrameworkTrait
{
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
