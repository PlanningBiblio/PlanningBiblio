<?php
/*
Planning Biblio, Version 1.5.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : setup/fin.php
Création : mai 2011
Dernière modification : 18 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Dernière page d'installation. Reçoit les informations du formualire de la page setup/config.php (informations sur le 
responsable du planning). Insère l'utilisateur dans la base de données (table personnel et config pour le nom et l'email 
du responsable.
Affiche le message "configuration terminée" et invite l'utilisateur à se connecter au planning
*/

include "../include/config.php";
include "header.php";

$url=$_POST['url'];
$nom=$_POST['nom'];
$prenom=$_POST['prenom'];
$login=$_POST['login'];
$password=$_POST['password'];
$email=$_POST['email'];
$dbprefix=$_POST['dbprefix'];
$erreur=false;

if(strlen($password)<6){
  echo "<p style='color:red'>Le mot de passe doit comporter au moins 6 caractères.<br/>\n";
  echo "<a href='javascript:history.back();'>Retour</a></p>\n";
  include "footer.php";
  exit;
}

if($password!=$_POST['password2']){
  echo "<p style='color:red'>Les mots de passe ne correspondent pas.<br/>\n";
  echo "<a href='javascript:history.back();'>Retour</a></p>\n";
  include "footer.php";
  exit;
}
	
$sql="INSERT INTO `{$dbprefix}personnel` (`nom`,`prenom`,`mail`,`postes`,`actif`,`droits`,`login`,`password`,`commentaires`) ";
$sql.="VALUES ('$nom','$prenom','$email','a:11:{i:0;s:2:\"20\";i:1;s:2:\"22\";i:2;s:2:\"13\";i:3;s:1:\"1\";i:4;s:1:\"5\";i:5;s:2:\"21\";i:6;s:2:\"12\";i:7;s:2:\"17\";i:8;s:1:\"4\";i:9;i:99;i:10;i:100;}','Inactif',";
$sql.="'a:11:{i:0;s:2:\"22\";i:1;s:2:\"13\";i:2;s:1:\"1\";i:3;s:1:\"5\";i:4;s:2:\"21\";i:5;s:2:\"23\";i:6;s:2:\"12\";i:7;s:2:\"17\";i:8;s:1:\"4\";i:9;i:99;i:10;i:100;}',";
$sql.="'$login',MD5('$password'),'Compte créé lors de l\'installation du planning');";

$db=new db();
$db->query($sql);
if($db->error){
  $erreur=true;
}

$db=new db();
$db->query("UPDATE `{$dbprefix}config` SET `valeur`='$url' WHERE `nom`='url';");
if($db->error){
  $erreur=true;
}

if($erreur){
  echo "<p style='color:red'>Il y a eu des erreurs.</p>\n";
  echo "<a href='javascript:history.back();'>Retour</a>\n";
}
else{
  echo "<h3>La configuration est terminée.</h3>\n";
  echo "Veuillez verifier l'installation. Si tout fonctionne, supprimez le dossier \"setup\".<br/>\n";
  echo "<p><a href='{$url}/authentification.php?newlogin=$login'>Se connecter au planning</a><br/><br/></p>\n";
}
include "footer.php";
?>