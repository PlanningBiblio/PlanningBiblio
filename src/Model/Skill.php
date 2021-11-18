<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity(repositoryClass="App\Repository\SkillRepository") @Table(name="activites")
 **/
class Skill extends PLBEntity{
    /** @Id @Column(type="integer", length = 11) @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $nom;

    /** @Column(type="datetime") **/
    protected $supprime;
}
