<?php

namespace App\Model;

use App\Repository\AbsenceReasonRepository;
use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity(repositoryClass: AbsenceReasonRepository::class)]
#[Table(name: 'select_abs')]
class AbsenceReason extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: Types::TEXT)]
    protected ?string $valeur = null;

    #[Column]
    protected ?int $rang = null;

    #[Column]
    protected ?int $type = null;

    #[Column(length: 255)]
    protected ?string $notification_workflow = null;

    #[Column]
    protected ?int $teleworking = null;
}
