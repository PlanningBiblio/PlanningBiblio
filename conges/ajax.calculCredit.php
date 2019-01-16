<?php
/**
Planning Biblio, Plugin Congés Version 2.4.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.calculCredit.php
Création : 2 août 2013
Dernière modification : 28 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Calcul le nombre d'heures correspondant à un congé
Appelé en arrière plan par la fonction JS calculCredit() (fichier conges/js/script.conges.js)
  lors du clic sur le bouton calculer du formulaire de saisie de congés (fichier conges/enregistrer.php)
*/

require_once "../../include/config.php";
require_once "class.conges.php";

// Initilisation des variables
$debut=dateSQL(filter_input(INPUT_GET, "debut", FILTER_CALLBACK, array("options"=>"sanitize_dateFr")));
$fin=dateSQL(filter_input(INPUT_GET, "fin", FILTER_CALLBACK, array("options"=>"sanitize_dateFr")));
$hre_debut=filter_input(INPUT_GET, "hre_debut", FILTER_CALLBACK, array("options"=>"sanitize_time"));
$hre_fin=filter_input(INPUT_GET, "hre_fin", FILTER_CALLBACK, array("options"=>"sanitize_time"));
$perso_id=filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);

$c=new conges();
$c->calculCredit($debut, $hre_debut, $fin, $hre_fin, $perso_id);
$result=$c->error?array("error"):array("OK");
$result[]=$c->heures;   // HH.CC (heures et centièmes)
$result[]=$c->heures2;  // HHhMM (heures et minutes)
echo json_encode($result);
