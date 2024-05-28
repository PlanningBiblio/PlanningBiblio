<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity] // @Table(name="cron")
class Cron extends PLBEntity
{
    #[Id] // @Column(type="integer") @GeneratedValue *
    protected $id;

    #[Column(type: 'string', length: 2)] // *
    protected $m;

    #[Column(type: 'string', length: 2)] // *
    protected $h;

    #[Column(type: 'string', length: 2)] // *
    protected $dom;

    #[Column(type: 'string', length: 2)] // *
    protected $mon;

    #[Column(type: 'string', length: 2)] // *
    protected $dow;

    #[Column(type: 'text')] // *
    protected $command;

    #[Column(type: 'text')] // *
    protected $comments;

    #[Column(type: 'datetime')] // *
    protected $last;

    #[Column(type: 'boolean')] // *
    protected $disabled;
}
