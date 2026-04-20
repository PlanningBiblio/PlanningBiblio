<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: 'activites')]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTime $supprime = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $network_id = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDelete(): ?\DateTime
    {
        return $this->supprime;
    }

    public function setDelete(?\DateTime $delete): static
    {
        $this->supprime = $delete;

        return $this;
    }

    public function disable(): void {
        $this->supprime = new \DateTime();
    }

    public function enable(): void {
        $this->supprime = null;
    }

    public function getNetwork(): ?int
    {
        return $this->network_id;
    }

    public function setNetwork(?int $network_id): static{
        $this->network_id = $network_id;
        return $this;
    }
}
