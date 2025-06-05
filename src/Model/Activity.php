<?php
/**
 * Mapping : 
 * - Entity / Table : Activity / activites
 * - name / nom
 * - archived / supprime
 */

namespace App\Model;

use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityRepository") @ORM\Table(name="activites")
 **/
class Activity extends PLBEntity
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
    private ?int $id = null;

    /** @ORM\Column(type="text") **/
    private ?string $nom = null;

    /** @ORM\Column **/
    private ?\DateTime $supprime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getArchived(): ?\DateTime
    {
        return $this->supprime;
    }

    public function setArchived(\DateTime $archived): static
    {
        $this->supprime = $archived;

        return $this;
    }
}
