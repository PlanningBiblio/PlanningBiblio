<?php

namespace App\Entity;

use App\Repository\AbsenceReasonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbsenceReasonRepository::class)]
#[ORM\Table(name: 'select_abs')]
class AbsenceReason
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $valeur = '';

    #[ORM\Column]
    private int $rang = 0;

    #[ORM\Column]
    private int $type = 0;

    #[ORM\Column(length: 255)]
    private string $notification_workflow = 'A';

    #[ORM\Column]
    private bool $teleworking = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotificationWorkflow(): string
    {
        return $this->notification_workflow;
    }

    public function setNotificationWorkflow(string $notificationWorkflow): static
    {
        $this->notification_workflow = $notificationWorkflow;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rang;
    }

    public function setRank(int $rang): static
    {
        $this->rang = $rang;

        return $this;
    }

    public function isTeleworking(): bool
    {
        return $this->teleworking;
    }

    public function setTeleworking(bool $teleworking): static
    {
        $this->teleworking = $teleworking;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): string
    {
        return $this->valeur;
    }

    public function setValue(string $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }
}
