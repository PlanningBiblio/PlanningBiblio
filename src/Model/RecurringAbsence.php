<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="absences_recurrentes")
 **/
class RecurringAbsence extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $uid;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="text") **/
    protected $event;

    /** @Column(type="integer") **/
    protected $end;

    /** @Column(type="datetime") **/
    protected $timestamp;

    /** @Column(type="datetime") **/
    protected $last_update;

    /** @Column(type="datetime") **/
    protected $last_check;
}
