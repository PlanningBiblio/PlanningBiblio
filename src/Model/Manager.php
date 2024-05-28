<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue, ManyToOne, JoinColumn};

#[Entity(repositoryClass: \App\Repository\ManagerRepository::class)] // @Table(name="responsables")
class Manager extends PLBEntity {

    #[Id] // @Column(type="integer") @GeneratedValue *
    protected $id;

    #[JoinColumn(name: 'perso_id', referencedColumnName: 'id')]
    #[ManyToOne(targetEntity: \Agent::class, inversedBy: 'managers')]
    protected $perso_id;

    #[JoinColumn(name: 'responsable', referencedColumnName: 'id')]
    #[ManyToOne(targetEntity: \Agent::class, inversedBy: 'managed')]
    protected $responsable;

    #[Column(type: 'integer', length: 1)] // *
    protected $level1 = 1;

    #[Column(type: 'integer', length: 1)] // *
    protected $level2 = 0;

    #[Column(type: 'integer', length: 1)] // *
    protected $notification_level1 = 0;

    #[Column(type: 'integer', length: 1)] // *
    protected $notification_level2 = 0;
}
