<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'acces')]
class Access
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $myId = null;
    // FIXME Replace with $id when the id() setter/getter will be replaced with getId and setId

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $nom = null;

    #[ORM\Column]
    protected ?int $groupe_id = null;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $groupe = null;

    #[ORM\Column(length: 255)]
    protected ?string $page = null;

    #[ORM\Column]
    protected ?int $ordre = null;

    #[ORM\Column(length: 255)]
    protected ?string $categorie = null;

    // FIXME Remove function id() when the id() setter/getter will be replaced with getId and setId
    public function id(): ?int
    {
        return $this->myId;
    }

    public function getId(): ?int
    {
        return $this->myId;
    }
    
    public function getGroupId(): ?int
    {
        return $this->groupe_id;
    }

    public function setGroupId(int $groupId): static
    {
        $this->groupe_id = $groupId;

        return $this;
    }
}
