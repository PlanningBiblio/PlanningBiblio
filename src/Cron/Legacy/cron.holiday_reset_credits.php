<?php
/**
Planno
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

Création : 13 août 2013
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Tâche planifiée de mise à zéro des crédits de congés
*/

require_once(__DIR__ . '/../../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../../public/include/db.php');

$config = $GLOBALS['config'];
$CSRFSession = $GLOBALS['CSRFSession'];

// Ajout d'une ligne d'information dans le tableau des congés
$p=new personnel();
$p->supprime=array(0,1);
$p->fetch();
if ($p->elements) {
    foreach ($p->elements as $elem) {
        $credits=array();
        $credits['conges_credit'] = floatval($elem['conges_annuel']) - floatval($elem['conges_anticipation']);
        $credits['conges_anticipation'] = 0;

        if ($config['Conges-transfer-comp-time']) {
            $credits['conges_reliquat'] = floatval($elem['conges_credit']) + floatval($elem['comp_time']);
            $credits['comp_time'] = 0;
        } else {
            $credits['conges_reliquat'] = $elem['conges_credit'];
            $credits['comp_time'] = $elem['comp_time'];
        }

        $c=new conges();
        $c->perso_id=$elem['id'];
        $c->CSRFToken = $CSRFSession;
        $c->maj($credits, "modif", true);
    }
}

// Modifie les crédits
$db=new db();
$db->CSRFToken = $CSRFSession;
if ($config['Conges-transfer-comp-time']) {
    $db->update("personnel", "conges_reliquat=(conges_credit+comp_time)");
} else {
    $db->update("personnel", "conges_reliquat=conges_credit");
}
if ($config['Conges-transfer-comp-time']) {
    $db=new db();
    $db->CSRFToken = $CSRFSession;
    $db->update("personnel", "comp_time='0.00'");
}
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update("personnel", "conges_credit=(conges_annuel-conges_anticipation)");
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update("personnel", "conges_anticipation=0.00");
