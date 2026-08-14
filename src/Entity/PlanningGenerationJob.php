<?php

namespace App\Entity;

use App\Repository\PlanningGenerationJobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningGenerationJobRepository::class)]
#[ORM\Table(name: 'pl_generation_planning')]
class PlanningGenerationJob
{
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_SUCCES = 'succes';
    public const STATUT_ECHEC = 'echec';
    public const STATUT_CONFLIT = 'conflit';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date_generation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_fin = null;

    #[ORM\Column(nullable: true)]
    private ?int $site = null;

    #[ORM\Column]
    private ?array $json_envoye = [];

    #[ORM\Column(nullable: true)]
    private ?array $json_recu = null;

    /**
     * Affectations reçues de l'algorithme mais non importées car l'agent a déposé une indisponibilité
     * (absence/congé/récupération) qui chevauche le créneau, entre l'envoi du JSON et la réception de la réponse.
     */
    #[ORM\Column(nullable: true)]
    private ?array $conflicts = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = self::STATUT_EN_COURS;

    #[ORM\Column(nullable: true)]
    private ?int $created_by = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error_message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateGeneration(): ?\DateTime
    {
        return $this->date_generation;
    }

    public function setDateGeneration(?\DateTime $dateGeneration): static
    {
        $this->date_generation = $dateGeneration;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTime $dateDebut): static
    {
        $this->date_debut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->date_fin = $dateFin;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(?int $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getJsonEnvoye(): ?array
    {
        return $this->json_envoye;
    }

    public function setJsonEnvoye(?array $jsonEnvoye): static
    {
        $this->json_envoye = $jsonEnvoye;

        return $this;
    }

    public function getJsonRecu(): ?array
    {
        return $this->json_recu;
    }

    public function setJsonRecu(?array $jsonRecu): static
    {
        $this->json_recu = $jsonRecu;

        return $this;
    }

    public function getConflicts(): ?array
    {
        return $this->conflicts;
    }

    public function setConflicts(?array $conflicts): static
    {
        $this->conflicts = $conflicts;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function setCreatedBy(?int $createdBy): static
    {
        $this->created_by = $createdBy;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->error_message = $errorMessage;

        return $this;
    }
}
