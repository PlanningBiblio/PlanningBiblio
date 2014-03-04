<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : personnel/ajax.statuts.php
Création : 15 février 2014
Dernière modification : 15 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Enregistre la liste des statuts dans la base de données
Appelé lors du clic sur le bouton "Enregistrer" de la dialog box "Liste des statuts" à partir de la fiche agent
*/

session_start();
ini_set('display_errors',0);
ini_set('error_reporting',E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

include "../include/config.php";
include "../include/function.php";
$tab=json_decode($_POST['tab']);

$db=new db();
$db->delete("select_statuts");
foreach($tab as $elem){
  $db=new db();
  $db->insert2("select_statuts",array("valeur"=>$elem[0],"categorie"=>$elem[1],"rang"=>$elem[2]));
}
?>