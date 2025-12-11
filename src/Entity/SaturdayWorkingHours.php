<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'edt_samedi')]
class SaturdayWorkingHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $semaine = null;

    #[ORM\Column]
    private ?int $tableau = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getuserId(): ?int
    {
        return $this->perso_id;
    }

    public function setuserId($userId): static
    {
        $this->perso_id = $userId;

        return $this;
    }

    public function getWeek(): ?int
    {
        return $this->semaine;
    }

    public function setWeek($week): static
    {
        $this->semaine = $week;

        return $this;
    }

    public function getTable(): ?int
    {
        return $this->tableau;
    }

    public function setTable($table): static
    {
        $this->tableau = $table;

        return $this;
    }
}
