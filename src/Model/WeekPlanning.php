<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="planning_hebdo")
 **/
class WeekPlanning extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="date") **/
    protected $debut;

    /** @Column(type="date") **/
    protected $fin;

    /** @Column(type="text") **/
    protected $temps;

    /** @Column(type="text") **/
    protected $breaktime;

    /** @Column(type="datetime") **/
    protected $saisie;

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

    /** @Column(type="integer") **/
    protected $actuel;

    /** @Column(type="integer") **/
    protected $remplace;

    /** @Column(type="string") **/
    protected $cle;

    /** @Column(type="integer") **/
    protected $exception;

    /** @Column(type="integer") **/
    protected $nb_semaine;
}
