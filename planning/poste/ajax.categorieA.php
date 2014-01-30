<?php
/*
Planning Biblio, Version 1.6.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/validation.php
Création : 28 octobre 2013
Dernière modification : 31 octobre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Contrôle en arrière plan si un agent de catégorie A est placé en fin de service.
Permet d'afficher ou de masquer l'alerte "pas d'agent de catégorie A en fin de service" en haut du planning
Page appellée par la fonction JavaScript verif_categorieA lors du chargement de la page planning/poste/index.php et lors de la modification 
d'une cellule (fonction JS bataille_navale)
Affiche "true" ou "false"
*/

session_start();

// Includes
include "../../include/config.php";
include "class.planning.php";

// Si l'option CatAFinDeService n'est pas choisie ou si l'utilisateur n'a pas le droits de modifier le planning, 
// on retourne "true" de façon à ne pas afficher l'alerte 
if(!in_array(12,$_SESSION['droits']) or !$config['CatAFinDeService']){
  echo "true";
  exit;
}

// Recherche s'il y a des agents de catégorie A en fin de service
$p=new planning();
$p->date=$_GET['date'];
$p->site=$_GET['site'];
$p->finDeService();

if($p->categorieA){
  echo "true";
}
else{
  echo "false";
}
?>