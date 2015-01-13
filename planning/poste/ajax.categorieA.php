<?php
/*
Planning Biblio, Version 1.8.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/validation.php
Création : 28 octobre 2013
Dernière modification : 13 janvier 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Contrôle en arrière plan si un agent de catégorie A est placé en fin de service.
Permet d'afficher ou de masquer l'alerte "pas d'agent de catégorie A en fin de service" en haut du planning
Page appellée par la fonction JavaScript verif_categorieA lors du chargement de la page planning/poste/index.php et lors de la modification 
d'une cellule (fonction JS bataille_navale)
Affiche "true" ou "false"
*/

// Includes
include "../../include/config.php";
include "class.planning.php";

// Recherche s'il y a des agents de catégorie A en fin de service
$p=new planning();
$p->date=$_POST['date'];
$p->site=$_POST['site'];
$p->finDeService();

if($p->categorieA){
  echo json_encode("true");
}
else{
  echo json_encode("false");
}
?>