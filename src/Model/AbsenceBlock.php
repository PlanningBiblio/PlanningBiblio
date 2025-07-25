<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity]
#[Table(name: 'absence_blocks')]
class AbsenceBlock extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTime $start = null;

    #[Column(type: Types::DATE_MUTABLE)]
    protected ?\DateTime $end = null;

    #[Column(type: Types::TEXT)]
    protected ?string $comment = null;

}
