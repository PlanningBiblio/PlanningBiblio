<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'lignes')]
class SeparationLine
{
    #[ORM\Id]
    #[ORM\GeneratedName]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $nom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->nom;
    }

    public function setName(string $name): static
    {
        $this->nom = $name;

        return $this;
    }
}
