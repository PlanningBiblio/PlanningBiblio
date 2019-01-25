<?php
/**
Planning Biblio, Plugin Conges Version 2.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/planning.php
Création : 24 octobre 2013
Dernière modification : 17 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier intégré au planning (planning/poste/index.php)
Ajoute les agents en congés à la liste des absents
*/

// $conges = liste des congés du jour, créé par planning_cellules.php inclus plutôt dans planning/poste/index.php

foreach ($conges as $elem) {
    if ($elem['valide']>0) {
        $elem['motif']="Cong&eacute; pay&eacute;";
        $absences_planning[]=$elem;
        $absences_id[]=$elem['perso_id'];
    }
}

usort($absences_planning, 'cmp_nom_prenom_debut_fin');
