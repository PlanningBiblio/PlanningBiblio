<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'jours_feries')]
class PublicHoliday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $annee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $jour = null;

    #[ORM\Column]
    private ?int $ferie = null;

    #[ORM\Column]
    private ?int $fermeture = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
