<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'config_network')]
class ConfigNetwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'configNetworks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Network $network = null;

    #[ORM\ManyToOne(inversedBy: 'configNetworks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Config $config = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $value = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(Network $network): self
    {
        $this->network = $network;
        return $this;
    }

    public function getConfig(): ?Config
    {
        return $this->config;
    }

    public function setConfig(?Config $config): void
    {
        $this->config = $config;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}