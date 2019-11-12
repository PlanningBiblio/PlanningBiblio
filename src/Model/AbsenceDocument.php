<?php

namespace App\Model;

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

}
