<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue, OneToMany};
require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/include/db.php');

/**
 * @Entity(repositoryClass="App\Repository\AgentRepository") @Table(name="personnel")
 **/
class Agent extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="text") **/
    protected $nom;

    /** @Column(type="text") **/
    protected $prenom;

    /** @Column(type="text") **/
    protected $mail;

    /** @Column(type="text") **/
    protected $statut;

    /** @Column(type="string") **/
    protected $categorie;

    /** @Column(type="text") **/
    protected $service;

    /** @Column(type="date") **/
    protected $arrivee;

    /** @Column(type="date") **/
    protected $depart;

    /** @Column(type="text") **/
    protected $postes;

    /** @Column(type="string") **/
    protected $actif;

    /** @Column(type="json") **/
    protected $droits;

    /** @Column(type="string") **/
    protected $login;

    /** @Column(type="string") **/
    protected $password;

    /** @Column(type="text") **/
    protected $commentaires;

    /** @Column(type="datetime") **/
    protected $last_login;

    /** @Column(type="string", length=6) **/
    protected $heures_hebdo;

    /** @Column(type="float") **/
    protected $heures_travail;

    /** @Column(type="text") **/
    protected $sites;

    /** @Column(type="text") **/
    protected $temps;

    /** @Column(type="text") **/
    protected $informations;

    /** @Column(type="text") **/
    protected $recup;

    /** @Column(type="string") **/
    protected $supprime;

    /** @Column(type="text") **/
    protected $mails_responsables;

    /** @Column(type="string") **/
    protected $matricule;

    /** @Column(type="string") **/
    protected $code_ics;

    /** @Column(type="text") **/
    protected $url_ics;

    /** @Column(type="string", length=10) **/
    protected $check_ics;

    /** @Column(type="integer") **/
    protected $check_hamac;

    /** @Column(type="float") **/
    protected $conges_credit;

    /** @Column(type="float") **/
    protected $conges_reliquat;

    /** @Column(type="float") **/
    protected $conges_anticipation;

    /** @Column(type="float") **/
    protected $comp_time;

    /** @Column(type="float") **/
    protected $conges_annuel;

    /**
     * @OneToMany(targetEntity="Manager", mappedBy="perso_id", cascade={"ALL"})
     */
    protected $managers;

    /**
     * @OneToMany(targetEntity="Manager", mappedBy="responsable", cascade={"ALL"})
     */
    protected $managed;

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

    public function isManagerOf($agent_ids = array())
    {
        $managed_ids = array_map(function($m) {
            return $m->perso_id()->id();
        }, $this->getManaged());

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
            return array('temps' => json_decode($this->temps()));
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

}
