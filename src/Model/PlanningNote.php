<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="pl_notes")
 **/
class PlanningNote extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="date") **/
    protected $date;

    /** @Column(type="integer") **/
    protected $site;

    /** @Column(type="text") */
    protected $text;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="datetime") **/
    protected $timestamp;

}
