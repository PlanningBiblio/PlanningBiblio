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
use App\Planno\Helper\HourHelper;

require_once __DIR__ . '/../../legacy/Common/function.php';

class AgentRepository extends EntityRepository
{
    private $module = 'absence';

    private $needed_level1 = 200;

    private $needed_level2 = 500;

    private $by_agent_param = 'Absences-notifications-agent-par-agent';

    private $agent_id;

    private $check_by_site = true;

    /**
     * @return mixed[]
     */
    public function getAllSkills(): array {
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

    public function purgeAll(): int
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
            if (count($planningPositionLocks) > 0) { $delete = false; continue; }

            // Managers
            $managerCriteria = new \Doctrine\Common\Collections\Criteria();
            $managerCriteria
                ->orWhere($managerCriteria->expr()->eq('perso_id', $agent))
                ->orWhere($managerCriteria->expr()->eq('responsable', $agent));

            $managers = $entityManager->getRepository(Manager::class)->matching($managerCriteria);
            if (count($managers) > 0) { $delete = false; continue; }

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

    /**
     * @return array{id: int<1, max>, name: mixed}[]
     */
    public function getManagedSitesFor($loggedin_id): array
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
                $sites = $m->getUser()->getSites();
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

    public function getValidationLevelFor($loggedin_id, String $workflow = 'A'): array
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
                $sites = $agent->getSites();
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

    public function getByDeletionStatus(array $deletionStatus)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.supprime IN (:deletion)')
            ->setParameter('deletion', $deletionStatus);

        return $qb->getQuery()->getResult();
    }

    /* Returns an array of sites for the given agents */
    /**
     * @return mixed[]
     */
    public function getSitesForAgents($agent_ids = array()): array
    {
        if ($GLOBALS['config']['Multisites-nombre'] == 1) {
            return array("1");
        }

        $entityManager = $this->getEntityManager();
        $agents = $entityManager->getRepository(Agent::class)->findBy(array('id' => $agent_ids));
        $sites_array = array();
        foreach ($agents as $agent) {
            $agent_sites = $agent->getSites();
            if (is_array($agent_sites)) {
                $sites_array = array_merge($sites_array, $agent_sites);
            }
        }
        $sites_array = array_unique($sites_array);
        $sites_array = array_values($sites_array);
        return $sites_array;
    }

    public function holidayCreditAndCompTimeToRemainder(): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.conges_reliquat', 'a.conges_credit + a.comp_time')
            ->getQuery();

