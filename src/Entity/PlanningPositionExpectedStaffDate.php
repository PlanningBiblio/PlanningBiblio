<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pl_poste_effectif_attendu_date')]
class PlanningPositionExpectedStaffDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column]
    private ?int $tableau = null;

    #[ORM\Column]
    private ?int $ligne = null;

    #[ORM\Column]
    private ?int $colonne = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $nb_attendu = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getTableau(): ?int
    {
        return $this->tableau;
    }

    public function setTableau(?int $tableau): static
    {
        $this->tableau = $tableau;

        return $this;
    }

    public function getLigne(): ?int
    {
        return $this->ligne;
    }

    public function setLigne(?int $ligne): static
    {
        $this->ligne = $ligne;

        return $this;
    }

    public function getColonne(): ?int
    {
        return $this->colonne;
    }

    public function setColonne(?int $colonne): static
    {
        $this->colonne = $colonne;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getExpectedStaff(): ?int
    {
        return $this->nb_attendu;
    }

    public function setExpectedStaff(?int $expectedStaff): static
    {
        $this->nb_attendu = $expectedStaff;

        return $this;
    }
}
