<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'config')]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $type = 'text';

    #[ORM\Column(length: 255)]
    private ?string $commentaires = '';

    #[ORM\Column(length: 255)]
    private ?string $categorie = '';

    #[ORM\Column(length: 255)]
    private ?string $valeurs = '';

    #[ORM\Column]
    private ?bool $technical = false;

    #[ORM\Column]
    private ?int $ordre = 0;

    #[ORM\OneToMany(mappedBy: 'config', targetEntity: ConfigTechnical::class)]
    private Collection $configTechnical;

    #[ORM\OneToMany(mappedBy: 'config', targetEntity: ConfigNetwork::class)]
    private Collection $configNetworks;

    public function __construct()
    {
        $this->configTechnical = new ArrayCollection();
        $this->configNetworks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->categorie;
    }

    public function setCategory(?string $category): static
    {
        $this->categorie = $category;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->commentaires;
    }

    public function setComment(?string $comments): static
    {
        $this->commentaires = $comments;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->nom;
    }

    public function setName(?string $name): static
    {
        $this->nom = $name;

        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->ordre;
    }

    public function setOrder(?int $order): static
    {
        $this->ordre = $order;

        return $this;
    }

    public function isTechnical(): ?bool
    {
        return $this->technical;
    }

    public function setTechnical(?bool $technical): static
    {
        $this->technical = $technical;

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

    public function getValues(): ?string
    {
        return $this->valeurs;
    }

    public function setValues(?string $values): static
    {
        $this->valeurs = $values;

        return $this;
    }

    public function getConfigTechnical(): Collection
    {
        return $this->configTechnical;
    }

    public function getConfigNetworks(): Collection
    {
        return $this->configNetworks;
    }
}
