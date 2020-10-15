<?php

namespace App\Model;

/**
 * @Entity @Table(name="absences_documents")
 **/
class AbsenceDocument extends PLBEntity
{
    public function __construct() {
        $this->upload_dir = __DIR__ . '/../../var/upload/' . $_ENV['APP_ENV'] . '/absences/';
    }

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

        $this->__construct();

        unlink($this->upload_dir . $this->absence_id . '/' . $this->id . '/' . $this->filename);
        rmdir($this->upload_dir . $this->absence_id . '/' . $this->id);
    }
}
