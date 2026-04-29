<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'site')]
class Site
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = '';

    #[ORM\ManyToOne(targetEntity: Network::class)]
    #[ORM\JoinColumn(name: "network_id", referencedColumnName: "id", nullable: false)]
    private ?Network $network = null;

    #[ORM\Column]
    private ?\DateTime $deleteDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(?Network $network): void
    {
        $this->network = $network;
    }

    public function getDeleteDate(): ?\DateTime
    {
        return $this->deleteDate;
    }

    public function setDeleteDate(?\DateTime $deleteDate): void
    {
        $this->deleteDate = $deleteDate;
    }
}