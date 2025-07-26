<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity]
#[Table(name: 'cron')]
class Cron extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(length: 2)]
    protected ?string $m = null;

    #[Column(length: 2)]
    protected ?string $h = null;

    #[Column(length: 2)]
    protected ?string $dom = null;

    #[Column(length: 2)]
    protected ?string $mom = null;

    #[Column(length: 2)]
    protected ?string $dow = null;

    #[Column(type: Types::TEXT)]
    protected ?string $command = null;

    #[Column(type: Types::TEXT)]
    protected ?string $comments = null;

    #[Column]
    protected ?\DateTime $last = null;

    #[Column]
    protected ?bool $disabled = null;
}
