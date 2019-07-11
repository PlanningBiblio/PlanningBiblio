<?php

namespace App\Entity;

/**
 * @Entity @Table(name="absences_infos")
 **/
class AbsenceInfo extends Entity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $debut;

    /** @Column(type="string") **/
    protected $fin;

    /** @Column(type="text") **/
    protected $texte;

}