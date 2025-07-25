<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity]
#[Table(name: 'log')]
class Logs extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'text')] // *
    protected $msg;

    #[Column(type: 'string')] // *
    protected $program;

    #[Column(type: 'datetime')]
    protected $timestamp;

}
