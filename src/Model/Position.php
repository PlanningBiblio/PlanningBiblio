<?php

namespace App\Model;

use App\Repository\PositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\Table(name: 'postes')]
class Position
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $nom = null;

    #[ORM\Column]
    private ?string $groupe = null;

    #[ORM\Column(length: 11)]
    private ?int $groupe_id = null;

    #[ORM\Column(length: 15)]
    private ?string $obligatoire = null;

    #[ORM\Column]
    private ?string $etage = null;

    #[ORM\Column]
    private array $activites = [];

    #[ORM\Column]
    private ?bool $statistiques = true;

    #[ORM\Column]
    private ?bool $teleworking = false;

    #[ORM\Column]
    private ?bool $bloquant = true;

    #[ORM\Column]
    private ?bool $lunch = false;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $site = null;

    #[ORM\Column]
    private array $categories = [];

    #[ORM\Column]
    private ?\DateTime $supprime = null;

    #[ORM\Column]
    private ?bool $quota_sp = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->nom;
    }

    public function setName(string $name): static
    {
        $this->nom = $name;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->groupe;
    }

    public function setGroup(string $group): static
    {
        $this->groupe = $group;

        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->groupe_id;
    }

    public function setGroupId(int $groupId): static
    {
        $this->groupe_id = $groupId;

        return $this;
    }

    public function getMandatory(): ?string
    {
        return $this->obligatoire;
    }

    public function setMandatory(string $mandatory): static
    {
        $this->obligatoire = $mandatory;

        return $this;
    }

    public function getFloor(): ?string
    {
        return $this->etage;
    }

    public function setFloor(string $floor): static
    {
        $this->etage = $floor;

        return $this;
    }

    public function getActivities(): array
    {
        return $this->activites;
    }

    public function setActivities(array $activities): static
    {
        $this->activites = $activities;

        return $this;
    }

    public function isStatistics(): ?bool
    {
        return $this->statistiques;
    }

    public function setStatistics(bool $statistics): static
    {
        $this->statistiques = $statistics;

        return $this;
    }

    public function isTeleworking(): ?bool
    {
        return $this->teleworking;
    }

    public function setTeleworking(bool $teleworking): static
    {
        $this->teleworking = $teleworking;

        return $this;
    }

    public function isBlocking(): ?bool
    {
        return $this->bloquant;
    }

    public function setBlocking(bool $blocking): static
    {
        $this->bloquant = $blocking;

        return $this;
    }

    public function isLunch(): ?bool
    {
        return $this->lunch;
    }

    public function setLunch(bool $lunch): static
    {
        $this->lunch = $lunch;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(int $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    public function getDelete(): ?\DateTime
    {
        return $this->supprime;
    }

    public function setDelete(\DateTime $delete): static
    {
        $this->supprime = $delete;

        return $this;
    }

    public function isQuotaSP(): ?bool
    {
        return $this->quota_sp;
    }

    public function setQuotaSP(bool $quotaSP): static
    {
        $this->quota_sp = $quotaSP;

        return $this;
    }
}
