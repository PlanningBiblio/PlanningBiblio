<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'absences_infos')]
class AbsenceInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $myId = null;
    // FIXME Replace with $id when the id() setter/getter will be replaced with getId and setId

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $texte = null;

    // FIXME Remove function id() when the id() setter/getter will be replaced with getId and setId
    public function id(): ?int
    {
        return $this->myId;
    }

    public function getId(): ?int
    {
        return $this->myId;
    }

    public function getStart(): ?\DateTime
    {
        return $this->debut;
    }

    public function setStart(\DateTime $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->fin;
    }

    public function setEnd(\DateTime $end): static
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
