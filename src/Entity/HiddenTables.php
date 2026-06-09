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
    private array $hidden_tables = [];

    #[ORM\Column(name: 'tableau')]
    private int $tableId = 0;

    #[ORM\Column(name: 'perso_id')]
    private int $userId = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function purge(): void
    {
        error_log('hidden tables purge');
    }

    public function getHiddenTables(): array
    {
        return $this->hidden_tables;
    }

    public function setHiddenTables(array $tables): static
    {
        $this->hidden_tables = $tables;

        return $this;
    }

    public function getTableId(): int
    {
        return $this->tableId;
    }

    public function setTableId(int $tableId): static
    {
        $this->tableId = $tableId;

        return $this;
    }

    public function getuserId(): int
    {
        return $this->userId;
    }

    public function setuserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
