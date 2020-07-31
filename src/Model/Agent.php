<?php

namespace App\Model;

include_once(__DIR__ . '/../../public/planningHebdo/class.planningHebdo.php');
require_once(__DIR__ . '/../../public/absences/class.absences.php');

/**
 * @Entity @Table(name="personnel")
 **/
class Agent extends PLBEntity {
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

    /** @Column(type="json_array") **/
    protected $droits;

    /** @Column(type="string") **/
    protected $login;

    /** @Column(type="string") **/
    protected $password;

    /** @Column(type="text") **/
    protected $commentaires;

    /** @Column(type="datetime") **/
    protected $last_login;

    /** @Column(type="string") **/
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

    /** @Column(type="string") **/
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

    public function is_agent_status_in_category($category) {
        $db = new \db();
        $db->select2("select_statuts", "categorie", array('valeur' => $this->statut()));
        $results = $db->result;
        if (!$results) { return false; }
        $categorie_id = $results[0]['categorie'];

        $db = new \db();
        $db->select2("select_categories", "valeur", array('id' => $categorie_id));
        $results = $db->result;
        if (!$results) { return false; }
        $categorie_name = $results[0]['valeur'];
        return ($categorie_name == htmlentities($category));
    }

    public function getWorkingHoursOn($date)
    {
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

    public function isAbsentOn($from, $to)
    {
        $a = new \absences();
        if ($a->check($this->id(), $from, $to, true)) {
            return true;
        }

        return false;
    }

    public function isPartiallyAbsentOn($from, $to)
    {
        $a = new \absences();
        if ($absences = $a->checkPartial($this->id(), $from, $to, true)) {
            return $absences;
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

    public function isInService($services)
    {
        if (empty($services)) {
            return true;
        }

        if (in_array($this->service, $services)) {
            return true;
        }

        return false;
    }

    public function isInSite($site) {
        $sites = json_decode($this->sites);

        if (empty($sites)) {
            return false;
        }

        if (in_array($site, $sites)) {
            return true;
        }

        return false;
    }

    public function hasSkills($skills = array())
    {
        if (empty($skills)) {
            return true;
        }

        $own_skills = array();
        if ($this->postes) {
            $own_skills = json_decode($this->postes);
        }
        foreach ($skills as $skill) {
            if (!in_array($skill, $own_skills)) {
                return false;
            }
        }

        return true;
    }
}
