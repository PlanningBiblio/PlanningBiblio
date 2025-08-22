<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'select_groupes')]
class SelectGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $valeur = null;

    #[ORM\Column]
    private ?int $rang = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->valeur;
    }

    public function setValue(string $value): static
    {
        $this->valeur = $value;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rang;
    }

    public function setRank(int $rank): static
    {
        $this->rang = $rank;

        return $this;
    }
}
