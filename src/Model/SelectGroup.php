<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="select_groupes")
 **/
class SelectGroup extends PLBEntity {
    /** @Id @Column(type="integer", length = 11) @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $valeur;

    /** @Column(type="integer") **/
    protected $rang;
}
