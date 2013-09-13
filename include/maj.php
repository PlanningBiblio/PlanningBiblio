<?php
/*
Planning Biblio, Version 1.5.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : include/maj.php
Création : mai 2011
Dernière modification : 13 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Ce fichier permet de mettre à jour la base de données lors de la mise à jour de l'application.
Cette page est appelée par la page index.php si la version du fichier index.php et différente de la version enregistrée
dans la base de données
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

echo "Mise &agrave; jour de la base de donn&eacute;es version {$config['Version']} --> $version<br/>\n";

$sql=array();

//	Mise a jour de la base version 1.0 --> 1.1

if(strcmp("1.1",$config['Version'])>0){
  //	Maj de la table config pour ameliorer l'affichage
  $sql[]="ALTER TABLE `{$dbprefix}config` ADD `categorie` VARCHAR( 20 ) NOT NULL;";
  $sql[]="ALTER TABLE `{$dbprefix}config` ADD `valeurs` TEXT NOT NULL;";
  $sql[]="ALTER TABLE `{$dbprefix}config` ADD `ordre` INT(2) NOT NULL;";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Messagerie' WHERE `nom` LIKE 'Mail%' OR `nom` LIKE 'email_%';";
  $sql[]="UPDATE `{$dbprefix}config` SET `type`='enum',`valeurs`='IsSMTP,IsMail', `commentaires`='Telling the class to use SMTP'  WHERE `nom`='Mail-IsMail-IsSMTP';";
  $sql[]="UPDATE `{$dbprefix}config` SET `type`='enum',`valeurs`=',ssl,tls', `commentaires`='Cryptage utilis&eacute; par le serveur STMP'  WHERE `nom`='Mail-SMTPSecure';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='1' WHERE `categorie`='Messagerie';";

  //	Ajout du Dimanche
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Dimanche','boolean','0','Utiliser le planning le dimanche','','','');";
  //	Mise a  jour de la version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.1' WHERE `nom`='Version';";
}


//	Mise a  jour de la base version 1.1 --> 1.2
if(strcmp("1.2",$config['Version'])>0){
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Personnel - Suppression liste','21','Gestion du personnel','personnel/suppression-liste.php');";

  //	Mise a jour modeles (nommage des modeles)
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_tab` CHANGE `date` `nom` VARCHAR( 30 ) NOT NULL;"; 
  $sql[]="UPDATE `{$dbprefix}acces` SET `page` = 'planning/postes_cfg/copie.php' WHERE `page` ='planning/postes_cfg/horaires_copie.php';";
  $sql[]="CREATE TABLE `{$dbprefix}pl_poste_modeles_tab` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `nom` VARCHAR(30) NOT NULL , `jour` INT NOT NULL, `tableau` INT NOT NULL);";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL,'Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/modif.php');";
  $sql[]="UPDATE `{$dbprefix}pl_poste_lignes` SET `tableau`=SUBSTRING(`tableau`,1,LENGTH(`tableau`)-5) WHERE `type`='titre' AND SUBSTRING(`tableau`,-5)='Titre';";
  $sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab_affect` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `date` DATE NOT NULL , `tableau` INT NOT NULL);";

  //	Ajout du choix du nombre de semaine pour les planning personnel
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'nb_semaine','enum','1','Nombre de semaine pour l\'emploi du temps','','1,2','');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'ctrlHresAgents','boolean','1','Contrôle des heures des agents le samedi et le dimanche','','','');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'agentsIndispo','boolean','1','Afficher les agents indisponibles','','','');";
  //	Choix des lignes de separation
  $sql[]="CREATE TABLE `{$dbprefix}lignes` (id int primary key auto_increment, nom varchar(50));";
  $sql[]="INSERT INTO `{$dbprefix}lignes` VALUES (null,'Magasins');";
  $sql[]="INSERT INTO `{$dbprefix}lignes` VALUES (null,'Mezzanine');";
  $sql[]="INSERT INTO `{$dbprefix}lignes` VALUES (null,'Rez de chaussée');";
  $sql[]="INSERT INTO `{$dbprefix}lignes` VALUES (null,'Rez de jardin');";
  //	2 services 
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_tab_affect` ADD service INT(3) NOT NULL;";
  //	Informations
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL,'Informations',23,'Informations','infos/index.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL,'Informations',23,'Informations','infos/modif.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL,'Informations',23,'Informations','infos/supprime.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL,'Informations',23,'Informations','infos/ajout.php');";
  $sql[]="CREATE TABLE `{$dbprefix}infos` (`id` INT PRIMARY KEY AUTO_INCREMENT, `debut` DATE, `fin` DATE, text TEXT);";
  //	Postes non-bloquant
  $sql[]="ALTER TABLE `{$dbprefix}postes` ADD `bloquant` enum('0','1') DEFAULT '1';";

  //	Mise a jour de la version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.2' WHERE `nom`='Version';";
}


//	Mise a jour de la base version 1.2 --> 1.2.1
if(strcmp("1.2.1",$config['Version'])>0){
  //	Correction de l'erreur de nommage du champ texte
  $sql[]="ALTER TABLE `{$dbprefix}infos` CHANGE `text` `texte` TEXT;";
  //	Mise a jour de la version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.2.1' WHERE `nom`='Version';";
}

//	Execution des requetes et affichage
foreach($sql as $elem){
  $db=new db();
  $db->query($elem);
  if(!$db->error)
    echo "$elem : <font style='color:green;'>OK</font><br/>\n";
  else
    echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
}
$sql=array();

//	Mise a jour de la base version 1.2.1 --> 1.3
if(strcmp("1.3",$config['Version'])>0){
  //	ligne_id plutot que son nom dans la table pl_poste_ligne
  $db=new db();
  $db->query("SELECT * FROM `{$dbprefix}lignes`");
  if($db->result){
    foreach($db->result as $elem){
      $sql[]="UPDATE `{$dbprefix}pl_poste_lignes` SET poste='{$elem['id']}' WHERE poste='{$elem['nom']}' AND type='ligne';";
    }
  }
  //	Page ligne_sep.php, groupes.php
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/lignes_sep.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/groupes.php');";

  $sql[]="ALTER TABLE`{$dbprefix}lignes` CHANGE `nom` `nom` VARCHAR(200);";

  //	Ajout des groupes de tableaux : Table pl_poste_grp
  $sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab_grp` (`id` INT AUTO_INCREMENT PRIMARY KEY, `nom` TEXT, `Lundi` INT, `Mardi` INT, `Mercredi` INT, `Jeudi` INT, `Vendredi` INT, `Samedi` INT, `Dimanche` INT);";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.3' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.3 --> 1.3.1
if(strcmp("1.3.1",$config['Version'])>0){
  //	Droits d'accès aux pages "modèles"
  $db=new db();
  $db->query("SELECT * FROM `{$dbprefix}acces` WHERE `page` LIKE 'planning/modeles%';");
  if(!$db->result){
    $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL, 'Planning - Modèles', 12, 'Modification du planning','planning/modeles/index.php');";
    $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL, 'Planning - Modèles', 12, 'Modification du planning','planning/modeles/modif.php');";
    $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL, 'Planning - Modèles', 12, 'Modification du planning','planning/modeles/valid.php');";
  }
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.3.1' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.3.1 --> 1.3.3
if(strcmp("1.3.3",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='1,2,3' WHERE `nom`='nb_semaine';";
  $sql[]="INSERT INTO `{$dbprefix}config` (nom,type,valeur,valeurs,ordre,commentaires) VALUES ('heuresHebdoPrecision','enum','heure','heure,demi-heure,quart d&apos;heure','0','Pr&eacute;cision des heures hebdomadaires');";
  $sql[]="INSERT INTO `{$dbprefix}config` (nom,ordre,commentaires) VALUES ('dateDebutPlHebdo','0','Date de d&eacute;but permettant la rotation des plannings hebdomadaires (pour l&apos;utilisation de 3 plannings hebdomadaires. Format YYYY-MM-DD)');";
  $sql[]="ALTER TABLE `{$dbprefix}personnel` CHANGE `heuresHebdo`  `heuresHebdo` FLOAT(5);";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.3.3' WHERE `nom`='Version';";
}


//	Mise a jour de la base version 1.3 --> 1.4
if(strcmp("1.4",$config['Version'])>0){
  htmlentitiesBdd("select_abs",array("valeur"));
  htmlentitiesBdd("select_services",array("valeur"));
  htmlentitiesBdd("select_statuts",array("valeur"));
  htmlentitiesBdd("personnel",array("nom","prenom","mail","statut","service","recup","commentaires","informations"));
  htmlentitiesBdd("absences",array("motif","commentaires","etat"));
  htmlentitiesBdd("absences_infos",array("texte"));
  htmlentitiesBdd("acces",array("nom","groupe"));
  htmlentitiesBdd("activites",array("nom"));
  htmlentitiesBdd("config",array("valeur","commentaires"));
  htmlentitiesBdd("infos",array("texte"));
  htmlentitiesBdd("lignes",array("nom"));
  htmlentitiesBdd("pl_poste_modeles",array("nom","commentaire"));
  htmlentitiesBdd("pl_poste_modeles_tab",array("nom"));
  htmlentitiesBdd("pl_poste_tab",array("nom"));
  htmlentitiesBdd("postes",array("nom"));
  htmlentitiesBdd("pl_poste_lignes",array("poste"));

  //	htmlentities pour le champ 'actif' de la table personnel
  $sql[]="ALTER TABLE `{$dbprefix}personnel` CHANGE `actif` `actif` VARCHAR(20);";
  $sql[]="UPDATE `{$dbprefix}personnel` SET `actif`='Supprim&eacute;' WHERE `actif` LIKE 'Supprim%';";
  //	Nettoyage de la table personnel
  $sql[]="ALTER TABLE `{$dbprefix}personnel` DROP `conges_annuel`, DROP `conges_restant`, DROP `conges_reliquat`;";
  $sql[]="ALTER TABLE `{$dbprefix}personnel` DROP `responsabilite`;";
  //	Coloration des cellules en fonction du statut
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'statutCouleur','boolean','0','Colorer les cellules en fonction du statut','','','');";
  //	Suppression de la ligne "url_auth" dans la table config
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='url_auth';";
  //	Affichage des étages dans le planning
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'affiche_etage','boolean','0','Afficher les étages des postes dans le planning','','','');";
  //	Sécurité, menudiv et majdb sont appelées par index.php
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null, 'Modification du planning - menudiv','12','Modification du planning','planning/poste/menudiv.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null, 'Modification du planning - majdb','12','Modification du planning','planning/poste/majdb.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/groupes2.php');";
  $sql[]="UPDATE `{$dbprefix}config` SET `nom` ='heuresPrecision' WHERE `nom`='heuresHebdoPrecision';";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'absences_planning','boolean','0','Afficher la liste des absences sur la page du planning','','','');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.4' WHERE `nom`='Version';";
}


//	Mise a  jour de la base version 1.4 -> 1.5
if(strcmp("1.5",$config['Version'])>0){
  // Methode d'authentification
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Auth-Mode','enum','SQL','Methode d&apos;authentification','','SQL','');";
  // Multisites
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-nombre','enum','1','Nombre de sites','Multisites','1,2','3');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site1','text','','Nom du site N°1','Multisites','','3');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-resp1','text','','Nom du responsable du site N°1','Multisites','','3');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-email1','text','','Email du responsable du site N°1','Multisites','','3');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site2','text','','Nom du site N°2','Multisites','','3');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-resp2','text','','Nom du responsable du site N°2','Multisites','','3');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-email2','text','','Email du responsable du site N°2','Multisites','','3');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-agentsMultisites','boolean','0','Les agents peuvent travailler sur plusieurs sites','Multisites','','3');";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_tab_affect` CHANGE `service` `site` INT;";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste` ADD `site` INT(3) DEFAULT 1;";
  $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `site` INT(1);";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_verrou` ADD `site` INT(1) NOT NULL DEFAULT '1';";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_modeles` ADD `site` INT(1) NOT NULL DEFAULT '1';";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_modeles_tab` ADD `site` INT(1) NOT NULL DEFAULT '1';";
  // Sécurité
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/groupes2.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Personnel - Importation','21','Gestion du personnel','personnel/import.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Jours fériés','25','Gestion des jours fériés','joursFeries/index.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Jours fériés','25','Gestion des jours fériés','joursFeries/valid.php');";
  // Plugins
  $sql[]="CREATE TABLE `{$dbprefix}plugins` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `nom` VARCHAR(30) NOT NULL);";
  // Nettoyage
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='acces_refuse';";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='poste_operateur';";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='statutCouleur';";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='email_responsable';";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='responsable';";
  $sql[]="UPDATE `{$dbprefix}config` SET `nom`='Mail-IsEnabled' WHERE `nom`='email_actif';";
  // Heures de travail
  $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `heuresTravail` FLOAT(5);";
  // Signatures des emails
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (NULL, 'Mail-Signature', 'textarea', 'Ce message a été envoyé par Planning Biblio.\nMerci de ne pas y répondre.', 'Signature des e-mails','Messagerie','','1');";
  // Menu
  $sql[]="CREATE TABLE `{$dbprefix}menu` (`id` INT AUTO_INCREMENT PRIMARY KEY, `niveau1` INT, `niveau2` INT, `titre` VARCHAR(100), `url` VARCHAR(500));";
  $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES 
    ('10','0','Absences','absences/index.php'),('10','10','Voir les absences','absences/voir.php'),
    ('10','20','Ajouter une absence','absences/ajouter.php'),('10','30','Informations','absences/infos.php'),
    ('20','0','Agenda','agenda/index.php'),('30','0','Planning','planning/poste/index.php'),
    ('40','0','Statistiques','statistiques/index.php'),('40','10','Feuille de temps','statistiques/temps.php'),
    ('40','20','Par agent','statistiques/agents.php'),('40','30','Par poste','statistiques/postes.php'),
    ('40','40','Par poste (Synthèse)','statistiques/postes_synthese.php'),
    ('40','50','Postes de renfort','statistiques/postes_renfort.php'),
    ('50','0','Administration','admin/index.php'),('50','10','Informations','infos/index.php'),
    ('50','20','Les activités','activites/index.php'),('50','30','Les agents','personnel/index.php'),
    ('50','40','Les postes','postes/index.php'),('50','50','Les modèles','planning/modeles/index.php'),
    ('50','60','Les tableaux','planning/postes_cfg/index.php'),('50','70','Les jours fériés','joursFeries/index.php'),
    ('50','80','Configuration','admin/config.php'),
    ('60','0','Aide','aide/index.php');";
  // Cron
  $sql[]="CREATE TABLE `{$dbprefix}cron` (`id` INT AUTO_INCREMENT PRIMARY KEY, `m` VARCHAR(2), `h` VARCHAR(2), `dom` VARCHAR(2), `mon` VARCHAR(2), `dow` VARCHAR(2), `command` VARCHAR(200), `comments` VARCHAR(500), `last` DATETIME NULL DEFAULT '0000-00-00 00:00:00');";

 // Jours féries
  $sql[]="CREATE TABLE `{$dbprefix}joursFeries` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `annee` VARCHAR(10), `jour` DATE, `ferie` INT(1), `fermeture` INT(1), `nom` TEXT, `commentaire` TEXT);";

  // Congés
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste` CHANGE `absent` `absent` ENUM('0','1','2') NOT NULL DEFAULT '0';";

  // Absences après validation
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'absencesApresValidation','boolean','1','Autoriser l&apos;enregistrement des absences apr&egrave;s validation des plannings','','','');";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.5 -> 1.5.1
if(strcmp("1.5.1",$config['Version'])>0){
  // MenuDiv, heures des 4 dernières semaines
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'hres4semaines','boolean','0','Afficher le total d&apos;heures des 4 derni&egrave;res semaine dans le menu','','','');";
  // Affichage des absences en bas du planning
  $sql[]="UPDATE `{$dbprefix}config` SET `type`='enum', `valeurs`=',simple,détaillé,absents et présents', `valeur`='' WHERE nom='absences_planning';";
  // Accès aux agendas et modification des absences
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Voir les agendas de tous','3','Voir les agendas de tous','');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (null,'Modifier ses propres absences','6','Modifier ses propres absences','');";
  $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`='100', `groupe`='' WHERE `page` LIKE 'absences/modif%';";
  $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`='100', `groupe`='' WHERE `page` LIKE 'absences/delete.php';";
  // Configuration
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='10' WHERE ORDRE='1';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='15' WHERE ORDRE='3';";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Authentification', `ordre`='5', `commentaires`='M&eacute;thode d&apos;authentification' WHERE `nom`='Auth-Mode';";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('Auth-Anonyme','boolean','0','Autoriser les logins anonymes','Authentification','5');";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Affichage', `ordre`='7', `commentaires`='Afficher les &eacute;tages des postes dans le planning' WHERE `nom`='affiche_etage';";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Affichage', `ordre`='7', `commentaires`='Afficher le total d&apos;heures des 4 derni&egrave;res semaines dans le menu' WHERE `nom`='hres4semaines';";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Affichage', `ordre`='7' WHERE `nom`='absences_planning';";

  // Affichage personnalisé sur la page d'accueil
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`commentaires`,`categorie`,`ordre`) VALUES ('titre','text','Titre affich&eacute; sur la page d&apos;accueil','Affichage','7');";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.1' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.5.1 -> 1.5.2
if(strcmp("1.5.2",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.2' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.5.2 -> 1.5.3
if(strcmp("1.5.3",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.3' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.5.3 -> 1.5.4
if(strcmp("1.5.4",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.4' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.5.4 -> 1.5.5
if(strcmp("1.5.5",$config['Version'])>0){
  //	Suppression de la ligne "url" dans la table config
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='url';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.5' WHERE `nom`='Version';";
}

//	Mise a  jour de la base version 1.5.5 -> 1.5.6
if(strcmp("1.5.6",$config['Version'])>0){
  $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES 
    ('40','24','Par service','statistiques/service.php'), ('40','26','Par statut','statistiques/statut.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL, 'Statistiques', 17, 'Statistiques', 'statistiques/service.php'),
    (NULL, 'Statistiques', 17, 'Statistiques', 'statistiques/statut.php');";
  $sql[]="CREATE TABLE `{$dbprefix}select_etages` (`id` int(11) NOT NULL AUTO_INCREMENT, `valeur` text NOT NULL DEFAULT '', 
    `rang` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  $sql[]="INSERT INTO `{$dbprefix}select_etages` (`valeur`,`rang`) VALUES ('Mezzanine',1),('RDC',2),('RDJ',3),('Magasins',4);";
  $sql[]="ALTER TABLE `{$dbprefix}postes` CHANGE `etage` `etage` TEXT;";
  $sql[]="ALTER TABLE `{$dbprefix}postes` ADD `site` INT(1) DEFAULT '1';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.6' WHERE `nom`='Version';";
}

//	Execution des requetes et affichage
foreach($sql as $elem){
  $db=new db();
  $db->query($elem);
  if(!$db->error)
    echo "$elem : <font style='color:green;'>OK</font><br/>\n";
  else
    echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
}

echo "<br/><br/><a href='index.php'>Continuer</a>\n";
include "include/footer.php";

function htmlentitiesBdd($table,$champs){
  $dbprefix=$GLOBALS['dbprefix'];
  foreach($champs as $champ){
    $db=new db();
    $db->query("ALTER TABLE `{$dbprefix}$table` CHANGE `$champ` `$champ` TEXT");
    echo "ALTER TABLE `{$dbprefix}$table` CHANGE `$champ` `$champ` TEXT";
    if(!$db->error)
      echo "$elem : <font style='color:green;'>OK</font><br/>\n";
    else
      echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
  }
  $db=new db();
  $db->select($table);
  if($db->result){
    foreach($db->result as $elem){
      $tab=array();
      foreach($champs as $champ){
	$tab[$champ]=$elem[$champ];
      }
      $db2=new db();
      $db2->update2latin1($table,$tab,array("id"=>$elem['id']));
    }
  }
}

exit;
?>