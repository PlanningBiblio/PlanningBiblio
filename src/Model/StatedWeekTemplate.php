<?php

namespace App\Model;

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

    public function __construct() {
        $this->times = new ArrayCollection();
    }

    public function addTime(StatedWeekTimeTemplate $time)
    {
        $this->times->add($time);
        $time->template($this);
    }
}
