<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'absences_recurrentes')]
class RecurringAbsence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $uid = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $event = null;

    #[ORM\Column]
    private ?int $end = null;

    #[ORM\Column]
    private ?\DateTime $timestamp = null;

    #[ORM\Column]
    private ?\DateTime $last_update = null;

    #[ORM\Column]
    private ?\DateTime $last_check = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
