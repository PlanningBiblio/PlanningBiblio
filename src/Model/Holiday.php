<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity @Table(name="conges")
 **/
class Holiday extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="datetime") **/
    protected $debut;

    /** @Column(type="datetime") **/
    protected $fin;

    /** @Column(type="integer", length=4) **/
    protected $halfday;

    /** @Column(type="string", length=20) **/
    protected $start_halfday;

    /** @Column(type="string", length=20) **/
    protected $end_halfday;

    /** @Column(type="text")**/
    protected $commentaires;

    /** @Column(type="text")**/
    protected $refus;

    /** @Column(type="string", length=20) **/
    protected $heures;

    /** @Column(type="string", length=20) **/
    protected $debit;

    /** @Column(type="datetime") **/
    protected $saisie;

    /** @Column(type="integer", length=11) **/
    protected $saisie_par;

    /** @Column(type="integer", length=11) **/
    protected $modif;

    /** @Column(type="datetime") **/
    protected $modification;

    /** @Column(type="integer", length=11) **/
    protected $valide_n1;

    /** @Column(type="datetime") **/
    protected $validation_n1;

    /** @Column(type="integer", length=11) **/
    protected $valide;

    /** @Column(type="datetime") **/
    protected $validation;

    /** @Column(type="float")**/
    protected $solde_prec;

    /** @Column(type="float")**/
    protected $solde_actuel;

    /** @Column(type="float")**/
    protected $recup_prec;

    /** @Column(type="float")**/
    protected $recup_actuel;

    /** @Column(type="float")**/
    protected $reliquat_prec;

    /** @Column(type="float")**/
    protected $reliquat_actuel;

    /** @Column(type="float")**/
    protected $anticipation_prec;

    /** @Column(type="float")**/
    protected $anticipation_actuel;

    /** @Column(type="integer", length=11) **/
    protected $supprime;

    /** @Column(type="datetime") **/
    protected $suppr_date;

    /** @Column(type="integer", length=11) **/
    protected $information;

    /** @Column(type="datetime") **/
    protected $info_date;

    /** @Column(type="integer", length=11) **/
    protected $regul_id;

    /** @Column(type="integer", length=11) **/
    protected $origin_id;
}