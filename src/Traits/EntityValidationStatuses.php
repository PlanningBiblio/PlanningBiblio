<?php

namespace App\Traits;

use App\PlanningBiblio\ValidationAwareEntity;
use App\Entity\Agent;
use App\Entity\ConfigParam;

trait EntityValidationStatuses
{
    public function getStatusesParams($agent_ids, $module, $entity_id = null, String $workflow = 'A')
    {
        if (!$agent_ids) {
            throw new \Exception("EntityValidationStatuses::getStatusesParams: No agent");
        }

        $show_select = false;

        $entity = new ValidationAwareEntity($module, $entity_id);
        list($entity_state, $entity_state_desc) = $entity->status();

        // At this point, overtime entities
        // and holiday are treated the same.
        // This was not the case in ValidationAwareEntity.
        $module = $module == 'overtime' ? 'holiday' : $module;

        $adminN1 = true;
        $adminN2 = true;
        foreach ($agent_ids as $id) {
            list($N1, $N2) = $this->entityManager
                ->getRepository(Agent::class)
                ->setModule($module)
                ->forAgent($id)
                ->getValidationLevelFor($_SESSION['login_id'], $workflow);

            $adminN1 = $N1 ? $adminN1 : false;
            $adminN2 = $N2 ? $adminN2 : false;
        }
        $show_select = $adminN1 || $adminN2;
        $show_n1 = $adminN1 || $adminN2;
        $show_n2 = $adminN2;

        // Simplified absence validation schema for workflow B
        $configByAgent = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => 'Absences-notifications-agent-par-agent'])
            ->getValue();

        if ($module == 'absence' && $configByAgent && $workflow == 'B') {
            $show_n1 = false;
        } 

        // Only adminN2 can change statuses of
        // validated N2 entities.
        if (in_array($entity_state, [1, -1]) && !$adminN2) {
            $show_select = false;
        }

        // Prevent user without right L1 to directly validate l2
        if (!$adminN1 && $entity_state == 0 && $entity->needsValidationL1()) {
            $show_select = false;
        }

        // Accepted N2 holidays cannot be changed.
        if ($entity_state == 1 && $module == 'holiday') {
            $show_select = false;
        }
        $params = array(
            'entity_state_desc' => $entity_state_desc,
            'entity_state'      => $entity_state,
            'show_select'       => $show_select,
            'show_n1'           => $show_n1,
            'show_n2'           => $show_n2,
        );
        return $params;
    }
}
