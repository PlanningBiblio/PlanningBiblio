<?php

namespace App\Model;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity(repositoryClass: SkillRepository::class)]
#[Table(name: 'activites')]
class Skill extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'string')] // *
    protected $nom;

    #[Column(type: 'datetime')] // *
    protected $supprime;
}
