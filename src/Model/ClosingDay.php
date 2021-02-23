<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="jours_feries")
 **/
class ClosingDay extends PLBEntity{
    /** @Id @Column(type="integer", length = 11) @GeneratedValue **/
    protected $id;

    /** @Column(type="string", length = 10) **/
    protected $annee;

    /** @Column(type="date") **/
    protected $jour;

    /** @Column(type="boolean") **/
    protected $ferie;

    /** @Column(type="boolean") **/
    protected $fermeture;

    /** @Column(type="text") **/
    protected $nom;

    /** @Column(type="text") **/
    protected $commentaire;
}
