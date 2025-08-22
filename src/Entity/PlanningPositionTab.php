<?php

namespace App\Entity;

use App\Repository\PlanningPositionTabRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningPositionTabRepository::class)]
#[ORM\Table(name: 'pl_poste_tab')]
class PlanningPositionTab
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $tableau = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $site = null;

    #[ORM\Column]
    private ?\DateTime $supprime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTable(): ?string
    {
        return $this->tableau;
    }

    public function setTable(?string $tableau): static
    {
        $this->tableau = $table;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->nom;
    }

    public function setName(?string $name): static
    {
        $this->nom = $name;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(?int $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getDelete(): ?\DateTime
    {
        return $this->supprime;
    }

    public function setDelete(?\DateTime $delete): static
    {
        $this->supprime = $delete;

        return $this;
    }
}
