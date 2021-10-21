<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity @Table(name="ip_blocker")
 **/
class IPBlocker extends PLBEntity
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $ip;

    /** @Column(type="string") **/
    protected $login;

    /** @Column(type="string") **/
    protected $status;

    /** @Column(type="datetime") **/
    protected $timestamp;

}
