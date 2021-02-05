<?php

namespace App\Model;

/**
 * @Entity @Table(name="stated_week_planning_times")
 **/
class StatedWeekTimes extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $agent_id;

    /** @Column(type="integer") **/
    protected $column_id;

    /** @Column(type="string",nullable=true) **/
    protected $start_time;

    /** @Column(type="string",nullable=true) **/
    protected $end_time;

}
