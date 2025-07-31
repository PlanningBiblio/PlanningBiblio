<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?array $jsson = null;

    #[ORM\Column]
    private array $jjson = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJsson(): ?array
    {
        return $this->jsson;
    }

    public function setJsson(?array $jsson): static
    {
        $this->jsson = $jsson;

        return $this;
    }

    public function getJjson(): array
    {
        return $this->jjson;
    }

    public function setJjson(array $jjson): static
    {
        $this->jjson = $jjson;

        return $this;
    }
}
