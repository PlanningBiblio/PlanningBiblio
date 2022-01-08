<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

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

    /** @Column(type="datetime") **/
    protected $requested_on;

    /** @Column(type="integer") **/
    protected $requester_time;

    /** @Column(type="integer") **/
    protected $asked;

    /** @Column(type="integer") **/
    protected $asked_time;

    /** @Column(type="integer") **/
    protected $accepted_by;

    /** @Column(type="datetime") **/
    protected $accepted_on;

    /** @Column(type="integer") **/
    protected $rejected_by;

    /** @Column(type="datetime") **/
    protected $rejected_on;

    /** @Column(type="integer") **/
    protected $validated_by;

    /** @Column(type="datetime") **/
    protected $validated_on;

    /** @Column(type="string") **/
    protected $status;
}
