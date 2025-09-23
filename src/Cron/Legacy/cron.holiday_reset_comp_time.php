<?php
/**
Planno
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

Dernière modification : 5 décembre 2022
@author Alex Arnaud <alex.arnaud@biblibre.com>

Description :
Tâche planifiée de mise à zéro des crédits de récupération
*/

require_once(__DIR__ . '/../../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../../public/include/db.php');

$CSRFSession = $GLOBALS['CSRFSession'];

// Ajout d'une ligne d'information dans le tableau des congés
$p=new personnel();
$p->supprime=array(0,1);
$p->fetch();
if ($p->elements) {
    foreach ($p->elements as $elem) {
        $credits=array();
        $credits['comp_time'] = 0;
        $credits['conges_credit'] = $elem['conges_credit'];
        $credits['conges_anticipation'] = $elem['conges_anticipation'];
        $credits['conges_reliquat'] = $elem['conges_reliquat'];

        $c=new conges();
        $c->perso_id=$elem['id'];
        $c->CSRFToken = $CSRFSession;
        $c->maj($credits, "modif", true);
    }
}

// Modifie les crédits
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update("personnel", "comp_time='0.00'");
