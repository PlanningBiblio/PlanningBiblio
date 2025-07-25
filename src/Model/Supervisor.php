<?php

// FIXME Use Manager instead of Supervisor
// duplicate class

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity]
#[Table(name: 'responsables')]
class Supervisor extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'integer')] // *
    protected $perso_id;

    #[Column(type: 'integer')] // *
    protected $responsable;

    #[Column(type: 'integer')] // *
    protected $notification;

}
