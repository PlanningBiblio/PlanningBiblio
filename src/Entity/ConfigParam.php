<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'config')]
class ConfigParam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $valeur = null;

    #[ORM\Column(length: 255)]
    private ?string $commentaires = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $valeurs = null;

    #[ORM\Column]
    private ?bool $technical = false;

    #[ORM\Column(length: 255)]
    private ?string $extra = null;

    #[ORM\Column]
    private ?int $ordre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->categorie;
    }

    public function setCategory(string $category): static
    {
        $this->categorie = $category;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->commentaires;
    }

    public function setComment(string $comments): static
    {
        $this->commentaires = $comments;

        return $this;
    }

    public function getExtra(): ?string
    {
        return $this->extra;
    }

    public function setExtra(string $extra): static
    {
        $this->extra = $extra;

        return $this;
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

    public function getOrder(): ?int
    {
        return $this->ordre;
    }

    public function setOrder(int $order): static
    {
        $this->ordre = $order;

        return $this;
    }

    public function isTechnical(): ?bool
    {
        return $this->technical;
    }

    public function setTechnical(bool $technical): static
    {
        $this->technical = $technical;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->valeur;
    }

    public function setValue(string $value): static
    {
        $this->valeur = $value;

        return $this;
    }

    public function getValues(): ?string
    {
        return $this->valeurs;
    }

    public function setValues(string $values): static
    {
        $this->valeurs = $values;

        return $this;
    }
}
