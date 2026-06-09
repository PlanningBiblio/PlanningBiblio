<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'network')]
class Network
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = '';

    #[ORM\Column]
    private ?DateTime $deletedDate = null;

    #[ORM\OneToMany(mappedBy: 'network', targetEntity: Site::class)]
    private Collection $sites;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDeletedDate(): ?DateTime
    {
        return $this->deletedDate;
    }

    public function setDeletedDate(DateTime $deletedDate): self
    {
        $this->deletedDate = $deletedDate;
        return $this;
    }

    public function getSites(): Collection
    {
        return $this->sites;
    }
}