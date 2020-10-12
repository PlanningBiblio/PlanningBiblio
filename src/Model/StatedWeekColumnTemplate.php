<?php

namespace App\Model;

/**
 * @Entity @Table(name="stated_week_planning_column_templates")
 **/
class StatedWeekColumnTemplate extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $template_id;

    /** @Column(type="integer") **/
    protected $day_index;

    /** @Column(type="string") **/
    protected $slot;

    /** @Column(type="time") **/
    protected $starttime;

    /** @Column(type="time") **/
    protected $endtime;

    /**
     * @ManyToOne(targetEntity="StatedWeekTemplate", inversedBy="columns")
     * @JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;
}
