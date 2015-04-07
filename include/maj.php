<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/maj.php
Création : mai 2011
Dernière modification : 7 avril 2015
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
  //	Ajout des nouvelles statistiques dans le menu
  $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES 
    ('40','24','Par service','statistiques/service.php'), ('40','26','Par statut','statistiques/statut.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL, 'Statistiques', 17, 'Statistiques', 'statistiques/service.php'),
    (NULL, 'Statistiques', 17, 'Statistiques', 'statistiques/statut.php');";
  //	Modification des étages dans les selects
  $sql[]="CREATE TABLE `{$dbprefix}select_etages` (`id` int(11) NOT NULL AUTO_INCREMENT, `valeur` text NOT NULL DEFAULT '', 
    `rang` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  $sql[]="INSERT INTO `{$dbprefix}select_etages` (`valeur`,`rang`) VALUES ('Mezzanine',1),('RDC',2),('RDJ',3),('Magasins',4);";
  $sql[]="ALTER TABLE `{$dbprefix}postes` CHANGE `etage` `etage` TEXT;";
  $sql[]="ALTER TABLE `{$dbprefix}postes` ADD `site` INT(1) DEFAULT '1';";
  //	Numéro de version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.5' WHERE `nom`='Version';";
}

if(strcmp("1.5.6",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.6' WHERE `nom`='Version';";
}

if(strcmp("1.5.7",$config['Version'])>0){
  $sql[]="ALTER TABLE `{$dbprefix}personnel` CHANGE `supprime` `supprime` ENUM('0','1','2') NOT NULL DEFAULT '0';";
  $sql[]="UPDATE `{$dbprefix}personnel` SET `supprime`='2' WHERE `supprime`='1';";
  $sql[]="UPDATE `{$dbprefix}personnel` SET `supprime`='1' WHERE `actif` LIKE 'Supprim%';";
  $sql[]="UPDATE `{$dbprefix}menu` SET `titre`='Jours de fermeture' WHERE `url`='joursFeries/index.php';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.7' WHERE `nom`='Version';";
}

if(strcmp("1.5.8",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.8' WHERE `nom`='Version';";
}

if(strcmp("1.5.9",$config['Version'])>0){
  $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `categorie` VARCHAR(30) NOT NULL DEFAULT '';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.5.9' WHERE `nom`='Version';";
}

if(strcmp("1.6",$config['Version'])>0){
  $sql[]="ALTER TABLE `{$dbprefix}config` CHANGE `categorie` `categorie` VARCHAR (100);";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='0',`categorie`='Divers' WHERE `categorie` LIKE '';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='3' WHERE `categorie`='Affichage';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='7' WHERE `categorie`='Authentification';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='5', `categorie`='Menu d&eacute;roulant du planning' WHERE `nom` IN ('toutlemonde','agentsIndispo','hres4semaines');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('EDTSamedi', 'boolean', '0', 'Emplois du temps diff&eacute;rents les semaines o&ugrave; les samedis sont travaill&eacute;s', 'Divers','0');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('ClasseParService', 'boolean', '1', 'Classer les agents par service dans le menu d&eacute;roulant du planning','Menu d&eacute;roulant du planning','5');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Alerte2SP', 'boolean', '0', 'Alerter si l&apos;agent fera 2 plages de service public de suite','Menu d&eacute;roulant du planning','5');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('CatAFinDeService', 'boolean', '0', 'Alerter si aucun agent de cat&eacute;gorie A n&apos;est plac&eacute; en fin de service','Divers','0');";
  $sql[]="CREATE TABLE `{$dbprefix}EDTSamedi` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `perso_id` INT(11) NOT NULL , `semaine` DATE);";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6' WHERE `nom`='Version';";
}

if(strcmp("1.6.1",$config['Version'])>0){
  include "majconfig.php";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_tab` ADD `site` INT(2) NOT NULL DEFAULT 1;";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_tab_grp` ADD `site` INT(2) NOT NULL DEFAULT 1;";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.1' WHERE `nom`='Version';";
}

if(strcmp("1.6.2",$config['Version'])>0){
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) 
    VALUES ('Recup-Agent','enum',',Texte,Menu d&eacute;roulant','Texte','Type de champ pour la r&eacute;cup&eacute;ration des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu déroulant</b>','Congés','40');";
  $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `mailResponsable`  TEXT, ADD `matricule` VARCHAR(100);";
  $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques', 17, 'Statistiques', 'statistiques/samedis.php');";
  $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES ('40','60','Samedis','statistiques/samedis.php');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-validation','boolean','0','Les absences doivent &ecirc;tre valid&eacute;es par un administrateur avant d&apos;&ecirc;tre prises en compte','Absences','2');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-notifications','enum','A tous','Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A tous,A l&apos;agent concern&eacute; seulement','A qui les notifications d&apos;absences doivent-elles &ecirc;tre envoy&eacute;es','Absences','2');";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Absences', `ordre`='2' WHERE `nom`='absencesApresValidation';";
  $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `valide` INT(11) NOT NULL DEFAULT 0, ADD `validation` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';";
  $sql[]="UPDATE `{$dbprefix}absences` SET `valide`='1', `validation`=`demande`;";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-adminSeulement','boolean','0','Autoriser la saisie des absences aux administrateurs seulement.','Absences','2');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.2' WHERE `nom`='Version';";
}