        $query->execute();
    }

    public function holidayCreditToRemainder(): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.conges_reliquat', 'a.conges_credit')
            ->getQuery();

        $query->execute();
    }

    public function holidayResetCompTime(): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.comp_time', 0)
            ->getQuery();

        $query->execute();
    }

    public function holidayResetCredit(): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $query = $builder->update(Agent::class, 'a')
            ->set('a.conges_credit', 'a.conges_annuel - a.conges_anticipation')
            ->set('a.conges_anticipation', 0)
            ->getQuery();

        $query->execute();
    }

    public function holidayResetRemainder(): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->update(Agent::class, 'a')->set('a.conges_reliquat', 0);
        $builder->getQuery()->execute();
    }

    /**
     * Marks agents as deleted when their departure date is in the past.
     *
     * It sets agents as deleted both in column "supprime(=1)" and "actif(='Supprim&eacute;')" if:
     *  - the departure date is before today,
     *  - the departure date is not equal to '0000-00-00',
     *  - the agent is not already marked as deleted.
     * 
     */
    public function updateAsDeletedByDepartDate(): int
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->update()
            ->set('p.supprime', ':supprime')
            ->set('p.actif', ':actif')
            ->where('p.depart < CURRENT_DATE()')
            ->andWhere("p.depart <> '0000-00-00'")
            ->andWhere('p.actif NOT LIKE :actifLike')
            ->setParameter('supprime', 1)
            ->setParameter('actif', 'Supprimé')
            ->setParameter('actifLike', 'Supprim%')
            ->getQuery()
            ->execute();
    }

    /**
     * Marks the given agents as deleted and sets their departure date to today.
     *
     * This method updates agents identified by their IDs by setting the deletion
     * flag, updating their status, and assigning today's date as the departure date.
     *
     * @param array|int $ids Agent ID or list of agent IDs
     * @return int Number of affected rows
     */
    public function updateAsDeletedAndDepartTodayById($ids): int
    {
        $ids = is_array($ids) ? $ids : [$ids];
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->update()
            ->set('p.supprime', 1)
            ->set('p.actif', ':actif')
            ->set('p.depart', ':today')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->setParameter('actif', 'Supprimé')
            ->setParameter('today', date('Y-m-d'))
            ->getQuery()
            ->execute();
    }

    /**
     * Find the list of distinct agent statuses.
     *
     * This method returns all unique values of the "statut" field
     * from the personnel records.
     *
     * @return array List of distinct statuses
     */
    public function findDistinctStatuts(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.statut')
            ->groupBy('p.statut')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Find the list of distinct agent services.
     *
     * This method returns all unique values of the "service" field
     * from the personnel records.
     *
     * @return array List of distinct services
     */
    public function findDistinctServices(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.service')
            ->groupBy('p.service')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * getExportIcsURL
     * Retourne l'URL ICS de l'agent.
     * @param int $id : id de l'agent
     * @return string $url
     */
    public function getExportIcsURL($id): string
    {
        $entityManager = $this->getEntityManager();
        $config = $entityManager->getRepository(Config::class)->findOneBy(['nom' => 'ICS-Code']);
        $url = "/ical?id=$id";

        if ($config->getValue()) {
            $agent = $this->find($id);
            $url .= '&code=' . $agent->getICSCode();
        }

        return $url;
    }

    public function fetchCredits(?int $userId): array
    {
        if (!$userId) {
            return [
                'annuel' => 0,
                'anticipation' => 0,
                'credit' => 0,
                'recup' => 0,
                'reliquat' => 0,
                'annuelHeures' => 0,
                'anticipationHeures' => 0,
                'creditHeures' => 0,
                'recupHeures' => 0,
                'reliquatHeures' => 0,
                'annuelMinutes' => 0,
                'anticipationMinutes' => 0,
                'creditMinutes' => 0,
                'recupMinutes' => 0,
                'reliquatMinutes' => 0,
            ];
        }

        $agent = $this->find($userId);

        $decimal_annuel       = $agent->getHolidayAnnualCredit();
        $decimal_anticipation = $agent->getHolidayAnticipation();
        $decimal_credit       = $agent->getHolidayCredit();
        $decimal_comp_time    = $agent->getHolidayCompTime();
        $decimal_reliquat     = $agent->getHolidayRemainder();

        $annuel       = HourHelper::decimalToHoursMinutes($decimal_annuel);
        $anticipation = HourHelper::decimalToHoursMinutes($decimal_anticipation);
        $credit       = HourHelper::decimalToHoursMinutes($decimal_credit);
        $comp_time    = HourHelper::decimalToHoursMinutes($decimal_comp_time);
        $reliquat     = HourHelper::decimalToHoursMinutes($decimal_reliquat);
        
        return [
            'annuel'              => $decimal_annuel,
            'annuelHeures'        => $annuel['hours'],
            'annuelMinutes'       => $annuel['minutes'],
            'anticipation'        => $decimal_anticipation,
            'anticipationHeures'  => $anticipation['hours'],
            'anticipationMinutes' => $anticipation['minutes'],
            'credit'              => $decimal_credit,
            'creditHeures'        => $credit['hours'],
            'creditMinutes'       => $credit['minutes'],
            'recup'               => $decimal_comp_time,
            'recupHeures'         => $comp_time['hours'],
            'recupMinutes'        => $comp_time['minutes'],
            'reliquat'            => $decimal_reliquat,
            'reliquatHeures'      => $reliquat['hours'],
            'reliquatMinutes'     => $reliquat['minutes']
        ];
    }

    /**
     * Finds all agent logins that are not deleted.
     *
     * This method returns the list of agent logins
     * whose deletion flag is different from the deleted value "2".
     *
     * @return array List of agent logins
     */
    public function findAllLoginsNotDeleted(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.login')
            ->where('a.supprime != :deleted')
            ->setParameter('deleted', 2)
            ->orderBy('a.login', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    // Will replance personnel::delete
    public function delete($ids): void
    {
        // Suppresion des informations de la table personnel
        // NB : les entrées ne sont pas complétement supprimées car nous devons les garder pour l'historique des plannings et les statistiques. Mais les données personnelles sont anonymisées.
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->update(Agent::class, 'a')
            ->set('a.supprime', ':supprime')
            ->set('a.login', "CONCAT('deleted_', a.id)")
            ->set('a.nom', "CONCAT('Agent_', a.id)")
            ->set('a.prenom', ':vide_string')
            ->set('a.mail', ':vide_string')
            ->set('a.arrivee', ':null')
            ->set('a.depart', ':null')
            ->set('a.postes', ':vide_array')
            ->set('a.droits', ':vide_array')
            ->set('a.password', ':vide_string')
            ->set('a.commentaires', ':comment')
            ->set('a.last_login', ':null')
            ->set('a.temps', ':vide_array')
            ->set('a.informations', ':vide_string')
            ->set('a.recup', ':vide_string')
            ->set('a.heures_travail', 0)
            ->set('a.heures_hebdo', ':vide_string')
            ->set('a.sites', ':vide_array')
            ->set('a.mails_responsables', ':vide_string')
            ->set('a.matricule', ':null')
            ->set('a.code_ics', ':null')
            ->set('a.url_ics', ':null')
            ->set('a.check_ics', ':null')
            ->set('a.check_hamac', ':false')
            ->set('a.conges_credit', ':null')
            ->set('a.conges_reliquat', ':null')
            ->set('a.conges_anticipation', ':null')
            ->set('a.conges_annuel', ':null')
            ->set('a.comp_time', ':null')
            ->setParameter('supprime', 2)
            ->setParameter('null', null)
            ->setParameter('vide_array', '[]')
            ->setParameter('vide_string', '')
            ->setParameter('false', false)
            ->setParameter('comment', "Suppression définitive le ".date("d/m/Y"))
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        // Suppression des informations sur les absences
        // NB : les entrées ne sont pas complétement supprimées car nous devons les garder pour l'historique des plannings et les statistiques. Mais les données personnelles sont anonymisées.
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->update(Absence::class, 'a')
            ->set('a.commentaires', ':vide_string')
            ->set('a.motif_autre', ':vide_string')
            ->where('a.perso_id IN (:ids)')
            ->setParameter('vide_string', '')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        // Suppression des informations sur les congés
        // NB : les entrées ne sont pas complétement supprimées car nous devons les garder pour l'historique des plannings et les statistiques. Mais les données personnelles sont anonymisées.
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->update(Holiday::class, 'h')
            ->set('h.commentaires', ':null')
            ->set('h.refus', ':null')
            ->set('h.heures', ':null')
            ->set('h.solde_prec', ':null')
            ->set('h.solde_actuel', ':null')
            ->set('h.recup_prec', ':null')
            ->set('h.recup_actuel', ':null')
            ->set('h.reliquat_prec', ':null')
            ->set('h.reliquat_actuel', ':null')
            ->set('h.anticipation_prec', ':null')
            ->set('h.anticipation_actuel', ':null')
            ->where('h.perso_id IN (:ids)')
            ->setParameter('null', null)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        // Suppresion des informations sur les récupérations
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(OverTime::class, 'o')
            ->where('o.perso_id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        // Suppression des informations sur les heures de présence
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(WorkingHour::class, 'w')
            ->where('w.perso_id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    // Will replace personnel::fetch
    public function get($orderBy = 'nom', $actif = null, $name = null)
    {
        $supprime = strstr($actif, "Supprim") ? array(1) : array(0);
        $actif = strstr($actif, "Supprim") ? 'Supprim%' : $actif;// TODO migration (update as deleted)

        $fields = array_map('trim', explode(',', $orderBy));
        $orders = [];
        foreach ($fields as $field)
            $orders[] = 'a.' . $field;
        $orderBy = implode(', ', $orders);

        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->where("a.id <> 2")
            ->andWhere("a.supprime IN (:supprime)");
        
        if (!empty($actif))
        {
            $qb ->andWhere("a.actif LIKE :actif")
                ->setParameter('actif', $actif);
        }

        $qb ->setParameter('supprime', $supprime)
            ->orderBy($orderBy);

        $agents = $qb
            ->getQuery()
            ->getResult();

        if ($name)
        {
            foreach($agents as $agent)
            {
                if (pl_stristr($agent->getFirstname(), $name) or pl_stristr($agent->getLastname(), $name))
                {
                    return $agent;
                }
            }
        } else {
            return $agents;// sortir des entities
        }
    }
}
