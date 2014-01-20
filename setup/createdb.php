<?php
/*
Planning Biblio, Version 1.6.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : setup/createdb.php
Création : mai 2011
Dernière modification : 14 décembre 2012
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
$req="SELECT * FROM `mysql`.`user` WHERE `User`='{$_POST['dbuser']}' AND `Host`='localhost';";
$dbconn=mysql_connect("localhost",$_POST['adminuser'],$_POST['adminpass']);
$query=mysql_query($req,$dbconn);
if(mysql_fetch_array($query)){
  $user_exist=true;
}
mysql_close($dbconn);

//	Suppression de l'utilisateur si demandé
if(isset($_POST['dropuser'])){
  $dbconn=mysql_connect("localhost",$_POST['adminuser'],$_POST['adminpass']);
  $query=mysql_query($req,$dbconn);
  if($user_exist){
    $sql[]="DROP USER '{$_POST['dbuser']}'@'localhost';";
    $user_exist=false;
  }
}
//	Suppression de la base si demandé
if(isset($_POST['dropdb']))
	$sql[]="DROP DATABASE IF EXISTS `{$_POST['dbname']}` ;";

//	Création de l'utilisateur 
if(!$user_exist){
  $sql[]="CREATE USER '{$_POST['dbuser']}'@'localhost' IDENTIFIED BY '{$_POST['dbpass']}';";
  $sql[]="GRANT USAGE ON * . * TO '{$_POST['dbuser']}'@'localhost' IDENTIFIED BY '{$_POST['dbpass']}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";
}

//	Création de la base
$sql[]="CREATE DATABASE IF NOT EXISTS `{$_POST['dbname']}` ;";
$sql[]="GRANT ALL PRIVILEGES ON `{$_POST['dbname']}` . * TO '{$_POST['dbuser']}'@'localhost';";

$sql[]="USE {$_POST['dbname']};";

//	Création des tables
include "db_structure.php";

//	Insertion des données
include "db_data.php";

$dbconn=mysql_connect("localhost",$_POST['adminuser'],$_POST['adminpass']);
if($dbconn){
  foreach($sql as $elem){
    $message.=str_replace("\n","<br/>",$elem)."<br/>";
    if(trim($elem)){
      if(!mysql_query($elem,$dbconn)){
	$erreur=true;
	$message.="<p style='color:red'>ERROR : ";
	$message.=mysql_error();
	$message.="</p>\n";
      }
    }
  }
  mysql_close($dbconn);
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