<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'hidden_tables')]
class HiddenTables
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column]
    private ?int $tableau = null;

    #[ORM\Column]
    private ?array $hidden_tables = [];

    public function getHiddenTables(): ?array
    {
        return $this->hidden_tables;
    }

    public function setHiddenTables(?array $hiddenTables): static
    {
        $this->hidden_tables = $hiddenTables;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTable(): ?int
    {
        return $this->tableau;
    }

    public function setTable(?int $table): static
    {
        $this->tableau = $table;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->perso_id;
    }

    public function setUserId(?int $userId): static
    {
        $this->perso_id = $userId;

        return $this;
    }

    public function purge(): void
    {
        error_log("hidden tables purge");
    }
}
