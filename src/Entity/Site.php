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

    #[ORM\Column]
    private ?\DateTime $deletedDate = null;

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

    public function getDeletedDate(): ?\DateTime
    {
        return $this->deletedDate;
    }

    public function setDeletedDate(?\DateTime $deletedDate): void
    {
        $this->deletedDate = $deletedDate;
    }
}