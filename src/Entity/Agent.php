<?php

namespace App\Entity;

use App\Planno\Helper\HourHelper;
use App\Repository\AgentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

require_once(__DIR__ . '/../../legacy/Class/class.absences.php');
require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Common/db.php');

#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\Table(name: 'personnel')]
class Agent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = '';

    #[ORM\Column(type: Types::TEXT)]
    private ?string $prenom = '';

    #[ORM\Column(type: Types::TEXT)]
    private ?string $mail = '';

    #[ORM\Column(type: Types::TEXT)]
    private ?string $statut = '';

    #[ORM\Column]
    private ?string $categorie = '';

    #[ORM\Column(type: Types::TEXT)]
    private ?string $service = '';

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $arrivee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $depart = null;

    #[ORM\Column]
    private ?array $postes = [];

    #[ORM\Column]
    private ?string $actif = 'Actif';

    #[ORM\Column]
    private ?array $droits = [];

    #[ORM\Column]
    private ?string $login = '';

    #[ORM\Column]
    private ?string $password = '';

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaires = '';

    #[ORM\Column]
    private ?\DateTime $last_login = null;

    #[ORM\Column(length: 6)]
    private ?string $heures_hebdo = '';

    #[ORM\Column]
    private ?float $heures_travail = 0;

    #[ORM\Column]
    private ?array $sites = [];

    #[ORM\Column]
    private ?array $temps = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $informations = '';

    #[ORM\Column(type: Types::TEXT)]
    private ?string $recup = '';

    #[ORM\Column]
    private ?int $supprime = 0;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $mails_responsables = '';

    #[ORM\Column]
    private ?string $matricule = '';

    #[ORM\Column]
    private ?string $code_ics = '';

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url_ics = '';

    #[ORM\Column]
    private ?array $check_ics = [0,0,0];

    #[ORM\Column]
    private ?bool $check_hamac = false;

    #[ORM\Column]
    private ?bool $check_ms_graph = false;

    #[ORM\Column]
    private ?float $conges_credit = 0;

    #[ORM\Column]
    private ?float $conges_reliquat = 0;

    #[ORM\Column]
    private ?float $conges_anticipation = 0;

    #[ORM\Column]
    private ?float $comp_time = 0;

    #[ORM\Column]
    private ?float $conges_annuel = 0;

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

    public function getHolidayAnnualCredit(): ?float
    {
        return $this->conges_annuel;
    }

    public function setHolidayAnnualCredit(?float $annualCredit): static
    {
        $this->conges_annuel = $annualCredit;

        return $this;
    }

    public function getHolidayAnticipation(): ?float
    {
        return $this->conges_anticipation;
    }

    public function setHolidayAnticipation(?float $holidayAnticipation): static
    {
        $this->conges_anticipation = $holidayAnticipation;

        return $this;
    }

    public function getHolidayCompTime(): ?float
    {
        return $this->comp_time;
    }

    public function setHolidayCompTime(?float $holidayCompTime): static
    {
        $this->comp_time = $holidayCompTime;

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

    public function getHolidayRemainder(): ?float
    {
        return $this->conges_reliquat;
    }

    public function setHolidayRemainder(?float $holidayRemainder): static
    {
        $this->conges_reliquat = $holidayRemainder;

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

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getSites(): ?array
    {
        return $this->sites;
    }

    public function setSites(?array $sites): static
    {
        $this->sites = $sites;

        return $this;
    }

    public function getSkills(): ?array
    {
        return $this->postes;
    }

    public function setSkills(?array $skills): static
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

    public function getWeeklyServiceHours($formated = false): ?string
    {
        if ($formated and is_numeric($this->heures_hebdo)) {
            $hourHelper = new HourHelper();
            $time = $hourHelper->decimalToHoursMinutes($this->heures_hebdo);
            return $time['as_string'];
        }

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

    public function getWorkingHours(): ?array
    {
        return $this->temps;
    }

    public function setWorkingHours(?array $workingHours): static
    {
        $this->temps = $workingHours;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->commentaires;
    }

    public function setComment(?string $comment): static
    {
        $this->commentaires = $comment;

        return $this;
    }
    public function getLastLogin(): ?\DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTime $lastLogin): static
    {
        $this->last_login = $lastLogin;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->informations;
    }

    public function setInformation(?string $information): static
    {
        $this->informations = $information;

        return $this;
    }
    
    public function getRecoveryMenu(): ?string
    {
        return $this->recup;
    }

    public function setRecoveryMenu(?string $recoveryMenu): static
    {
        $this->recup = $recoveryMenu;

        return $this;
    }
    
    public function getManagersMails(): ?string
    {
        return $this->mails_responsables;
    }

    public function setManagersMails(?string $managersMails): static
    {
        $this->mails_responsables = $managersMails;

        return $this;
    }

    public function isHamacCheck(): ?bool
    {
        return $this->check_hamac;
    }

    public function setHamacCheck(?bool $hamacCheck): static
    {
        $this->check_hamac = $hamacCheck;

        return $this;
    }

    public function isMsGraphCheck(): ?bool
    {
        return $this->check_ms_graph;
    }

    public function setMsGraphCheck(?bool $msGraphCheck): static
    {
        $this->check_ms_graph = $msGraphCheck;

        return $this;
    }

    public function getEmployeeNumber(): ?string
    {
        return $this->matricule;
    }

    public function setEmployeeNumber(?string $employeeNumber): static
    {
        $this->matricule = $employeeNumber;

        return $this;
    }

    public function getIcsCheck(): ?array
    {
        return $this->check_ics;
    }

    public function setIcsCheck(?array $icsCheck): static
    {
        $this->check_ics = $icsCheck;

        return $this;
    }

    public function getIcsUrl(): ?string
    {
        return $this->url_ics;
    }

    public function setIcsUrl(?string $icsUrl): static
    {
        $this->url_ics = $icsUrl;

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

    public function addManaged(Manager $managed): void
    {
        $this->managed->add($managed);
        $managed->setManager($this);
    }

    public function isManagerOf($agent_ids = array(), $requested_level = null): bool
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

    public function can_access(array $accesses): bool {
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

    /**
     * @return mixed[]
     */
    public function get_planning_unit_mails(): array {
        $config = $GLOBALS['config'];

        // Get mails defined in Mail-Planning config element.
        $unit_mails = array();
        if ($config['Mail-Planning']) {
            $unit_mails = explode(";", trim($config['Mail-Planning']));
            $unit_mails = array_map('trim', $unit_mails);
        }

        // Add mails defined by sites (Multisites-siteX-mail).
        $sites = $this->sites;
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

    public function isAbsentOn($from, $to): bool
    {
        $a = new \absences();
        return $a->check($this->id, $from, $to, true);
    }

    public function isOnVacationOn($from, $to): bool
    {
        $c = new \conges();
        return $c->check($this->id, $from, $to, true);
    }

    public function getWorkingHoursOn($date)
    {
        $config = $GLOBALS['config'];

        if (!$config['PlanningHebdo']) {
            return array('temps' => $this->temps);
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

    public function isBlockedOn($date, $start, $end): bool
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

    /**
     * @return int[]
     */
    public function managedSites($needed_l1, $needed_l2): array
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

    public function inOneOfSites($sites): bool
    {
        $agent_sites = $this->sites;

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
