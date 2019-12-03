<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : setup/db_structure.php
Création : mai 2011
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Requêtes SQL créant les tables lors de l'installation.
Ce fichier est appelé par le fichier setup/createdb.php. Les requêtes sont stockées dans le tableau $sql et executées par le
fichier setup/createdb.php
*/

$sql[]="SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';";

$sql[]="CREATE TABLE `{$dbprefix}absences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT '0',
  `debut` DATETIME NOT NULL,
  `fin` DATETIME NOT NULL,
  `motif` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `motif_autre` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `commentaires` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `etat` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `demande` DATETIME NOT NULL,
  `valide` INT(11) NOT NULL DEFAULT 0,
  `validation` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valide_n1` INT(11) NOT NULL DEFAULT 0,
  `validation_n1` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pj1` INT(1) DEFAULT 0,
  `pj2` INT(1) DEFAULT 0,
  `so` INT(1) DEFAULT 0,
  `groupe` VARCHAR(14) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `cal_name` VARCHAR(300) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ical_key` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `uid` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `rrule` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `id_origin` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cal_name`(`cal_name`),
  KEY `perso_id`(`perso_id`),
  KEY `debut`(`debut`),
  KEY `fin`(`fin`),
  KEY `groupe`(`groupe`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}absences_infos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` date NOT NULL DEFAULT '0000-00-00',
  `fin` date NOT NULL DEFAULT '0000-00-00',
  `texte` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}absences_recurrentes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `uid` VARCHAR(50) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `perso_id` INT,
  `event` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `end` ENUM ('0','1') NOT NULL DEFAULT '0',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_check` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid`(`uid`),
  KEY `perso_id`(`perso_id`), 
  KEY `end`(`end`),
  KEY `last_check`(`last_check`)) 
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}acces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `groupe_id` INT(11) NOT NULL,
  `groupe` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `page` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ordre` INT(2) NOT NULL DEFAULT 0,
  `categorie` VARCHAR(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `supprime` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}appel_dispo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` int(11) NOT NULL DEFAULT '1',
  `poste` int(11) NOT NULL DEFAULT '0',
  `date` VARCHAR(10) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `debut` VARCHAR(8) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `fin` VARCHAR(8) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `destinataires` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `sujet` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `message` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `valeur` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `commentaires` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `categorie` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `valeurs` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ordre` INT(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}heures_absences` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `semaine` DATE,
  `update_time` INT(11),
  `heures` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}heures_sp` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `semaine` DATE,
  `update_time` INT(11),
  `heures` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}hidden_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT '0',
  `tableau` int(11) NOT NULL DEFAULT '0',
  `hidden_tables` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}lignes` (
  `id` int AUTO_INCREMENT,
  nom TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `program` VARCHAR(30) COLLATE utf8_unicode_ci NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}infos` (
  `id` INT AUTO_INCREMENT,
  `debut` DATE,
  `fin` DATE,
  texte TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

// ip_blocker
$sql[]="CREATE TABLE `{$dbprefix}ip_blocker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `login` VARCHAR(100) COLLATE utf8_unicode_ci NULL,
  `status` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` DATE,
  `site` INT(3) NOT NULL DEFAULT 1,
  `text` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `perso_id` INT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `date` VARCHAR(10) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `site` INT(2) NOT NULL DEFAULT '1',
  `update_time` TIMESTAMP,
  `data` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  
$sql[]="ALTER TABLE `{$dbprefix}pl_notifications` ADD KEY `date` (`date`), ADD KEY `site` (`site`);";

$sql[]="CREATE TABLE `{$dbprefix}personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `prenom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mail` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `statut` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `categorie` VARCHAR(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `service` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `arrivee` date NOT NULL DEFAULT '0000-00-00',
  `depart` date NOT NULL DEFAULT '0000-00-00',
  `postes` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `actif` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'true',
  `droits` VARCHAR(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `login` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `commentaires` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `heures_hebdo` VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `heures_travail` FLOAT(5) NOT NULL,
  `sites` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `temps` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `informations` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `recup` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `supprime` ENUM('0','1','2') NOT NULL DEFAULT '0',
  `mails_responsables` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `matricule` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code_ics` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `url_ics` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `check_ics` VARCHAR(10) COLLATE utf8_unicode_ci NULL DEFAULT '[1,1,1]',
  `check_hamac` INT(1) NOT NULL DEFAULT '1',
  `conges_credit` FLOAT(10),
  `conges_reliquat` FLOAT(10),
  `conges_anticipation` FLOAT(10),
  `comp_time` FLOAT(10),
  `conges_annuel` FLOAT(10),
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `poste` int(11) NOT NULL DEFAULT '0',
  `absent` enum('0','1','2') NOT NULL DEFAULT '0',
  `chgt_login` int(4) DEFAULT NULL,
  `chgt_time` DATETIME NOT NULL,
  `debut` TIME NOT NULL,
  `fin` TIME NOT NULL,
  `supprime` ENUM('0','1') DEFAULT '0',
  `site` INT(3) DEFAULT '1',
  `grise` ENUM('0','1') DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `site` (`site`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_cellules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` INT(11) NOT NULL,
  `tableau` INT(11) NOT NULL,
  `ligne` INT(11) NOT NULL,
  `colonne` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_horaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` time NOT NULL DEFAULT '00:00:00',
  `fin` time NOT NULL DEFAULT '00:00:00',
  `tableau` INT(11) NOT NULL,
  `numero` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` INT(11) NOT NULL,
  `tableau` INT(11) NOT NULL,
  `ligne` INT(11) NOT NULL,
  `poste` VARCHAR(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('poste','ligne','titre','classe') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_modeles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `perso_id` INT(11) NOT NULL,
  `poste` INT(11) NOT NULL,
  `commentaire` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `debut` TIME NOT NULL,
  `fin` TIME NOT NULL,
  `tableau` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `jour` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `site` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_modeles_tab` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `jour` INT NOT NULL,
  `tableau` INT NOT NULL,
  `site` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tableau` INT(20) NOT NULL,
  `nom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `site` INT(2) NOT NULL DEFAULT 1,
  `supprime` TIMESTAMP NULL DEFAULT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab_affect` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `tableau` INT NOT NULL,
  `site` INT(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab_grp` (
  `id` INT AUTO_INCREMENT,
  `nom` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `lundi` INT,
  `mardi` INT,
  `mercredi` INT,
  `jeudi` INT,
  `vendredi` INT,
  `samedi` INT,
  `dimanche` INT,
  `site` INT(2) NOT NULL DEFAULT 1,
  `supprime` TIMESTAMP NULL DEFAULT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";


$sql[]="CREATE TABLE `{$dbprefix}pl_poste_verrou` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `verrou` int(1) NOT NULL DEFAULT '0',
  `validation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `perso` int(11) NOT NULL DEFAULT '0',
  `verrou2` int(1) NOT NULL DEFAULT '0',
  `validation2` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `perso2` int(11) NOT NULL DEFAULT '0',
  `vivier` int(1) NOT NULL DEFAULT '0',
  `site` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}postes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `nom` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `groupe` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `groupe_id` int(11) NOT NULL DEFAULT '0',
  `obligatoire` VARCHAR(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `etage` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `activites` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `statistiques` ENUM('0','1') DEFAULT '1',
  `bloquant` enum('0','1') DEFAULT '1',
  `site` INT(1) DEFAULT '1',
  `categories` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `supprime` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}responsables` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `perso_id` INT(11) NOT NULL DEFAULT '0', 
  `responsable` INT(11) NOT NULL DEFAULT '0', 
  `notification` INT(1) NOT NULL DEFAULT '0', 
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_abs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  `type` INT(1) NOT NULL DEFAULT '0',
  `notification_workflow` CHAR(1) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_etages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_groupes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rang` INT(11) NOT NULL,
  `couleur` VARCHAR(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_statuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  `couleur` VARCHAR(7) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `categorie` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}menu` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT, 
  `niveau1` INT(11) NOT NULL, 
  `niveau2` INT(11) NOT NULL, 
  `titre` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `url` VARCHAR(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `condition` VARCHAR(100) COLLATE utf8_unicode_ci NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}cron` (
  `id` INT AUTO_INCREMENT, 
  `m` VARCHAR(2) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `h` VARCHAR(2) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `dom` VARCHAR(2) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `mon` VARCHAR(2) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `dow` VARCHAR(2) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `command` VARCHAR(200) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `comments` VARCHAR(500) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `last` DATETIME NULL DEFAULT '0000-00-00 00:00:00', 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}jours_feries` (
  `id` INT NOT NULL AUTO_INCREMENT, 
  `annee` VARCHAR(10) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `jour` DATE, 
  `ferie` INT(1), 
  `fermeture` INT(1), 
  `nom` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `commentaire` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}edt_samedi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `perso_id` INT(11) NOT NULL,
  `semaine` DATE,
  `tableau` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";


// Module planningHebdo
$sql[]="CREATE TABLE `{$dbprefix}planning_hebdo` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `perso_id` INT(11) NOT NULL, 
  `debut` DATE NOT NULL, 
  `fin` DATE NOT NULL, 
  `temps` TEXT COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `breaktime` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  `modif` INT(11) NOT NULL DEFAULT '0',
  `modification` TIMESTAMP, 
  `valide_n1` INT(11) NOT NULL DEFAULT '0',
  `validation_n1` TIMESTAMP NULL DEFAULT NULL, 
  `valide` INT(11) NOT NULL DEFAULT '0',
  `validation` TIMESTAMP, 
  `actuel` INT(1) NOT NULL DEFAULT '0', 
  `remplace` INT(11) NOT NULL DEFAULT '0',
  `cle` VARCHAR( 100 ) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cle` (`cle`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

// Agents volants
$sql[] = "CREATE TABLE `{$dbprefix}volants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `date` DATE NULL DEFAULT NULL, 
  `perso_id` INT(11) NOT NULL DEFAULT '0', 
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

// Création de la table conges
$sql[]="CREATE TABLE `{$dbprefix}conges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `perso_id` INT(11) NOT NULL,
  `debut` DATETIME NOT NULL,
  `fin` DATETIME NOT NULL,
  `halfday` tinyint NULL DEFAULT 0,
  `start_halfday` varchar(20) COLLATE utf8_unicode_ci NULL DEFAULT '',
  `end_halfday` varchar(20) COLLATE utf8_unicode_ci NULL DEFAULT '',
  `commentaires` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `refus` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `heures` VARCHAR(20) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `debit` VARCHAR(20) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `saisie_par` INT NOT NULL,
  `modif` INT(11) NOT NULL DEFAULT '0',
  `modification` TIMESTAMP,
  `valide_n1` INT(11) NOT NULL DEFAULT '0',
  `validation_n1` TIMESTAMP,
  `valide` INT(11) NOT NULL DEFAULT '0',
  `validation` TIMESTAMP,
  `solde_prec` FLOAT(10),
  `solde_actuel` FLOAT(10),
  `recup_prec` FLOAT(10),
  `recup_actuel` FLOAT(10),
  `reliquat_prec` FLOAT(10),
  `reliquat_actuel` FLOAT(10),
  `anticipation_prec` FLOAT(10),
  `anticipation_actuel` FLOAT(10),
  `supprime` INT(11) NOT NULL DEFAULT 0,
  `suppr_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `information` INT(11) NOT NULL DEFAULT 0,
  `info_date` TIMESTAMP NULL DEFAULT NULL);";

// Création de la table conges_infos
$sql[]="CREATE TABLE `{$dbprefix}conges_infos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `debut` DATE NULL,
  `fin` DATE NULL,
  `texte` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";

// Création de la table récupérations
$sql[]="CREATE TABLE `{$dbprefix}recuperations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `perso_id` INT(11) NOT NULL,
  `date` DATE NULL,
  `date2` DATE NULL,
  `heures` FLOAT(5),
  `etat` VARCHAR(20) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `commentaires` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `saisie_par` INT NOT NULL,
  `modif` INT(11) NOT NULL DEFAULT '0',
  `modification` TIMESTAMP,
  `valide_n1` INT(11) NOT NULL DEFAULT 0,
  `validation_n1` DATETIME NULL DEFAULT NULL,
  `valide` INT(11) NOT NULL DEFAULT '0',
  `validation` TIMESTAMP,
  `refus` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `solde_prec` FLOAT(10),
  `solde_actuel` FLOAT(10));";

// Création de la table conges_cet
$sql[]="CREATE TABLE `{$dbprefix}conges_cet` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `perso_id` INT(11) NOT NULL,
  `jours` INT(11) NOT NULL DEFAULT '0',
  `commentaires` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `saisie_par` INT NOT NULL,
  `modif` INT(11) NOT NULL DEFAULT '0',
  `modification` TIMESTAMP,
  `valide_n1` INT(11) NOT NULL DEFAULT '0',
  `validation_n1` TIMESTAMP,
  `valide_n2` INT(11) NOT NULL DEFAULT '0',
  `validation_n2` TIMESTAMP,
  `refus` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `solde_prec` FLOAT(10),
  `solde_actuel` FLOAT(10),
  `annee` VARCHAR(10) COLLATE utf8_unicode_ci NULL DEFAULT NULL);";

// Création de la table absences_documents
$sql[] = "CREATE TABLE `{$dbprefix}absences_documents` (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  absence_id int(11) NOT NULL,
  `filename` TEXT COLLATE utf8_unicode_ci NOT NULL,
  date DATETIME NOT NULL);";
