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
  `debut` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `motif` text NOT NULL,
  `motif_autre` text NOT NULL,
  `commentaires` text NOT NULL,
  `etat` text NOT NULL,
  `demande` datetime NOT NULL,
  `valide` INT(11) NOT NULL DEFAULT 0,
  `validation` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valide_n1` INT(11) NOT NULL DEFAULT 0,
  `validation_n1` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pj1` INT(1) DEFAULT 0,
  `pj2` INT(1) DEFAULT 0,
  `so` INT(1) DEFAULT 0,
  `groupe` VARCHAR(14) NULL DEFAULT NULL,
  `cal_name` VARCHAR(300) NOT NULL,
  `ical_key` TEXT NOT NULL,
  `last_modified` VARCHAR(255),
  `uid` TEXT NULL DEFAULT NULL,
  `rrule` TEXT NULL DEFAULT NULL,
  `id_origin` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cal_name`(`cal_name`),
  KEY `perso_id`(`perso_id`),
  KEY `debut`(`debut`),
  KEY `fin`(`fin`),
  KEY `groupe`(`groupe`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}absences_infos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` date DATE',
  `fin` date DATE,
  `texte` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}absences_recurrentes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `uid` VARCHAR(50), 
  `perso_id` INT,
  `event` TEXT,
  `end` ENUM ('0','1') NOT NULL DEFAULT '0',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` TIMESTAMP,
  `last_check` TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid`(`uid`),
  KEY `perso_id`(`perso_id`), 
  KEY `end`(`end`),
  KEY `last_check`(`last_check`)) 
  ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}acces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `groupe` text NOT NULL,
  `page` varchar(50) NOT NULL,
  `ordre` INT(2) NOT NULL DEFAULT 0,
  `categorie` VARCHAR(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `supprime` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}appel_dispo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` int(11) NOT NULL DEFAULT '1',
  `poste` int(11) NOT NULL DEFAULT '0',
  `date` VARCHAR(10), 
  `debut` VARCHAR(8),
  `fin` VARCHAR(8),
  `destinataires` TEXT,
  `sujet` TEXT,
  `message` TEXT,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `valeur` text NOT NULL,
  `commentaires` text NOT NULL,
  `categorie` VARCHAR( 100 ) NOT NULL,
  `valeurs` TEXT NOT NULL,
  `extra` VARCHAR(100) NULL DEFAULT NULL,
  `ordre` INT(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}heures_absences` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `semaine` DATE,
  `update_time` INT(11),
  `heures` TEXT,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}heures_sp` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `semaine` DATE,
  `update_time` INT(11),
  `heures` TEXT,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}hidden_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT '0',
  `tableau` int(11) NOT NULL DEFAULT '0',
  `hidden_tables` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}lignes` (
  `id` int AUTO_INCREMENT,
  nom text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg` TEXT NULL,
  `program` VARCHAR(30) NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}infos` (
  `id` INT AUTO_INCREMENT,
  `debut` DATE,
  `fin` DATE,
  texte TEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

// ip_blocker
$sql[]="CREATE TABLE `{$dbprefix}ip_blocker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
	`ip` VARCHAR(20) NOT NULL,
	`login` VARCHAR(100) NULL,
	`status` VARCHAR(10) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` DATE,
  `site` INT(3) NOT NULL DEFAULT 1,
  `text` TEXT,
  `perso_id` INT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `date` DATE,
  `site` INT(2) NOT NULL DEFAULT '1',
  `update_time` TIMESTAMP,
  `data` TEXT,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";
  
$sql[]="ALTER TABLE `{$dbprefix}pl_notifications` ADD KEY `date` (`date`), ADD KEY `site` (`site`);";

$sql[]="CREATE TABLE `{$dbprefix}personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL DEFAULT '',
  `prenom` text NOT NULL DEFAULT '',
  `mail` text NOT NULL DEFAULT '',
  `statut` text NOT NULL DEFAULT '',
  `categorie` VARCHAR(30) NOT NULL DEFAULT '',
  `service` text NOT NULL,
  `arrivee` date NOT NULL DEFAULT '0000-00-00',
  `depart` date NOT NULL DEFAULT '0000-00-00',
  `postes` text NOT NULL,
  `actif` varchar(20) NOT NULL DEFAULT 'true',
  `droits` varchar(500) NOT NULL,
  `login` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `commentaires` text NOT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `heures_hebdo` VARCHAR(6) NOT NULL,
  `heures_travail` FLOAT(5) NOT NULL,
  `sites` TEXT NOT NULL DEFAULT '',
  `temps` text NOT NULL,
  `informations` text NOT NULL,
  `recup` text NOT NULL,
  `supprime` ENUM('0','1','2') NOT NULL DEFAULT '0',
  `mails_responsables` TEXT NOT NULL DEFAULT '',
  `matricule` VARCHAR(100) NOT NULL DEFAULT '',
  `code_ics` VARCHAR(100) NULL DEFAULT NULL,
  `url_ics` TEXT NULL DEFAULT NULL,
  `check_ics` VARCHAR(10) NULL DEFAULT '[1,1,1]',
  `check_hamac` INT(1) NOT NULL DEFAULT '1',
  `conges_credit` FLOAT(10),
  `conges_reliquat` FLOAT(10),
  `conges_anticipation` FLOAT(10),
  `comp_time` FLOAT(10),
  `conges_annuel` FLOAT(10),
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `poste` int(11) NOT NULL DEFAULT '0',
  `absent` enum('0','1','2') NOT NULL DEFAULT '0',
  `chgt_login` int(4) DEFAULT NULL,
  `chgt_time` datetime NOT NULL,
  `debut` time NOT NULL,
  `fin` time NOT NULL,
  `supprime` ENUM('0','1') DEFAULT '0',
  `site` INT(3) DEFAULT '1',
  `grise` ENUM('0','1') DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `site` (`site`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_cellules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `tableau` int(11) NOT NULL,
  `ligne` int(11) NOT NULL,
  `colonne` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_horaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` time NOT NULL DEFAULT '00:00:00',
  `fin` time NOT NULL DEFAULT '00:00:00',
  `tableau` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `tableau` int(11) NOT NULL,
  `ligne` int(11) NOT NULL,
  `poste` varchar(30) NOT NULL,
  `type` enum('poste','ligne','titre','classe') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_modeles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` INT(11) NOT NULL DEFAULT '0',
  `perso_id` int(11) NOT NULL,
  `poste` int(11) NOT NULL,
  `commentaire` TEXT NOT NULL,
  `debut` time NOT NULL,
  `fin` time NOT NULL,
  `tableau` varchar(20) NOT NULL,
  `jour` varchar(10) NOT NULL,
  `site` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_modeles_tab` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` TEXT NOT NULL,
  `model_id` INT(11) NOT NULL DEFAULT '0',
  `jour` INT NOT NULL,
  `tableau` INT NOT NULL,
  `site` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tableau` int(20) NOT NULL,
  `nom` text NOT NULL,
  `site` INT(2) NOT NULL DEFAULT 1,
  `supprime` TIMESTAMP NULL DEFAULT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab_affect` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `tableau` INT NOT NULL,
  `site` INT(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab_grp` (
  `id` INT AUTO_INCREMENT,
  `nom` TEXT,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";


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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}postes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL DEFAULT '',
  `groupe` TEXT NOT NULL DEFAULT '',
  `groupe_id` int(11) NOT NULL DEFAULT '0',
  `obligatoire` varchar(15) NOT NULL,
  `etage` TEXT NOT NULL,
  `activites` text NOT NULL,
  `statistiques` ENUM('0','1') DEFAULT '1',
  `teleworking` ENUM('0','1') NOT NULL DEFAULT '0',
  `bloquant` enum('0','1') DEFAULT '1',
  `site` INT(1) DEFAULT '1',
  `categories` TEXT NULL DEFAULT NULL,
  `supprime` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}responsables` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `perso_id` INT(11) NOT NULL DEFAULT '0', 
  `responsable` INT(11) NOT NULL DEFAULT '0', 
  `notification` INT(1) NOT NULL DEFAULT '0', 
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}select_abs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  `type` INT(1) NOT NULL DEFAULT '0',
  `notification_workflow` CHAR(1) NULL DEFAULT NULL,
  `teleworking` INT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}select_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}select_etages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}select_groupes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}select_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL,
  `couleur` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}select_statuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  `couleur` varchar(7) NOT NULL,
  `categorie` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}menu` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT, 
  `niveau1` INT(11) NOT NULL, 
  `niveau2` INT(11) NOT NULL, 
  `titre` VARCHAR(100) NOT NULL, 
  `url` VARCHAR(500) NOT NULL, 
  `condition` VARCHAR(100) NULL, 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}cron` (
  `id` INT AUTO_INCREMENT, 
  `m` VARCHAR(2), 
  `h` VARCHAR(2), 
  `dom` VARCHAR(2), 
  `mon` VARCHAR(2), 
  `dow` VARCHAR(2), 
  `command` VARCHAR(200), 
  `comments` VARCHAR(500),
  `last` DATETIME NULL DEFAULT '0000-00-00 00:00:00', 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}jours_feries` (
  `id` INT NOT NULL AUTO_INCREMENT, 
  `annee` VARCHAR(10), 
  `jour` DATE, 
  `ferie` INT(1), 
  `fermeture` INT(1), 
  `nom` TEXT,
  `commentaire` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}edt_samedi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `perso_id` INT(11) NOT NULL,
  `semaine` DATE,
  `tableau` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";


// Module planningHebdo
$sql[]="CREATE TABLE `{$dbprefix}planning_hebdo` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `perso_id` INT(11) NOT NULL, 
  `debut` DATE NOT NULL, 
  `fin` DATE NOT NULL, 
  `temps` TEXT NOT NULL, 
  `breaktime` TEXT NOT NULL,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  `modif` INT(11) NOT NULL DEFAULT '0',
  `modification` TIMESTAMP, 
  `valide_n1` INT(11) NOT NULL DEFAULT '0',
  `validation_n1` TIMESTAMP NULL DEFAULT NULL, 
  `valide` INT(11) NOT NULL DEFAULT '0',
  `validation` TIMESTAMP, 
  `actuel` INT(1) NOT NULL DEFAULT '0', 
  `remplace` INT(11) NOT NULL DEFAULT '0',
  `cle` VARCHAR( 100 ) NULL DEFAULT NULL,
  `exception` INT(11) NOT NULL DEFAULT 0,
  `nb_semaine` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cle` (`cle`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[]="CREATE TABLE `{$dbprefix}planning_hebdo_periodes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `annee` VARCHAR(9),
  `dates` TEXT
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

// Agents volants
$sql[] = "CREATE TABLE `{$dbprefix}volants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `date` DATE NULL DEFAULT NULL, 
  `perso_id` INT(11) NOT NULL DEFAULT '0', 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

// Création de la table conges
$sql[]="CREATE TABLE `{$dbprefix}conges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `perso_id` INT(11) NOT NULL,
  `debut` DATETIME NOT NULL,
  `fin` DATETIME NOT NULL,
  `halfday` tinyint NULL DEFAULT 0,
  `start_halfday` varchar(20) NULL DEFAULT '',
  `end_halfday` varchar(20) NULL DEFAULT '',
  `commentaires` TEXT,
  `refus` TEXT,
  `heures` VARCHAR(20),
  `debit` VARCHAR(20),
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
  `info_date` TIMESTAMP NULL DEFAULT NULL,
  `regul_id` INT(11) NULL DEFAULT NULL,
  `origin_id` INT(11) NULL DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

// Création de la table conges_infos
$sql[]="CREATE TABLE `{$dbprefix}conges_infos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `debut` DATE NULL,
  `fin` DATE NULL,
  `texte` TEXT NULL,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

// Création de la table récupérations
$sql[]="CREATE TABLE `{$dbprefix}recuperations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `perso_id` INT(11) NOT NULL,
  `date` DATE NULL,
  `date2` DATE NULL,
  `heures` FLOAT(5),
  `etat` VARCHAR(20),
  `commentaires` TEXT,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `saisie_par` INT NOT NULL,
  `modif` INT(11) NOT NULL DEFAULT '0',
  `modification` TIMESTAMP,
  `valide_n1` INT(11) NOT NULL DEFAULT 0,
  `validation_n1` DATETIME NULL DEFAULT NULL,
  `valide` INT(11) NOT NULL DEFAULT '0',
  `validation` TIMESTAMP,
  `refus` TEXT,
  `solde_prec` FLOAT(10),
  `solde_actuel` FLOAT(10)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

// Création de la table conges_cet
$sql[]="CREATE TABLE `{$dbprefix}conges_cet` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `perso_id` INT(11) NOT NULL,
  `jours` INT(11) NOT NULL DEFAULT '0',
  `commentaires` TEXT,
  `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `saisie_par` INT NOT NULL,
  `modif` INT(11) NOT NULL DEFAULT '0',
  `modification` TIMESTAMP,
  `valide_n1` INT(11) NOT NULL DEFAULT '0',
  `validation_n1` TIMESTAMP,
  `valide_n2` INT(11) NOT NULL DEFAULT '0',
  `validation_n2` TIMESTAMP,
  `refus` TEXT,
  `solde_prec` FLOAT(10),
  `solde_actuel` FLOAT(10),
  `annee` VARCHAR(10)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

// Création de la table absences_documents
$sql[] = "CREATE TABLE `{$dbprefix}absences_documents` (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  absence_id int(11) NOT NULL,
  filename text NOT NULL,
  date DATETIME NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[] = "CREATE TABLE `{$dbprefix}notifications_history` (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  subject text NOT NULL,
  message text NOT NULL,
  date DATETIME NOT NULL,
  status text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[] = "CREATE TABLE `{$dbprefix}notifications_history_agents` (
  notification_id int(11) NOT NULL,
  agent_id int(11) NOT NULL,
  FOREIGN KEY(notification_id) REFERENCES {$dbprefix}notifications_history(id),
  FOREIGN KEY(agent_id) REFERENCES {$dbprefix}personnel(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";
