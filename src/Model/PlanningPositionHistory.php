<?php

namespace App\Model;

use App\Repository\PlanningPositionHistoryRepository;
use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity(repositoryClass: PlanningPositionHistoryRepository::class)]
#[Table(name: 'pl_position_history')]

class PlanningPositionHistory extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'json')] // *
    protected $perso_ids;

    #[Column(type: 'date')] // *
    protected $date;

    #[Column(type: 'time')] // *
    protected $beginning;

    #[Column(type: 'time')] // *
    protected $end;

    #[Column(type: 'integer')] // *
    protected $site = 1;

    #[Column(type: 'integer')] // *
    protected $position;

    #[Column(type: 'string')] // *
    protected $action;

    #[Column(type: 'boolean')] // *
    protected $undone = 0;

    #[Column(type: 'boolean')] // *
    protected $archive = 0;

    #[Column(type: 'boolean')] // *
    protected $play_before = 0;

    #[Column(type: 'integer')] // *
    protected $updated_by;

    #[Column(type: 'datetime')] // *
    protected $updated_at;
}
