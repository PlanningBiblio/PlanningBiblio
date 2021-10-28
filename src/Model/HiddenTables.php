<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @Entity @Table(name="hidden_tables")
 **/
class HiddenTables extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $perso_id;

    /** @Column(type="integer") **/
    protected $tableau;

    /** @Column(type="text") **/
    protected $hidden_tables;

    /**
     * @ManyToOne(targetEntity="PlanningPositionTab", inversedBy="tableaux")
     * @JoinColumn(name="tableau", referencedColumnName="tableau")
     */
    protected $positiontab;


    public function purge()
    {
        error_log("hidden tables purge");
    }
}
