<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $test = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $array = [];

    #[ORM\Column]
    private ?float $ffloat = null;

    #[ORM\Column]
    private ?bool $bool = null;

    #[ORM\Column]
    private ?int $iint = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(string $test): static
    {
        $this->test = $test;

        return $this;
    }

    public function getArray(): array
    {
        return $this->array;
    }

    public function setArray(array $array): static
    {
        $this->array = $array;

        return $this;
    }

    public function getFfloat(): ?float
    {
        return $this->ffloat;
    }

    public function setFfloat(float $ffloat): static
    {
        $this->ffloat = $ffloat;

        return $this;
    }

    public function isBool(): ?bool
    {
        return $this->bool;
    }

    public function setBool(bool $bool): static
    {
        $this->bool = $bool;

        return $this;
    }

    public function getIint(): ?int
    {
        return $this->iint;
    }

    public function setIint(int $iint): static
    {
        $this->iint = $iint;

        return $this;
    }
}
