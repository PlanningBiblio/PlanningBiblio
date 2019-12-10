<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="stated_week_plannings")
 **/
class StatedWeek extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="date") **/
    protected $date;

    /** @Column(type="boolean") **/
    protected $locked;

    /**
     * @OneToMany(targetEntity="StatedWeekColumn", mappedBy="planning", cascade={"ALL"})
     */
    protected $columns;

    /**
     * @OneToMany(targetEntity="StatedWeekJob", mappedBy="planning", cascade={"ALL"})
     */
    protected $jobs;

    public function __construct() {
        $this->columns = new ArrayCollection();

        $this->jobs = new ArrayCollection();
    }

    public function addColumn(StatedWeekColumn $column)
    {
        $this->columns->add($column);
        $column->planning($this);
    }

    public function addJob(StatedWeekJob $job)
    {
        $this->jobs->add($job);
        $job->planning($this);
    }
}
