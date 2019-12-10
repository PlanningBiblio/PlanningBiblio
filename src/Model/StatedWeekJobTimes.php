<?php

namespace App\Model;

/**
 * @Entity @Table(name="stated_week_planning_job_times")
 **/
class StatedWeekJobTimes extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $agent_id;

    /** @Column(type="integer") **/
    protected $job_id;

    /** @Column(type="string") **/
    protected $times;
}
