<?php

namespace App\Model;

/**
 * @Entity @Table(name="interchanges")
 **/
class Interchange extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $planning;

    /** @Column(type="integer") **/
    protected $requester;

    /** @Column(type="integer") **/
    protected $requester_time;

    /** @Column(type="integer") **/
    protected $asked;

    /** @Column(type="integer") **/
    protected $asked_time;

    /** @Column(type="string") **/
    protected $status;
}