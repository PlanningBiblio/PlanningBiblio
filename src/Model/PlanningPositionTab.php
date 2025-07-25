<?php

namespace App\Model;

use App\Repository\PlanningPositionTabRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity(repositoryClass: PlanningPositionTabRepository::class)]
#[Table(name: 'pl_poste_tab')]
class PlanningPositionTab extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'integer')] // *
    protected $tableau;

    #[Column(type: 'text')] // *
    protected $nom;

    #[Column(type: 'integer')] // *
    protected $site;

    #[Column(type: 'datetime')] // *
    protected $supprime;

}
