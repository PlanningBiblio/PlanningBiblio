<?php

namespace App\Model;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: 'activites')]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedName]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTime $supprime = null;

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

    public function getDelete(): ?\DateTime
    {
        return $this->supprime;
    }

    public function setDelete(\DateTime $delete): static
    {
        $this->supprime = $delete;

        return $this;
    }

    public function disable() {
        $this->supprime(new \DateTime());
    }

    public function enable() {
        $this->supprime = null;
    }
}
