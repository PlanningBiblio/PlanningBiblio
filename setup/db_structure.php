<?php
/*
Planning Biblio, Version 1.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : setup/db_structure.php
Création : mai 2011
Dernière modification : 20 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

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
  `nbjours` int(11) NOT NULL DEFAULT '0',
  `motif` text NOT NULL DEFAULT '',
  `commentaires` text NOT NULL,
  `etat` text NOT NULL,
  `demande` datetime NOT NULL,
  `valide` INT(11) NOT NULL DEFAULT 0,
  `validation` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valideN1` INT(11) NOT NULL DEFAULT 0,
  `validationN1` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}absences_infos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` date NOT NULL DEFAULT '0000-00-00',
  `fin` date NOT NULL DEFAULT '0000-00-00',
  `texte` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}acces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `groupe` text NOT NULL,
  `page` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) NOT NULL,
  `type` varchar(20) NOT NULL,
  `valeur` text NOT NULL,
  `commentaires` text NOT NULL,
  `categorie` VARCHAR( 100 ) NOT NULL,
  `valeurs` TEXT NOT NULL,
  `ordre` INT(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}lignes` (
  `id` int AUTO_INCREMENT,
  nom text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}infos` (
  `id` INT AUTO_INCREMENT,
  `debut` DATE,
  `fin` DATE,
  texte TEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

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
  `login` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `commentaires` text NOT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `heuresHebdo` FLOAT(5) NOT NULL,
  `heuresTravail` FLOAT(5) NOT NULL,
  `site` INT(1) NOT NULL,
  `temps` text NOT NULL,
  `informations` text NOT NULL,
  `recup` text NOT NULL,
  `supprime` ENUM('0','1','2') NOT NULL DEFAULT '0',
  `mailResponsable` TEXT NOT NULL DEFAULT '',
  `matricule` VARCHAR(100) NOT NULL DEFAULT '',
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
  `chgt_time` datetime NOT NULL,
  `debut` time NOT NULL,
  `fin` time NOT NULL,
  `supprime` ENUM('0','1') DEFAULT '0',
  `site` INT(3) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_cellules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `tableau` varchar(20) NOT NULL,
  `ligne` int(11) NOT NULL,
  `colonne` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_horaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` time NOT NULL DEFAULT '00:00:00',
  `fin` time NOT NULL DEFAULT '00:00:00',
  `tableau` varchar(15) NOT NULL,
  `numero` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `tableau` varchar(20) NOT NULL,
  `ligne` int(11) NOT NULL,
  `poste` varchar(30) NOT NULL,
  `type` enum('poste','ligne','titre') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_modeles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `perso_id` int(11) NOT NULL,
  `poste` int(11) NOT NULL,
  `commentaire` TEXT NOT NULL,
  `debut` time NOT NULL,
  `fin` time NOT NULL,
  `tableau` varchar(20) NOT NULL,
  `jour` varchar(10) NOT NULL,
  `site` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_modeles_tab` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` TEXT NOT NULL,
  `jour` INT NOT NULL,
  `tableau` INT NOT NULL,
  `site` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}pl_poste_tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tableau` int(20) NOT NULL,
  `nom` text NOT NULL,
  `site` INT(2) NOT NULL DEFAULT 1,
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
  `nom` TEXT,
  `Lundi` INT,
  `Mardi` INT,
  `Mercredi` INT,
  `Jeudi` INT,
  `Vendredi` INT,
  `Samedi` INT,
  `Dimanche` INT,
  `site` INT(2) NOT NULL DEFAULT 1,
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
  `nom` text NOT NULL DEFAULT '',
  `groupe` varchar(30) NOT NULL DEFAULT '',
  `groupe_id` int(11) NOT NULL DEFAULT '0',
  `obligatoire` varchar(15) NOT NULL,
  `etage` TEXT NOT NULL,
  `activites` text NOT NULL,
  `statistiques` ENUM('0','1') DEFAULT '1',
  `bloquant` enum('0','1') DEFAULT '1',
  `site` INT(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_abs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_etages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL,
  `couleur` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}select_statuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL DEFAULT '',
  `rang` int(11) NOT NULL DEFAULT '0',
  `couleur` varchar(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}plugins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}menu` (
  `id` INT(11) NOT NULL  AUTO_INCREMENT, 
  `niveau1` INT(11) NOT NULL, 
  `niveau2` INT(11) NOT NULL, 
  `titre` VARCHAR(100) NOT NULL, 
  `url` VARCHAR(500) NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}joursFeries` (
  `id` INT NOT NULL AUTO_INCREMENT, 
  `annee` VARCHAR(10), 
  `jour` DATE, 
  `ferie` INT(1), 
  `fermeture` INT(1), 
  `nom` TEXT,
  `commentaire` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[]="CREATE TABLE `{$dbprefix}EDTSamedi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `perso_id` INT(11) NOT NULL,
  `semaine` DATE,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

?>