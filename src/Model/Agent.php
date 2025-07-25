<?php

namespace App\Model;

use App\Repository\AgentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue, OneToMany};

require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/include/db.php');

#[Entity(repositoryClass: AgentRepository::class)]
#[Table(name: 'personnel')]
class Agent extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: Types::TEXT)]
    protected ?string $nom = null;

    #[Column(type: Types::TEXT)]
    protected ?string $prenom = null;

    #[Column(type: Types::TEXT)]
    protected ?string $mail = null;

    #[Column(type: Types::TEXT)]
    protected ?string $statut = null;

    #[Column(length: 255)]
    protected ?string $categorie = null;

    #[Column(type: Types::TEXT)]
    protected ?string $service = null;

    #[Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTime $arrivee = null;

    #[Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTime $depart = null;

    #[Column(type: Types::TEXT)]
    protected ?string $postes = null;

    #[Column(length: 255)]
    protected ?string $actif = null;

    #[Column]
    protected array $droits = [];

    #[Column(length: 255)]
    protected ?string $login = null;

    #[Column(length: 255)]
    protected ?string $password = null;

    #[Column(type: Types::TEXT)]
    protected ?string $commentaires = null;

    #[Column]
    protected ?\DateTime $last_login = null;

    #[Column(length: 6)]
    protected ?string $heures_hebdo = null;

    #[Column]
    protected ?float $heures_travail = null;

    #[Column(type: Types::TEXT)]
    protected ?string $sites = null;

    #[Column(type: Types::TEXT)]
    protected ?string $temps = null;

    #[Column(type: Types::TEXT)]
    protected ?string $informations = null;

    #[Column(type: Types::TEXT)]
    protected ?string $recup = null;

    #[Column(length: 255)]
    protected ?string $supprime = null;

    #[Column(type: Types::TEXT)]
    protected ?string $mails_responsables = null;

    #[Column(length: 255)]
    protected ?string $matricule = null;

    #[Column(length: 255)]
    protected ?string $code_ics = null;

    #[Column(type: Types::TEXT)]
    protected ?string $url_ics = null;

    #[Column(length: 10)]
    protected ?string $check_ics = null;

    #[Column]
    protected ?int $check_hamac = null;

    #[Column]
    protected ?bool $check_ms_graph = null;

    #[Column]
    protected ?float $conges_credit = null;

    #[Column]
    protected ?float $conges_reliquat = null;

    #[Column]
    protected ?float $conges_anticipation = null;

    #[Column]
    protected ?float $comp_time = null;

    #[Column]
    protected ?float $conges_annuel = null;

    /**
     * @var Collection<int, Manager>
     */
    #[OneToMany(mappedBy: 'perso_id', targetEntity: Manager::class, cascade: ['ALL'])]
    protected Collection $managers;

    /**
     * @var Collection<int, Manager>
     */
    #[OneToMany(mappedBy: 'responsable', targetEntity: Manager::class, cascade: ['ALL'])]
    protected Collection $managed;

    public function __construct() {
        $this->managers = new ArrayCollection();
        $this->managed = new ArrayCollection();
    }

    public function getManaged()
    {
        return $this->managed->toArray();
    }

    public function getManagers()
    {
        return $this->managers->toArray();
    }

    public function addManaged(Manager $managed)
    {
        $this->managed->add($managed);
        $managed->responsable($this);
    }

    public function isManagerOf($agent_ids = array(), $requested_level = null)
    {
        $managed_ids = array();
        $managed = $this->getManaged();

        foreach ($managed as $m) {
            if (!$requested_level
                or ($requested_level && $m->{$requested_level}())) {
                $managed_ids[] = $m->perso_id()->id();
            }
        }

        foreach ($agent_ids as $id) {
            if (!in_array($id, $managed_ids)) {
                return false;
            }
        }

        return true;
    }

    public function can_access(array $accesses) {
        if (empty($accesses)) {
            return false;
        }

        $droits = $this->droits();
        $multisites = $GLOBALS['config']['Multisites-nombre'];

        // Right 21 (Edit personnel) gives right 4 (Show personnel)
        if (in_array(21, $droits)) {
            $droits[] = 4;
        }

        foreach ($accesses as $access) {
            if (in_array($access->groupe_id(), $droits)) {
                return true;
            }
        }

        // Multisites rights associated with page access
        $multisites_rights = array(201,301);
        if ($multisites > 1) {
            if (in_array($accesses[0]->groupe_id(), $multisites_rights)) {
                for ($i = 1; $i <= $multisites; $i++) {
                    $droit = $accesses[0]->groupe_id() -1 + $i;
                    if (in_array($droit, $droits)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function get_planning_unit_mails() {
        $config = $GLOBALS['config'];

        // Get mails defined in Mail-Planning config element.
        $unit_mails = array();
        if ($config['Mail-Planning']) {
            $unit_mails = explode(";", trim($config['Mail-Planning']));
            $unit_mails = array_map('trim', $unit_mails);
        }

        // Add mails defined by sites (Multisites-siteX-mail).
        $sites = json_decode($this->sites());
        if (is_array($sites)) {
            foreach ($sites as $site) {
                $site_mail_config = "Multisites-site$site-mail";
                if ($config[$site_mail_config]) {
                    $site_mails = explode(';', $config[$site_mail_config]);
                    $site_mails = array_map('trim', $site_mails);
                    $unit_mails = array_merge($unit_mails, $site_mails);
                }
            }
        }

        $unit_mails = array_unique($unit_mails);

        return $unit_mails;
    }

    public function get_manager_emails() {
        $emails_string = $this->mails_responsables();

        if ($emails_string == '') {
            return array();
        }

        return explode(';', $emails_string);
    }

    public function isAbsentOn($from, $to)
    {
        $a = new \absences();
        if ($a->check($this->id(), $from, $to, true)) {
            return true;
        }

        return false;
    }

    public function isOnVacationOn($from, $to)
    {
        $c = new \conges();
        if ($c->check($this->id(), $from, $to, true)) {
            return true;
        }

        return false;
    }

    public function getWorkingHoursOn($date)
    {
        $config = $GLOBALS['config'];

        if (!$config['PlanningHebdo']) {
            return array('temps' => json_decode($this->temps(), true));
        }

        $working_hours = new \planningHebdo();
        $working_hours->perso_id = $this->id;
        $working_hours->debut = $date;
        $working_hours->fin = $date;
        $working_hours->valide = false;
        $working_hours->fetch();

        if (empty($working_hours->elements)) {
            return array();
        }

        return $working_hours->elements[0];
    }

    public function isBlockedOn($date, $start, $end)
    {
        $id = $this->id();

        $db=new \db();
        $db->select(
            'pl_poste',
            'poste',
            "`perso_id` = $id and `debut` < '$end' and `fin` > '$start' and `date`='$date' and `supprime`='0'"
        );

        $postes = array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $postes[] = $elem['poste'];
            }
        }

        if (empty($postes)) {
            return false;
        }

        foreach ($postes as $poste) {
            $db=new \db();
            $db->select('postes', 'bloquant', "`id` = $poste");
            if ($db->result && $db->result[0]['bloquant']) {
                return true;
            }
        }

        return false;
    }

    public function skills()
    {
        $skills = json_decode($this->postes());
        return is_array($skills) ? $skills : [];
    }

    public function managedSites($needed_l1, $needed_l2)
    {
        $sites_number = $GLOBALS['config']['Multisites-nombre'];

        // Module workinghour, no multisites.
        if ($needed_l1 == 1100) {
            $sites_number = 1;
        }

        $rights = $this->droits();

        $managed_sites = array();
        for ($i = 1; $i <= $sites_number; $i++) {
            if (in_array($needed_l1 + $i, $rights)
                or in_array($needed_l2 + $i, $rights)) {
                $managed_sites[] = $i;
            }
        }

        return $managed_sites;
    }

    public function inOneOfSites($sites)
    {
        $agent_sites = json_decode($this->sites(), true);

        if (!is_array($agent_sites)) {
            return false;
        }

        foreach ($agent_sites as $site) {
            if (in_array($site, $sites)) {
                return true;
            }
        }

        return false;
    }
}
