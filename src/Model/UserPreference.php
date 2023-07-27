<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity @Table(name="user_preferences")
 **/
class UserPreference extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="string") **/
    protected $pref;

    /** @Column(type="string") **/
    protected $value;

    /** @Column(type="string") **/
    protected $category;

    /** @Column(type="string") **/
    protected $description;
}
