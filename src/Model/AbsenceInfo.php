<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity @Table(name="absences_infos")
 **/
class AbsenceInfo extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="date") **/
    protected $debut;

    /** @Column(type="date") **/
    protected $fin;

    /** @Column(type="text") **/
    protected $texte;

}
