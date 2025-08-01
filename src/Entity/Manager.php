<?php

namespace App\Entity;

use App\Entity\Agent;
use App\Repository\ManagerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManagerRepository::class)]
#[ORM\Table(name: 'responsables')]
class Manager
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'perso_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(inversedBy: 'managers')]
    private ?Agent $perso_id = null;

    #[ORM\JoinColumn(name: 'responsable', referencedColumnName: 'id')]
    #[ORM\ManyToOne(inversedBy: 'managed')]
    private ?Agent $responsable = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $level1 = 1;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $level2 = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $notification_level1 = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $notification_level2 = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Agent
    {
        return $this->perso_id;
    }

    public function setUser(Agent $user): static
    {
        $this->perso_id = $user;

        return $this;
    }

    public function getManager(): ?Agent
    {
        return $this->responsable;
    }

    public function setManager(Agent $user): static
    {
        $this->responsable = $user;

        return $this;
    }

    public function getLevel1(): ?int
    {
        return $this->level1;
    }

    public function setLevel1(int $level1): static
    {
        $this->level1 = $level1;

        return $this;
    }

    public function getLevel1Notification(): ?int
    {
        return $this->notification_level1;
    }

    public function setLevel1Notification(int $level1Notification): static
    {
        $this->notification_level1 = $level1Notification;

        return $this;
    }

    public function getLevel2(): ?int
    {
        return $this->level2;
    }

    public function setLevel2(int $level2): static
    {
        $this->level2 = $level2;

        return $this;
    }

    public function getLevel2Notification(): ?int
    {
        return $this->notification_level2;
    }

    public function setLevel2Notification(int $level2Notification): static
    {
        $this->notification_level2 = $level2Notification;

        return $this;
    }
}
