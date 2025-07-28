<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_modeles_tab')]
class Model
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $model_id = null;

    #[ORM\Column]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $jour = null;

    #[ORM\Column]
    private ?int $tableau = null;

    #[ORM\Column]
    private ?int $site = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModelId(): ?int
    {
        return $this->model_id;
    }

    public function setModelId(int $modelId): static
    {
        $this->model_id = $modelId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->nom;
    }

    public function setName(string $name): static
    {
        $this->nom = $name;

        return $this;
    }

    public function getDay(): ?int
    {
        return $this->jour;
    }

    public function setDay(int $day): static
    {
        $this->jour = $day;

        return $this;
    }

    public function getFramework(): ?int
    {
        return $this->tableau;
    }

    public function setFramework(int $framework): static
    {
        $this->tableau = $framework;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(int $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function isWeek()
    {
        if ($this->jour != 9) {
            return true;
        }

        return false;
    }
}
