<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity]
#[Table(name: 'hidden_tables')]
class HiddenTables extends PLBEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column(type: 'integer')] // *
    protected $perso_id;

    #[Column(type: 'integer')] // *
    protected $tableau;

    #[Column(type: 'text')] // *
    protected $hidden_tables;

    public function purge()
    {
        error_log("hidden tables purge");
    }
}

//    #[Column(type: Types::TEXT)]
//    protected ?string $text = null;
//
//    #[Column(type: Types::DATE_MUTABLE)]
//    protected ?\DateTime $date = null;
//
//    #[Column]
//    protected ?\DateTime $date = null;
//
//    #[Column]
//    protected ?int $id = null;
//
//    #[Column(type: Types::SMALLINT)]
//    protected ?int $level1 = null;
//
//    #[Column(length: 255)]
//    protected ?string $name = null;
//
//    #[Column]
//    protected array $json = [];
//
//    #[Column]
//    protected ?float $ffloat = null;
//
//    #[Column]
//    protected ?bool $boolean = null;
//
//    #[Column(type: Types::ARRAY)]
//    private array $array = [];
//
//    /**
//     * @var Collection<int, Absence>
//     */
//    #[OneToMany(mappedBy: 'test', targetEntity: Absence::class)]
//    private Collection $onetomany;
//
//
//    #[ManyToOne(inversedBy: 'onetomany')]
//    private ?Test4 $test = null;
//
//    public function getTest(): ?Test4
//    {
//        return $this->test;
//    }
//
//    public function setTest(?Test4 $test): static
//    {
//        $this->test = $test;
//
//        return $this;
//    }
