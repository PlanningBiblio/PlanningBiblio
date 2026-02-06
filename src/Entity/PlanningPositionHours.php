<?php

namespace App\Entity;

use App\Repository\PlanningPositionHoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningPositionHoursRepository::class)]
#[ORM\Table(name: 'pl_poste_horaires')]
class PlanningPositionHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $debut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $fin = null;

    #[ORM\Column]
    private ?int $tableau = null;

    #[ORM\Column]
    private ?int $numero = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->fin;
    }
}
