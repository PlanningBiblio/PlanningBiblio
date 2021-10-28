<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

use App\Model\HiddenTables;

/**
 * @Entity(repositoryClass="App\Repository\PlanningPositionTabRepository") @Table(name="pl_poste_tab")
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

    public function purge()
    {
        $tableau = $this->tableau;
        error_log($tableau);
    }
}
