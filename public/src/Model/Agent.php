<?php

namespace Model;

/**
 * @Entity @Table(name="personnel")
 **/
class Agent extends Entity {
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

    public function can_access(array $accesses) {
        $droits = $this->droits();
        $multisites = $GLOBALS['config']['Multisites-nombre'];

        // Right 21 (Edit personnel) gives right 4 (Show personnel)
        if (in_array(21, $droits)) {
            $droits[] = 4;
        }

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
}
