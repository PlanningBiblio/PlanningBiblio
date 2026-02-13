<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_cellules')]
class PlanningPositionCells
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
    private ?int $colonne = null;

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

    public function getColumn(): ?int
    {
        return $this->colonne;
    }

    public function setColumn(?int $column): static
    {
        $this->colonne = $column;

        return $this;
    }
}
