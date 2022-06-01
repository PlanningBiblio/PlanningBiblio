<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, ManyToMany, JoinTable, JoinColumn, GeneratedValue};

/**
 * @Entity @Table(name="notifications_history")
 **/
class NotificationsHistory extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="text") **/
    protected $subject;

    /** @Column(type="text") **/
    protected $message;

    /** @Column(type="datetime") */
    protected $date;

    /** @Column(type="text") **/
    protected $status;

    /**
     * @ManyToMany(targetEntity="Agent")
     *      @JoinTable(name="notifications_history_agents",
     *      joinColumns={@JoinColumn(name="notification_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="agent_id", referencedColumnName="id")})
     */
    private $agents;

    public function __construct()
    {
        $this->agents = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getAgents()
    {
        return $this->agents;
    }
}