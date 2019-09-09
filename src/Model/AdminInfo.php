<?php

namespace App\Model;

/**
 * @Entity @Table(name="infos")
 **/
class AdminInfo extends PLBEntity
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
