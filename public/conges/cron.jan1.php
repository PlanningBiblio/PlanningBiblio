<?php
/**
Planno
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/conges/cron.jan1.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier executant des taches planifiées au 1er janvier pour le module Conges.
Page appelée par le fichier include/cron.php
Met à jour les crédits de congés

Spécificités BSG :
Ce fichier est habituellement exécuté au 1er septembre et nommé cron.sept1.php
La BSG ayant besoin d'effectuer cette remise à zéro au 1er janvier, il a été renommé et la date d'exécution a été décalée.
La BSG ne souhaite pas mettre à zéro le crédit de récupération. Les lignes correspondantes ont été comméntées.
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
        $credits['conges_credit'] = floatval($elem['conges_annuel']) - floatval($elem['conges_anticipation']);
        $credits['comp_time'] = $elem['comp_time'];
        $credits['conges_anticipation'] = 0;
        $credits['conges_reliquat'] = $elem['conges_credit'];

        $c=new conges();
        $c->perso_id=$elem['id'];
        $c->CSRFToken = $CSRFSession;
        $c->maj($credits, "modif", true);
    }
}

// Modifie les crédits
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update("personnel", "conges_reliquat=conges_credit");
// $db=new db();
// $db->CSRFToken = $CSRFSession;
// $db->update("personnel", "comp_time='0.00'");
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update("personnel", "conges_credit=(conges_annuel-conges_anticipation)");
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update("personnel", "conges_anticipation=0.00");
