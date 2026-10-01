<?php

namespace App\Entity;

use App\Repository\PublicHolidayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicHolidayRepository::class)]
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
    private ?bool $ferie = true;

    #[ORM\Column]
    private ?bool $fermeture = false;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?\DateTime
    {
        return $this->jour;
    }

    public function setDate(?\DateTime $day): static
    {
        $this->jour = $day;

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

    public function getYear(): ?string
    {
        return $this->annee;
    }

    public function setYear(?string $year): static
    {
        $this->annee = $year;

        return $this;
    }

    public function isClosed(): bool
    {
        return $this->fermeture;
    }

    public function setClosed(?bool $closed): static
    {
        $this->fermeture = $closed;

        return $this;
    }

    public function isPublicHoliday(): bool
    {
        return $this->ferie;
    }

    public function setPublicHoliday(?bool $holiday): static
    {
        $this->ferie = $holiday;

        return $this;
    }
}
