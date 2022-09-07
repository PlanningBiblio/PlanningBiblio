<?php
/**
Planno
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/conges/cron.apr1.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier executant des taches planifiées au 1er avril pour le module Conges.
Page appelée par le fichier include/cron.php
Supprime le reliquat à tous les agents

Spécificités BSG :
Ce fichier est habituellement exécuté au 1er janvier et nommé cron.jan1.php.
La BSG souhaitant exécuter ses actions au 1er avril, il a été renommé et la date d'exécution a été décalée.
*/

require_once "class.conges.php";
require_once "personnel/class.personnel.php";

// Ajout d'une ligne d'information dans le tableau des congés
$p=new personnel();
$p->supprime=array(0,1);
$p->fetch();
if ($p->elements) {
    foreach ($p->elements as $elem) {
        $credits=array();
        $credits['conges_credit'] = $elem['conges_credit'];
        $credits['comp_time'] = $elem['comp_time'];
        $credits['conges_anticipation'] = $elem['conges_anticipation'];
        $credits['conges_reliquat'] = 0;

        $c=new conges();
        $c->perso_id=$elem['id'];
        $c->CSRFToken = $CSRFSession;
        $c->maj($credits, "modif", true);
    }
}

// Modifie les crédits
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update('personnel', array('conges_reliquat' => '0.00'));
