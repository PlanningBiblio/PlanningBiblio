<?php

namespace App\Model;

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

    /** @Column(type="integer") **/
    protected $ordre;
}
