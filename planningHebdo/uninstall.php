<?php
/*
Planning Biblio, Plugin planningHebdo Version 1.3.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : plugins/planningHebdo/uninstall.php
Création : 23 juillet 2013
Dernière modification : 15 août 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier permettant la désinstallation du plugin planningHebdo. Supprime les informations LDAP de la base de données
*/

session_start();

// Sécurité
if($_SESSION['login_id']!=1){
  echo "<br/><br/><h3>Vous devez vous connecter au planning<br/>avec le login \"admin\" pour pouvoir d&eacute;sinstaller ce plugin.</h3>\n";
  echo "<a href='../../index.php'>Retour au planning</a>\n";
  exit;
}


$version="1.3.1";
include_once "../../include/config.php";
$sql=array();

// Droits d'accès
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `groupe_id`='24';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='plugins/planningHebdo/monCompte.php';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='plugins/planningHebdo/valid.php';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='plugins/planningHebdo/modif.php';";

// Suppression des tables
$sql[]="DROP TABLE `{$dbprefix}planningHebdo`;";
$sql[]="DROP TABLE `{$dbprefix}planningHebdoConfig`;";
$sql[]="DROP TABLE `{$dbprefix}planningHebdoPeriodes`;";

// Suppression du sous-menu Planning de présence dans le menu Administration
$sql[]="DELETE FROM `{$dbprefix}menu` WHERE `url`='plugins/planningHebdo/index.php';";

// Cron
$sql[]="DELETE FROM `{$dbprefix}cron` WHERE `command`='plugins/planningHebdo/cron.daily.php';";

//	Inscription du plugin planningHebdo dans la base
$sql[]="DELETE FROM `{$dbprefix}plugins` WHERE `nom`='planningHebdo';";

foreach($sql as $elem){
  $db=new db();
  $db->query($elem);
  if(!$db->error)
    echo "$elem : <font style='color:green;'>OK</font><br/>\n";
  else
    echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
}

echo "<br/><br/><a href='../../index.php'>Retour au planning</a>\n";
?>
