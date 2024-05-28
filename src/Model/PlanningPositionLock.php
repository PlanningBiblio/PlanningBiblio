<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity(repositoryClass: \App\Repository\PlanningPositionLockRepository::class)] // @Table(name="pl_poste_verrou")
class PlanningPositionLock extends PLBEntity
{
    #[Id] // @Column(type="integer") @GeneratedValue *
    protected $id;

    #[Column(type: 'date')] // *
    protected $date;

    #[Column(type: 'integer')] // *
    protected $verrou;

    #[Column(type: 'datetime')] // *
    protected $validation;

    #[Column(type: 'integer')] // *
    protected $perso;

    #[Column(type: 'integer')] // *
    protected $verrou2;

    #[Column(type: 'datetime')] // *
    protected $validation2;

    #[Column(type: 'integer')] // *
    protected $perso2;

    #[Column(type: 'integer')] // *
    protected $vivier;

    #[Column(type: 'integer')] // *
    protected $site;
}
