<?php
/**
Planning Biblio, Version 2.4.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : setup/fin.php
Création : mai 2011
Dernière modification : 13 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Dernière page d'installation. Reçoit les informations du formualire de la page setup/config.php (informations sur le 
responsable du planning). Insère l'utilisateur dans la base de données (table personnel et config pour le nom et l'email 
du responsable.
Affiche le message "configuration terminée" et invite l'utilisateur à se connecter au planning
*/

$version="2.4.4";
include "../include/config.php";
include "header.php";

$nom=filter_input(INPUT_POST,"nom",FILTER_SANITIZE_STRING);
$prenom=filter_input(INPUT_POST,"prenom",FILTER_SANITIZE_STRING);
$password=filter_input(INPUT_POST,"password",FILTER_UNSAFE_RAW);
$password2=filter_input(INPUT_POST,"password2",FILTER_UNSAFE_RAW);
$email=filter_input(INPUT_POST,"email",FILTER_UNSAFE_RAW);
$erreur=false;

if(strlen($password)<6){
  echo "<p style='color:red'>Le mot de passe doit comporter au moins 6 caractères.<br/>\n";
  echo "<a href='javascript:history.back();'>Retour</a></p>\n";
  include "footer.php";
  exit;
}

if($password!=$password2){
  echo "<p style='color:red'>Les mots de passe ne correspondent pas.<br/>\n";
  echo "<a href='javascript:history.back();'>Retour</a></p>\n";
  include "footer.php";
  exit;
}
	
$password=md5($password);
$db=new db();
$db->update2("personnel",array("nom"=>$nom, "prenom"=>$prenom, "password"=>$password, "mail"=>$email), array("id"=>"1"));
if($db->error){
  $erreur=true;
}

if($erreur){
  echo "<p style='color:red'>Il y a eu des erreurs.</p>\n";
  echo "<a href='javascript:history.back();'>Retour</a>\n";
}
else{
  echo "<h3>L'installation est terminée.</h3>\n";
  echo "Veuillez verifier l'installation.<br/>Si tout fonctionne, supprimez le dossier \"setup\".<br/>\n";
  echo "<p><a href='../authentification.php?newlogin=admin' class='ui-button'>Se connecter au planning</a><br/><br/></p>\n";
}
include "footer.php";
?>
