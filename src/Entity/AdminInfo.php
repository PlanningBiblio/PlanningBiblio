<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'infos')]
class AdminInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $debut = null;

    #[ORM\Column(length: 255)]
    protected ?string $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $texte = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $network_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?string
    {
        return $this->debut;
    }

    public function setStart(?string $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->fin;
    }

    public function setEnd(?string $end): static
    {
        $this->fin = $end;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->texte;
    }

    public function setComment(?string $comment): static
    {
        $this->texte = $comment;

        return $this;
    }

    public function getNetworkId(): ?int
    {
        return $this->network_id;
    }

    public function setNetworkId(?int $network_id): static
    {
        $this->network_id = $network_id;
        return $this;
    }
}
