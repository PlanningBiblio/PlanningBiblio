<?php

namespace App\Entity;

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

    public function getComment(): ?string
    {
        return $this->commentaire;
    }

    public function setComment(?string $comment): static
    {
        $this->commentaire = $comment;

        return $this;
    }

    public function getDay(): ?string
    {
        return $this->jour;
    }

    public function setDay(?string $day): static
    {
        $this->jour = $day;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->fin;
    }

    public function setEnd(?\DateTime $end): static
    {
        $this->fin = $end;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModelId(): ?int
    {
        return $this->model_id;
    }

    public function setModelId(?int $modelId): static
    {
        $this->model_id = $modelId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->perso_id;
    }

    public function setUserId(?int $userId): static
    {
        $this->perso_id = $userId;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(?int $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getSkill(): ?int
    {
        return $this->poste;
    }

    public function setSkill(?int $skill): static
    {
        $this->poste = $skill;

        return $this;
    }

    public function getStart(): ?\DateTime
    {
        return $this->debut;
    }

    public function setStart(?\DateTime $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function getTable(): ?string
    {
        return $this->tableau;
    }

    public function setTable(?string $table): static
    {
        $this->tableau = $table;

        return $this;
    }
}
