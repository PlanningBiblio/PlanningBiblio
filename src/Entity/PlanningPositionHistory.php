<?php

namespace App\Entity;

use App\Repository\PlanningPositionHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningPositionHistoryRepository::class)]
#[ORM\Table(name: 'pl_position_history')]

class PlanningPositionHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private array $perso_ids = [];

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $beginning = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $end = null;

    #[ORM\Column]
    private ?int $site = 1;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column]
    private ?string $action = null;

    #[ORM\Column]
    private ?bool $undone = false;

    #[ORM\Column]
    private ?bool $archive = false;

    #[ORM\Column]
    private ?bool $play_before = false;

    #[ORM\Column]
    private ?int $updated_by = null;

    #[ORM\Column]
    private ?\DateTime $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsers(): array
    {
        return $this->perso_ids;
    }

    public function setUsers(?array $users): static
    {
        $this->perso_ids = $users;

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

    public function getStart(): ?\DateTime
    {
        return $this->beginning;
    }

    public function setStart(?\DateTime $start): static
    {
        $this->beginning = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(?\DateTime $end): static
    {
        $this->end = $end;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function isUndone(): ?bool
    {
        return $this->undone;
    }

    public function setUndone(?bool $undone): static
    {
        $this->undone = $undone;

        return $this;
    }

    public function isArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(?bool $archive): static
    {
        $this->archive = $archive;

        return $this;
    }

    public function isPlayBefore(): ?bool
    {
        return $this->play_before;
    }

    public function setPlayBefore(?bool $playBefore): static
    {
        $this->play_before = $playBefore;

        return $this;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?int $updatedBy): static
    {
        $this->updated_by = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updated_at = $updatedAt;

        return $this;
    }
}
