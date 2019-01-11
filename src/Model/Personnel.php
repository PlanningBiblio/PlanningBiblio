<?php

namespace Model;

/**
 * @Entity @Table(name="personnel")
 **/
class Personnel extends Entity {
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

    /** @Column(type="text") **/
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

    public function droits_list() {
        $permissions = $this->droits();

        $permissions = html_entity_decode($permissions, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        return json_decode($permissions);
    }

    public function can_access(array $accesses, $page) {
        $droits = $this->droits_list();

        foreach ($accesses as $access) {
            if (in_array($access->groupe_id(), $droits)) {
                return true;
            }
        }
        return false;
    }
}
