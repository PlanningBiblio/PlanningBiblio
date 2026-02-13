<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'jours_feries')]
class PublicHoliday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $annee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $jour = null;

    #[ORM\Column]
    private ?int $ferie = null;

    #[ORM\Column]
    private ?int $fermeture = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?string
    {
        return $this->annee;
    }

    public function setYear(?string $year): static
    {
        $this->annee = $year;
        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->jour;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->jour = $date;
        return $this;
    }

    public function getDayOff(): ?int
    {
        return $this->ferie;
    }

    public function setDayOff(?int $dayOff): static
    {
        $this->ferie = $dayOff;
        return $this;
    }

    public function getDayClosed(): ?int
    {
        return $this->fermeture;
    }

    public function setDayClosed(?int $dayClosed): static
    {
        $this->fermeture = $dayClosed;
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

    public function getComment(): ?string
    {
        return $this->commentaire;
    }

    public function setComment(?string $comment): static
    {
        $this->commentaire = $comment;
        return $this;
    }
}
