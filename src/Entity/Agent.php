<?php

namespace App\Entity;

use App\Repository\AgentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/include/db.php');

#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\Table(name: 'personnel')]
class Agent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $mail = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $service = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $arrivee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $depart = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $postes = null;

    #[ORM\Column]
    private ?string $actif = null;

    #[ORM\Column]
    private ?array $droits = null;

    #[ORM\Column]
    private ?string $login = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaires = null;

    #[ORM\Column]
    private ?\DateTime $last_login = null;

    #[ORM\Column(length: 6)]
    private ?string $heures_hebdo = null;

    #[ORM\Column]
    private ?float $heures_travail = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $sites = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $temps = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $informations = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $recup = null;

    #[ORM\Column]
    private ?int $supprime = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $mails_responsables = null;

    #[ORM\Column]
    private ?string $matricule = null;

    #[ORM\Column]
    private ?string $code_ics = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url_ics = null;

    #[ORM\Column(length: 10)]
    private ?string $check_ics = null;

    #[ORM\Column]
    private ?int $check_hamac = null;

    #[ORM\Column]
    private ?bool $check_ms_graph = null;

    #[ORM\Column]
    private ?float $conges_credit = null;

    #[ORM\Column]
    private ?float $conges_reliquat = null;

    #[ORM\Column]
    private ?float $conges_anticipation = null;

    #[ORM\Column]
    private ?float $comp_time = null;

    #[ORM\Column]
    private ?float $conges_annuel = null;

    /**
     * @var Collection<int, Manager>
     */
    #[ORM\OneToMany(mappedBy: 'perso_id', targetEntity: Manager::class, cascade: ['ALL'])]
    private Collection $managers;

    /**
     * @var Collection<int, Manager>
     */
    #[ORM\OneToMany(mappedBy: 'responsable', targetEntity: Manager::class, cascade: ['ALL'])]
    private Collection $managed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getACL(): ?array
    {
        return $this->droits;
    }

    public function setACL(?array $acl): static
    {
        $this->droits = $acl;

        return $this;
    }

    public function getActive(): ?string
    {
        return $this->actif;
    }

    public function setActive(?string $active): static
    {
        $this->actif = $active;

        return $this;
    }

    public function getAnticipation(): ?float
    {
        return $this->conges_anticipation;
    }

    public function setAnticipation(?float $anticipation): static
    {
        $this->conges_anticipation = $anticipation;

        return $this;
    }

    public function getArrival(): ?\DateTime
    {
        return $this->arrivee;
    }

    public function setArrival(?\DateTime $arrival): static
    {
        $this->arrivee = $arrival;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->categorie;
    }

    public function setCategory(?string $category): static
    {
        $this->categorie = $category;

        return $this;
    }

    public function getCompTime(): ?float
    {
        return $this->comp_time;
    }

    public function setCompTime(?float $compTime): static
    {
        $this->comp_time = $compTime;

        return $this;
    }

    public function getDeletion(): ?int
    {
        return $this->supprime;
    }

    public function setDeletion(?int $deletionStatus): static
    {
        $this->supprime = $deletionStatus;

        return $this;
    }

    public function getDeparture(): ?\DateTime
    {
        return $this->depart;
    }

    public function setDeparture(?\DateTime $departure): static
    {
        $this->depart = $departure;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->prenom;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->prenom = $firstname;

        return $this;
    }

    public function getAnnualCredit(): ?float
    {
        return $this->conges_annuel;
    }

    public function setAnnualCredit(?float $annualCredit): static
    {
        $this->conges_annuel = $annualCredit;

        return $this;
    }

    public function getHolidayCredit(): ?float
    {
        return $this->conges_credit;
    }

    public function setHolidayCredit(?float $holidayCredit): static
    {
        $this->conges_credit = $holidayCredit;

        return $this;
    }

    public function getICSCode(): ?string
    {
        return $this->code_ics;
    }

    public function setICSCode(?string $iCSCode): static
    {
        $this->code_ics = $iCSCode;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->nom;
    }

    public function setLastname(?string $lastname): static
    {
        $this->nom = $lastname;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRemainder(): ?float
    {
        return $this->conges_reliquat;
    }

    public function setRemainder(?float $remainder): static
    {
        $this->conges_reliquat = $remainder;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getSites(): ?string
    {
        return $this->sites;
    }

    public function setSites(?string $sites): static
    {
        $this->sites = $sites;

        return $this;
    }

    public function getSkills(): ?string
    {
        return $this->postes;
    }

    public function setSkills(?string $skills): static
    {
        $this->postes = $skills;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->statut;
    }

    public function setStatus(?string $status): static
    {
        $this->statut = $status;

        return $this;
    }

    public function getWeeklyServiceHours(): ?string
    {
        return $this->heures_hebdo;
    }

    public function setWeeklyServiceHours(?string $weeklyServiceHours): static
    {
        $this->heures_hebdo = $weeklyServiceHours;

        return $this;
    }

    public function getWeeklyWorkingHours(): ?float
    {
        return $this->heures_travail;
    }

    public function setWeeklyWorkingHours(?float $weeklyWorkingHours): static
    {
        $this->heures_travail = $weeklyWorkingHours;

        return $this;
    }

    public function getWorkingHours(): ?string
    {
        return $this->temps;
    }

    public function setWorkingHours(?string $workingHours): static
    {
        $this->temps = $workingHours;

        return $this;
    }

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
        $managed->setManager($this);
    }

    public function isManagerOf($agent_ids = array(), $requested_level = null)
    {
        $managed_ids = array();
        $managed = $this->getManaged();

        $levelMethod = $requested_level == 'level1' ? 'getLevel1' : 'getLevel2';

        foreach ($managed as $m) {
            if (!$requested_level
                or ($requested_level && $m->{$levelMethod}())) {
                $managed_ids[] = $m->getUser()->getId();
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

        $droits = $this->droits;
        $multisites = $GLOBALS['config']['Multisites-nombre'];

        // Right 21 (Edit personnel) gives right 4 (Show personnel)
        if (in_array(21, $droits)) {
            $droits[] = 4;
        }

        foreach ($accesses as $access) {
            if (in_array($access->getGroupId(), $droits)) {
                return true;
            }
        }

        // Multisites rights associated with page access
        $multisites_rights = array(201,301);
        if ($multisites > 1) {
            if (in_array($accesses[0]->getGroupId(), $multisites_rights)) {
                for ($i = 1; $i <= $multisites; $i++) {
                    $droit = $accesses[0]->getGroupId() -1 + $i;
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
        $sites = json_decode($this->sites);
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
        $emails_string = $this->mails_responsables;

        if ($emails_string == '') {
            return array();
        }

        return explode(';', $emails_string);
    }

    public function isAbsentOn($from, $to)
    {
        $a = new \absences();
        if ($a->check($this->id, $from, $to, true)) {
            return true;
        }

        return false;
    }

    public function isOnVacationOn($from, $to)
    {
        $c = new \conges();
        if ($c->check($this->id, $from, $to, true)) {
            return true;
        }

        return false;
    }

    public function getWorkingHoursOn($date)
    {
        $config = $GLOBALS['config'];

        if (!$config['PlanningHebdo']) {
            return array('temps' => json_decode($this->temps, true));
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
        $id = $this->id;

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
        $skills = json_decode($this->postes);
        return is_array($skills) ? $skills : [];
    }

    public function managedSites($needed_l1, $needed_l2)
    {
        $sites_number = $GLOBALS['config']['Multisites-nombre'];

        // Module workinghour, no multisites.
        if ($needed_l1 == 1100) {
            $sites_number = 1;
        }

        $rights = $this->droits;

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
        $agent_sites = json_decode($this->sites, true);

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
