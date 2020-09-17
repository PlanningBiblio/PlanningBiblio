<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="select_abs")
 **/
class AbsenceReason extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="text") **/
    protected $valeur;

    /** @Column(type="integer") **/
    protected $rang;

    /** @Column(type="integer") **/
    protected $type;

    /** @Column(type="string") **/
    protected $notification_workflow;
}
