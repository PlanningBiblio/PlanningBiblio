<?php
/*
Planning Biblio, Version 1.8.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : setup/createdb.php
Création : mai 2011
Dernière modification : 9 octobre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Créer la base de données. Vérifie si la base et l'utilisateur MySQL existent. Les supprimes si demandé. Créé l'utilisateur et 
la base.
Inclus le fichiers setup/db_structure.php et setup/db_data.php afin de créer les tables et les remplir.
Inclus ensuite le fichier setup/createconfig.php si la base a été créée correctement

Ce fichier valide le formulaire de la page setup/index.php
*/

//	Variables
$dbprefix=$_POST['dbprefix'];
$sql=Array();
$erreur=false;
$message="<p style='color:red'>Il y a eu des erreurs pendant la création de la base de données.<br/></p>\n";

//	Entête
include "header.php";

//	Vérifions si l'utilisateur existe
$user_exist=false;
$req="SELECT * FROM `mysql`.`user` WHERE `User`='{$_POST['dbuser']}' AND `Host`='{$_POST['dbhost']}';";
$dbconn=mysqli_connect($_POST['dbhost'],$_POST['adminuser'],$_POST['adminpass'],'mysql');
$query=mysqli_query($dbconn,$req);
if(mysqli_fetch_array($query)){
  $user_exist=true;
}
mysqli_close($dbconn);

//	Suppression de l'utilisateur si demandé
if(isset($_POST['dropuser'])){
  $dbconn=mysqli_connect($_POST['dbhost'],$_POST['adminuser'],$_POST['adminpass'],'mysql');
  $query=mysqli_query($dbconn,$req);
  if($user_exist){
    $sql[]="DROP USER '{$_POST['dbuser']}'@'{$_POST['dbhost']}';";
    $user_exist=false;
  }
}
//	Suppression de la base si demandé
if(isset($_POST['dropdb'])){
  $sql[]="DROP DATABASE IF EXISTS `{$_POST['dbname']}` ;";
}

//	Création de l'utilisateur 
if(!$user_exist){
  $sql[]="CREATE USER '{$_POST['dbuser']}'@'localhost' IDENTIFIED BY '{$_POST['dbpass']}';";
}
$sql[]="GRANT USAGE ON `{$_POST['dbname']}` . * TO '{$_POST['dbuser']}'@'localhost' IDENTIFIED BY '{$_POST['dbpass']}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";

//	Création de la base
$sql[]="CREATE DATABASE IF NOT EXISTS `{$_POST['dbname']}` ;";
$sql[]="GRANT ALL PRIVILEGES ON `{$_POST['dbname']}` . * TO '{$_POST['dbuser']}'@'localhost';";

$sql[]="USE {$_POST['dbname']};";

//	Création des tables
include "db_structure.php";

//	Insertion des données
include "db_data.php";

$dbconn=mysqli_connect($_POST['dbhost'],$_POST['adminuser'],$_POST['adminpass']);
if($dbconn){
  foreach($sql as $elem){
    $message.=str_replace("\n","<br/>",$elem)."<br/>";
    if(trim($elem)){
      if(!mysqli_query($dbconn,$elem)){
	$erreur=true;
	$message.="<p style='color:red'>ERROR : ";
	$message.=mysqli_error($dbconn);
	$message.="</p>\n";
      }
    }
  }
  mysqli_close($dbconn);
}
else{
  $erreur=true;
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