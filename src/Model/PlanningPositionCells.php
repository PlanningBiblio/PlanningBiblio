<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="pl_poste_cellules")
 **/
class PlanningPositionCells extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $numero;

    /** @Column(type="integer") **/
    protected $tableau;

    /** @Column(type="integer") **/
    protected $ligne;

    /** @Column(type="integer") **/
    protected $colonne;
}
