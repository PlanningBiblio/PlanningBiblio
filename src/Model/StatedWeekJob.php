<?php

namespace App\Model;

/**
 * @Entity @Table(name="stated_week_planning_job")
 **/
class StatedWeekJob extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $planning_id;

    /** @Column(type="string") **/
    protected $name;

    /** @Column(type="string") **/
    protected $description;

    /**
     * @ManyToOne(targetEntity="StatedWeek", inversedBy="jobs")
     * @JoinColumn(name="planning_id", referencedColumnName="id")
     */
    protected $planning;
}
