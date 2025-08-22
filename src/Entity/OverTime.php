<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'recuperations')]
class OverTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, name: 'date')]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date2 = null;

    #[ORM\Column]
    private ?float $heures = null;

    #[ORM\Column]
    private ?string $etat = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaires = null;

    #[ORM\Column]
    private ?\DateTime $saisie = null;

    #[ORM\Column]
    private ?int $saisie_par = null;

    #[ORM\Column]
    private ?int $modif = null;

    #[ORM\Column]
    private ?\DateTime $modification = null;

    #[ORM\Column]
    private ?int $valide_n1 = null;

    #[ORM\Column]
    private ?\DateTime $validation_n1 = null;

    #[ORM\Column]
    private ?int $valide = null;

    #[ORM\Column]
    private ?\DateTime $validation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $refus = null;

    #[ORM\Column]
    private ?float $solde_prec = null;

    #[ORM\Column]
    private ?float $solde_actuel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?int
    {
        return $this->perso_id;
    }

    public function setUser(?int $user): static
    {
        $this->perso_id = $user;

        return $this;
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

    public function getDate2(): ?\DateTime
    {
        return $this->date2;
    }

    public function setDate2(?\DateTime $date): static
    {
        $this->date2 = $date;

        return $this;
    }

    public function getHours(): ?float
    {
        return $this->heures;
    }

    public function setHours(?float $hours): static
    {
        $this->heures = $hours;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->commentaires;
    }

    public function setComment(?string $comment): static
    {
        $this->commentaires = $comment;

        return $this;
    }

    public function getEntry(): ?int
    {
        return $this->saisie_par;
    }

    public function setEntry(?int $userId): static
    {
        $this->saisie_par = $userId;

        return $this;
    }

    public function getEntryDate(): ?\DateTime
    {
        return $this->saisie;
    }

    public function setEntryDate(?\DateTime $date): static
    {
        $this->saisie = $date;

        return $this;
    }

    public function getChange(): ?int
    {
        return $this->modif;
    }

    public function setChange(?int $userId): static
    {
        $this->modif = $userId;

        return $this;
    }

    public function getValidLevel1(): ?int
    {
        return $this->valide_n1;
    }

    public function setValidLevel1(?int $userId): static
    {
        $this->valide_n1 = $userId;

        return $this;
    }

    public function getValidLevel1Date(): ?\DateTime
    {
        return $this->validation_n1;
    }

    public function setValidLevel1Date(?\DateTime $date): static
    {
        $this->validation_n1 = $date;

        return $this;
    }

    public function getValidLevel2(): ?int
    {
        return $this->valide;
    }

    public function setValidLevel2(?int $userId): static
    {
        $this->valide = $userId;

        return $this;
    }

    public function getValidLevel2Date(): ?\DateTime
    {
        return $this->validation;
    }

    public function setValidLevel2Date(?\DateTime $date): static
    {
        $this->validation = $date;

        return $this;
    }

    public function getPreviousCredit(): ?float
    {
        return $this->solde_prec;
    }

    public function setPreviousCredit(?float $credit): static
    {
        $this->solde_prec = $credit;

        return $this;
    }

    public function getActualCredit(): ?float
    {
        return $this->solde_actuel;
    }

    public function setActualCredit(?float $credit): static
    {
        $this->solde_actuel = $credit;

        return $this;
    }
}
