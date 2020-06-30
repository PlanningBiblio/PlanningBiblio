<?php

namespace App\Model;

/**
 * @Entity @Table(name="pl_poste_tab_affect")
 **/
class PlanningTableUse extends PLBEntity {
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="date") **/
    protected $date;

    /** @Column(type="integer") **/
    protected $tableau;

    /** @Column(type="integer") **/
    protected $site;
}
