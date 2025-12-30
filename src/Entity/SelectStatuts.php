<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'select_statuts')]
class SelectStatuts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    protected ?string $valeur = '';

    #[ORM\Column]
    protected ?int $rang = 0;

    #[ORM\Column]
    protected ?string $couleur = '';

    #[ORM\Column]
    protected ?int $categorie = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->valeur;
    }

    public function setValue(?string $value): static
    {
        $this->valeur = $value;
        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rang;
    }

    public function setRank(?int $rank): static
    {
        $this->rang = $rank;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->couleur;
    }

    public function setColor(?string $color): static
    {
        $this->couleur = $color;
        return $this;
    }

    public function getCategory(): ?int
    {
        return $this->categorie;
    }

    public function setCategory(?int $category): static
    {
        $this->categorie = $category;
        return $this;
    }
}