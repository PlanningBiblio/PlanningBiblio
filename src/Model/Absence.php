<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="App\Repository\AbsenceRepository") @Table(name="absences")
 **/
class Absence extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="datetime") **/
    protected $debut;

    /** @Column(type="datetime") */
    protected $fin;

    /** @Column(type="text") */
    protected $motif;

    /** @Column(type="text") */
    protected $motif_autre;

    /** @Column(type="text") */
    protected $commentaires;

    /** @Column(type="text") */
    protected $etat;

    /** @Column(type="datetime") */
    protected $demande;

    /** @Column(type="integer") */
    protected $valide;

    /** @Column(type="datetime") */
    protected $validation;

    /** @Column(type="integer") */
    protected $valide_n1;

    /** @Column(type="datetime") */
    protected $validation_n1;

    /** @Column(type="integer") */
    protected $pj1;

    /** @Column(type="integer") */
    protected $pj2;

    /** @Column(type="integer") */
    protected $so;

    /** @Column(type="string") */
    protected $groupe;

    /** @Column(type="text") */
    protected $cal_name;

    /** @Column(type="text") */
    protected $ical_key;

    /** @Column(type="string") */
    protected $last_modified;

    /** @Column(type="text") */
    protected $uid;

    /** @Column(type="text") */
    protected $rrule;

    /** @Column(type="integer") */
    protected $id_origin;
}
