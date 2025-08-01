<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_notes')]
class PlanningNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?int $site = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column]
    private ?\DateTime $timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComments(): ?string
    {
        return $this->text;
    }

    public function setComments(string $comments): static
    {
        $this->text = $comments;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(int $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTime $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getUser(): ?int
    {
        return $this->perso_id;
    }

    public function setUser(int $user): static
    {
        $this->perso_id = $user;

        return $this;
    }
}
