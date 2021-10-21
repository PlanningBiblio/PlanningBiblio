<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="conges_infos")
 **/
class HolidayInfo extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="date") **/
    protected $debut;

    /** @Column(type="date") **/
    protected $fin;

    /** @Column(type="text") **/
    protected $texte;

    /** @Column(type="datetime") **/
    protected $saisie;

}
