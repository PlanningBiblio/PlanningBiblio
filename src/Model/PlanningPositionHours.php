<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="pl_poste_horaires")
 **/
class PlanningPositionHours extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="time") **/
    protected $debut;

    /** @Column(type="time") **/
    protected $fin;

    /** @Column(type="integer") **/
    protected $tableau;

    /** @Column(type="integer") **/
    protected $numero;
}
