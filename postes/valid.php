<?php
/*
Planning Biblio, Version 1.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : postes/valid.php
Création : mai 2011
Dernière modification : 5 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Valide l'ajout, la modification ou la suppression d'un poste.

Page appelée par le fichier index.php.
*/

require_once "class.postes.php";

$id=$_GET['id'];
$action=$_GET['action'];

if(!trim($_GET['nom']) and $action!="supprime"){
  echo "<h3>Liste des postes</h3>\n";
  echo "Le nom est obligatoire<br/><br/>\n";
  echo "<a href='index.php?page=postes/index.php'>Retour</a>\n";
  exit;
}

if(isset($_GET['nom'])){
  $nom=trim($_GET['nom']);
  $etage=$_GET['etage'];
  $activites=isset($_GET['activites'])?serialize($_GET['activites']):null;
  $obligatoire=$_GET['obligatoire'];
  $statistiques=$_GET['statistiques'];
  $bloquant=$_GET['bloquant'];
  $site=$_GET['site'];
  $categorie=$_GET['categorie'];

  $data=array("nom"=>$nom,"obligatoire"=>$obligatoire,"etage"=>$etage,"activites"=>$activites,
  "statistiques"=>$statistiques,"bloquant"=>$bloquant,"site"=>$site,"categorie"=>$categorie);
}

switch($action){
  case "ajout" :	
    $db=new db();
    $db->insert2("postes",$data);
    break;

  case "modif" :
    $db=new db();
    $db->update2("postes",$data,array("id"=>$id));
    break;
	  
  case "supprime" :
    $db=new db();
    $db->delete2("postes",array("id"=>$id));
    break;
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=postes/index.php';</script>\n";
?>