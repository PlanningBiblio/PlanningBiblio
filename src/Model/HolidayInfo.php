<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'conges_infos')]
class HolidayInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $texte = null;

    #[ORM\Column]
    private ?\DateTime $saisie = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
