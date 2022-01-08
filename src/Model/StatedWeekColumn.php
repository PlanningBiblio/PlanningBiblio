<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue, ManyToOne, JoinColumn};

/**
 * @Entity @Table(name="stated_week_planning_columns")
 **/
class StatedWeekColumn extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $planning_id;

    /** @Column(type="string", columnDefinition="ENUM('first-slot', 'second-slot', 'third-slot')") **/
    protected $type;

    /** @Column(type="time") **/
    protected $starttime;

    /** @Column(type="time") **/
    protected $endtime;

    /**
     * @ManyToOne(targetEntity="StatedWeek", inversedBy="columns")
     * @JoinColumn(name="planning_id", referencedColumnName="id")
     */
    protected $planning;

}
