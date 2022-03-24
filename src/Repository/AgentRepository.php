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
    private $module = 'absence';

    private $needed_level1 = 200;

    private $needed_level2 = 500;

    private $by_agent_param = 'Absences-notifications-agent-par-agent';

    private $agent_id = null;

    private $check_by_site = true;

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

    public function setModule($name = 'absence')
    {
        if (!in_array($name, array('absence', 'holiday', 'workinghour'))) {
            throw new \Exception("AgentRepository::setModule: Unsupported module $name");
        }

        $this->agent_id = null;
        $this->module = $name;

        if ($name == 'workinghour') {
            $this->needed_level1 = 1100;
            $this->needed_level2 = 1200;
            $this->by_agent_param = 'PlanningHebdo-notifications-agent-par-agent';
            $this->check_by_site = false;
        }

        if ($name == 'absence') {
            $this->needed_level1 = 200;
            $this->needed_level2 = 500;
            $this->by_agent_param = 'Absences-notifications-agent-par-agent';
            $this->check_by_site = true;
        }

        if ($name == 'holiday') {
            $this->needed_level1 = 400;
            $this->needed_level2 = 600;
            $this->by_agent_param = 'Absences-notifications-agent-par-agent';
            $this->check_by_site = true;
        }

        return $this;
    }

    public function forAgent($id)
    {
        if ($id) {
            $this->agent_id = $id;
        }

        return $this;
    }

    public function getManagedSitesFor($loggedin_id)
    {
        $entityManager = $this->getEntityManager();
        $loggedin = $entityManager->find(Agent::class, $loggedin_id);
        $by_agent_param = $entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        $sites_number = $entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => 'Multisites-nombre'])->valeur();

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled.
        if ($by_agent_param->valeur()) {
            $managed_sites = array();

            foreach ($loggedin->getManaged() as $m) {
                $managed_sites = array_merge($managed_sites, json_decode($m->perso_id()->sites()));
            }

            $managed_sites = array_unique($managed_sites);

        }

        $rights = $loggedin->droits();

        $sites_select = array();
        for ($i = 1; $i <= $sites_number; $i++) {
            $name = $entityManager->getRepository(ConfigParam::class)
                ->findOneBy(['nom' => "Multisites-site$i"])->valeur();

            if ($by_agent_param->valeur()) {
                if (in_array($i, $managed_sites)) {
                    $sites_select[] = array('id' => $i, 'name' => $name);
                }
                continue;
            }

            if (in_array(($this->needed_level1 + $i), $rights)
                or in_array(($this->needed_level2 + $i), $rights)) {

                $sites_select[] = array('id' => $i, 'name' => $name);
            }
        }

        return $sites_select;
    }

    public function getManagedFor($loggedin_id, $deleted = 0)
    {
        $entityManager = $this->getEntityManager();
        $loggedin = $entityManager->find(Agent::class, $loggedin_id);
        $by_agent_param = $entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        $sites_number = $entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => 'Multisites-nombre'])->valeur();

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled.
        if ($by_agent_param->valeur()) {
            $managed = array_map(function($m) {
                return $m->perso_id();
            }, $loggedin->getManaged());

            // Prevent adding logged in twice.
            if (!$loggedin->isManagerOf(array($loggedin->id()))) {
                $managed[] = $loggedin;
            }

            usort($managed, function($a, $b) { return ($a->nom() < $b->nom()) ? -1 : 1; });

            return $managed;
        }

        $rights = $loggedin->droits();
        $managed_sites = $loggedin->managedSites($this->needed_level1, $this->needed_level2);

        if (!empty($managed_sites)) {
            $agents = $entityManager->getRepository(Agent::class)
            ->getAgentsList($deleted);

            foreach ($agents as $index => $agent) {
                // Filter agents by sites
                // Only for absence and holidays.
                // There is no rights by sites
                // for working hours.
                if ($this->check_by_site && $sites_number > 1) {
                    // Always keep logged in agent.
                    if ($agent->id() == $loggedin->id()) {
                        continue;
                    }

                    if (!$agent->inOneOfSites($managed_sites)) {
                        unset($agents[$index]);
                    }
                }
            }

            return $agents;
        }

        return array($loggedin);
    }

    public function getValidationLevelFor($loggedin_id)
    {

        $entityManager = $this->getEntityManager();
        $loggedin = $entityManager->find(Agent::class, $loggedin_id);
        $by_agent_param = $entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        $sites = array(1);
        if ($this->check_by_site) {
            $sites = array();
            $sites_number = $entityManager->getRepository(ConfigParam::class)
                ->findOneBy(['nom' => 'Multisites-nombre'])->valeur();

            for ($i = 1; $i <= $sites_number; $i++) {
                $sites[] = $i;
            }

            // will only check for agent sites
            if ($this->agent_id) {
                $agent = $entityManager->find(Agent::class, $this->agent_id);
                $sites = json_decode($agent->sites()) ?? array();
            }
        }

        $l1 = false;
        $l2 = false;

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled and we ask for a specific agent.
        if ($by_agent_param->valeur() and $this->agent_id) {
            if ($loggedin->isManagerOf(array($this->agent_id), 'level1')) {
                $l1 = true;
            }

            if ($loggedin->isManagerOf(array($this->agent_id), 'level2')) {
                $l2 = true;
            }

            return array($l1, $l2);
        }

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled but no agent is specified.
        // So look for max admin level on managed agents.
        if ($by_agent_param->valeur()) {
            $managed = $this->getManagedFor($loggedin_id);
            foreach ($managed as $m) {
                if ($loggedin->isManagerOf(array($m->id()), 'level1')) {
                    $l1 = true;
                }

                if ($loggedin->isManagerOf(array($m->id()), 'level2')) {
                    $l2 = true;
                }
            }

            return array($l1, $l2);
        }

        // No validation by agent.
        // Check for module rights.
        $agent_rights = $loggedin->droits();

        foreach ($sites as $i) {
            if (in_array($this->needed_level1 + $i, $agent_rights)) {
                $l1 = true;
            }

            if (in_array($this->needed_level2 + $i, $agent_rights)) {
                $l2 = true;
            }
        }

        return array($l1, $l2);
    }

    public function getAgentsList($deleted = 0)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('a')
                ->from(Agent::class, 'a')
                ->andWhere('a.id != :all')
                ->addOrderBy('a.nom', 'ASC')
                ->setParameter('all', 2);

        if (!$deleted) {
            $builder->andWhere('a.supprime = :deleted')
                    ->setParameter('deleted', '0');
        }

        $agents = $builder->getQuery()->getResult();

        return $agents;
    }
}
