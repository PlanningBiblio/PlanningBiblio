<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

#[Entity]
#[Table(name: 'absences_documents')]
class AbsenceDocument extends PLBEntity
{
    protected $upload_dir = '';

    #[Id]
    #[GeneratedValue]
    #[Column]
    protected $id;

    #[Column]
    protected ?int $absence_id = null;

    #[Column(type: Types::TEXT)]
    protected ?string $filename = null;

    #[Column]
    protected ?\DateTime $date = null;

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