if(strcmp("1.6.3",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}pl_poste_cellules` SET `tableau`='1' WHERE `tableau`='general';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_cellules` SET `tableau`='2' WHERE `tableau`='reserve';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_cellules` SET `tableau`='3' WHERE `tableau`='rangement';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_horaires` SET `tableau`='1' WHERE `tableau`='general';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_horaires` SET `tableau`='2' WHERE `tableau`='reserve';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_horaires` SET `tableau`='3' WHERE `tableau`='rangement';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_lignes` SET `tableau`='1' WHERE `tableau`='general';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_lignes` SET `tableau`='2' WHERE `tableau`='reserve';";
  $sql[]="UPDATE `{$dbprefix}pl_poste_lignes` SET `tableau`='3' WHERE `tableau`='rangement';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.3' WHERE `nom`='Version';";
}

if(strcmp("1.6.4",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.4' WHERE `nom`='Version';";
}

if(strcmp("1.6.5",$config['Version'])>0){
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('display_errors','boolean','0','Afficher les erreurs PHP','D&eacute;bogage','4');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('error_reporting','enum','4','0,1,2,3,4,5','Type d&apos;erreurs PHP &agrave; afficher','D&eacute;bogage','4');";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`=' Divers' WHERE `categorie`='Divers';";
  $sql[]="UPDATE `{$dbprefix}config` SET `type`='date', `commentaires`='Date de d&eacute;but permettant la rotation des plannings hebdomadaires (pour l&apos;utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)' WHERE `nom`='dateDebutPlHebdo';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='A qui les notifications de nouvelles absences doivent-elles &ecirc;tre envoy&eacute;es', 
    `valeurs`='Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A la cellule planning,A tous,A l&apos;agent concern&eacute;' 
    WHERE `nom`='Absences-notifications';";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-notifications2','enum','Au responsable direct','Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A la cellule planning,A tous,A l&apos;agent concern&eacute;','A qui les notifications de validation niveau 1 doivent-elles &ecirc;tre envoy&eacute;es','Absences','2');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-notifications3','enum','A l&apos;agent concern&eacute;','Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A la cellule planning,A tous,A l&apos;agent concern&eacute;','A qui les notifications de validation niveau 2 doivent-elles &ecirc;tre envoy&eacute;es','Absences','2');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Mail-Planning','textarea','Adresses e-mails de la cellule planning, s&eacute;par&eacute;es par des ;','Messagerie','10');";
  $sql[]="UPDATE `{$dbprefix}config` SET `nom`='Absences-apresValidation' WHERE `nom`='absencesApresValidation';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='10' WHERE `nom`='Absences-apresValidation';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='20' WHERE `nom`='Absences-adminSeulement';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='30' WHERE `nom`='Absences-validation';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='40' WHERE `nom`='Absences-notifications';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='50' WHERE `nom`='Absences-notifications2';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='60' WHERE `nom`='Absences-notifications3';";
  $sql[]="UPDATE `{$dbprefix}config` SET `nom`='Absences-planning', `categorie`='Absences', `ordre`='25' WHERE `nom`='absences_planning';";
  $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `valideN1` INT(11) NOT NULL DEFAULT 0, ADD `validationN1` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';";
  $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`) VALUES ('Gestion des absences, validation N2', 8, 'Gestion des absences, validation N2');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.5' WHERE `nom`='Version';";
}

if(strcmp("1.6.6",$config['Version'])>0){
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Planning-sansRepas','boolean','1','Afficher une notification pour les Sans Repas dans le menu d&eacute;roulant et dans le planning','Menu d&eacute;roulant du planning','10'),
    ('Planning-dejaPlace','boolean','1','Afficher une notification pour les agents d&eacute;j&agrave; plac&eacute; sur un poste dans le menu d&eacute;roulant du planning','Menu d&eacute;roulant du planning','11');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.6' WHERE `nom`='Version';";
}

if(strcmp("1.6.7",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.7' WHERE `nom`='Version';";
}

if(strcmp("1.6.8",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.6.8' WHERE `nom`='Version';";
}

if(strcmp("1.7",$config['Version'])>0){
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_cellules` CHANGE `tableau` `tableau` INT(11);";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_horaires` CHANGE `tableau` `tableau` INT(11);";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_lignes` CHANGE `tableau` `tableau` INT(11);";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7' WHERE `nom`='Version';";
}

if(strcmp("1.7.1",$config['Version'])>0){
  //	Ajout des catégories
  $sql[]="CREATE TABLE `{$dbprefix}select_categories` (`id` int(11) NOT NULL AUTO_INCREMENT, `valeur` text NOT NULL DEFAULT '', 
    `rang` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  $sql[]="INSERT INTO `{$dbprefix}select_categories` (`valeur`,`rang`) VALUES ('Cat&eacute;gorie A',10),('Cat&eacute;gorie B',20),('Cat&eacute;gorie C',30),('Commun',40);";
  $sql[]="ALTER TABLE `{$dbprefix}postes` ADD `categorie` VARCHAR(20);";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.1' WHERE `nom`='Version';";
}

if(strcmp("1.7.2",$config['Version'])>0){
  //	Modification des catégories liées aux statuts
  $sql[]="ALTER TABLE `{$dbprefix}select_statuts` ADD `categorie` INT(11);";
  $sql[]="UPDATE `{$dbprefix}select_statuts` SET `categorie`='1' WHERE `valeur` IN ('Conservateur','Biblioth&eacute;caire');";
  $sql[]="UPDATE `{$dbprefix}select_statuts` SET `categorie`='2' WHERE `valeur` IN ('BAS');";
  $sql[]="UPDATE `{$dbprefix}select_statuts` SET `categorie`='3' WHERE `valeur` IN ('Magasinier','Etudiant','Moniteur');";
  $sql[]="ALTER TABLE `{$dbprefix}postes` CHANGE `categorie` `categorie` INT(11) NOT NULL DEFAULT '0';";
  $sql[]="UPDATE `{$dbprefix}select_categories` SET `valeur`='Cat&eacute;gorie A' WHERE `valeur`='Catégorie A';";
  $sql[]="UPDATE `{$dbprefix}select_categories` SET `valeur`='Cat&eacute;gorie B' WHERE `valeur`='Catégorie B';";
  $sql[]="UPDATE `{$dbprefix}select_categories` SET `valeur`='Cat&eacute;gorie C' WHERE `valeur`='Catégorie C';";

  // Notifications d'absences
  $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='Aucune notification,Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A la cellule planning,A tous,A l&apos;agent concern&eacute;' 
    WHERE `nom`='Absences-notifications';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='Aucune notification,Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A la cellule planning,A tous,A l&apos;agent concern&eacute;' 
    WHERE `nom`='Absences-notifications2';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='Aucune notification,Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A la cellule planning,A tous,A l&apos;agent concern&eacute;' 
    WHERE `nom`='Absences-notifications3';";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-notifications-titre','text','Titre personnalis&eacute; pour les notifications de nouvelles absences','Absences','70'),
    ('Absences-notifications-message','textarea','Message personnalis&eacute; pour les notifications de nouvelles absences','Absences','80');";

  $db_tmp=new db();
  $db_tmp->query("SELECT * FROM `{$dbprefix}config` WHERE `nom`='Absences-notifications';");
  $valeur1=$db_tmp->result[0]['valeur'];
  $db_tmp=new db();
  $db_tmp->query("SELECT * FROM `{$dbprefix}config` WHERE `nom`='Absences-notifications2';");
  $valeur2=$db_tmp->result[0]['valeur'];

  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='A qui les notifications de modification d&apos;absences doivent-elles &ecirc;tre envoy&eacute;es',
    `valeur`='$valeur1' WHERE `nom`='Absences-notifications2';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='A qui les notifications de validation niveau 1 doivent-elles &ecirc;tre envoy&eacute;es',
    `valeur`='$valeur2' WHERE `nom`='Absences-notifications3';";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-notifications4','enum','A l&apos;agent concern&eacute;','Aucune notification,Aux agents ayant le droit de g&eacute;rer les absences,Au responsable direct,A la cellule planning,A tous,A l&apos;agent concern&eacute;','A qui les notifications de validation niveau 2 doivent-elles &ecirc;tre envoy&eacute;es','Absences','65');";

  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.2' WHERE `nom`='Version';";
}

if(strcmp("1.7.3",$config['Version'])>0){
  $sql[]="ALTER TABLE `{$dbprefix}postes` CHANGE `categorie` `categories` TEXT NOT NULL DEFAULT '';";
  $sql[]="DELETE FROM `{$dbprefix}select_categories` WHERE `valeur`='Commun';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.3' WHERE `nom`='Version';";
}

if(strcmp("1.7.4",$config['Version'])>0){
  $sql[]="ALTER TABLE `{$dbprefix}select_abs` ADD `type` INT(1) NOT NULL DEFAULT '0';";
  $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `motif_autre` TEXT NOT NULL DEFAULT '';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.4' WHERE `nom`='Version';";
}

if(strcmp("1.7.5",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.5' WHERE `nom`='Version';";
}

if(strcmp("1.7.6",$config['Version'])>0){
  // Affichage des absences en attente de validation N2
  $sql[]="UPDATE `{$dbprefix}absences` SET `valideN1`=`valideN1`/2;";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.6' WHERE `nom`='Version';";
}

if(strcmp("1.7.7",$config['Version'])>0){
  // Plusieurs sites possibles par agent
  $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `sites` TEXT NOT NULL DEFAULT '';";
  $tmp=new db();
  $tmp->select("personnel","id,site");
  foreach($tmp->result as $elem){
    $sites=serialize(array($elem['site']));
    $sql[]="UPDATE `{$dbprefix}personnel` SET `sites`='$sites' WHERE `id`='{$elem['id']}';";
  }
  $sql[]="ALTER TABLE `{$dbprefix}personnel` DROP `site`;";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='Multisites-agentsMultisites';";
  // Statistiques : suppression des colonnes 19-20 et 20-22 selon la configuration
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES
    ('Statistiques-19-20','boolean','1','Affiche ou non la colonne 19h-20h dans les statistiques','Statistiques','10'),
    ('Statistiques-20-22','boolean','1','Affiche ou non la colonne 20h-22h dans les statistiques','Statistiques','20');";
  // Modification du champ login dans la table personnel
  $sql[]="ALTER TABLE `{$dbprefix}personnel` CHANGE `login` `login` VARCHAR(100) NOT NULL DEFAULT '';";
  // Modification de la config pour les select Absences-notification
  $sql[]="UPDATE `{$dbprefix}config` SET `type`='enum2', `valeurs`=
    'a:6:{i:0;a:2:{i:0;i:0;i:1;s:19:\"Aucune notification\";}i:1;a:2:{i:0;i:1;i:1;s:54:\"Aux agents ayant le droit de g&eacute;rer les absences\";}i:2;a:2:{i:0;i:2;i:1;s:21:\"Au responsable direct\";}i:3;a:2:{i:0;i:3;i:1;s:21:\"A la cellule planning\";}i:4;a:2:{i:0;i:4;i:1;s:6:\"A tous\";}i:5;a:2:{i:0;i:5;i:1;s:30:\"A l&apos;agent concern&eacute;\";}}' 
    WHERE `nom` IN ('Absences-notifications','Absences-notifications2','Absences-notifications3','Absences-notifications4');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='0' WHERE `valeur` = 'Aucune notification';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1' WHERE `valeur` LIKE 'Aux agents ayant le droit de%';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2' WHERE `valeur` = 'Au responsable direct';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='3' WHERE `valeur` = 'A la cellule planning';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='4' WHERE `valeur` = 'A tous';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='5' WHERE `valeur` LIKE '%agent concern%';";

  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Absences' WHERE `categorie`='Asbences';";
  
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.7' WHERE `nom`='Version';";
}

if(strcmp("1.7.8",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `nom`='Affichage-titre', `ordre` ='20' WHERE `nom`='titre';";
  $sql[]="UPDATE `{$dbprefix}config` SET `nom`='Affichage-etages', `ordre` ='30'  WHERE `nom`='affiche_etage';";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Affichage-theme','text','default','Affichage',10,'Th&egrave;me de l&apos;application.');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.8' WHERE `nom`='Version';";
}

if(strcmp("1.7.9",$config['Version'])>0){
  $sql[]="ALTER TABLE `{$dbprefix}plugins` ADD `version` VARCHAR(20);";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.7.9' WHERE `nom`='Version';";
}

if(strcmp("1.8",$config['Version'])>0){
  $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES ('40','70','Absences','statistiques/absences.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques', 17, 'Statistiques', 'statistiques/absences.php');";
  $sql[]="INSERT INTO `{$dbprefix}acces` VALUES (NULL, 'Planning par poste - Semaine', 99, '', 'planning/poste/semaine.php');";
  $sql[]="CREATE TABLE `{$dbprefix}pl_notes` (`id` int(11) NOT NULL AUTO_INCREMENT, `date` DATE, `site` INT(3) NOT NULL DEFAULT 1, `text` TEXT, PRIMARY KEY (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8' WHERE `nom`='Version';";
}

if(strcmp("1.8.1",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.1' WHERE `nom`='Version';";
}

if(strcmp("1.8.2",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.2' WHERE `nom`='Version';";
}

if(strcmp("1.8.3",$config['Version'])>0){
  //	Ajout des infos LDAP dans la table config
  $db=new db();
  $db->select("config","*","categorie='LDAP'");
  if(!$db->result){
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Host','Nom d&apos;hote ou IP du serveur LDAP','LDAP','20');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Port','Port du serveur LDAP','LDAP','20');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`commentaires`,`categorie`,`valeurs`,`ordre`) VALUES ('LDAP-Protocol','enum','Protocol du serveur LDAP','LDAP','ldap,ldaps','20');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Suffix','Suffix LDAP','LDAP','20');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Filter','Filtre','LDAP','20');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-RDN','DN de connexion au serveur LDAP','LDAP','20');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Password','password','Mot de passe de connexion','LDAP','20');";
  }
  //	Ajout des infos CAS dans la table config
  $db=new db();
  $db->select("config","*","categorie='CAS'");
  if(!$db->result){
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Hostname','Nom d&apos;h&ocirc;te du serveur CAS','CAS','30');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Port','8080','Port serveur CAS','CAS','30');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Version','2','Version du serveur CAS','CAS','30');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-CACert','Certificat de l&apos;Autorit&eacute; de Certification','CAS','30');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-URI','cas','Page de connexion CAS','CAS','30');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-URI-Logout','cas/logout','Page de d&eacute;connexion CAS','CAS','30');";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.3' WHERE `nom`='Version';";
  }
  $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='SQL,LDAP,LDAP-SQL,CAS,CAS-SQL' WHERE `nom`='Auth-Mode';";
  $sql[]="DELETE FROM `{$dbprefix}plugins` WHERE `nom`='ldap'";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.3' WHERE `nom`='Version';";
}

if(strcmp("1.8.4",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.4' WHERE `nom`='Version';";
}

if(strcmp("1.8.5",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='1,2,3,4,5,6,7,8,9,10' WHERE `nom`='Multisites-nombre';";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site3','text','','Nom du site N°3','Multisites','','15');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site4','text','','Nom du site N°4','Multisites','','15');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site5','text','','Nom du site N°5','Multisites','','15');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site6','text','','Nom du site N°6','Multisites','','15');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site7','text','','Nom du site N°7','Multisites','','15');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site8','text','','Nom du site N°8','Multisites','','15');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site9','text','','Nom du site N°9','Multisites','','15');";
  $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Multisites-site10','text','','Nom du site N°10','Multisites','','15');";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='Multisites-resp1';";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='Multisites-email1';";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='Multisites-resp2';";
  $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='Multisites-email2';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.5' WHERE `nom`='Version';";
}

if(strcmp("1.8.6",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Planning' WHERE `categorie`='Menu d&eacute;roulant du planning';";
  $sql[]="INSERT INTO `{$dbprefix}config` (`type`,`nom`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES 
    ('enum','Planning-NbAgentsCellule','1,2,3,4,5,6,7,8,10,11,12,13,14,15,16,17,18,19,20','2','Nombre d&apos;agents maximum par cellule','Planning','2');";
  $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/poste/validation.php';";
  $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='absences/ctrl_ajax.php';";
  $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/export.php';";
  $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/postes_cfg/suppression.php';";
  $sql[]= "ALTER TABLE  `{$dbprefix}absences` ADD `pj1` INT(1) DEFAULT 0, ADD `pj2` INT(1) DEFAULT 0, ADD `so` INT(1) DEFAULT 0;";
  $sql[]="UPDATE  `{$dbprefix}acces` SET `groupe`='Gestion des absences, validation N1' WHERE `groupe_id`='1';";
  $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`) VALUES ('Gestion des absences, pi&egrave;ces justificatives', 701, 'Gestion des absences, pi&egrave;ces justificatives');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.6' WHERE `nom`='Version';";
}

