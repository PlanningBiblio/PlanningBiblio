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

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function setPosition(int $position): static
    {
        $this->poste = $position;

        return $this;
    }
    
    public function setStart(\DateTime $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function setEnd(\DateTime $end): static
    {
        $this->fin = $end;

        return $this;
    }

    public function setUser(int $user): static
    {
        $this->perso_id = $user;

        return $this;
    }
    
    public function setAbsent(string $absent): static
    {
        $this->absent = $absent;

        return $this;
    }
    
    public function setDelete(string $delete): static
    {
        $this->supprime = $delete;

        return $this;
    }

    public function setGrey(string $grey): static
    {
        $this->grise = $grey;

        return $this;
    }
}
