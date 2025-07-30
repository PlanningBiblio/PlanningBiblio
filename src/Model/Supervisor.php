<?php

// FIXME Use Manager instead of Supervisor : Duplicate class

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'responsables')]
class Supervisor
{
    #[ORM\Id]
    #[ORM\GeneratedName]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column]
    private ?int $responsable = null;

    #[ORM\Column]
    private ?int $notification = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
