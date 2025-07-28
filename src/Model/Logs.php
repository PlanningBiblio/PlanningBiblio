<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'log')]
class Logs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $msg = null;

    #[ORM\Column]
    private ?string $program = null;

    #[ORM\Column]
    private ?\DateTime $timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
