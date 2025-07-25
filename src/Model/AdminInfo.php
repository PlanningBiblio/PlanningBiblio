<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity]
#[Table(name: 'infos')]
class AdminInfo extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(length: 255)]
    protected ?string $debut = null;

    #[Column(length: 255)]
    protected ?string $fin = null;

    #[Column(type: Types::TEXT)]
    protected ?string $texte = null;
}
