<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity]
#[Table(name: 'absences_infos')]
class AbsenceInfo extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTime $debut = null;

    #[Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTime $fin = null;

    #[Column(type: Types::TEXT)]
    protected ?string $texte = null;

}
