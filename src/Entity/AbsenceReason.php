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
    private ?string $valeur = null;

    #[ORM\Column]
    private ?int $rang = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(length: 255)]
    private ?string $notification_workflow = null;

    #[ORM\Column]
    private ?int $teleworking = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotificationWorkflow(): ?string
    {
        return $this->notification_workflow;
    }

    public function setNotificationWorkflow(?string $notificationWorkflow): static
    {
        $this->notification_workflow = $notificationWorkflow;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rang;
    }

    public function setRank(?int $rang): static
    {
        $this->rang = $rang;

        return $this;
    }

    public function getTeleworking(): ?int
    {
        return $this->teleworking;
    }

    public function setTeleworking(?int $teleworking): static
    {
        $this->teleworking= $teleworking;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->valeur;
    }

    public function setValue(?string $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }
}
