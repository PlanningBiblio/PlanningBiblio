<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_modeles')]
class ModelAgent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $model_id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column]
    private ?int $poste = null;

    #[ORM\Column]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $debut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $tableau = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $jour = null;

    #[ORM\Column]
    private ?int $site = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
