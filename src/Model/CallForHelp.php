<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="appel_dispo")
 **/
class CallForHelp extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $site;

    /** @Column(type="integer") **/
    protected $poste;

    /** @Column(type="datetime") **/
    protected $date;

    /** @Column(type="datetime") **/
    protected $debut;

    /** @Column(type="datetime") **/
    protected $fin;

    /** @Column(type="text") **/
    protected $destinataires;

    /** @Column(type="text") **/
    protected $sujet;

    /** @Column(type="text") **/
    protected $message;

    /** @Column(type="datetime") **/
    protected $timestamp;
}
