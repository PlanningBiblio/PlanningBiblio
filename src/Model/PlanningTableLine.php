<?php

namespace App\Model;

/**
 * @Entity @Table(name="pl_poste_lignes")
 **/
class PlanningTableLine extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $numero;

    /** @Column(type="integer") **/
    protected $tableau;

    /** @Column(type="integer") **/
    protected $ligne;

    /** @Column(type="string") **/
    protected $poste;

    /** @Column(type="string", columnDefinition="ENUM('poste', 'ligne', 'titre', 'classe')") **/
    protected $type;
}
