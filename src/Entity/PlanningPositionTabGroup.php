<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_tab_grp')]
class PlanningPositionTabGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $lundi = null;

    #[ORM\Column]
    private ?int $mardi = null;

    #[ORM\Column]
    private ?int $mercredi = null;

    #[ORM\Column]
    private ?int $jeudi = null;

    #[ORM\Column]
    private ?int $vendredi = null;

    #[ORM\Column]
    private ?int $samedi = null;

    #[ORM\Column]
    private ?int $dimanche = null;

    #[ORM\Column]
    private ?int $site = null;

    #[ORM\Column]
    private ?\DateTime $supprime = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
