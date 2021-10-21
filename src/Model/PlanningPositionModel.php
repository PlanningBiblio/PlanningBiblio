<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="pl_poste_modeles")
 **/
class PlanningPositionModel extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $model_id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="integer") **/
    protected $poste;

    /** @Column(type="text") **/
    protected $commentaire;

    /** @Column(type="time") **/
    protected $debut;

    /** @Column(type="time") **/
    protected $fin;

    /** @Column(type="string") **/
    protected $tableau;

    /** @Column(type="string") **/
    protected $jour;

    /** @Column(type="integer") **/
    protected $site;
}
