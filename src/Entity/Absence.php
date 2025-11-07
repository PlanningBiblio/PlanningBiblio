<?php

namespace App\Entity;

use App\Repository\AbsenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbsenceRepository::class)]
#[ORM\Table(name: 'absences')]
class Absence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $perso_id = null;

    #[ORM\Column]
    private ?\DateTime $debut = null;

    #[ORM\Column]
    private ?\DateTime $fin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motif = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motif_autre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaires = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $etat = null;

    #[ORM\Column]
    private ?\DateTime $demande = null;

    #[ORM\Column]
    private ?int $valide = null;

    #[ORM\Column]
    private ?\DateTime $validation = null;

    #[ORM\Column]
    private ?int $valide_n1 = null;

    #[ORM\Column]
    private ?\DateTime $validation_n1 = null;

    #[ORM\Column]
    private ?int $pj1 = null;

    #[ORM\Column]
    private ?int $pj2 = null;

    #[ORM\Column]
    private ?int $so = null;

    #[ORM\Column]
    private ?string $groupe = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $cal_name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $ical_key = null;

    #[ORM\Column]
    private ?string $last_modified = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $uid = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rrule = null;

    #[ORM\Column]
    private ?int $id_origin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTime
    {
        return $this->debut;
    }

    public function setStart(?\DateTime $start): static
    {
        $this->debut = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->fin;
    }

    public function setEnd(?\DateTime $end): static
    {
        $this->fin = $end;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPersoId(): ?int
    {
        return $this->perso_id;
    }

    public function getComments(): ?string
    {
        return $this->commentaires;
    }

    public function getValideN1(): ?int
    {
        return $this->valide_n1;
    }

    public function getValideN2(): ?int
    {
        return $this->valide;
    }

    public function setAll(?array $all): static
    {
        $debut = new \DateTime($all['debut']);
        $fin   = new \DateTime($all['fin']);
        $demande   = new \DateTime($all['demande']);
        $validation = new \DateTime($all['validation']);
        $validationN1 = new \DateTime($all['validation_n1']);

        $this->debut = $debut;
        $this->fin = $fin;
        $this->demande = $demande;
        $this->validation = $validation;
        $this->validation_n1 = $validationN1;

        $this->perso_id = $all['perso_id'] ?? null;
        $this->valide = $all['valide'] ?? 0;
        $this->valide_n1 = $all['valide_n1'] ?? 0;
        $this->motif = $all['motif'] ?? '';
        $this->commentaires = $all['commentaires'] ?? '';
        $this->cal_name = 'hamac';
        $this->ical_key = $all['ical_key'] ?? null;
        $this->uid = $all['uid'] ?? null;

        $this->motif_autre = $all['motif_autre'] ?? '';
        $this->etat = $all['etat'] ?? '';
        $this->id_origin = $all['id_origin'] ?? 0;

        return $this;
    }
}
