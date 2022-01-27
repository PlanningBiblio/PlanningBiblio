<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="pl_poste_tab_grp")
 **/
class PlanningPositionTabGroup extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="text") **/
    protected $nom;

    /** @Column(type="integer") **/
    protected $lundi;

    /** @Column(type="integer") **/
    protected $mardi;

    /** @Column(type="integer") **/
    protected $mercredi;

    /** @Column(type="integer") **/
    protected $jeudi;

    /** @Column(type="integer") **/
    protected $vendredi;

    /** @Column(type="integer") **/
    protected $samedi;

    /** @Column(type="integer") **/
    protected $dimanche;

    /** @Column(type="integer") **/
    protected $site;

    /** @Column(type="datetime") **/
    protected $supprime;

}
