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
class PublicHoliday extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $annee;

    /** @Column(type="date") **/
    protected $jour;

    /** @Column(type="integer") */
    protected $ferie;

    /** @Column(type="integer") */
    protected $fermeture;

    /** @Column(type="text") */
    protected $nom;

    /** @Column(type="text") */
    protected $commentaire;
}
