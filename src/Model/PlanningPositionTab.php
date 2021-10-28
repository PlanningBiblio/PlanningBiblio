<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\OneToMany;

use App\Model\HiddenTables;

/**
 * @Entity @Table(name="pl_poste_tab", indexes={
 *  @Index(name="tableau_idx", columns={"tableau"})
 * })
 **/
class PlanningPositionTab extends PLBEntity
{
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

    /**
     * @OneToMany(targetEntity="HiddenTables", mappedBy="positiontab", cascade={"ALL"})
     */
    protected $tableaux;

    public function __construct() {
        $this->tableaux = new ArrayCollection();
    }

    public function purge()
    {
        $tableau = $this->tableau;
        error_log($tableau);
    }

    public function addHiddenTables($hiddenTable)
    {
        $this->tableaux->add($hiddenTable);
        $hiddenTable->positiontab($this);
    }

}
