<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $level1 = null;

    #[ORM\Column]
    private ?int $level2 = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 100)]
    private ?string $url = null;

    #[ORM\Column(length: 100)]
    private ?string $requirement = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLevel2(): ?int
    {
        return $this->level2;
    }

    public function setLevel2(int $level2): static
    {
        $this->level2 = $level2;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getRequirement(): ?string
    {
        return $this->requirement;
    }

    public function setRequirement(string $requirement): static
    {
        $this->requirement = $requirement;

        return $this;
    }
}
