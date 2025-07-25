<?php

namespace App\Model;

use App\Repository\ManagerRepository;
use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue, ManyToOne, JoinColumn};

#[Entity(repositoryClass: ManagerRepository::class)]
#[Table(name: 'responsables')]
class Manager extends PLBEntity {

    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[JoinColumn(name: 'perso_id', referencedColumnName: 'id')]
    #[ManyToOne(inversedBy: 'managers')]
    protected ?Agent $perso_id = null;

    #[JoinColumn(name: 'responsable', referencedColumnName: 'id')]
    #[ManyToOne(inversedBy: 'managed')]
    protected ?Agent $responsable = null;

    #[Column(type: Types::SMALLINT)]
    protected ?int $level1 = 0;

    #[Column(type: Types::SMALLINT)]
    protected ?int $level2 = 0;

    #[Column(type: Types::SMALLINT)]
    protected ?int $notification_level1 = 0;

    #[Column(type: Types::SMALLINT)]
    protected ?int $notification_level2 = 0;
}
