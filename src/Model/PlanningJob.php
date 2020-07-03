<?php

namespace App\Model;

/**
 * @Entity @Table(name="pl_poste")
 **/
class PlanningJob extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="date") **/
    protected $date;

    /** @Column(type="integer") **/
    protected $poste;

    /** @Column(type="integer") **/
    protected $absent;

    /** @Column(type="integer") **/
    protected $chgt_login;

    /** @Column(type="datetime") **/
    protected $chgt_time;

    /** @Column(type="time") **/
    protected $debut;

    /** @Column(type="time") **/
    protected $fin;

    /** @Column(type="integer") **/
    protected $supprime;

    /** @Column(type="integer") **/
    protected $site;

    /** @Column(type="integer") **/
    protected $grise;
}
