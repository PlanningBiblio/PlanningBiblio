<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

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

    /** @Column(type="datetime") */
    protected $fin;

    /** @Column(type="string") */
    protected $start_halfday;

    /** @Column(type="string") */
    protected $end_halfday;

    /** @Column(type="text") */
    protected $commentaires;

    /** @Column(type="text") */
    protected $refus;

    /** @Column(type="string") */
    protected $heures;

    /** @Column(type="string") */
    protected $debit;

    /** @Column(type="datetime") */
    protected $saisie;

    /** @Column(type="integer") */
    protected $saisie_par;

    /** @Column(type="integer") */
    protected $modif;

    /** @Column(type="datetime") */
    protected $modification;

    /** @Column(type="integer") */
    protected $valide_n1;

    /** @Column(type="datetime") */
    protected $validation_n1;

    /** @Column(type="integer") */
    protected $valide;

    /** @Column(type="datetime") */
    protected $validation;

    /** @Column(type="float") */
    protected $solde_prec;

    /** @Column(type="float") */
    protected $solde_actuel;

    /** @Column(type="float") */
    protected $recup_prec;

    /** @Column(type="float") */
    protected $recup_actuel;

    /** @Column(type="float") */
    protected $reliquat_prec;

    /** @Column(type="float") */
    protected $reliquat_actuel;

    /** @Column(type="float") */
    protected $anticipation_prec;

    /** @Column(type="float") */
    protected $anticipation_actuel;

    /** @Column(type="integer") */
    protected $supprime;

    /** @Column(type="datetime") */
    protected $suppr_date;

    /** @Column(type="integer") */
    protected $information;

    /** @Column(type="datetime") */
    protected $info_date;

    /** @Column(type="integer") */
    protected $regul_id;

    /** @Column(type="integer") */
    protected $origin_id;

}
