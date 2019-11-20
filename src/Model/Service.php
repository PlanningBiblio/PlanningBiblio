<?php

namespace App\Model;

/**
 * @Entity @Table(name="select_services")
 **/
class Service extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $valeur;

    /** @Column(type="integer")  **/
    protected $rang;

}