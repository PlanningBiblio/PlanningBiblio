<?php

namespace App\Model;

use App\Repository\PositionRepository;
use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity(repositoryClass: PositionRepository::class)]
#[Table(name: 'postes')]
class Position extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'string')] // *
    protected $nom;

    #[Column(type: 'string')] // *
    protected $groupe;

    #[Column(type: 'integer', length: 11)] // *
    protected $groupe_id;

    #[Column(type: 'text', length: 15)] // *
    protected $obligatoire;

    #[Column(type: 'text')] // *
    protected $etage;

    #[Column(type: 'json')] // *
    protected $activites;

    #[Column(type: 'text', columnDefinition: "ENUM('0','1')")] // *
    protected $statistiques;

    #[Column(type: 'text', columnDefinition: "ENUM('0','1')")] // *
    protected $teleworking;

    #[Column(type: 'text', columnDefinition: "ENUM('0','1')")] // *
    protected $bloquant;

    #[Column(type: 'boolean')] // *
    protected $lunch = false;

    #[Column(type: 'integer', length: 1)] // *
    protected $site;

    #[Column(type: 'json')] // *
    protected $categories;

    #[Column(type: 'datetime')] // *
    protected $supprime;

    /** @Column(type="boolean")**/
    protected $quota_sp = true;
}
