<?php

namespace App\Model;

/**
 * @Entity @Table(name="stated_week_planning_pauses")
 **/
class StatedWeekPause extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $agent_id;

    /** @Column(type="integer") **/
    protected $planning_id;

    /**
     * @ManyToOne(targetEntity="StatedWeek", inversedBy="pauses")
     * @JoinColumn(name="planning_id", referencedColumnName="id")
     */
    protected $planning;
}
