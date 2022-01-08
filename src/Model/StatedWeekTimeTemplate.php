<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue, ManyToOne, JoinColumn};

/**
 * @Entity @Table(name="stated_week_planning_time_templates")
 **/
class StatedWeekTimeTemplate extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $template_id;

    /** @Column(type="integer") **/
    protected $day_index;

    /** @Column(type="string") **/
    protected $job;

    /** @Column(type="integer") **/
    protected $agent_id;

    /** @Column(type="time") **/
    protected $starttime;

    /** @Column(type="time") **/
    protected $endtime;

    /** @Column(type="time") **/
    protected $breaktime;

    /**
     * @ManyToOne(targetEntity="StatedWeekTemplate", inversedBy="times")
     * @JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;
}
