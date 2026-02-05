<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_tab_affect')]
class PlanningPositionTabAffectation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?int $tableau = null;

    #[ORM\Column]
    private ?int $site = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

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
    
    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(?int $site): static
    {
        $this->site = $site;

        return $this;
    }
}
