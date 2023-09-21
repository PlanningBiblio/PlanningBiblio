<?php
/**
Planno
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

Création : 13 août 2013
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Tâche planifiée de suppression des reliquats de congés
*/

require_once(__DIR__ . '/../../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../../public/personnel/class.personnel.php');
require_once(__DIR__ . '/../../../public/include/db.php');

$CSRFSession = $GLOBALS['CSRFSession'];

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
