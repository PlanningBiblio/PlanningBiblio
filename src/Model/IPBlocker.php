<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ip_blocker')]
class IPBlocker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $ip = null;

    #[ORM\Column]
    private ?string $login = null;

    #[ORM\Column]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTime $timestamp = null;

}
