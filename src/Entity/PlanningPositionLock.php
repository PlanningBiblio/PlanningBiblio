<?php

namespace App\Entity;

use App\Repository\PlanningPositionLockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningPositionLockRepository::class)]
#[ORM\Table(name: 'pl_poste_verrou')]
class PlanningPositionLock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?int $verrou = null;

    #[ORM\Column]
    private ?\DateTime $validation = null;

    #[ORM\Column]
    private ?int $perso = null;

    #[ORM\Column]
    private ?int $verrou2 = null;

    #[ORM\Column]
    private ?\DateTime $validation2 = null;

    #[ORM\Column]
    private ?int $perso2 = null;

    #[ORM\Column]
    private ?int $vivier = null;

    #[ORM\Column]
    private ?int $site = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLock2(): ?int
    {
        return $this->verrou2;
    }

    public function setLock2(?int $lock2): static
    {
        $this->verrou2 = $lock2;

        return $this;
    }

    public function getValidation2(): ?\DateTime
    {
        return $this->validation2;
    }

    public function setValidation2(?\DateTime $validation2): static
    {
        $this->validation2 = $validation2;

        return $this;
    }

    public function getUser2(): ?int
    {
        return $this->perso2;
    }

    public function setUser2(?int $user2): static
    {
        $this->perso2 = $user2;

        return $this;
    }
}
