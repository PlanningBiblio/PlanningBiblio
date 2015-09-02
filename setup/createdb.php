<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : setup/createdb.php
Création : mai 2011
Dernière modification : 4 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Créer la base de données. Vérifie si la base et l'utilisateur MySQL existent. Les supprimes si demandé. Créé l'utilisateur et 
la base.
Inclus le fichiers setup/db_structure.php et setup/db_data.php afin de créer les tables et les remplir.
Inclus ensuite le fichier setup/createconfig.php si la base a été créée correctement

Ce fichier valide le formulaire de la page setup/index.php
*/

//	Variables
$dbhost=filter_input(INPUT_POST,"dbhost",FILTER_SANITIZE_STRING);
$dbname=filter_input(INPUT_POST,"dbname",FILTER_SANITIZE_STRING);
$dbAdminUser=filter_input(INPUT_POST,"adminuser",FILTER_SANITIZE_STRING);
$dbAdminPass=filter_input(INPUT_POST,"adminpass",FILTER_UNSAFE_RAW);
$dbuser=filter_input(INPUT_POST,"dbuser",FILTER_SANITIZE_STRING);
$dbpass=filter_input(INPUT_POST,"dbpass",FILTER_UNSAFE_RAW);
$dbprefix=filter_input(INPUT_POST,"dbprefix",FILTER_SANITIZE_STRING);
$dropUser=filter_input(INPUT_POST,"dropuser",FILTER_SANITIZE_STRING);
$dropDB=filter_input(INPUT_POST,"dropdb",FILTER_SANITIZE_STRING);

$sql=Array();
$erreur=false;
$message="<p style='color:red'>Il y a eu des erreurs pendant la création de la base de données.<br/></p>\n";

//	Entête
include "header.php";

// Initialisation de la connexion MySQL
$dblink=mysqli_init();
/*
$dbhost=mysqli_real_escape_string($dblink,$dbhost);
$dbAdminUser=mysqli_real_escape_string($dblink,$dbAdminUser);
$dbAdminPass=mysqli_real_escape_string($dblink,$dbAdminPass);
*/
//	Vérifions si l'utilisateur existe
$user_exists=false;
$req="SELECT * FROM `mysql`.`user` WHERE `User`='$dbuser' AND `Host`='$dbhost';";
$dbconn=mysqli_real_connect($dblink,$dbhost,$dbAdminUser,$dbAdminPass,'mysql');

$dbname=mysqli_real_escape_string($dblink,$dbname);
$dbuser=mysqli_real_escape_string($dblink,$dbuser);
$dbpass=mysqli_real_escape_string($dblink,$dbpass);

$query=mysqli_query($dblink,$req);
if(mysqli_fetch_array($query)){
  $user_exists=true;
}

//	Suppression de l'utilisateur si demandé
if($dropUser){
  if($user_exists){
    $sql[]="DROP USER '$dbuser'@'$dbhost';";
    $user_exists=false;
  }
}
//	Suppression de la base si demandé
if($dropDB){
  $sql[]="DROP DATABASE IF EXISTS `$dbname` ;";
}

//	Création de l'utilisateur 
if(!$user_exists){
  $sql[]="CREATE USER '$dbuser'@'localhost' IDENTIFIED BY '$dbpass';";
}
$sql[]="GRANT USAGE ON `$dbname` . * TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";

//	Création de la base
$sql[]="CREATE DATABASE IF NOT EXISTS `$dbname` ;";
$sql[]="GRANT ALL PRIVILEGES ON `$dbname` . * TO '$dbuser'@'localhost';";

$sql[]="USE $dbname;";

//	Création des tables
include "db_structure.php";

//	Insertion des données
include "db_data.php";


if($dbconn){
  foreach($sql as $elem){
    $message.=str_replace("\n","<br/>",$elem)."<br/>";
    if(trim($elem)){
      if(!mysqli_multi_query($dblink,$elem)){
	$erreur=true;
	$message.="<p style='color:red'>ERROR : ";
	$message.=mysqli_error($dblink);
	$message.="</p>\n";
      }
    }
  }
  mysqli_close($dblink);
}
else{
  $erreur=true;
  $message.="<p style='color:red'>ERROR : Impossible de se connecter au serveur MySQL</p>\n";
}

$message.="<p><a href='index.php'>Retour</a></p>\n";

if($erreur){
  echo $message;
}
else{
  echo "<p>La base de donnée a bien été créée.</p>\n";
  include "createconfig.php";
}

include "footer.php";
?>