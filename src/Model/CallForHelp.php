<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity]
#[Table(name: 'appel_dispo')]
class CallForHelp extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column]
    protected ?int $site = null;

    #[Column]
    protected ?int $poste = null;

    #[Column]
    protected ?\DateTime $date = null;

    #[Column]
    protected ?\DateTime $debut = null;

    #[Column]
    protected ?\DateTime $fin = null;

    #[Column(type: Types::TEXT)]
    protected ?string $destinataires = null;

    #[Column(type: Types::TEXT)]
    protected ?string $sujet = null;

    #[Column(type: Types::TEXT)]
    protected ?string $message = null;

    #[Column]
    protected ?\DateTime $timestamp = null;
}
