<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cron')]
class Cron
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    private ?string $m = null;

    #[ORM\Column(length: 2)]
    private ?string $h = null;

    #[ORM\Column(length: 2)]
    private ?string $dom = null;

    #[ORM\Column(length: 2)]
    private ?string $mon = null;

    #[ORM\Column(length: 2)]
    private ?string $dow = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $command = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comments = null;

    #[ORM\Column]
    private ?\DateTime $last = null;

    #[ORM\Column]
    private ?bool $disabled = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getM(): ?string
    {
        return $this->m;
    }

    public function setM(string $m): static
    {
        $this->m = $m;

        return $this;
    }

    public function getH(): ?string
    {
        return $this->h;
    }

    public function setH(string $h): static
    {
        $this->h = $h;

        return $this;
    }

    public function getDom(): ?string
    {
        return $this->dom;
    }

    public function setDom(string $dom): static
    {
        $this->dom = $dom;

        return $this;
    }

    public function getMon(): ?string
    {
        return $this->mon;
    }

    public function setMon(string $mon): static
    {
        $this->mon = $mon;

        return $this;
    }

    public function getDow(): ?string
    {
        return $this->dow;
    }

    public function setDow(string $dow): static
    {
        $this->dow = $dow;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): static
    {
        $this->command = $command;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comments;
    }

    public function setComment(string $comment): static
    {
        $this->comments = $comment;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function getLast(): ?\DateTime
    {
        return $this->last;
    }

    public function setLast(\DateTime $last): static
    {
        $this->last = $last;

        return $this;
    }

    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }
}
