<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="pl_poste")
 **/
class PlanningPoste extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="date") **/
    protected $date;

    /** @Column(type="integer") **/
    protected $poste;

    /** @Column(type="string") **/
    protected $absent;

    /** @Column(type="integer") **/
    protected $chgt_login;

    /** @Column(type="datetime") **/
    protected $chgt_time;

    /** @Column(type="time") **/
    protected $debut;

    /** @Column(type="time") **/
    protected $fin;

    /** @Column(type="string") **/
    protected $supprime;

    /** @Column(type="integer") **/
    protected $site;

    /** @Column(type="string") **/
    protected $grise;
}
