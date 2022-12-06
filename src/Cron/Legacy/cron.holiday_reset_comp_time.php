<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : src/Cron/Legacy/cron.holiday_reset_comp_time.php
Création : 5 décembre 2022
Dernière modification : 5 décembre 2022
@author Alex Arnaud <alex.arnaud@biblibre.com>

Description :
Fichier executant des taches planifiées au 1er septembre pour le plugin Conges.
Page appelée par le fichier include/cron.php
Met à jour les crédits de récupération
*/

require_once(__DIR__ . '/../../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../../public/personnel/class.personnel.php');
require_once(__DIR__ . '/../../../public/include/db.php');

// Ajout d'une ligne d'information dans le tableau des congés
$p=new personnel();
$p->supprime=array(0,1);
$p->fetch();
if ($p->elements) {
    foreach ($p->elements as $elem) {
        $credits=array();
        $credits['comp_time'] = 0;

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
