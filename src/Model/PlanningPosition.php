<?php

namespace App\Model;

use App\Repository\PlanningPositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningPositionRepository::class)]
#[ORM\Table(name: 'pl_poste')]
class PlanningPosition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?int $poste = null;

    #[ORM\Column]
    private ?string $absent = null;

    #[ORM\Column]
    private ?int $chgt_login = null;

    #[ORM\Column]
    private ?\DateTime $chgt_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $debut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $fin = null;

    #[ORM\Column]
    private ?string $supprime = null;

    #[ORM\Column]
    private ?int $site = null;

    #[ORM\Column]
    private ?string $grise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->poste;
    }

    public function setPosition(int $position): static
    {
        $this->poste = $position;

        return $this;
    }
}
