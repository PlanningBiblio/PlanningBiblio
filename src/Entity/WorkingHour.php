<?php

namespace App\Entity;

use App\Repository\WorkingHourRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkingHourRepository::class)]
#[ORM\Table(name: 'planning_hebdo')]
class WorkingHour
{
    #[ORM\Id]
    #[ORM\GeneratedName]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $temps = null;

    #[ORM\Column]
    private ?array $breaktime = null;

    #[ORM\Column]
    private ?\DateTime $saisie = null;

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
    private ?bool $actuel = null;

    #[ORM\Column]
    private ?int $remplace = null;

    #[ORM\Column]
    private ?string $cle = null;

    #[ORM\Column]
    private ?int $exception = null;

    #[ORM\Column]
    private ?int $nb_semaine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBreaktime(): ?array
    {
        return $this->breaktime;
    }

    public function setBreaktime(?array $breaktime): static
    {
        $this->breaktime = $breaktime;

        return $this;
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

    public function getKey(): ?string
    {
        return $this->cle;
    }

    public function setKey(?string $key): static
    {
        $this->cle = $key;
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

    public function getWorkingHours(): ?string
    {
        return $this->temps;
    }

    public function setWorkingHours(?string $workingHours): static
    {
        $this->temps = $workingHours;

        return $this;
    }

    public function getEntryDate(): ?\DateTime
    {
        return $this->saisie;
    }

    public function setEntryDate(?\DateTime $entryDate): static
    {
        $this->saisie = $entryDate;

        return $this;
    }

    public function getChange(): ?int
    {
        return $this->modif;
    }

    public function setChange(?int $change): static
    {
        $this->modif = $change;

        return $this;
    }

    public function getChangeDate(): ?\DateTime
    {
        return $this->modification;
    }

    public function setChangeDate(?\DateTime $modification): static
    {
        $this->modification = $modification;

        return $this;
    }

    public function getValidLevel1(): ?int
    {
        return $this->valide_n1;
    }

    public function setValidLevel1(?int $level1): static
    {
        $this->valide_n1 = $level1;

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

    public function setValidLevel2(?int $level2): static
    {
        $this->valide = $level2;

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

    public function isCurrent(): ?bool
    {
        return $this->actuel;
    }

    public function setCurrent(?bool $current): static
    {
        $this->actuel = $current;

        return $this;
    }

    public function getReplace(): ?int
    {
        return $this->remplace;
    }

    public function setReplace(?int $replace): static
    {
        $this->remplace = $replace;

        return $this;
    }
    
    public function getException(): ?int
    {
        return $this->exception;
    }

    public function setException(?int $exception): static
    {
        $this->exception = $exception;

        return $this;
    }

    public function getNumberOfWeeks(): ?int
    {
        return $this->nb_semaine;
    }

    public function setNumberOfWeeks(?int $number): static
    {
        $this->nb_semaine = $number;

        return $this;
    }
}
