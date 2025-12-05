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

    public function getAttachmentNA(): ?int
    {
        return $this->so;
    }

    public function setAttachmentNA(?int $attachmentNA): static
    {
        $this->so = $attachmentNA;

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

    public function getCalName(): ?string
    {
        return $this->cal_name;
    }

    public function setCalName(?string $calName): static
    {
        $this->cal_name = $calName;

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

    public function getEnd(): ?\DateTime
    {
        return $this->fin;
    }

    public function setEnd(?\DateTime $end): static
    {
        $this->fin = $end;

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

    public function getICalKey(): ?string
    {
        return $this->ical_key;
    }

    public function setICalKey(?string $iCalKey): static
    {
        $this->ical_key = $iCalKey;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOriginId(): ?int
    {
        return $this->id_origin;
    }

    public function setOriginId(?int $originId): static
    {
        $this->id_origin = $originId;

        return $this;
    }

    public function getOtherReason(): ?string
    {
        return $this->motif_autre;
    }

    public function setOtherReason(?string $otherReason): static
    {
        $this->motif_autre = $otherReason;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->motif;
    }

    public function setReason(?string $reason): static
    {
        $this->motif = $reason;

        return $this;
    }

    public function getRequestDate(): ?\DateTime
    {
        return $this->demande;
    }

    public function setRequestDate(?\DateTime $requestDate): static
    {
        $this->demande = $requestDate;

        return $this;
    }

    public function getRRule(): ?string
    {
        return $this->rrule;
    }

    public function setRRule(?string $rRule): static
    {
        $this->rrule = $rRule;

        return $this;
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

    public function getStatus(): ?string
    {
        return $this->etat;
    }

    public function setStatus(?string $status): static
    {
        $this->etat = $status;

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

    public function getUserId(): ?int
    {
        return $this->perso_id;
    }

    public function setUserId(?int $user): static
    {
        $this->perso_id = $user;

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

    public function getValidLevel1Date(): ?\DateTime
    {
        return $this->validation_n1;
    }

    public function setValidLevel1Date(?\DateTime $validLevel1Date): static
    {
        $this->validation_n1 = $validLevel1Date;

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
}
