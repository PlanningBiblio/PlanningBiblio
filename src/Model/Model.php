<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="pl_poste_modeles_tab")
 **/
class Model extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer") **/
    protected $model_id;

    /** @Column(type="string") **/
    protected $nom;

    /** @Column(type="integer") **/
    protected $jour;

    /** @Column(type="integer") **/
    protected $tableau;

    /** @Column(type="integer") **/
    protected $site;

    public function isWeek()
    {
        if ($this->jour != 9) {
            return true;
        }

        return false;
    }

}
