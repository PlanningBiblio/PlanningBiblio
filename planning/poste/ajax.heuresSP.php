<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.heuresSP.php
Création : 30 juillet 2015
Dernière modification : 31 août 2015
Auteur : Jérôme Combes jerome@planningbilbio.fr

Description :
Calcul les heures de service public à effectuer pour la semaine courante pour chaque agent
Script appelé en ajax lors du chargement de la page planning/poste/index.php (voir planning/poste/js/planning.js)
- Recherche les heures hebdomadaires que les agents doivent faire dans la semaine en cours dans la table heures_SP
- Si l'information n'est pas présente dans la table heures_SP, effectue les calculs, les stock dans la table 
  et les met en session ($_SESSION['oups']['heuresSP'])
- Calcul effectué à partir des planning de présence de la table planningHebdo si le module est activé 
  sinon,à partir des planning de présence de la table personnel
- Les heures de SP calculées pour chaque agent et placées dans $_SESSION['oups']['heuresSP'] sont utilisées dans le menu permettant
  de placer les agents dans les plannings. (ajax.menudiv.php, class.planning.php planning::menudivAfficheAgents)
- Elles sont calculées en ajax lors du 1er chargement du planning de la semaine et mises en cache 
  afin de ne pas ralentir le chargement du menu.
- Elles sont recalculées si la table planningHebdo (si module activé) ou la table personnel (si module PH désactivé) est modifiée
*/

session_start();

ini_set("display_error",0);

require_once "../../include/config.php";
require_once "../../include/function.php";

//	Initilisation des variables
$date=filter_input(INPUT_POST,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));

$heuresSP=calculHeuresSP($date);
$_SESSION['oups']['heuresSP'] = $heuresSP;
?>