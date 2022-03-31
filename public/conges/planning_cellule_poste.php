<?php
/**
Planning Biblio, Plugin Conges Version 2.4.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/planning_cellule_poste.php
Création : 30 janvier 2014
Dernière modification : 29 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier intégré au planning (planning/poste/fonctions.php)
Vérifie si l'agent placé dans la cellule est en congés, s'il y a un congé validé: on le barre en orange, s'il y a un congé non validé : on l'affiche en orange
*/

$conge_valide=false;

// On marque les congés
foreach ($GLOBALS['conges'] as $conge) {
    if ($conge['perso_id']==$elem['perso_id'] and $conge['debut']<"$date {$elem['fin']}" and $conge['fin']>"$date {$elem['debut']}") {
        // Congé validé : orange barré
        if ($conge['valide']>0) {
            $class_tmp[]="orange";
            $class_tmp[]="striped";
            $conge_valide=true;
            $json_line['absent'] = true;
            break;  // Garder le break à cet endroit pour que les congés validées prennent le dessus sur les non-validés
        }
        // congé non-validée : orange, sauf si une absence validée existe
        elseif ($GLOBALS['config']['Absences-non-validees'] and !$absence_valide) {
            $class_tmp[]="orange";
            $title = $nom_affiche.' : Congé non-valid&eacute;';
        }
    }
}

// Il peut y avoir des absences  et des congés validés et non validés. Si une absence ou un congé est validé, la cellule sera barrée et on n'affichera pas "Congé non-validé"
if ($conge_valide or $absence_valide) {
    $title = null;
}
