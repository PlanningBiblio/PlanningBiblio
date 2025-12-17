<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\OverTime;
use App\Entity\Detached;
use App\Entity\HiddenTables;
use App\Entity\Holiday;
use App\Entity\Manager;
use App\Entity\PlanningNote;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionLock;
use App\Entity\PlanningPositionModel;
use App\Entity\RecurringAbsence;
use App\Entity\SaturdayWorkingHours;
use App\Entity\WorkingHour;
use App\Entity\Config;

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
            $activites = $agent->getSkills();
            if (is_array($activites)) {
                foreach ($activites as $activite) {
                    array_push($all_skills, $activite);
                }
            }
        }
        $all_skills = array_unique($all_skills);
        return $all_skills;
    }

    public function getMaxId() {
        $entityManager = $this->getEntityManager();
        $id = $entityManager->createQueryBuilder()
            ->select('MAX(a.id)')
            ->from(Agent::class, 'a')
            ->getQuery()
            ->getSingleScalarResult();

        return $id;
    }

    public function purgeAll()
    {
        $agents = $this->findBy(['supprime' => '2']);
        $entityManager = $this->getEntityManager();
        $deleted_agents = 0;

        foreach ($agents as $agent) {
            $delete = true;
            $perso_id = $agent->getId();
            $classes = [
                Absence::class,
                OverTime::class,
                Detached::class,
                HiddenTables::class,
                Holiday::class,
                PlanningNote::class,
                PlanningPosition::class,
                PlanningPositionModel::class,
                RecurringAbsence::class,
                SaturdayWorkingHours::class,
                WorkingHour::class
            ];

            foreach ($classes as $class) {
                $objects = $entityManager->getRepository($class)->findBy(['perso_id' => $perso_id]);
                if (count($objects)) { $delete = false; continue 2; }
            }

            // Special cases:
            // PlanningPositionLock::class
            $planningPositionLockCriteria = new \Doctrine\Common\Collections\Criteria();
            $planningPositionLockCriteria
                ->orWhere($planningPositionLockCriteria->expr()->eq('perso', $perso_id))
                ->orWhere($planningPositionLockCriteria->expr()->eq('perso2', $perso_id));

            $planningPositionLocks = $entityManager->getRepository(PlanningPositionLock::class)->matching($planningPositionLockCriteria);
            if (count($planningPositionLocks)) { $delete = false; continue; }

            // Managers
            $managerCriteria = new \Doctrine\Common\Collections\Criteria();
            $managerCriteria
                ->orWhere($managerCriteria->expr()->eq('perso_id', $agent))
                ->orWhere($managerCriteria->expr()->eq('responsable', $agent));

            $managers = $entityManager->getRepository(Manager::class)->matching($managerCriteria);
            if (count($managers)) { $delete = false; continue; }

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
        $by_agent_param = $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        $sites_number = $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'Multisites-nombre'])->getValue();

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled.
        if ($by_agent_param->getValue()) {
            $managed_sites = array();

            foreach ($loggedin->getManaged() as $m) {
                $sites = json_decode($m->getUser()->getSites(), true) ?? array();
                $managed_sites = array_merge($managed_sites, $sites);
            }

            $managed_sites = array_unique($managed_sites);

        }

        $rights = $loggedin->getACL();

        $sites_select = array();
        for ($i = 1; $i <= $sites_number; $i++) {
            $name = $entityManager->getRepository(Config::class)
                ->findOneBy(['nom' => "Multisites-site$i"])->getValue();

            if ($by_agent_param->getValue()) {
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
        $by_agent_param = $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        $sites_number = $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'Multisites-nombre'])->getValue();

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled.
        if ($by_agent_param->getValue()) {
            $managed = array_map(function($m) {
                return $m->getUser();
            }, $loggedin->getManaged());

            // Prevent adding logged in twice.
            if (!$loggedin->isManagerOf(array($loggedin->getId()))) {
                $managed[] = $loggedin;
            }

            usort($managed, function($a, $b) { return ($a->getLastname() < $b->getLastname()) ? -1 : 1; });

            return $managed;
        }

        $rights = $loggedin->getACL();
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
                    if ($agent->getId() == $loggedin->getId()) {
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

    public function getValidationLevelFor($loggedin_id, String $workflow = 'A')
    {

        $entityManager = $this->getEntityManager();
        $loggedin = $entityManager->find(Agent::class, $loggedin_id);
        $by_agent_param = $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => $this->by_agent_param]);

        $sites_number = $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'Multisites-nombre'])->getValue();

        $sites = array(1);
        if ($this->check_by_site && $sites_number > 1) {
            $sites = array();

            for ($i = 1; $i <= $sites_number; $i++) {
                $sites[] = $i;
            }

            // will only check for agent sites
            if ($this->agent_id) {
                $agent = $entityManager->find(Agent::class, $this->agent_id);
                $sites = json_decode($agent->getSites()) ?? array();
            }
        }

        $l1 = false;
        $l2 = false;

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled and we ask for a specific agent.
        if ($by_agent_param->getValue() and $this->agent_id) {
            if ($loggedin->isManagerOf(array($this->agent_id), 'level1')) {
                $l1 = true;
            }

            if ($loggedin->isManagerOf(array($this->agent_id), 'level2')) {
                $l2 = true;
            }

            if ($l1 and $workflow == 'B') {
                $l2 = true;
            }

            return array($l1, $l2);
        }

        // Param Absences-notifications-agent-par-agent
        // or PlanningHebdo-notifications-agent-par-agent
        // is enabled but no agent is specified.
        // So look for max admin level on managed agents.
        if ($by_agent_param->getValue()) {
            $managed = $this->getManagedFor($loggedin_id);
            foreach ($managed as $m) {
                if ($loggedin->isManagerOf(array($m->getId()), 'level1')) {
                    $l1 = true;
                }

                if ($loggedin->isManagerOf(array($m->getId()), 'level2')) {
                    $l2 = true;
                }
            }

            return array($l1, $l2);
        }

        // No validation by agent.
        // Check for module rights.
        $agent_rights = $loggedin->getACL();

        // Give rigths for deleted agents
        // Avoid "Access denied" when modifying absences with several agents and some of them are deleted
        if (isset($agent) and $agent->getDeletion() == 2) {
            return array(true, true);
        }

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

    /* Returns an array of sites for the given agents */
    public function getSitesForAgents($agent_ids = array())
    {
        if ($GLOBALS['config']['Multisites-nombre'] == 1) {
            return array("1");
        }

        $entityManager = $this->getEntityManager();
        $agents = $entityManager->getRepository(Agent::class)->findBy(array('id' => $agent_ids));
        $sites_array = array();
        foreach ($agents as $agent) {
            $agent_sites = json_decode(html_entity_decode($agent->getSites(), ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            if (is_array($agent_sites)) {
                $sites_array = array_merge($sites_array, $agent_sites);
            }
        }
        $sites_array = array_unique($sites_array);
        $sites_array = array_values($sites_array);
        return $sites_array;
    }

    public function holidayCreditAndCompTimeToRemainder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.conges_reliquat', 'a.conges_credit + a.comp_time')
            ->getQuery();

        $query->execute();
    }

    public function holidayCreditToRemainder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.conges_reliquat', 'a.conges_credit')
            ->getQuery();

        $query->execute();
    }

    public function holidayResetCompTime()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.comp_time', 0)
            ->getQuery();

        $query->execute();
    }

    public function holidayResetCredit()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.conges_credit', 'a.conges_annuel - a.conges_anticipation')
            ->set('a.conges_anticipation', 0)
            ->getQuery();

        $query->execute();
    }

    public function holidayResetRemainder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->update(Agent::class, 'a')->set('a.conges_reliquat', 0);
        $builder->getQuery()->execute();
    }

    public function getAgentsByDeletion(array $deletion)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.supprime IN (:deletion)')
            ->setParameter('deletion', $deletion);

        return $qb->getQuery()->getResult();
    }// it's same as one in Xren200:MT50832_absence_import_csv_command_merge_ver2
}
