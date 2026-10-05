<?php

namespace App\Entity;

use App\Repository\DetachedRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetachedRepository::class)]
#[ORM\Table(name: 'detached')]
class Detached
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(name: 'user_id')]
    private int $userId = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
