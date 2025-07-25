<?php

namespace App\Model;

use App\Entity\Test4;
use App\Repository\AbsenceRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity(repositoryClass: AbsenceRepository::class)]
#[Table(name: 'absences')]
class Absence extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column]
    protected ?int $perso_id = null;

    #[Column]
    protected ?\DateTime $debut = null;

    #[Column]
    protected ?\DateTime $fin = null;

    #[Column(type: Types::TEXT)]
    protected ?string $motif = null;

    #[Column(type: Types::TEXT)]
    protected ?string $motif_autre = null;

    #[Column(type: Types::TEXT)]
    protected ?string $commentaires = null;

    #[Column(type: Types::TEXT)]
    protected ?string $etat = null;

    #[Column]
    protected ?\DateTime $demande = null;

    #[Column]
    protected ?int $valide = null;

    #[Column]
    protected ?\DateTime $validation = null;

    #[Column]
    protected ?int $valide_n1 = null;

    #[Column]
    protected ?\DateTime $validation_n1 = null;

    #[Column]
    protected ?int $pj1 = null;

    #[Column]
    protected ?int $pj2 = null;

    #[Column]
    protected ?int $so = null;

    #[Column(type: 'string')]
    protected $groupe;

    #[Column(type: Types::TEXT)]
    protected ?string $cal_name = null;

    #[Column(type: Types::TEXT)]
    protected ?string $ical_key = null;

    #[Column(type: 'string')]
    protected $last_modified;

    #[Column(type: Types::TEXT)]
    protected ?string $uid = null;

    #[Column(type: Types::TEXT)]
    protected ?string $rrule = null;

    #[Column]
    protected ?int $id_origin = null;
}
