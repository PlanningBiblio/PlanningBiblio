<?php

namespace App\Entity;

use App\Repository\HolidayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HolidayRepository::class)]
#[ORM\Table(name: 'conges')]
class Holiday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column]
    private ?\DateTime $debut = null;

    #[ORM\Column]
    private ?\DateTime $fin = null;

    #[ORM\Column]
    private ?int $halfday = null;

    #[ORM\Column(length: 20)]
    private ?string $start_halfday = null;

    #[ORM\Column(length: 20)]
    private ?string $end_halfday = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaires = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $refus = null;

    #[ORM\Column(length: 20)]
    private ?string $heures = null;

    #[ORM\Column(length: 20)]
    private ?string $debit = null;

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

    #[ORM\Column]
    private ?float $solde_prec = null;

    #[ORM\Column]
    private ?float $solde_actuel = null;

    #[ORM\Column]
    private ?float $recup_prec = null;

    #[ORM\Column]
    private ?float $recup_actuel = null;

    #[ORM\Column]
    private ?float $reliquat_prec = null;

    #[ORM\Column]
    private ?float $reliquat_actuel = null;

    #[ORM\Column]
    private ?float $anticipation_prec = null;

    #[ORM\Column]
    private ?float $anticipation_actuel = null;

    #[ORM\Column]
    private ?int $supprime = null;

    #[ORM\Column]
    private ?\DateTime $suppr_date = null;

    #[ORM\Column]
    private ?int $information = null;

    #[ORM\Column]
    private ?\DateTime $info_date = null;

    #[ORM\Column]
    private ?int $regul_id = null;

    #[ORM\Column]
    private ?int $origin_id = null;

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

    public function getStart(): ?\DateTime
    {
        return $this->debut;
    }

    public function setStart(?\DateTime $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->fin;
    }

    public function setEnd(?\DateTime $end): static
    {
        $this->fin = $end;

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

    public function getDelete(): ?int
    {
        return $this->supprime;
    }

    public function setDelete(?int $userId): static
    {
        $this->supprime = $userId;

        return $this;
    }

    public function getInfo(): ?int
    {
        return $this->information;
    }

    public function setInfo(?int $userId): static
    {
        $this->information = $userId;

        return $this;
    }

    public function getHalfDay(): ?int
    {
        return $this->halfday;
    }

    public function setHalfDay(?int $halfDay): static
    {
        $this->halfday = $halfDay;

        return $this;
    }

    public function getHalfDayStart(): ?string
    {
        return $this->start_halfday;
    }

    public function setHalfDayStart(?string $halfDayStart): static
    {
        $this->start_halfday = $halfDayStart;

        return $this;
    }

    public function getHalfDayEnd(): ?string
    {
        return $this->end_halfday;
    }

    public function setHalfDayEnd(?string $halfDayEnd): static
    {
        $this->end_halfday = $halfDayEnd;

        return $this;
    }

    public function getDebit(): ?string
    {
        return $this->debit;
    }

    public function setDebit(?string $debit): static
    {
        $this->debit = $debit;

        return $this;
    }

    public function getHours(): ?string
    {
        return $this->heures;
    }

    public function setHours(?string $hours): static
    {
        $this->heures = $hours;

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

    public function getPreviousCompTime(): ?float
    {
        return $this->recup_prec;
    }

    public function setPreviousCompTime(?float $compTime): static
    {
        $this->recup_prec = $compTime;

        return $this;
    }

    public function getActualCompTime(): ?float
    {
        return $this->recup_actuel;
    }

    public function setActualCompTime(?float $compTime): static
    {
        $this->recup_actuel = $compTime;

        return $this;
    }

    public function getPreviousRemainder(): ?float
    {
        return $this->reliquat_prec;
    }

    public function setPreviousRemainder(?float $remainder): static
    {
        $this->reliquat_prec = $remainder;

        return $this;
    }

    public function getActualRemainder(): ?float
    {
        return $this->reliquat_actuel;
    }

    public function setActualRemainder(?float $remainder): static
    {
        $this->reliquat_actuel = $remainder;

        return $this;
    }

    public function getPreviousAnticipation(): ?float
    {
        return $this->anticipation_prec;
    }

    public function setPreviousAnticipation(?float $anticipation): static
    {
        $this->anticipation_prec = $anticipation;

        return $this;
    }

    public function getActualAnticipation(): ?float
    {
        return $this->anticipation_actuel;
    }

    public function setActualAnticipation(?float $anticipation): static
    {
        $this->anticipation_actuel = $anticipation;

        return $this;
    }
}
