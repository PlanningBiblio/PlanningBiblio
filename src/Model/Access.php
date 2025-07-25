<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity]
#[Table(name: 'acces')]
class Access extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: Types::TEXT)]
    protected ?string $nom = null;

    #[Column]
    protected ?int $groupe_id = null;

    #[Column(type: Types::TEXT)]
    protected ?string $groupe = null;

    #[Column(length: 255)]
    protected ?string $page = null;

    #[Column]
    protected ?int $ordre = null;

    #[Column(length: 255)]
    protected ?string $categorie = null;
}
