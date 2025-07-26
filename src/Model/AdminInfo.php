<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'infos')]
class AdminInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private $myId;
    // FIXME Replace with $id when the id() setter/getter will be replaced with getId and setId

    #[ORM\Column(length: 255)]
    protected ?string $debut = null;

    #[ORM\Column(length: 255)]
    protected ?string $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $texte = null;

    // FIXME Remove function id() when the id() setter/getter will be replaced with getId and setId
    public function id(): ?int
    {
        return $this->myId;
    }

    public function getId(): ?int
    {
        return $this->myId;
    }

    public function getStart(): ?string
    {
        return $this->debut;
    }

    public function setStart(string $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->fin;
    }

    public function setEnd(string $end): static
    {
        $this->fin = $end;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->texte;
    }

    public function setComment(string $comment): static
    {
        $this->texte = $comment;

        return $this;
    }
}
