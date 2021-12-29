<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity @Table(name="config")
 **/
class ConfigParam extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $nom;

    /** @Column(type="string") **/
    protected $type;

    /** @Column(type="string") **/
    protected $valeur;

    /** @Column(type="string") **/
    protected $commentaires;

    /** @Column(type="string") **/
    protected $categorie;

    /** @Column(type="string") **/
    protected $valeurs;

    /** @Column(type="string") **/
    protected $extra;

    /** @Column(type="integer") **/
    protected $ordre;
}
