<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="recuperations")
 **/
class OverTime extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="date") **/
    protected $date;

    /** @Column(type="date") **/
    protected $date2;

    /** @Column(type="float") **/
    protected $heures;

    /** @Column(type="string") **/
    protected $etat;

    /** @Column(type="text") **/
    protected $commentaires;

    /** @Column(type="datetime") **/
    protected $saisie;

    /** @Column(type="integer") **/
    protected $saisie_par;

    /** @Column(type="integer") **/
    protected $modif;

    /** @Column(type="datetime") **/
    protected $modification;

    /** @Column(type="integer") **/
    protected $valide_n1;

    /** @Column(type="datetime") **/
    protected $validation_n1;

    /** @Column(type="integer") **/
    protected $valide;

    /** @Column(type="datetime") **/
    protected $validation;

    /** @Column(type="text") **/
    protected $refus;

    /** @Column(type="float") **/
    protected $solde_prec;

    /** @Column(type="float") **/
    protected $solde_actuel;
}
