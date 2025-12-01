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

    public function getUser(): ?int
    {
        return $this->perso_id;
    }

    public function setUser(?int $user): static
    {
        $this->perso_id = $user;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->commentaires;
    }

    public function setComment(?string $comment): static
    {
        $this->commentaires = $comment;

        return $this;
    }

    public function getValidLevel1(): ?int
    {
        return $this->valide_n1;
    }

    public function setValidLevel1(?int $validLevel1): static
    {
        $this->valide_n1 = $validLevel1;

        return $this;
    }

    public function getValidLevel2(): ?int
    {
        return $this->valide;
    }

    public function setValidLevel2(?int $validLevel2): ?static
    {
        $this->valide = $validLevel2;

        return $this;
    }

    public function getRequestDate(): ?\DateTime
    {
        return $this->demande;
    }

    public function setRequestDate(?\DateTime $request_date): static
    {
        $this->demande = $request_date;

        return $this;
    }

    public function getMotivation(): ?string
    {
        return $this->motif;
    }

    public function setMotivation(?string $motivation): static
    {
        $this->motif = $motivation;

        return $this;
    }

    public function getOtherMotivation(): ?string
    {
        return $this->motif_autre;
    }

    public function setOtherMotivation(?string $otherMotivation): static
    {
        $this->motif_autre = $otherMotivation;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->etat;
    }

    public function setState(?string $state): static
    {
        $this->etat = $state;

        return $this;
    }

    public function getValidLevel2Date(): ?\DateTime
    {
        return $this->validation;
    }

    public function setValidLevel2Date(?\DateTime $validLevel2Date): static
    {
        $this->validation = $validLevel2Date;

        return $this;
    }

    public function getValidLevel1Date(): ?\DateTime
    {
        return $this->validation_n1;
    }

    public function setValidLevel1Date(?\DateTime $validLevel1Date): static
    {
        $this->validation_n1 = $validLevel1Date;

        return $this;
    }

    public function getAttachment1(): ?int
    {
        return $this->pj1;
    }

    public function setAttachment1(?int $attachment1): static
    {
        $this->pj1 = $attachment1;

        return $this;
    }

    public function getAttachment2(): ?int
    {
        return $this->pj2;
    }

    public function setAttachment2(?int $attachment2): static
    {
        $this->pj2 = $attachment2;

        return $this;
    }

    public function getSignatureRequired(): ?int
    {
        return $this->so;
    }

    public function setSignatureRequired(?int $signatureRequired): static
    {
        $this->so = $signatureRequired;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->groupe;
    }

    public function setGroup(?string $group): static
    {
        $this->groupe = $group;

        return $this;
    }

    public function getCalendarName(): ?string
    {
        return $this->cal_name;
    }

    public function setCalendarName(?string $calendarName): static
    {
        $this->cal_name = $calendarName;

        return $this;
    }

    public function getICalendarKey(): ?string
    {
        return $this->ical_key;
    }

    public function setICalendarKey(?string $iCalendarKey): static
    {
        $this->ical_key = $iCalendarKey;

        return $this;
    }

    public function getLastModified(): ?string
    {
        return $this->last_modified;
    }

    public function setLastModified(?string $lastModified): static
    {
        $this->last_modified = $lastModified;

        return $this;
    }

    public function getRecurrenceRule(): ?string
    {
        return $this->rrule;
    }

    public function setRecurrenceRule(?string $rrule): static
    {
        $this->rrule = $rrule;

        return $this;
    }

    public function getIdOrigin(): ?int
    {
        return $this->id_origin;
    }

    public function setIdOrigin(?int $id_origin): static
    {
        $this->id_origin = $id_origin;

        return $this;
    }
}
