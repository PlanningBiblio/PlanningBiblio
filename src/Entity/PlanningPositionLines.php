<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_lignes')]
class PlanningPositionLines
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column]
    private ?int $tableau = null;

    #[ORM\Column]
    private ?int $ligne = null;

    #[ORM\Column]
    private ?string $poste = null;

    #[ORM\Column]
    private ?string $type = 'poste';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->numero;
    }

    public function setNumber(?int $number): static
    {
        $this->numero = $number;

        return $this;
    }

    public function getTable(): ?int
    {
        return $this->tableau;
    }

    public function setTable(?int $table): static
    {
        $this->tableau = $table;

        return $this;
    }

    public function getLine(): ?int
    {
        return $this->ligne;
    }

    public function setLine(?int $line): static
    {
        $this->ligne = $line;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->poste;
    }

    public function setPosition(?string $position): static
    {
        $this->poste = $position;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

}