if(strcmp("1.8.7",$config['Version'])>0){
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Planning-lignesVides','boolean','1','Planning',3,'Afficher ou non les lignes vides dans les plannings valid&eacute;s');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.7' WHERE `nom`='Version';";
}

if(strcmp("1.8.8",$config['Version'])>0){
  // Plusieurs emails de responsables dans la fiche des agents
  $sql[]="ALTER TABLE `{$dbprefix}personnel` CHANGE `mailResponsable` `mailsResponsables` TEXT;";

  // Commentaires config LDAP
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom d&apos;h&ocirc;te ou adresse IP du serveur LDAP' WHERE `nom`='LDAP-Host';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Protocol utilis&eacute;' WHERE `nom`='LDAP-Protocol';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Base LDAP' WHERE `nom`='LDAP-Suffix';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='DN de connexion au serveur LDAP, laissez vide si connexion anonyme' WHERE `nom`='LDAP-RDN';";

  // Cases à cocher pour la sélection des notifications dans la config.
  $sql[]="UPDATE `{$dbprefix}config` SET `type`='checkboxes', `valeurs`='a:4:{i:0;a:2:{i:0;i:0;i:1;s:54:\"Aux agents ayant le droit de g&eacute;rer les absences\";}i:1;a:2:{i:0;i:1;i:1;s:24:\"Aux responsables directs\";}i:2;a:2:{i:0;i:2;i:1;s:21:\"A la cellule planning\";}i:3;a:2:{i:0;i:3;i:1;s:30:\"A l&apos;agent concern&eacute;\";}}' where `nom` LIKE 'Absences-notifications%' AND `type`='enum2';";

  // Converti les choix actuels pour les notifications
  $db=new db();
  $db->select("config","nom,valeur","`nom` LIKE 'Absences-notifications%' AND `type`='enum2'");
  foreach($db->result as $elem){
    $valeur=$elem['valeur'];
    if($valeur==0){$valeur=addslashes(serialize(array()));}
    if($valeur==1){$valeur=addslashes(serialize(array(0)));}
    if($valeur==2){$valeur=addslashes(serialize(array(1)));}
    if($valeur==3){$valeur=addslashes(serialize(array(2)));}
    if($valeur==4){$valeur=addslashes(serialize(array(0,1,2,3)));}
    if($valeur==5){$valeur=addslashes(serialize(array(3)));}
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$valeur' where `nom` = '{$elem['nom']}';";
  }
  $sql[]="UPDATE `{$dbprefix}config` SET `nom`='Absences-notifications1' WHERE `nom`='Absences-notifications';";

  // Modification des commentaires
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Destinataires des notifications de nouvelles absences' WHERE `nom`='Absences-notifications1';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Destinataires des notifications de modification d&apos;absences' WHERE `nom`='Absences-notifications2';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Destinataires des notifications des validations niveau 1' WHERE `nom`='Absences-notifications3';";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Destinataires des notifications des validations niveau 2' WHERE `nom`='Absences-notifications4';";

  // Mise à jour de la version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.8' WHERE `nom`='Version';";
}

if(strcmp("1.8.9",$config['Version'])>0){
  // Mise à jour de la version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.8.9' WHERE `nom`='Version';";
}

if(strcmp("1.9",$config['Version'])>0){
  $sql[]="UPDATE  `{$dbprefix}acces` SET `groupe`='Gestion des absences' WHERE `groupe_id`='1';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.9' WHERE `nom`='Version';";
}

if(strcmp("1.9.1",$config['Version'])>0){
  $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/poste/verrou.php';";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.9.1' WHERE `nom`='Version';";
}

if(strcmp("1.9.2",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.9.2' WHERE `nom`='Version';";
}

if(strcmp("1.9.3",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.9.3' WHERE `nom`='Version';";
}

if(strcmp("1.9.4",$config['Version'])>0){
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1.9.4' WHERE `nom`='Version';";
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