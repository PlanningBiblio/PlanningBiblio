<?php

namespace App\Model;

/**
 * @Entity @Table(name="pl_poste_tab")
 **/
class PlanningTableConfig extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $tableau;

    /** @Column(type="text") **/
    protected $nom;

    /** @Column(type="integer") **/
    protected $site;

    /** @Column(type="datetime") **/
    protected $supprime;
}
