<?php

namespace App\Model;

use Doctrine\ORM\Mapping\{Entity, Table, Id, Column, GeneratedValue};

/**
 * @Entity @Table(name="site_mail")
 **/
class SiteMail extends PLBEntity{
    /** @Id @Column(type="integer", length = 11) @GeneratedValue **/
    protected $site_id;

    /** @Column(type="string") **/
    protected $mail;

}
