<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity @Table(name="hidden_sites")
 **/
class HiddenSites extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="string") **/
    protected $hidden_sites;
}
