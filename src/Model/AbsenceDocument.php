<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="absences_documents")
 **/
class AbsenceDocument extends PLBEntity
{
    const UPLOAD_DIR = '/../../upload/absences/';

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $absence_id;

    /** @Column(type="text") **/
    protected $filename;

    /** @Column(type="datetime") */
    protected $date;

    public function deleteFile() {
        if (!$this->absence_id || !$this->filename || !$this->id) return;
        unlink(__DIR__ . AbsenceDocument::UPLOAD_DIR . $this->absence_id . '/' . $this->id . '/' . $this->filename);
        rmdir(__DIR__ . AbsenceDocument::UPLOAD_DIR . $this->absence_id . '/' . $this->id);
    }
}
