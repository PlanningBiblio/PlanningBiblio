<?php

namespace App\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'absences_documents')]
class AbsenceDocument
{
    private $upload_dir = '';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private $myId;
    // FIXME Replace with $id when the id() setter/getter will be replaced with getId and setId

    #[ORM\Column]
    private ?int $absence_id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $filename = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    // FIXME Remove function id() when the id() setter/getter will be replaced with getId and setId
    public function id(): ?int
    {
        return $this->myId;
    }

    public function getId(): ?int
    {
        return $this->myId;
    }

    public function getAbsenceId(): ?int
    {
        return $this->absence_id;
    }

    public function setAbsenceId(int $absenceId): static
    {
        $this->absence_id = $absenceId;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function deleteFile() {
        if (!$this->absence_id || !$this->filename || !$this->id) return;

        unlink($this->upload_dir() . $this->absence_id . '/' . $this->id . '/' . $this->filename);
        rmdir($this->upload_dir() . $this->absence_id . '/' . $this->id);
    }

    public function upload_dir() {
        if (!$this->upload_dir) {
            $this->upload_dir = __DIR__ . '/../../var/upload/' . $_ENV['APP_ENV'] . '/absences/';
        }

        return $this->upload_dir;
    }
}
