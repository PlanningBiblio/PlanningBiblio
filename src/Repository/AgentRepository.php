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
use App\Model\ConfigParam;

class AgentRepository extends EntityRepository
{
    private $needed_level1 = 201;

    private $needed_level2 = 501;

    private $by_agent_param = 'Absences-notifications-agent-par-agent';

    private $check_by_agent = true;

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

    public function setModule($name = 'absence', $check_by_agent = true)
    {
        if (!in_array($name, array('absence', 'holiday', 'workinghour'))) {
            throw new \Exception("AgentRepository::setModule: Unsupported module $name");
        }

        $this->check_by_agent = $check_by_agent;

        if ($name == 'workinghour') {
            $this->needed_level1 = 1101;
            $this->needed_level2 = 1201;
            $this->by_agent_param = 'PlanningHebdo-notifications-agent-par-agent';
        }

        return $this;
    }

    public function getManagedFor($loggedin_id)
    {
        $entityManager = $this->getEntityManager();
        $loggedin = $entityManager->find(Agent::class, $loggedin_id);
        $by_agent_param = $entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled.
        if ($by_agent_param->valeur()) {
            $managed = array_map(function($m) {
                return $m->perso_id();
            }, $loggedin->getManaged());

            $managed[] = $loggedin;

            return $managed;
        }

        $rights = $loggedin->droits();

        if (in_array($this->needed_level1, $rights)
            or in_array($this->needed_level2, $rights)) {

            $agents = $entityManager->getRepository(Agent::class)
            ->findBy(['supprime' => '0']);

            return $agents;
        }

        return array($loggedin);
    }

    public function getValidationLevelFor($loggedin_id, $agent_id = null)
    {

        $entityManager = $this->getEntityManager();
        $loggedin = $entityManager->find(Agent::class, $loggedin_id);
        $by_agent_param = $entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        $l1 = false;
        $l2 = false;

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled.
        if ($by_agent_param->valeur() and $this->check_by_agent) {
            if ($loggedin->isManagerOf(array($agent_id), 'level1')) {
                $l1 = true;
            }

            if ($loggedin->isManagerOf(array($agent_id), 'level2')) {
                $l2 = true;
            }

            return array($l1, $l2);
        }

        // No validation by agent.
        // Check for module rights.
        $agent_rights = $loggedin->droits();

        $l1 = in_array($this->needed_level1, $agent_rights);
        $l2 = in_array($this->needed_level2, $agent_rights);

        return array($l1, $l2);
    }
}
