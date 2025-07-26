<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'absence_blocks')]
class AbsenceBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $myId = null;
    // FIXME Replace with $id when the id() setter/getter will be replaced with getId and setId

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $end = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    // FIXME Remove function id() when the id() setter/getter will be replaced with getId and setId
    public function id(): ?int
    {
        return $this->myId;
    }

    public function getId(): ?int
    {
        return $this->myId;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(\DateTime $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(\DateTime $end): static
    {
        $this->end= $end;

        return $this;
    }
}
