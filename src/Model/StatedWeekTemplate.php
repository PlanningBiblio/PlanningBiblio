<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue, OneToMany};
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="stated_week_planning_templates")
 **/
class StatedWeekTemplate extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $name;

    /** @Column(type="string", columnDefinition="ENUM('day', 'week')") **/
    protected $type;

    /**
     * @OneToMany(targetEntity="StatedWeekTimeTemplate", mappedBy="template", cascade={"ALL"})
     */
    protected $times;

    /**
     * @OneToMany(targetEntity="StatedWeekColumnTemplate", mappedBy="template", cascade={"ALL"})
     */
    protected $columns;

    public function __construct() {
        $this->times = new ArrayCollection();
        $this->columns = new ArrayCollection();
    }

    public function addTime(StatedWeekTimeTemplate $time)
    {
        $this->times->add($time);
        $time->template($this);
    }

    public function addColumn(StatedWeekColumnTemplate $column)
    {
        $this->columns->add($column);
        $column->template($this);
    }
}
