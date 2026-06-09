<?php

namespace App\Entity;

use App\Repository\AccessRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessRepository::class)]
#[ORM\Table(name: 'acces')]
class Access
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $nom = null;

    #[ORM\Column]
    protected ?int $groupe_id = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $groupe = null;

    #[ORM\Column(length: 255)]
    protected ?string $page = null;

    #[ORM\Column]
    protected ?int $ordre = null;

    #[ORM\Column(length: 255)]
    protected ?string $categorie = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getGroupId(): ?int
    {
        return $this->groupe_id;
    }

    public function setGroupId(?int $groupId): static
    {
        $this->groupe_id = $groupId;

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

    public function getGroup(): ?string
    {
        return $this->groupe;
    }

    public function setGroup(?string $group): static
    {
        $this->groupe = $group;

        return $this;
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

    public function getOrder(): ?int
    {
        return $this->ordre;
    }

    public function setOrder(?int $order): static
    {
        $this->ordre = $order;

        return $this;
    }
}
