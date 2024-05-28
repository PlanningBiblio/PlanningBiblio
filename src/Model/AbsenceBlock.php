<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity] // @Table(name="absence_blocks")
class AbsenceBlock extends PLBEntity
{
    #[Id] // @Column(type="integer") @GeneratedValue *
    protected $id;

    #[Column(type: 'date')] // *
    protected $start;

    #[Column(type: 'date')] // *
    protected $end;

    #[Column(type: 'text')] // *
    protected $comment;

}
