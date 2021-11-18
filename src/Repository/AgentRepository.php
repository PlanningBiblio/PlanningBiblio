<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use App\Model\Absence;
use App\Model\Agent;
use App\Model\CompTime;
use App\Model\Detached;
use App\Model\HiddenTables;
use App\Model\Holiday;
use App\Model\HolidayCET;
use App\Model\PlanningNote;
use App\Model\PlanningPosition;
use App\Model\PlanningPositionLock;
use App\Model\PlanningPositionModel;
use App\Model\RecurringAbsence;
use App\Model\SaturdayWorkingHours;
use App\Model\Supervisor;
use App\Model\WeekPlanning;

class AgentRepository extends EntityRepository
{

    public function getAllSkills() {
        $entityManager = $this->getEntityManager();
        $agents = $entityManager->getRepository(Agent::class)->findAll();
        $all_skills = array();
        foreach ($agents as $agent) {
            $activites = $agent->postes();
            if (is_array($activites)) {
                foreach ($activites as $activite) {
                    array_push($all_skills, $activite);
                }
            }
        }
        $all_skills = array_unique($all_skills);
        return $all_skills;
    }

    public function purgeAll()
    {
        $agents = $this->findBy(['supprime' => '2']);
        $entityManager = $this->getEntityManager();
        $deleted_agents = 0;

        foreach ($agents as $agent) {
            $delete = true;
            $perso_id = $agent->id();
            $classes = [
                Absence::class,
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
                if (count($objects)) { $delete = false; break; }
            }

            // Special cases:
            // PlanningPositionLock::class
            $planningPositionLockCriteria = new \Doctrine\Common\Collections\Criteria();
            $planningPositionLockCriteria
                ->orWhere($planningPositionLockCriteria->expr()->contains('perso', $perso_id))
                ->orWhere($planningPositionLockCriteria->expr()->contains('perso2', $perso_id));

            $planningPositionLocks = $entityManager->getRepository(PlanningPositionLock::class)->matching($planningPositionLockCriteria);
            if (count($planningPositionLocks)) { $delete = false; }

            // Supervisors
            $supervisorCriteria = new \Doctrine\Common\Collections\Criteria();
            $supervisorCriteria
                ->orWhere($supervisorCriteria->expr()->contains('perso_id', $perso_id))
                ->orWhere($supervisorCriteria->expr()->contains('responsable', $perso_id));

            $supervisors = $entityManager->getRepository(Supervisor::class)->matching($supervisorCriteria);
            if (count($supervisors)) { $delete = false; }

            if ($delete == true) {
                $entityManager->remove($agent);
                $deleted_agents++;
            }
            $entityManager->flush();
        }
        return $deleted_agents;
    }
}
