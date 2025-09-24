<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/planning/poste/fonctions.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fonctions utilisées par les pages des dossiers planning/poste et planning/postes_cgf
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once "../../include/accessDenied.php";
    exit;
}

/**
 * @return mixed[]
 */
function deja_place($date, $poste): array
{
    $deja=array(0);
    $db=new db();
    $db->select2("pl_poste", "perso_id", array("date"=>$date, "absent"=>"0", "poste"=>$poste), "GROUP BY `perso_id`");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $deja[]=$elem['perso_id'];
        }
    }
    return $deja;
}

/**
 * @return mixed[]
 */
function deuxSP($date, $debut, $fin): array
{
    $tab=array(0);
    $db=new db();
    $db->select("pl_poste", "perso_id", "absent = '0' AND date='$date' AND (debut='$fin' OR fin='$debut')", "group by perso_id");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $tab[]=$elem['perso_id'];
        }
    }
    return $tab;
}

//--------		Vérifier si le poste demandé appartient à un groupe, si oui, on recherche les personnes qualifiées pour ce groupe (poste=groupe) --------//
function groupe($poste)
{
    $db=new db();
    $db->query("SELECT `groupe_id` FROM `{$GLOBALS['config']['dbprefix']}postes` WHERE `id`='$poste';");
    if ($db->result and $db->result[0]['groupe_id']!=0) {
        $poste=$db->result[0]['groupe_id'];
    }
    return $poste;
}
//--------		FIN Vérifier si le poste demandé appartient à un groupe, si oui, on recherche les personnes qualifiées pour ce groupe (poste=groupe) ---------//

//		-------------	paramétrage de la largeur des colonnes		--------------//
function nb30($debut, $fin)
{
    $tmpFin=explode(":", $fin);
    $tmpDebut=explode(":", $debut);
    $time=(($tmpFin[0]*60)+$tmpFin[1]-($tmpDebut[0]*60)-$tmpDebut[1])/15;
    return $time;
}
//		-------------	FIN paramétrage de la largeur des colonnes		--------------//
