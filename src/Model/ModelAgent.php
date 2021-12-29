<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity @Table(name="pl_poste_modeles")
 **/
class ModelAgent extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $model_id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="integer") **/
    protected $poste;

    /** @Column(type="string") **/
    protected $commentaire;

    /** @Column(type="time") **/
    protected $debut;

    /** @Column(type="time") **/
    protected $fin;

    /** @Column(type="text") **/
    protected $tableau;

    /** @Column(type="text") **/
    protected $jour;

    /** @Column(type="integer") **/
    protected $site;

}
