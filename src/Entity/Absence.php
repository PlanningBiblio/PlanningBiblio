<?php

namespace App\Entity;

use App\Repository\AbsenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbsenceRepository::class)]
#[ORM\Table(name: 'absences')]
class Absence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column]
    private ?\DateTime $debut = null;

    #[ORM\Column]
    private ?\DateTime $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motif = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motif_autre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaires = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $etat = null;

    #[ORM\Column]
    private ?\DateTime $demande = null;

    #[ORM\Column]
    private ?int $valide = null;

    #[ORM\Column]
    private ?\DateTime $validation = null;

    #[ORM\Column]
    private ?int $valide_n1 = null;

    #[ORM\Column]
    private ?\DateTime $validation_n1 = null;

    #[ORM\Column]
    private ?int $pj1 = null;

    #[ORM\Column]
    private ?int $pj2 = null;

    #[ORM\Column]
    private ?int $so = null;

    #[ORM\Column]
    private ?string $groupe = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $cal_name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $ical_key = null;

    #[ORM\Column]
    private ?string $last_modified = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $uid = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rrule = null;

    #[ORM\Column]
    private ?int $id_origin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTime
    {
        return $this->debut;
    }

    public function setStart(?\DateTime $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->fin;
    }

    public function setEnd(?\DateTime $end): static
    {
        $this->fin = $end;

        return $this;
    }

    public function getValidLevel1(): ?int
    {
        return $this->valide_n1;
    }

    public function setValidLevel1(?int $valid): static
    {
        $this->valide_n1 = $valid;

        return $this;
    }

    public function getValidLevel2(): ?int
    {
        return $this->valide;
    }

    public function setValidLevel2(?int $valid): static
    {
        $this->valide = $valid;

        return $this;
    }
}
