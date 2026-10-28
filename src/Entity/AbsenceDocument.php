<?php

namespace App\Entity;

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
    private ?int $id = null;

    #[ORM\Column]
    private ?int $absence_id = 0;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $filename = '';

    #[ORM\Column(type: Types::STRING)]
    private ?string $storage_id = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAbsenceId(): ?int
    {
        return $this->absence_id;
    }

    public function setAbsenceId(?int $absenceId): static
    {
        $this->absence_id = $absenceId;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getStorageId(): ?string
    {
        return $this->storage_id;
    }

    public function setStorageId(string $storage_id): static
    {
        $this->storage_id = $storage_id;

        return $this;
    }

    public function deleteFile(): void
    {
        if (!$this->absence_id || !$this->filename || !$this->id) return;

        $filepath = $this->getStoragePath();
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $directory = $this->getStorageDirectory();
        if (is_dir($directory)) {
            rmdir($directory);
        }
    }

    public function upload_dir() {
        if (!$this->upload_dir) {
            $this->upload_dir = __DIR__ . '/../../var/upload/' . $_ENV['APP_ENV'] . '/absences/';
        }

        return $this->upload_dir;
    }

    /**
     * Returns the absolute path of directory where the file is (or will be)
     * stored
     */
    public function getStorageDirectory(): string
    {
        return sprintf('%s%s/%s', $this->upload_dir(), $this->absence_id, $this->id);
    }

    /**
     * Returns the absolute path of the file
     */
    public function getStoragePath(): string
    {
        return sprintf('%s/%s', $this->getStorageDirectory(), $this->getStorageId());
    }
}
