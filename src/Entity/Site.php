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
    private ?array $mails = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTime $deleted_date = null;

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

    public function getMails(): ?array
    {
        return $this->mails ?? [];
    }

    public function setMails(?array $mails): void
    {
        $this->mails = $mails;
    }

    public function getDeletedDate(): ?\DateTime
    {
        return $this->deleted_date;
    }

    public function setDeletedDate(?\DateTime $deletedDate): void
    {
        $this->deleted_date = $deletedDate;
    }
}