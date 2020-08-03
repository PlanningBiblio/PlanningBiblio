<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/conges/ajax.verifConges.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Vérifie si la période demandée a déjà fait l'objet d'une demande de congés.
Appelé en arrière plan par la fonction JS verifConges()
*/

include(__DIR__.'/../init_ajax.php');
include "class.conges.php";

$debut = filter_input(INPUT_GET, 'debut', FILTER_SANITIZE_STRING);
$fin = filter_input(INPUT_GET, 'fin', FILTER_SANITIZE_STRING);
$hre_debut = filter_input(INPUT_GET, 'hre_debut', FILTER_SANITIZE_STRING);
$hre_fin = filter_input(INPUT_GET, 'hre_fin', FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$perso_id = filter_input(INPUT_GET, 'perso_id', FILTER_SANITIZE_NUMBER_INT);

$fin = $fin ?? $debut;
$hre_debut = $hre_debut ?? "00:00:00";
$hre_fin = $hre_fin ?? "23:59:59";

$warning = array();

if ($result = conges::exists($perso_id, "$debut $hre_debut", "$fin $hre_fin", $id)) {
    $warning['holiday'] = 'du ' . dateFr($result['from'], true) . ' au ' . dateFr($result['to'], true);
}


// Contrôle si placé sur des plannings en cours d'élaboration;
if (!isset($warning['holiday']) and $config['Conges-planningVide']==0) {
    // Dates à contrôler
    $date_debut=substr($debut, 0, 10);
    $date_fin=substr($fin, 0, 10);
  
    // Tableau des plannings en cours d'élaboration
    $planningsEnElaboration=array();
  
    // Pour chaque dates
    $date=$date_debut;
    while ($date<=$date_fin) {
        // Vérifie si les plannings de tous les sites sont validés
        $db=new db();
        $db->select2("pl_poste_verrou", "*", array("date"=>$date, "verrou2"=>"1"));
        // S'ils ne sont pas tous validés, vérifie si certains d'entre eux sont commencés
        if ($db->nb < $config['Multisites-nombre']) {
            // TODO : ceci peut être amélioré en cherchant en particulier si les sites non validés sont commencés, car les sites non validés et non commencés ne nous interressent pas.
            // for($i=1;$i<=$config['Multisites-nombre'];$i++){} // Attention, faire une première requête si $db->nb=0 pour éviter les erreurs foreach not array
            // Le nom des sites pourrait également être retourné
      
            $db2=new db();
            $db2->select2("pl_poste", "id", array("date"=>$date));
            // Si tous les sites ne sont pas validés et si certains sont commencés, on affichera la date correspondante
            if ($db2->result) {
                $planningsEnElaboration[]=date("d/m/Y", strtotime($date));
            }
        }
        $date=date("Y-m-d", strtotime($date." +1 day"));
    }
  
    // Affichage des dates correspondantes aux plannings en cours d'élaboration
    if (!empty($planningsEnElaboration)) {
        $warning["planning_started"]=implode(" ; ", $planningsEnElaboration);
    }
}


echo json_encode($warning);
