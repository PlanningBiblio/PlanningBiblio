<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity] // @Table(name="lignes")
class SeparationLine extends PLBEntity
{
    #[Id] // @Column(type="integer") @GeneratedValue *
    protected $id;

    #[Column(type: 'string')] // *
    protected $nom;
}
