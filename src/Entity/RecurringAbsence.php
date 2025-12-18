<?php

namespace App\Entity;

use App\Repository\RecurringAbsenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecurringAbsenceRepository::class)]
#[ORM\Table(name: 'absences_recurrentes')]
class RecurringAbsence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $uid = '';

    #[ORM\Column]
    private ?int $perso_id = 0;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $event = '';

    #[ORM\Column]
    private ?bool $end = false;

    #[ORM\Column]
    private ?\DateTime $timestamp = null;

    #[ORM\Column]
    private ?\DateTime $last_update = null;

    #[ORM\Column]
    private ?\DateTime $last_check = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(string $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->end;
    }

    public function setFinished(bool $finished): static
    {
        $this->end = $finished;

        return $this;
    }

    public function getLastCheck(): ?\DateTime
    {
        return $this->last_check;
    }

    public function setLastCheck(\DateTime $last_check): static
    {
        $this->last_check = $last_check;

        return $this;
    }

    public function getLastUpdate(): ?\DateTime
    {
        return $this->last_update;
    }

    public function setLastUpdate(\DateTime $last_update): static
    {
        $this->last_update = $last_update;

        return $this;
    }

    public function getTimeStamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    public function setTimeStamp(\DateTime $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->perso_id;
    }

    public function setUserId(int $userId): static
    {
        $this->perso_id = $userId;

        return $this;
    }
}
