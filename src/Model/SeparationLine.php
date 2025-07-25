<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity]
#[Table(name: 'lignes')]
class SeparationLine extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'string')] // *
    protected $nom;
}
