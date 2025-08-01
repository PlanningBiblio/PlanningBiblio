<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'edt_samedi')]
class SaturdayWorkingHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $semaine = null;

    #[ORM\Column]
    private ?int $tableau = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
