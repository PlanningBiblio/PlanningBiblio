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

    #[Column(type: Types::TEXT)]
    private ?string $hidden_tables = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function purge(): void
    {
        error_log("hidden tables purge");
    }
}
