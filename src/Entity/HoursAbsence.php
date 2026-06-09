<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'heures_absences')]
class HoursAbsence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $semaine = null;

    #[ORM\Column]
    private ?int $update_time = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $heures = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
