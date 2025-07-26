<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity]
#[Table(name: 'config')]
class ConfigParam extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(length: 255)]
    protected ?string $nom = null;

    #[Column(length: 255)]
    protected ?string $type = null;

    #[Column(length: 255)]
    protected ?string $valeur = null;

    #[Column(length: 255)]
    protected ?string $commentaires = null;

    #[Column(length: 255)]
    protected ?string $categorie = null;

    #[Column(length: 255)]
    protected ?string $valeurs = null;

    #[Column]
    protected ?bool $technical = false;

    #[Column(length: 255)]
    protected ?string $extra = null;

    #[Column]
    protected ?int $ordre = null;
}
