<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="activites")
 **/
class Skill extends PLBEntity{
    /** @Id @Column(type="integer", length = 11) @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $nom;

    /** @Column(type="datetime") **/
    protected $supprime;

    public function getNom(){
        return $this->nom;
    }
}
