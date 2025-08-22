<?php

namespace App\Entity;

use App\Repository\WorkingHourRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkingHourRepository::class)]
#[ORM\Table(name: 'planning_hebdo')]
class WorkingHour
{
    #[ORM\Id]
    #[ORM\GeneratedName]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $temps = null;

    #[ORM\Column]
    private ?array $breaktime = null;

    #[ORM\Column]
    private ?\DateTime $saisie = null;

    #[ORM\Column]
    private ?int $modif = null;

    #[ORM\Column]
    private ?\DateTime $modification = null;

    #[ORM\Column]
    private ?int $valide_n1 = null;

    #[ORM\Column]
    private ?\DateTime $validation_n1 = null;

    #[ORM\Column]
    private ?int $valide = null;

    #[ORM\Column]
    private ?\DateTime $validation = null;

    #[ORM\Column]
    private ?int $actuel = null;

    #[ORM\Column]
    private ?int $remplace = null;

    #[ORM\Column]
    private ?string $cle = null;

    #[ORM\Column]
    private ?int $exception = null;

    #[ORM\Column]
    private ?int $nb_semaine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBreaktime(): array
    {
        return $this->breaktime;
    }

    public function setBreaktime(?array $breaktime): static
    {
        $this->breaktime = $breaktime;

        return $this;
    }

    public function getUser(): int
    {
        return $this->perso_id;
    }

    public function setUser(?array $user): static
    {
        $this->perso_id = $user;

        return $this;
    }
}
