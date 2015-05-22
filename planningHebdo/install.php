<?php
/*
Planning Biblio, Plugin planningHebdo Version 1.3.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : plugins/planningHebdo/install.php
Création : 23 juillet 2013
Dernière modification : 5 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier permettant l'installation du plugin planningHebdo. Ajoute les informations nécessaires dans la base de données
*/

session_start();

// Sécurité
if($_SESSION['login_id']!=1){
  echo "<br/><br/><h3>Vous devez vous connecter au planning<br/>avec le login \"admin\" pour pouvoir installer ce plugin.</h3>\n";
  echo "<a href='../../index.php'>Retour au planning</a>\n";
  exit;
}

$version="1.3.1";
include_once "../../include/config.php";

$sql=array();

// Droits d'accès
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - Index','24','Validation des plannings de présences','plugins/planningHebdo/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - Configuration','24','Validation des plannings de présences','plugins/planningHebdo/configuration.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Modif','100','plugins/planningHebdo/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Mon Compte','100','plugins/planningHebdo/monCompte.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Validation','100','plugins/planningHebdo/valid.php');";

// Création des tables
$sql[]="CREATE TABLE `{$dbprefix}planningHebdo` (`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `perso_id` INT(11) NOT NULL, `debut` DATE NOT NULL, `fin` DATE NOT NULL, `temps` TEXT NOT NULL, `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `modif` INT(11) NOT NULL DEFAULT '0',`modification` TIMESTAMP, `valide` INT(11) NOT NULL DEFAULT '0',`validation` TIMESTAMP, `actuel` INT(1) NOT NULL DEFAULT '0', `remplace` INT(11) NOT NULL DEFAULT '0');";
$sql[]="CREATE TABLE `{$dbprefix}planningHebdoConfig` (`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `nom` VARCHAR(30), `valeur` TEXT);";
$sql[]="CREATE TABLE `{$dbprefix}planningHebdoPeriodes` (`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `annee` VARCHAR(9), `dates` TEXT);";

// Configuration
$sql[]="INSERT INTO `{$dbprefix}planningHebdoConfig` (`nom`,`valeur`) VALUES ('periodesDefinies','0'),('notifications','droit');";

// Menu administration
$sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES (50,75,'Plannings de présence','plugins/planningHebdo/index.php');";

// Cron
$sql[]="INSERT INTO `{$dbprefix}cron` (`h`,`m`,`dom`,`mon`,`dow`,`command`,`comments`) VALUES ('0','0','*','*','*','plugins/planningHebdo/cron.daily.php','Daily Cron for planningHebdo plugin');";

//	Inscription du plugin planningHebdo dans la base
$sql[]="INSERT INTO `{$dbprefix}plugins` (`nom`) VALUES ('planningHebdo');";

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
