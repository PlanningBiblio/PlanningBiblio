<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_tab_grp')]
class PlanningPositionTabGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $lundi = null;

    #[ORM\Column]
    private ?int $mardi = null;

    #[ORM\Column]
    private ?int $mercredi = null;

    #[ORM\Column]
    private ?int $jeudi = null;

    #[ORM\Column]
    private ?int $vendredi = null;

    #[ORM\Column]
    private ?int $samedi = null;

    #[ORM\Column]
    private ?int $dimanche = null;

    #[ORM\Column]
    private ?int $site = null;

    #[ORM\Column]
    private ?\DateTime $supprime = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMonday(): ?int
    {
        return $this->lundi;
    }

    public function setMonday(?int $monday): static
    {
        $this->lundi = $monday;
        return $this;
    }

    public function getTuesday(): ?int
    {
        return $this->mardi;
    }

    public function setTuesday(?int $tuesday): static
    {
        $this->mardi = $tuesday;
        return $this;
    }

    public function getWednesday(): ?int
    {
        return $this->mercredi;
    }

    public function setWednesday(?int $wednesday): static
    {
        $this->mercredi = $wednesday;
        return $this;
    }

    public function getThursday(): ?int
    {
        return $this->jeudi;
    }

    public function setThursday(?int $thursday): static
    {
        $this->jeudi = $thursday;
        return $this;
    }

    public function getFriday(): ?int
    {
        return $this->vendredi;
    }

    public function setFriday(?int $friday): static
    {
        $this->vendredi = $friday;
        return $this;
    }

    public function getSaturday(): ?int
    {
        return $this->samedi;
    }

    public function setSaturday(?int $saturday): static
    {
        $this->samedi = $saturday;
        return $this;
    }

    public function getSunday(): ?int
    {
        return $this->dimanche;
    }

    public function setSunday(?int $sunday): static
    {
        $this->dimanche = $sunday;
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
