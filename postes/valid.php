<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : postes/valid.php
Création : mai 2011
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Valide l'ajout ou la modification d'un poste.

Page appelée par le fichier index.php.
*/

require_once "class.postes.php";

$id=$_GET['id'];
$action=$_GET['action'];

if(!trim($_GET['nom'])){
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
  $site=isset($_GET['site'])?$_GET['site']:1;
  $categories=isset($_GET['categories'])?serialize($_GET['categories']):serialize(array());

  $data=array("nom"=>$nom,"obligatoire"=>$obligatoire,"etage"=>$etage,"activites"=>$activites,
  "statistiques"=>$statistiques,"bloquant"=>$bloquant,"site"=>$site,"categories"=>$categories);
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
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=postes/index.php';</script>\n";
?>