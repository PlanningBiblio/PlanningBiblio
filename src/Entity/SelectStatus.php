<?php

namespace App\Entity;

use App\Repository\SelectStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SelectStatusRepository::class)]
#[ORM\Table(name: 'select_statuts')]
class SelectStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $valeur = '';

    #[ORM\Column]
    private int $rang = 0;

    #[ORM\Column(type: 'string', length: 7)]
    private string $couleur = '';

    #[ORM\Column]
    private int $categorie = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $network_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return $this->valeur;
    }

    public function setValue(string $value): static
    {
        $this->valeur = $value;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rang;
    }

    public function setRank(int $rank): static
    {
        $this->rang = $rank;

        return $this;
    }

    public function getColor(): string
    {
        return $this->couleur;
    }

    public function setColor(string $color): static
    {
        $this->couleur = $color;

        return $this;
    }

    public function getCategory(): int
    {
        return $this->categorie;
    }

    public function setCategory(int $category): static
    {
        $this->categorie = $category;

        return $this;
    }

    public function getNetworkId(): ?int
    {
        return $this->network_id;
    }

    public function setNetworkId(?int $network_id): void
    {
        $this->network_id = $network_id;
    }
}
