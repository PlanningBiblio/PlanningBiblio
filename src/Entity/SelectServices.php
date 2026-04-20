<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'select_services')]
class SelectServices
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

     #[ORM\Column]
    private ?string $valeur = '';

    #[ORM\Column]
    private ?int $rang = 0;

    #[ORM\Column]
    private ?string $couleur = '';

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $network_id = null;

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

    public function getNetworkId(): ?int
    {
        return $this->network_id;
    }

    public function setNetworkId(?int $network_id): void
    {
        $this->network_id = $network_id;
    }
}
