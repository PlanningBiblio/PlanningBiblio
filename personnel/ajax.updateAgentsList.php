<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : personnel/ajax.updateAgentsList.php
Création : 30 octobre 2014
Dernière modification : 9 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Met à jour la liste des agents dans les select des pages absences/voir.php et plugins/conges/voir.php
Affiche dans cette liste les agents supprimés ou non en fonction de la variable $_GET['deleted']
Appelé en Ajax via la fonction JS updateAgentsList à partir de la page voir.php
*/

ini_set('display_errors',0);

include "../include/config.php";
include "class.personnel.php";

$p=new personnel();
if($_GET['deleted']=="yes"){
  $p->supprime=array(0,1);
}
$p->fetch();
$p->elements;

$tab=array();
foreach($p->elements as $elem){
  $tab[]=array("id"=>$elem['id'],"nom"=>$elem['nom'],"prenom"=>$elem['prenom']);
}
  
echo json_encode($tab);
?>