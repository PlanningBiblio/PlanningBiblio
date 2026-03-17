/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: planno-mariadb    Database: planno
-- ------------------------------------------------------
-- Server version	12.0.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `absence_blocks`
--

DROP TABLE IF EXISTS `absence_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absence_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` date NOT NULL DEFAULT '0000-00-00',
  `end` date NOT NULL DEFAULT '0000-00-00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absence_blocks`
--

LOCK TABLES `absence_blocks` WRITE;
/*!40000 ALTER TABLE `absence_blocks` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `absence_blocks` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `absences`
--

DROP TABLE IF EXISTS `absences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT 0,
  `debut` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `motif` text NOT NULL,
  `motif_autre` text NOT NULL,
  `commentaires` text NOT NULL,
  `etat` text NOT NULL,
  `demande` datetime NOT NULL,
  `valide` int(11) NOT NULL DEFAULT 0,
  `validation` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valide_n1` int(11) NOT NULL DEFAULT 0,
  `validation_n1` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pj1` int(1) DEFAULT 0,
  `pj2` int(1) DEFAULT 0,
  `so` int(1) DEFAULT 0,
  `groupe` varchar(14) DEFAULT NULL,
  `cal_name` varchar(300) NOT NULL,
  `ical_key` text NOT NULL,
  `last_modified` varchar(255) DEFAULT NULL,
  `uid` text DEFAULT NULL,
  `rrule` text DEFAULT NULL,
  `id_origin` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cal_name` (`cal_name`(250)),
  KEY `perso_id` (`perso_id`),
  KEY `debut` (`debut`),
  KEY `fin` (`fin`),
  KEY `groupe` (`groupe`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absences`
--

LOCK TABLES `absences` WRITE;
/*!40000 ALTER TABLE `absences` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `absences` VALUES
(1,8,'2023-01-26 00:00:00','2023-01-26 23:59:59','Congés payés','','','','2023-01-24 13:53:44',1,'2023-01-24 12:53:44',0,'0000-00-00 00:00:00',0,0,0,'','','',NULL,NULL,NULL,0),
(2,7,'2023-02-20 00:00:00','2023-03-05 23:59:59','Congés payés','','','','2023-01-24 15:45:25',1,'2023-01-24 14:45:25',0,'0000-00-00 00:00:00',0,0,0,'','','',NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `absences` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `absences_documents`
--

DROP TABLE IF EXISTS `absences_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absences_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `absence_id` int(11) NOT NULL,
  `filename` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absences_documents`
--

LOCK TABLES `absences_documents` WRITE;
/*!40000 ALTER TABLE `absences_documents` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `absences_documents` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `absences_infos`
--

DROP TABLE IF EXISTS `absences_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absences_infos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` date DEFAULT NULL,
  `fin` date DEFAULT NULL,
  `texte` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absences_infos`
--

LOCK TABLES `absences_infos` WRITE;
/*!40000 ALTER TABLE `absences_infos` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `absences_infos` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `absences_recurrentes`
--

DROP TABLE IF EXISTS `absences_recurrentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absences_recurrentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) DEFAULT NULL,
  `perso_id` int(11) DEFAULT NULL,
  `event` text DEFAULT NULL,
  `end` tinyint(1) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_check` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `perso_id` (`perso_id`),
  KEY `end` (`end`),
  KEY `last_check` (`last_check`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absences_recurrentes`
--

LOCK TABLES `absences_recurrentes` WRITE;
/*!40000 ALTER TABLE `absences_recurrentes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `absences_recurrentes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `acces`
--

DROP TABLE IF EXISTS `acces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `acces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `groupe` text NOT NULL,
  `page` varchar(50) NOT NULL,
  `ordre` int(2) NOT NULL DEFAULT 0,
  `categorie` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acces`
--

LOCK TABLES `acces` WRITE;
/*!40000 ALTER TABLE `acces` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `acces` VALUES
(96,'Planning Poste',301,'Création / modification des plannings, utilisation et gestion des modèles','',110,'Planning'),
(9,'Personnel - Password',100,'','personnel/password.php',0,''),
(15,'Absences - Infos',201,'Gestion des absences, validation niveau 1','',30,'Absences'),
(16,'Personnel - Index',4,'Voir les fiches des agents','',60,'Agents'),
(18,'Postes et activités',5,'Gestion des postes','',160,'Postes'),
(24,'Statistiques',17,'Accès aux statistiques','',170,'Statistiques'),
(32,'Liste des agents présents et absents',1301,'Accès aux statistiques Présents / Absents','',171,'Statistiques'),
(33,'Configuration avancée',20,'Configuration avancée','',0,''),
(35,'Personnel - Valid',21,'Gestion des agents','',70,'Agents'),
(36,'Gestion du personnel',21,'Gestion des agents','',70,'Agents'),
(38,'Configuration des horaires des tableaux',22,'Configuration des tableaux','planning/postes_cfg/horaires.php',140,'Planning'),
(39,'Configuration des horaires des tableaux',22,'Configuration des tableaux','',140,'Planning'),
(40,'Configuration des lignes des tableaux',22,'Configuration des tableaux','planning/postes_cfg/lignes.php',140,'Planning'),
(43,'Activités - Validation',5,'Gestion des postes','activites/valid.php',160,'Postes'),
(48,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(49,'Informations',23,'Informations','',0,''),
(53,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(54,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(55,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(56,'Modification des plannings - menudiv',1001,'Modification des plannings','planning/poste/menudiv.php',120,'Planning'),
(57,'Modification des plannings - majdb',1001,'Modification des plannings','planning/poste/majdb.php',120,'Planning'),
(59,'Jours fériés',25,'Gestion des jours fériés','',0,''),
(61,'Voir les agendas de tous',3,'Voir les agendas de tous','',55,'Agendas'),
(62,'Modifier ses propres absences',6,'Modifier ses propres absences','',20,'Absences'),
(64,'Gestion des absences, validation niveau 2',501,'Gestion des absences, validation niveau 2','',40,'Absences'),
(66,'Gestion des absences, pièces justificatives',701,'Gestion des absences, pièces justificatives','',50,'Absences'),
(67,'Planning Hebdo - Admin N1',1101,'Gestion des heures de présence, validation niveau 1','',80,'Heures de présence'),
(73,'Planning Hebdo - Admin N2',1201,'Gestion des heures de présence, validation niveau 2','',90,'Heures de présence'),
(74,'Modification des commentaires des plannings',801,'Modification des commentaires des plannings','',130,'Planning'),
(75,'Griser les cellules des plannings',901,'Griser les cellules des plannings','',125,'Planning'),
(78,'Congés - Index',100,'','conges/index.php',0,''),
(82,'Gestion des congés, validation niveau 2',601,'Gestion des congés, validation niveau 2','',76,'Congés'),
(86,'Gestion des congés, validation niveau 1',401,'Gestion des congés, validation niveau 1','',75,'Congés'),
(93,'Enregistrement d\'absences pour plusieurs agents',9,'Enregistrement d\'absences pour plusieurs agents','',25,'Absences');
/*!40000 ALTER TABLE `acces` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `activites`
--

DROP TABLE IF EXISTS `activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `supprime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activites`
--

LOCK TABLES `activites` WRITE;
/*!40000 ALTER TABLE `activites` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `activites` VALUES
(1,'Assistance audiovisuel',NULL),
(2,'Assistance autoformation',NULL),
(3,'Communication',NULL),
(4,'Communication réserve',NULL),
(5,'Inscription',NULL),
(6,'Prêt/retour de document',NULL),
(7,'Prêt de matériel',NULL),
(8,'Rangement',NULL),
(9,'Renseignement',NULL),
(10,'Renseignement bibliographique',NULL),
(11,'Renseignement réserve',NULL),
(12,'Renseignement spécialisé',NULL);
/*!40000 ALTER TABLE `activites` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `appel_dispo`
--

DROP TABLE IF EXISTS `appel_dispo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appel_dispo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` int(11) NOT NULL DEFAULT 1,
  `poste` int(11) NOT NULL DEFAULT 0,
  `date` varchar(10) DEFAULT NULL,
  `debut` varchar(8) DEFAULT NULL,
  `fin` varchar(8) DEFAULT NULL,
  `destinataires` text DEFAULT NULL,
  `sujet` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appel_dispo`
--

LOCK TABLES `appel_dispo` WRITE;
/*!40000 ALTER TABLE `appel_dispo` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `appel_dispo` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `valeur` text NOT NULL,
  `commentaires` text NOT NULL,
  `categorie` varchar(100) NOT NULL,
  `valeurs` text NOT NULL,
  `technical` tinyint(1) NOT NULL DEFAULT 0,
  `extra` varchar(100) DEFAULT NULL,
  `ordre` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=224 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `config` VALUES
(1,'Version','info','25.11.07','Version de l\'application',' Divers','',0,NULL,0),
(2,'URL','info','http://focal4','URL de l\'application',' Divers','',0,NULL,10),
(3,'Mail-IsEnabled','boolean','0','Active ou désactive l\'envoi des e-mails.','Messagerie','',1,NULL,10),
(4,'toutlemonde','boolean','0','Affiche ou non l\'utilisateur \"tout le monde\" dans le menu.','Planning','',0,NULL,5),
(5,'Mail-IsMail-IsSMTP','enum','IsSMTP','Utiliser un relais SMTP (IsSMTP) ou le programme \"mail\" du serveur (IsMail).','Messagerie','IsSMTP,IsMail',1,'onchange=\'mail_config();\'',20),
(185,'Conges-Heures','enum2','0','Permettre la saisie de congés sur quelques heures ou forcer la saisie de congés sur des journées complètes. Paramètre actif avec les options Conges-Mode=Heures et Conges-Recuperations=Dissocier','Congés','[[0,\"Forcer la saisie de congés sur journées entières\"],[1,\"Permettre la saisie de congés sur quelques heures\"]]',0,NULL,3),
(7,'Mail-Hostname','','','Nom d\'hôte du serveur pour l\'envoi des e-mails.','Messagerie','',1,NULL,30),
(8,'Mail-Host','','','Nom FQDN ou IP du serveur SMTP.','Messagerie','',1,NULL,40),
(9,'Mail-Port','','25','Port du serveur SMTP','Messagerie','',1,NULL,50),
(10,'Mail-SMTPSecure','enum','','Cryptage utilisé par le serveur STMP.','Messagerie',',ssl,tls',1,NULL,60),
(11,'Mail-SMTPAuth','boolean','0','Le serveur SMTP requiert-il une authentification ?','Messagerie','',1,NULL,80),
(12,'Mail-Username','','','Nom d\'utilisateur pour le serveur SMTP.','Messagerie','',1,NULL,90),
(13,'Mail-Password','password','::13914dcd77056a29186346cdb9027335','Mot de passe pour le serveur SMTP.','Messagerie','',1,NULL,100),
(14,'Mail-From','','notifications-planningbiblio@biblibre.com','Adresse e-mail de l\'expediteur.','Messagerie','',1,NULL,110),
(15,'Mail-FromName','','Planning','Nom de l\'expediteur.','Messagerie','',1,NULL,120),
(16,'Mail-Signature','textarea','Ce message a été envoyé par Planno.\r\nMerci de ne pas y répondre.','Signature des e-mails.','Messagerie','',1,NULL,130),
(17,'Dimanche','boolean','1','Utiliser le planning le dimanche',' Divers','',0,NULL,20),
(18,'nb_semaine','enum','1','Nombre de semaines pour la rotation des heures de présence. Les valeurs supérieures à 3 ne peuvent être utilisées que si le paramètre PlanningHebdo est coché','Heures de présence','1,2,3,4,5,6,7,8,9,10',0,NULL,0),
(19,'dateDebutPlHebdo','date','','Date de début permettant la rotation des heures de présence (pour l\'utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)','Heures de présence','',0,NULL,0),
(20,'ctrlHresAgents','boolean','1','Contrôle des heures des agents le samedi et le dimanche','Planning','',0,NULL,1),
(21,'agentsIndispo','boolean','1','Afficher les agents indisponibles','Planning','',0,NULL,5),
(22,'Granularite','enum2','1','Granularit&eacute; des champs horaires.',' Divers','[[1, \"Libre\"],[60,\"Heure\"],[30,\"Demi-heure\"],[15,\"Quart d\'heure\"],[5,\"5 minutes\"]]',0,NULL,30),
(23,'Absences-planning','enum2','1','Choix des listes de présence et d\'absences à afficher sous les plannings','Absences','[[0,\"\"],[1,\"simple\"],[2,\"détaillé\"],[3,\"absents et présents\"],[4,\"absents et présents filtrés par site\"]]',0,NULL,25),
(24,'Auth-Mode','enum','SQL','Méthode d\'authentification','Authentification','SQL,LDAP,LDAP-SQL,CAS,CAS-SQL,OpenIDConnect',1,NULL,5),
(25,'Absences-apresValidation','boolean','1','Autoriser l\'enregistrement d\'absences après validation des plannings','Absences','',0,NULL,10),
(26,'Absences-planningVide','boolean','1','Autoriser l\'enregistrement d\'absences sur des plannings en cours d\'élaboration','Absences','',0,NULL,8),
(27,'Multisites-nombre','enum','4','Nombre de sites','Multisites','1,2,3,4,5,6,7,8,9,10',0,NULL,10),
(28,'Multisites-site1','text','Site 1','Nom du site N°1','Multisites','',0,NULL,20),
(29,'Multisites-site1-mail','text','','Adresses e-mails de la cellule planning du site N°1, séparées par des ;','Multisites','',0,NULL,25),
(30,'Multisites-site2','text','Site 2','Nom du site N°2','Multisites','',0,NULL,30),
(31,'Multisites-site2-mail','text','','Adresses e-mails de la cellule planning du site N°2, séparées par des ;','Multisites','',0,NULL,35),
(32,'Multisites-site3','text','Site 3','Nom du site N°3','Multisites','',0,NULL,40),
(33,'Multisites-site3-mail','text','','Adresses e-mails de la cellule planning du site N°3, séparées par des ;','Multisites','',0,NULL,45),
(34,'Multisites-site4','text','Site 4','Nom du site N°4','Multisites','',0,NULL,50),
(35,'Multisites-site4-mail','text','','Adresses e-mails de la cellule planning du site N°4, séparées par des ;','Multisites','',0,NULL,55),
(36,'Multisites-site5','text','','Nom du site N°5','Multisites','',0,NULL,60),
(37,'Multisites-site5-mail','text','','Adresses e-mails de la cellule planning du site N°5, séparées par des ;','Multisites','',0,NULL,65),
(38,'Multisites-site6','text','','Nom du site N°6','Multisites','',0,NULL,70),
(39,'Multisites-site6-mail','text','','Adresses e-mails de la cellule planning du site N°6, séparées par des ;','Multisites','',0,NULL,75),
(40,'Multisites-site7','text','','Nom du site N°7','Multisites','',0,NULL,80),
(41,'Multisites-site7-mail','text','','Adresses e-mails de la cellule planning du site N°7, séparées par des ;','Multisites','',0,NULL,85),
(42,'Multisites-site8','text','','Nom du site N°8','Multisites','',0,NULL,90),
(43,'Multisites-site8-mail','text','','Adresses e-mails de la cellule planning du site N°8, séparées par des ;','Multisites','',0,NULL,95),
(44,'Multisites-site9','text','','Nom du site N°9','Multisites','',0,NULL,100),
(45,'Multisites-site9-mail','text','','Adresses e-mails de la cellule planning du site N°9, séparées par des ;','Multisites','',0,NULL,105),
(46,'Multisites-site10','text','','Nom du site N°10','Multisites','',0,NULL,110),
(47,'Multisites-site10-mail','text','','Adresses e-mails de la cellule planning du site N°10, séparées par des ;','Multisites','',0,NULL,115),
(48,'hres4semaines','boolean','0','Afficher le total d\'heures des 4 dernières semaine dans le menu','Planning','',0,NULL,5),
(49,'Auth-Anonyme','boolean','0','Autoriser les logins anonymes','Authentification','',1,NULL,7),
(50,'EDTSamedi','enum2','0','Horaires différents les semaines avec samedi travaillé et semaines à ouverture restreinte. Ce paramètre est ignoré si PlanningHebdo est activé.','Heures de présence','[[0, \"Désactivé\"], [1, \"Horaires différents les semaines avec samedi travaillé\"], [2, \"Horaires différents les semaines avec samedi travaillé et les semaines à ouverture restreinte\"]]',0,NULL,0),
(164,'Absences-journeeEntiere','boolean','1','Le paramètre \"Journée(s) entière(s)\" est coché par défaut lors de la saisie d\'une absence.','Absences','',0,NULL,38),
(51,'ClasseParService','boolean','0','Classer les agents par service dans le menu d&eacute;roulant du planning','Planning','',0,NULL,5),
(52,'Alerte2SP','boolean','1','Alerter si l&apos;agent fera 2 plages de service public de suite','Planning','',0,NULL,5),
(53,'CatAFinDeService','boolean','0','Alerter si aucun agent de catégorie A n\'est placé en fin de service','Planning','',0,NULL,2),
(54,'Conges-Recuperations','enum2','1','Traiter les r&eacute;cup&eacute;rations comme les cong&eacute;s (Assembler), ou les traiter s&eacute;par&eacute;ment (Dissocier)','Congés','[[0,\"Assembler\"],[1,\"Dissocier\"]]',0,NULL,3),
(55,'Recup-Agent','enum2','0','Type de champ pour la r&eacute;cup&eacute;ration des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu d&eacute;roulant</b>','Congés','[[0,\"\"],[1,\"Texte\"],[2,\"Menu déroulant\"]]',0,NULL,40),
(56,'Recup-SamediSeulement','boolean','0','Autoriser les demandes de récupération des samedis seulement','Congés','',0,NULL,20),
(57,'Recup-Uneparjour','boolean','1','Autoriser une seule demande de r&eacute;cup&eacute;ration par jour','Congés','',0,NULL,19),
(58,'Recup-DeuxSamedis','boolean','0','Autoriser les demandes de récupération pour 2 samedis','Congés','',0,NULL,30),
(59,'Recup-DelaiDefaut','text','7','Delai pour les demandes de récupération par d&eacute;faut (en jours)','Congés','',0,NULL,40),
(60,'Recup-DelaiTitulaire1','enum2','0','Delai pour les demandes de récupération des titulaires pour 1 samedi (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,50),
(61,'Recup-DelaiTitulaire2','enum2','0','Delai pour les demandes de récupération des titulaires pour 2 samedis (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,60),
(62,'Recup-DelaiContractuel1','enum2','0','Delai pour les demandes de récupération des contractuels pour 1 samedi (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,70),
(63,'Recup-DelaiContractuel2','enum2','0','Delai pour les demandes de récupération des contractuels pour 2 samedis (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,80),
(64,'Recup-notifications1','checkboxes','[\"2\"]','Destinataires des notifications de nouvelles demandes de crédit de récupérations','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(65,'Recup-notifications2','checkboxes','[\"2\"]','Destinataires des notifications de modification de crédit de récupérations','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(66,'Recup-notifications3','checkboxes','[\"1\"]','Destinataires des notifications des validations de crédit de récupérations niveau 1','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(67,'Recup-notifications4','checkboxes','[\"3\"]','Destinataires des notifications des validations de crédit de récupérations niveau 2','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(68,'Conges-Rappels','boolean','0','Activer / D&eacute;sactiver l&apos;envoi de rappels s&apos;il y a des cong&eacute;s non-valid&eacute;s','Congés','',0,NULL,7),
(69,'Conges-Rappels-Jours','text','14','Nombre de jours &agrave; contr&ocirc;ler pour l&apos;envoi de rappels sur les cong&eacute;s non-valid&eacute;s','Congés','',0,NULL,8),
(70,'Conges-Rappels-N1','checkboxes','[\"Mail-Planning\"]','A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 1','Congés','[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]',0,NULL,13),
(71,'Conges-Rappels-N2','checkboxes','[\"mails_responsables\"]','A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 2','Congés','[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]',0,NULL,15),
(72,'Conges-Validation-N2','enum2','0','La validation niveau 2 des cong&eacute;s peut se faire directement ou doit attendre la validation niveau 1','Congés','[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le cong&eacute; doit &ecirc;tre valid&eacute; au niveau 1\"]]',0,NULL,5),
(73,'Conges-Enable','boolean','0','Activer le module Congés','Congés','',0,NULL,1),
(74,'Absences-validation','boolean','1','Les absences doivent &ecirc;tre valid&eacute;es par un administrateur avant d&apos;&ecirc;tre prises en compte','Absences','',0,NULL,30),
(75,'Absences-non-validees','boolean','1','Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e','Absences','',0,NULL,35),
(76,'Absences-agent-preselection','boolean','0','Présélectionner l&apos;agent connecté lors de l&apos;ajout d&apos;une nouvelle absence.','Absences','',0,NULL,36),
(77,'Absences-tous','boolean','0','Autoriser l&apos;enregistrement d&apos;absences pour tous les agents en une fois','Absences','',0,NULL,37),
(78,'Absences-adminSeulement','boolean','0','Autoriser la saisie des absences aux administrateurs seulement.','Absences','',0,NULL,20),
(79,'Mail-Planning','textarea','','Adresses e-mails de la cellule planning, séparées par des ;','Messagerie','',1,NULL,140),
(80,'Planning-sansRepas','boolean','1','Afficher une notification pour les Sans Repas dans le menu d&eacute;roulant et dans le planning','Planning','',0,NULL,10),
(81,'Planning-dejaPlace','boolean','1','Afficher une notification pour les agents d&eacute;j&agrave; plac&eacute; sur un poste dans le menu d&eacute;roulant du planning','Planning','',0,NULL,20),
(82,'Planning-Heures','boolean','1','Afficher les heures &agrave; c&ocirc;t&eacute; du nom des agents dans le menu du planning','Planning','',0,NULL,25),
(83,'Planning-CommentairesToujoursActifs','boolean','0','Afficher la zone de commentaire m&ecirc;me si le planning n\'est pas encore commenc&eacute;.','Planning','',0,NULL,100),
(84,'Absences-notifications-A1','checkboxes','[\"2\"]','Destinataires des notifications de nouvelles absences (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,40),
(85,'Absences-notifications-A2','checkboxes','[\"2\"]','Destinataires des notifications de modification d&apos;absences (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,50),
(86,'Absences-notifications-A3','checkboxes','[\"1\"]','Destinataires des notifications des validations niveau 1 (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,60),
(87,'Absences-notifications-A4','checkboxes','[\"3\"]','Destinataires des notifications des validations niveau 2 (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,70),
(88,'Absences-notifications-agent-par-agent','boolean','1','Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres Absences-notifications-A1, A2, A3 et A4 ou B1, B2, B3 et B4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','Absences','',0,NULL,120),
(89,'Absences-notifications-titre','text','','Titre personnalis&eacute; pour les notifications de nouvelles absences','Absences','',0,NULL,130),
(90,'Absences-notifications-message','textarea','','Message personnalis&eacute; pour les notifications de nouvelles absences','Absences','',0,NULL,140),
(91,'Statistiques-Heures','textarea','','Afficher des statistiques sur les cr&eacute;neaux horaires voulus. Les cr&eacute;neaux doivent &ecirc;tre au format 00h00-00h00 et s&eacute;par&eacute;s par des ; Exemple : 19h00-20h00; 20h00-21h00; 21h00-22h00','Statistiques','',0,NULL,10),
(92,'Affichage-theme','text','default','Th&egrave;me de l&apos;application.','Affichage','',0,NULL,10),
(93,'Affichage-titre','text','','Titre affich&eacute; sur la page d&apos;accueil','Affichage','',0,NULL,20),
(94,'Affichage-etages','boolean','0','Afficher les &eacute;tages des postes dans le planning','Affichage','',0,NULL,30),
(95,'Planning-NbAgentsCellule','enum','4','Nombre maximum d\'agents par cellule','Planning','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20',0,NULL,3),
(96,'Planning-lignesVides','boolean','1','Afficher ou non les lignes vides dans les plannings validés','Planning','',0,NULL,4),
(97,'Planning-SR-debut','enum2','12:00:00','Heure de d&eacute;but pour la v&eacute;rification des sans repas','Planning','[[\"11:00:00\",\"11h00\"],[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"]]',0,NULL,11),
(98,'Planning-SR-fin','enum2','14:00:00','Heure de fin pour la v&eacute;rification des sans repas','Planning','[[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"],[\"15:00:00\",\"15h00\"]]',0,NULL,12),
(99,'Planning-Absences-Heures-Hebdo','boolean','0','Prendre en compte les absences pour calculer le nombre d&apos;heures de SP &agrave; effectuer. (Module PlanningHebdo requis)','Planning','',0,NULL,30),
(100,'CAS-Debug','boolean','0','Activer le débogage pour CAS. Créé un fichier \"cas_debug.txt\" dans le dossier \"[TEMP]\"','CAS','',1,NULL,50),
(101,'PlanningHebdo','boolean','1','Utiliser le module “Planning Hebdo”. Ce module permet d\'enregistrer plusieurs horaires de présence par agent en définissant des périodes d\'utilisation. (Incompatible avec l\'option EDTSamedi)','Heures de présence','',0,NULL,40),
(102,'PlanningHebdo-Agents','boolean','1','Autoriser les agents à saisir leurs heures de présence (avec le module Planning Hebdo). Les heures saisies devront être validées par un administrateur','Heures de présence','',0,NULL,50),
(103,'PlanningHebdo-Pause2','boolean','0','2 pauses dans une journ&eacute;e','Heures de présence','',0,NULL,60),
(104,'PlanningHebdo-notifications1','checkboxes','[\"3\"]','Destinataires des notifications d\'enregistrement de nouvelles heures de présence','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,70),
(105,'PlanningHebdo-notifications2','checkboxes','[\"3\"]','Destinataires des notifications de modification des heures de présence','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,72),
(106,'PlanningHebdo-notifications3','checkboxes','[\"2\"]','Destinataires des notifications des validations niveau 1','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,74),
(107,'PlanningHebdo-notifications4','checkboxes','[\"4\"]','Destinataires des notifications des validations niveau 2','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,76),
(108,'PlanningHebdo-notifications-agent-par-agent','boolean','1','Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres PlanningHebdo-notifications1, 2, 3 et 4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','Heures de présence','',0,NULL,80),
(109,'PlanningHebdo-Validation-N2','enum2','0','La validation niveau 2 des heures de présence peut se faire directement ou doit attendre la validation niveau 1','Heures de présence','[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le planning doit &ecirc;tre valid&eacute; au niveau 1\"]]',0,NULL,85),
(208,'Absences-Exclusion','enum2','0','Autoriser l\'affectation au planning des agents absents.','Absences','[[0, \"Les agents ayant une absence validée sont exclus des plannings.\"],[1,\"Les agents ayant des absences importées validées peuvent être ajoutés au planning.\"],[2,\"Les agents ayant des absences validées, importées ou non, peuvent être ajoutés au planning.\"]]',0,NULL,160),
(111,'Planning-TableauxMasques','boolean','1','Autoriser le masquage de certains tableaux du planning','Planning','',0,NULL,50),
(112,'Planning-AppelDispo','boolean','0','Permettre l&apos;envoi d&apos;un mail aux agents disponibles pour leur demander s&apos;ils sont volontaires pour occuper le poste choisi.','Planning','',0,NULL,60),
(113,'Planning-AppelDispoSujet','text','Appel à disponibilité [poste] [date] [debut]-[fin]','Sujet du mail pour les appels &agrave; disponibilit&eacute;','Planning','',0,NULL,70),
(114,'Planning-AppelDispoMessage','textarea','Chers tous,\r\n\r\nLe poste [poste] est vacant le [date] de [debut] à [fin].\r\n\r\nSi vous souhaitez occuper ce poste, vous pouvez répondre à cet e-mail.\r\n\r\nCordialement,\r\nLa cellule planning','Corps du mail pour les appels &agrave; disponibilit&eacute;','Planning','',0,NULL,80),
(115,'LDAP-Host','','','Nom d&apos;h&ocirc;te ou adresse IP du serveur LDAP','LDAP','',1,NULL,10),
(116,'LDAP-Port','','','Port du serveur LDAP','LDAP','',1,NULL,20),
(117,'LDAP-Protocol','enum','ldap','Protocol utilis&eacute;','LDAP','ldap,ldaps',1,NULL,30),
(118,'LDAP-Suffix','','','Base LDAP','LDAP','',1,NULL,40),
(119,'LDAP-Filter','','','Filtre LDAP. OpenLDAP essayez \"(objectclass=inetorgperson)\" , Active Directory essayez \"(&(objectCategory=person)(objectClass=user))\". Vous pouvez bien-s&ucirc;r personnaliser votre filtre.','LDAP','',1,NULL,50),
(120,'LDAP-RDN','','','DN de connexion au serveur LDAP, laissez vide si connexion anonyme','LDAP','',1,NULL,60),
(121,'LDAP-Password','password','::607def9e3096ea14e872b0b780599f25','Mot de passe de connexion','LDAP','',1,NULL,70),
(122,'LDAP-ID-Attribute','enum','uid','Attribut d&apos;authentification (OpenLDAP : uid, Active Directory : samaccountname)','LDAP','uid,samaccountname,supannaliaslogin',1,NULL,80),
(123,'LDAP-Matricule','text','','Attribut &agrave; importer dans le champ matricule (optionnel)','LDAP','',1,NULL,90),
(124,'CAS-Hostname','','','Nom d&apos;h&ocirc;te du serveur CAS','CAS','',1,NULL,30),
(125,'CAS-Port','','8080','Port serveur CAS','CAS','',1,NULL,30),
(126,'CAS-Version','enum','2.0','Version du serveur CAS','CAS','2.0,3.0,4.0',1,NULL,30),
(127,'CAS-CACert','','','Chemin absolut du certificat de l&apos;Autorit&eacute; de Certification. Si pas renseign&eacute;, l&apos;identit&eacute; du serveur ne sera pas v&eacute;rifi&eacute;e.','CAS','',1,NULL,30),
(128,'CAS-SSLVersion','enum2','1','Version SSL/TLS &agrave; utiliser pour les &eacute;changes avec le serveur CAS','CAS','[[1,\"TLSv1\"],[4,\"TLSv1_0\"],[5,\"TLSv1_1\"],[6,\"TLSv1_2\"]]',1,NULL,45),
(130,'CAS-URI','','cas','Page de connexion CAS','CAS','',1,NULL,30),
(131,'CAS-URI-Logout','','cas/logout','Page de d&eacute;connexion CAS','CAS','',1,NULL,30),
(132,'Rappels-Actifs','boolean','0','Activer les rappels','Rappels','',0,NULL,10),
(133,'Rappels-Jours','enum2','3','Nombre de jours &agrave; contr&ocirc;ler pour les rappels','Rappels','[[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7]]',0,NULL,20),
(134,'Rappels-Renfort','boolean','0','Contr&ocirc;ler les postes de renfort lors des rappels','Rappels','',0,NULL,30),
(135,'IPBlocker-TimeChecked','text','10','Recherche les &eacute;checs d&apos;authentification lors des N derni&egrave;res minutes. ( 0 = IPBlocker d&eacute;sactiv&eacute; )','Authentification','',1,NULL,40),
(136,'IPBlocker-Attempts','text','5','Nombre d&apos;&eacute;checs d&apos;authentification autoris&eacute;s lors des N derni&egrave;res minutes','Authentification','',1,NULL,50),
(137,'IPBlocker-Wait','text','10','Temps de blocage de l&apos;IP en minutes','Authentification','',1,NULL,60),
(138,'ICS-Server1','text','','URL du 1<sup>er</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server.domain.com/calendars/[email].ics','ICS','',1,NULL,10),
(139,'ICS-Pattern1','text','','Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 1<sup>er</sup> serveur. Ex: Agenda Personnel','ICS','',1,NULL,20),
(140,'ICS-Status1','enum2','CONFIRMED','Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','ICS','[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]',1,NULL,22),
(141,'ICS-Server2','text','','URL du 2<sup>&egrave;me</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics','ICS','',1,NULL,30),
(142,'ICS-Pattern2','text','','Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 2<sup>&egrave;me</sup> serveur. Ex: Congés','ICS','',1,NULL,40),
(143,'ICS-Status2','enum2','CONFIRMED','Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','ICS','[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]',1,NULL,42),
(144,'ICS-Server3','boolean','0','Utiliser une URL d&eacute;finie pour chaque agent dans le menu Administration / Les agents','ICS','',1,NULL,44),
(145,'ICS-Pattern3','text','','Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s depuis l&apos;URL d&eacute;finie dans la fiche des agents. Ex: Agenda personnel','ICS','',1,NULL,45),
(146,'ICS-Status3','enum2','CONFIRMED','Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','ICS','[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]',1,NULL,47),
(147,'ICS-Export','boolean','0','Autoriser l&apos;exportation des plages de service public sous forme de calendriers ICS. Un calendrier par agent, accessible &agrave; l&apos;adresse [SERVER]/ics/calendar.php?login=[login_de_l_agent]','ICS','',1,NULL,60),
(148,'ICS-Code','boolean','0','Prot&eacute;ger les calendriers ICS par des codes de façon &agrave; ce qu&apos;on ne puisse pas deviner les URLs. Si l&apos;option est activ&eacute;e, les URL seront du type : [SERVER]/ics/calendar.php?login=[login_de_l_agent]&amp;code=[code_al&eacute;atoire]','ICS','',1,NULL,70),
(149,'PlanningHebdo-CSV','text','','Emplacement du fichier CSV &agrave; importer (importation automatis&eacute;e) Ex: /dossier/fichier.csv','Heures de présence','',0,NULL,90),
(150,'Agenda-Plannings-Non-Valides','boolean','0','Afficher ou non les plages de service public des plannings non valid&eacute;s dans les agendas.','Agenda','',0,NULL,10),
(151,'Planning-agents-volants','boolean','1','Utiliser le module \"Agents volants\" permettant de diff&eacute;rencier un groupe d&apos;agents dans le planning','Planning','',0,NULL,90),
(152,'Hamac-csv','text','','Chemin d&apos;acc&egrave;s au fichier CSV pour l&apos;importation des absences depuis Hamac','Hamac','',1,NULL,10),
(153,'Hamac-motif','text','','Motif pour les absences import&eacute;s depuis Hamac. Ex: Hamac','Hamac','',1,NULL,20),
(154,'Hamac-status','enum2','1,2,3,5,6','Importer les absences valid&eacute;es et en attente de validation ou seulement les absences valid&eacute;es.','Hamac','[[\"1,2,3,5,6\",\"Valid&eacute;es et en attente de validation\"],[\"2\",\"Valid&eacute;es\"]]',1,NULL,30),
(155,'Hamac-id','enum2','login','Champ Planno à utiliser pour mapper les agents.','Hamac','[[\"login\",\"Login\"],[\"matricule\",\"Matricule\"]]',1,NULL,40),
(156,'Conges-Mode','enum2','jours','Décompte des congés en heures ou en jours','Congés','[[\"heures\",\"Heures\"],[\"jours\",\"Jours\"]]',0,NULL,2),
(157,'Absences-notifications-B1','checkboxes','[\"2\"]','Destinataires des notifications de nouvelles absences (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,80),
(158,'Absences-notifications-B2','checkboxes','[\"2\"]','Destinataires des notifications de modification d&apos;absences (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,90),
(159,'Absences-notifications-B3','checkboxes','[\"1\"]','Destinataires des notifications des validations niveau 1 (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,100),
(160,'Absences-notifications-B4','checkboxes','[\"3\"]','Destinataires des notifications des validations niveau 2 (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,110),
(161,'Absences-DelaiSuppressionDocuments','text','365','Les documents associ&eacute;s aux absences sont supprim&eacute;s au-del&agrave; du nombre de jours d&eacute;finis par ce param&egrave;tre.','Absences','',0,NULL,150),
(162,'Conges-demi-journees','boolean','0','Autorise la saisie de congés en demi-journée. Fonctionne uniquement avec le mode de saisie en jour','Congés','',0,NULL,8),
(163,'PlanningHebdo-PauseLibre','boolean','1','Ajoute la possibilité de saisir un temps de pause libre dans les heures de présence (Module Planning Hebdo uniquement)','Heures de présence','',0,NULL,65),
(165,'Journey-time-between-sites','text','0','Temps de trajet moyen entre sites (en minutes)','Planning','',0,NULL,95),
(166,'Journey-time-between-areas','text','0','Temps de trajet moyen entre zones (en minutes)','Planning','',0,NULL,96),
(167,'Journey-time-for-absences','text','0','Temps de trajet moyen entre une absence et un poste de service public (en minutes)','Planning','',0,NULL,97),
(168,'Conges-fullday-switching-time','text','4','Temps définissant la bascule entre une demi-journée et une journée complète lorsque les crédits de congés sont comptés en jours. Format : entier ou décimal. Exemple : pour 3h30, tapez 3.5','Congés','',0,NULL,9),
(169,'Conges-planningVide','boolean','1','Autoriser l\'enregistrement de congés sur des plannings en cours d\'élaboration','Congés','',0,NULL,11),
(170,'Conges-apresValidation','boolean','1','Autoriser l\'enregistrement de congés après validation des plannings','Congés','',0,NULL,12),
(171,'Conges-validation','boolean','1','Les congés doivent être validés par un administrateur avant d\'être pris en compte','Congés','',0,NULL,4),
(172,'Hamac-debug','boolean','0','Active le mode débugage pour l\'importation des absences depuis Hamac. Les informations de débugage sont écrites dans la table \"log\". Attention, si cette option est activée, la taille de la base de données augmente considérablement.','Hamac','',1,NULL,50),
(183,'Conges-tous','boolean','0','Autoriser l\'enregistrement de congés pour tous les agents en une fois','Congés','',0,NULL,6),
(184,'Conges-fullday-reference-time','text','','Temps de référence (en heures) pour une journée complète. Si ce champ est renseigné et que les crédits de congés sont gérés en jours, la différence de temps de chaque journée sera créditée ou débitée du solde des récupérations. Format : entier ou décimal. Exemple : pour 7h30, tapez 7.5','Congés','',0,NULL,10),
(186,'PlanningHebdo-DebutPauseLibre','enum2','12:00:00','Début de période de pause libre','Heures de présence','[[\"11:00:00\",\"11h00\"],[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"]]',0,NULL,66),
(187,'PlanningHebdo-FinPauseLibre','enum2','14:00:00','Fin de période de pause libre','Heures de présence','[[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"],[\"15:00:00\",\"15h00\"]]',0,NULL,67),
(188,'Planook','hidden','0','Version Lite Planook',' Divers','',0,NULL,0),
(189,'Absences-Validation-N2','enum2','0','La validation niveau 2 des absences peut se faire directement ou doit attendre la validation niveau 1','Absences','[[0,\"Validation directe autoris&eacute;e\"],[1,\"L\'absence doit &ecirc;tre valid&eacute; au niveau 1\"]]',0,NULL,31),
(190,'Auth-PasswordLength','text','8','Nombre minimum de caractères obligatoires pour le changement de mot de passe.','Authentification','',1,NULL,20),
(191,'legalNotices','textarea','','Mentions légales (exemple : notice RGPD). La syntaxe markdown peut être utilisée pour la saisie.','Mentions légales','',0,NULL,10),
(192,'Conges-transfer-comp-time','boolean','0','Transférer les récupérations restantes sur le reliquat','Congés','',0,NULL,16),
(193,'CAS-LoginAttribute','text','','Attribut CAS à utiliser pour mapper l\'utilisateur si et seulement si l\'UID CAS ne convient pas. Laisser ce champ vide par défaut. Exemple : \"mail\", dans ce cas, l\'adresse mail de l\'utilisateur est fournie par le serveur CAS et elle est renseignée dans le champ \"login\" des fiches agents de Planno.','CAS','',1,NULL,48),
(204,'LDIF-Encoding','enum','UTF-8','Encodage de caractères du fichier source','LDIF','UTF-8,ISO-8859-1',1,NULL,40),
(194,'ICS-Interval','text','365','Restriction de la période à exporter : renseigner le nombre de jours à rechercher dans le passé. Les événements à venir sont toujours exportés. Si le champ n\'est pas renseigné, tous les événements seront recherchés.','ICS','',1,NULL,80),
(195,'Affichage-Agent','color','#FFF3B3','Couleur des cellules de l\'agent connecté','Affichage','',0,NULL,40),
(196,'Absences-blocage','boolean','0','Permettre le blocage des absences et congés sur une période définie par les gestionnaires. Ce paramètre empêchera les agents qui n\'ont pas le droits de gérer les absences d\'enregistrer absences et congés sur les périodes définies. En configuration multi-sites, les agents de tous les sites seront bloqués sans distinction.','Absences','',0,NULL,5),
(197,'LDIF-File','text','','Emplacement d\'un fichier LDIF pour l\'importation des agents','LDIF','',1,NULL,10),
(198,'LDIF-ID-Attribute','enum','uid','Attribut d\'authentification (OpenLDAP : uid, Active Directory : samaccountname)','LDIF','uid,samaccountname,supannaliaslogin,employeenumber',1,NULL,20),
(199,'LDIF-Matricule','text','','Attribut à importer dans le champ matricule (optionnel)','LDIF','',1,NULL,30),
(200,'MT42875_dateDebutPlHebdo','hidden','nb_semaine: 1 (<5): nothing to do\n','','Heures de présence','',0,NULL,0),
(201,'ICS-Description1','boolean','1','Inclure la description de l\'événement importé dans le commentaire de l\'absence','ICS','',1,NULL,23),
(202,'ICS-Description2','boolean','1','Inclure la description de l\'événement importé dans le commentaire de l\'absence','ICS','',1,NULL,43),
(203,'ICS-Description3','boolean','1','Inclure la description de l\'événement importé dans le commentaire de l\'absence','ICS','',1,NULL,48),
(205,'Auth-LoginLayout','enum','firstname.lastname','Schéma à utiliser pour la construction des logins','Authentification','firstname.lastname,lastname.firstname,mail,mailPrefix',1,NULL,10),
(206,'Planning-InitialNotification','enum2','-2','Envoyer une notification aux agents lors de la validation des plannings les concernant','Planning','[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]',0,NULL,40),
(207,'Planning-ChangeNotification','enum2','-2','Envoyer une notification aux agents lors d\'une modification de planning les concernant','Planning','[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]',0,NULL,41),
(222,'Mail-SMTPAutoTLS','boolean','1','Activer ou désactiver le mode Auto TLS','Messagerie','',1,NULL,70),
(210,'OIDC-Provider','text','','OpenID Connect Provider.','OpenID Connect','',1,NULL,10),
(211,'OIDC-CACert','text','','Path to the OpenID Connect CA Certificate.','OpenID Connect','',1,NULL,20),
(212,'OIDC-ClientID','text','','OpenID Connect Client ID (not to be confused with Secret ID).','OpenID Connect','',1,NULL,30),
(213,'OIDC-ClientSecret','text','','OpenID Connect Secret Value (not to be confused with Secret ID).','OpenID Connect','',1,NULL,40),
(214,'OIDC-LoginAttribute','text','','OpenID Connect Login Attribute.','OpenID Connect','',1,NULL,50),
(215,'MSGraph-TenantID','text','','MS Graph Tenant ID.','Microsoft Graph API','',1,NULL,10),
(216,'MSGraph-ClientID','text','','MS Graph Client ID (not to be confused with Secret ID).','Microsoft Graph API','',1,NULL,20),
(217,'MSGraph-ClientSecret','text','','MS Graph Secret Value (not to be confused with Secret ID).','Microsoft Graph API','',1,NULL,30),
(218,'MSGraph-LoginSuffix','text','','Suffix that must be added to the Planno login to link with the MS login. Optional, empty by default.','Microsoft Graph API','',1,NULL,40),
(219,'MSGraph-IgnoredStatuses','text','free;tentative','List of statuses to ignore, separated by semicolons. Optional, \"free;tentative\" by default.','Microsoft Graph API','',1,NULL,50),
(220,'MSGraph-AbsenceReason','text','Office 365','Absence Reason to use for imported events. Optional, \"Outlook\" by default.','Microsoft Graph API','',1,NULL,60),
(221,'Planning-IgnoreBreaks','boolean','0','Si cette case est cochée, les périodes de pauses (ex: pause déjeuner) définies dans les heures de présence seront ignorées dans le menu permettant d\'ajouter les agents dans le planning et lors de l\'importation des modèles.','Planning','',0,NULL,0),
(223,'OIDC-Debug','boolean','0','Debug mode. Logs information to the log table.','OpenID Connect','',1,NULL,60);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `conges`
--

DROP TABLE IF EXISTS `conges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `conges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL,
  `debut` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `halfday` tinyint(4) DEFAULT 0,
  `start_halfday` varchar(20) DEFAULT '',
  `end_halfday` varchar(20) DEFAULT '',
  `commentaires` text DEFAULT NULL,
  `refus` text DEFAULT NULL,
  `heures` varchar(20) DEFAULT NULL,
  `debit` varchar(20) DEFAULT NULL,
  `saisie` timestamp NOT NULL DEFAULT current_timestamp(),
  `saisie_par` int(11) NOT NULL,
  `modif` int(11) NOT NULL DEFAULT 0,
  `modification` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valide_n1` int(11) NOT NULL DEFAULT 0,
  `validation_n1` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valide` int(11) NOT NULL DEFAULT 0,
  `validation` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `solde_prec` float DEFAULT NULL,
  `solde_actuel` float DEFAULT NULL,
  `recup_prec` float DEFAULT NULL,
  `recup_actuel` float DEFAULT NULL,
  `reliquat_prec` float DEFAULT NULL,
  `reliquat_actuel` float DEFAULT NULL,
  `anticipation_prec` float DEFAULT NULL,
  `anticipation_actuel` float DEFAULT NULL,
  `supprime` int(11) NOT NULL DEFAULT 0,
  `suppr_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `information` int(11) NOT NULL DEFAULT 0,
  `info_date` timestamp NULL DEFAULT NULL,
  `regul_id` int(11) DEFAULT NULL,
  `origin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conges`
--

LOCK TABLES `conges` WRITE;
/*!40000 ALTER TABLE `conges` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `conges` VALUES
(1,1,'2020-04-16 00:00:00','2020-04-16 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2020-04-16 10:00:29',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2020-04-16 10:00:29',NULL,NULL),
(2,1,'2022-04-28 00:00:00','2022-04-28 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2022-04-28 09:26:07',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2022-04-28 09:26:07',NULL,NULL),
(3,1,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(4,9,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(5,14,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(6,6,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(7,15,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(8,12,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(9,3,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(10,8,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(11,11,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(12,10,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(13,4,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(14,13,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(15,7,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(16,5,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(17,9,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(18,14,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(19,6,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(20,15,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(21,12,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(22,3,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(23,8,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(24,11,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(25,10,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(26,4,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(27,13,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(28,7,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL),
(29,5,'2023-01-23 00:00:00','2023-01-23 00:00:00',0,'','',NULL,NULL,NULL,NULL,'2023-01-23 14:46:41',0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,0,0,0,0,0,0,0,0,'0000-00-00 00:00:00',999999999,'2023-01-23 14:46:41',NULL,NULL);
/*!40000 ALTER TABLE `conges` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `conges_infos`
--

DROP TABLE IF EXISTS `conges_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `conges_infos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` date DEFAULT NULL,
  `fin` date DEFAULT NULL,
  `texte` text DEFAULT NULL,
  `saisie` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conges_infos`
--

LOCK TABLES `conges_infos` WRITE;
/*!40000 ALTER TABLE `conges_infos` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `conges_infos` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `cron`
--

DROP TABLE IF EXISTS `cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `m` varchar(2) DEFAULT NULL,
  `h` varchar(2) DEFAULT NULL,
  `dom` varchar(2) DEFAULT NULL,
  `mon` varchar(2) DEFAULT NULL,
  `dow` varchar(2) DEFAULT NULL,
  `name` varchar(30) NOT NULL DEFAULT '',
  `command` varchar(200) DEFAULT NULL,
  `comments` varchar(500) DEFAULT NULL,
  `last` datetime DEFAULT '0000-00-00 00:00:00',
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cron`
--

LOCK TABLES `cron` WRITE;
/*!40000 ALTER TABLE `cron` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `cron` VALUES
(1,'0','0','*','*','*','workingHourDaily','app:workinghour:daily','Daily Cron for Planning Hebdo module','2023-06-20 10:20:07',0),
(2,'0','0','1','1','*','holidayResetRemainder','app:holiday:reset:remainder --force','Reset holiday remainders','2023-01-23 15:46:41',0),
(3,'0','0','1','9','*','holidayResetCredit','app:holiday:reset:credits --force','Reset holiday credits','2022-09-12 15:31:30',0),
(4,'0','0','1','9','*','holidayResetCompTime','app:holiday:reset:comp-time --force','Reset holiday compensatory time','2023-01-23 15:46:41',0);
/*!40000 ALTER TABLE `cron` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(192) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `doctrine_migration_versions` VALUES
('App\\Migrations\\Version20250829094200','2025-12-09 14:16:50',37),
('App\\Migrations\\Version20250905075236','2025-12-09 14:16:50',1),
('App\\Migrations\\Version20250919105120','2025-12-09 14:16:50',1),
('App\\Migrations\\Version20251001063442','2025-12-09 14:16:50',0),
('App\\Migrations\\Version20251001100644','2025-12-09 14:16:50',1),
('App\\Migrations\\Version20251013130108','2025-12-09 14:16:50',1),
('App\\Migrations\\Version20251017094116','2025-12-09 14:16:50',0),
('App\\Migrations\\Version20251031113317','2025-12-09 14:16:50',0),
('App\\Migrations\\Version20251103113513','2025-12-09 14:16:50',1),
('App\\Migrations\\Version20251110094218','2025-12-09 14:16:50',0),
('App\\Migrations\\Version20251114074411','2025-12-09 14:16:50',38),
('App\\Migrations\\Version20251114094436','2025-12-09 14:16:50',0),
('App\\Migrations\\Version20251128093056','2025-12-09 14:16:50',0),
('App\\Migrations\\Version20251204105518','2025-12-09 14:16:50',0),
('App\\Migrations\\Version20251205162839','2025-12-09 14:16:50',0);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `edt_samedi`
--

DROP TABLE IF EXISTS `edt_samedi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `edt_samedi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL,
  `semaine` date DEFAULT NULL,
  `tableau` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edt_samedi`
--

LOCK TABLES `edt_samedi` WRITE;
/*!40000 ALTER TABLE `edt_samedi` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `edt_samedi` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `heures_absences`
--

DROP TABLE IF EXISTS `heures_absences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `heures_absences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semaine` date DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `heures` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heures_absences`
--

LOCK TABLES `heures_absences` WRITE;
/*!40000 ALTER TABLE `heures_absences` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `heures_absences` VALUES
(4,'2022-09-26',1664360334,'[]'),
(2,'2022-10-10',1664286786,'[]'),
(13,'2023-01-23',1674570836,'{\"8\":8}'),
(12,'2023-02-13',1674570328,'[]'),
(14,'2023-02-20',1674571548,'{\"7\":96}'),
(15,'2023-02-27',1674572437,'{\"7\":96}');
/*!40000 ALTER TABLE `heures_absences` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `heures_sp`
--

DROP TABLE IF EXISTS `heures_sp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `heures_sp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semaine` date DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `heures` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heures_sp`
--

LOCK TABLES `heures_sp` WRITE;
/*!40000 ALTER TABLE `heures_sp` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `heures_sp` VALUES
(2,'2022-10-10',1664286786,'{\"9\":48,\"6\":48,\"3\":48,\"8\":48,\"11\":48,\"10\":48,\"4\":48,\"7\":48,\"5\":48,\"2\":0}'),
(4,'2022-09-26',1664360334,'{\"9\":48,\"14\":36,\"6\":48,\"15\":36,\"12\":36,\"3\":48,\"8\":48,\"11\":48,\"10\":48,\"4\":48,\"13\":36,\"7\":48,\"5\":48,\"2\":0}'),
(12,'2023-01-23',1674570836,'{\"9\":48,\"14\":36,\"6\":48,\"15\":36,\"16\":39,\"12\":36,\"3\":48,\"8\":48,\"11\":48,\"10\":48,\"4\":48,\"13\":36,\"7\":48,\"5\":48,\"2\":0}'),
(11,'2023-02-13',1674570328,'{\"9\":48,\"14\":36,\"6\":48,\"15\":36,\"16\":39,\"12\":36,\"3\":48,\"8\":48,\"11\":48,\"10\":48,\"4\":48,\"13\":36,\"7\":48,\"5\":48,\"2\":0}'),
(13,'2023-02-20',1674571548,'{\"9\":48,\"14\":36,\"6\":48,\"15\":36,\"16\":39,\"12\":36,\"3\":48,\"8\":48,\"11\":48,\"10\":48,\"4\":48,\"13\":36,\"7\":48,\"5\":48,\"2\":0}'),
(14,'2023-02-27',1674572437,'{\"9\":48,\"14\":36,\"6\":48,\"15\":36,\"16\":39,\"12\":36,\"3\":48,\"8\":48,\"11\":48,\"10\":48,\"4\":48,\"13\":36,\"7\":48,\"5\":48,\"2\":0}');
/*!40000 ALTER TABLE `heures_sp` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `hidden_tables`
--

DROP TABLE IF EXISTS `hidden_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hidden_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT 0,
  `tableau` int(11) NOT NULL DEFAULT 0,
  `hidden_tables` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hidden_tables`
--

LOCK TABLES `hidden_tables` WRITE;
/*!40000 ALTER TABLE `hidden_tables` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `hidden_tables` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `infos`
--

DROP TABLE IF EXISTS `infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `infos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` date DEFAULT NULL,
  `fin` date DEFAULT NULL,
  `texte` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `infos`
--

LOCK TABLES `infos` WRITE;
/*!40000 ALTER TABLE `infos` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `infos` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `ip_blocker`
--

DROP TABLE IF EXISTS `ip_blocker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_blocker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL,
  `login` varchar(100) DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blocker`
--

LOCK TABLES `ip_blocker` WRITE;
/*!40000 ALTER TABLE `ip_blocker` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `ip_blocker` VALUES
(1,'127.0.0.1','admin','success','2020-04-16 10:00:29'),
(2,'192.168.1.29','admin','success','2020-05-13 14:47:22'),
(3,'10.152.170.1','admin','success','2022-04-28 09:26:04'),
(4,'10.168.168.10','admin','success','2022-09-12 13:31:30'),
(5,'10.168.168.10','admin','success','2022-09-27 13:35:02'),
(6,'10.168.168.10','jerome','success','2022-09-27 13:50:07'),
(7,'10.168.168.10','admin','success','2022-09-27 13:50:38'),
(8,'10.168.168.10','jerome','success','2022-09-27 13:50:56'),
(9,'10.168.168.10','jerome','success','2022-09-27 13:51:23'),
(10,'10.168.168.10','admin','success','2022-09-28 07:57:19'),
(11,'10.168.168.10','admin','success','2022-09-28 10:09:53'),
(12,'10.168.168.10','jerome','success','2022-09-28 10:18:16'),
(13,'fd42:2ea8:1226:6d4c:','admin','success','2023-01-24 12:50:23'),
(14,'fd42:2ea8:1226:6d4c:','admin','success','2023-01-24 16:38:34'),
(15,'10.152.170.1','admin','success','2023-06-20 08:20:13'),
(16,'10.152.170.1','jerome','success','2023-06-20 08:21:04'),
(17,'10.152.170.1','admin','success','2023-06-20 08:21:48'),
(18,'10.152.170.1','jerome','success','2023-06-20 08:22:14'),
(19,'10.152.170.1','jerome','success','2023-06-20 09:00:07'),
(20,'10.152.170.1','alex.alex','success','2023-06-20 09:02:07'),
(21,'10.152.170.1','jerome','success','2023-06-20 09:02:19'),
(22,'10.152.170.1','alex.alex','success','2023-06-20 09:03:56'),
(23,'10.152.170.1','jerome','success','2023-06-20 09:04:17'),
(24,'10.152.170.1','alex.alex','success','2023-06-20 09:06:08'),
(25,'10.152.170.1','jerome','success','2023-06-20 09:08:44'),
(26,'10.152.170.1','admin','success','2023-06-20 09:09:24'),
(27,'10.152.170.1','admin','success','2023-06-20 09:10:01'),
(28,'10.152.170.1','admin','success','2023-06-20 09:10:10'),
(29,'10.152.170.1','jerome','success','2023-06-20 09:19:44'),
(30,'10.152.170.1','aurelie.aurelie','success','2023-06-20 09:20:29'),
(31,'10.152.170.1','admin','success','2023-06-20 09:20:43'),
(32,'10.152.170.1','alex','success','2023-06-20 09:21:14'),
(33,'10.152.170.1','aurelie','success','2023-06-20 09:21:26');
/*!40000 ALTER TABLE `ip_blocker` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `jours_feries`
--

DROP TABLE IF EXISTS `jours_feries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jours_feries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `annee` varchar(10) DEFAULT NULL,
  `jour` date DEFAULT NULL,
  `ferie` int(1) DEFAULT NULL,
  `fermeture` int(1) DEFAULT NULL,
  `nom` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jours_feries`
--

LOCK TABLES `jours_feries` WRITE;
/*!40000 ALTER TABLE `jours_feries` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `jours_feries` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `lignes`
--

DROP TABLE IF EXISTS `lignes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lignes`
--

LOCK TABLES `lignes` WRITE;
/*!40000 ALTER TABLE `lignes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `lignes` VALUES
(1,'Magasins'),
(2,'Mezzanine'),
(3,'Rez de chaussée'),
(4,'Rez de jardin');
/*!40000 ALTER TABLE `lignes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg` text DEFAULT NULL,
  `program` varchar(30) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `log` VALUES
(1,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:52:06'),
(2,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:52:06'),
(3,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:52:35'),
(4,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:53:35'),
(5,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:53:50'),
(6,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:54:35'),
(7,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:55:35'),
(8,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:56:35'),
(9,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:57:35'),
(10,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:57:51'),
(11,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:58:27'),
(12,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:58:35'),
(13,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:58:56'),
(14,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 13:59:35'),
(15,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:00:35'),
(16,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:01:35'),
(17,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:02:35'),
(18,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:02:54'),
(19,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:03:35'),
(20,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:04:35'),
(21,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:05:35'),
(22,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:06:35'),
(23,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:06:59'),
(24,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:06:59'),
(25,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:07:35'),
(26,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:08:35'),
(27,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:09:35'),
(28,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:10:35'),
(29,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:11:35'),
(30,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:12:35'),
(31,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:13:35'),
(32,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:14:35'),
(33,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:15:35'),
(34,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:16:35'),
(35,'Exportation des plages de SP pour l\'agent #3','ICS Export','2022-09-27 14:17:35');
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `niveau1` int(11) NOT NULL,
  `niveau2` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `condition` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `menu` VALUES
(1,10,0,'Absences','/absence',NULL),
(2,10,10,'Voir les absences','/absence',NULL),
(3,10,20,'Ajouter une absence','/absence/add',NULL),
(4,10,30,'Informations','/absences/info','config!=Planook'),
(5,15,0,'Congés','/holiday/index','config=Conges-Enable'),
(6,15,10,'Liste des cong&eacute;s','/holiday/index','config=Conges-Enable'),
(7,15,15,'Liste des r&eacute;cup&eacute;rations','/holiday/index?recup=1','config=Conges-Enable;Conges-Recuperations'),
(8,15,20,'Poser des cong&eacute;s','/holiday/new','config=Conges-Enable'),
(9,15,24,'Poser des r&eacute;cup&eacute;rations','/comptime/add','config=Conges-Enable;Conges-Recuperations'),
(10,15,26,'Heures supplémentaires','/overtime','config=Conges-Enable'),
(11,15,30,'Informations','/holiday-info','config=Conges-Enable'),
(12,15,40,'Cr&eacute;dits','/holiday/accounts','config=Conges-Enable'),
(13,20,0,'Agenda','/calendar',NULL),
(14,30,0,'Planning','/',NULL),
(15,30,90,'Agents volants','/detached','config=Planning-agents-volants'),
(16,40,0,'Statistiques','/statistics','config!=Planook'),
(17,40,10,'Feuille de temps','/statistics/time','config!=Planook'),
(18,40,20,'Par agent','/statistics/agent','config!=Planook'),
(19,40,30,'Par poste','/statistics/position','config!=Planook'),
(20,40,40,'Par poste (Synth&egrave;se)','/statistics/positionsummary','config!=Planook'),
(21,40,50,'Postes de renfort','/statistics/supportposition','config!=Planook'),
(22,40,24,'Par service','/statistics/service','config!=Planook'),
(23,40,60,'Samedis','/statistics/saturday','config!=Planook'),
(24,40,70,'Absences','/statistics/absence','config!=Planook'),
(25,40,80,'Présents / absents','/statistics/attendeesmissing','config!=Planook'),
(26,40,26,'Par statut','/statistics/status','config!=Planook'),
(27,50,0,'Administration','/admin',NULL),
(28,50,10,'Informations','/admin/info','config!=Planook'),
(29,50,20,'Les activités','/skill','config!=Planook'),
(30,50,30,'Les agents','/agent',NULL),
(31,50,40,'Les postes','/position',NULL),
(32,50,50,'Les mod&egrave;les','/model',NULL),
(33,50,60,'Les tableaux','/framework',NULL),
(34,50,70,'Jours de fermeture','/closingday','config!=Planook&config=Conges-Enable'),
(35,50,75,'Heures de présence','/workinghour','config=PlanningHebdo'),
(36,50,77,'Notifications / Validations','/notification','config=Absences-notifications-agent-par-agent'),
(37,50,80,'Configuration fonctionnelle','/config',NULL),
(38,60,0,'Aide','/help',NULL),
(39,10,25,'Bloquer les absences','/absence/block','config=Absences-blocage'),
(40,50,90,'Configuration technique','/config/technical',NULL);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `personnel`
--

DROP TABLE IF EXISTS `personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `prenom` text NOT NULL,
  `mail` text NOT NULL,
  `statut` text NOT NULL,
  `categorie` varchar(30) NOT NULL DEFAULT '',
  `service` text NOT NULL,
  `arrivee` date NOT NULL DEFAULT '0000-00-00',
  `depart` date NOT NULL DEFAULT '0000-00-00',
  `postes` text NOT NULL,
  `actif` varchar(20) NOT NULL DEFAULT 'true',
  `droits` text NOT NULL,
  `login` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `commentaires` text NOT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `heures_hebdo` varchar(6) NOT NULL,
  `heures_travail` float NOT NULL,
  `sites` text NOT NULL,
  `temps` text NOT NULL,
  `informations` text NOT NULL,
  `recup` text NOT NULL,
  `supprime` tinyint(1) NOT NULL DEFAULT 0,
  `mails_responsables` text NOT NULL,
  `matricule` varchar(100) DEFAULT NULL,
  `code_ics` varchar(100) DEFAULT NULL,
  `url_ics` text DEFAULT NULL,
  `check_ics` varchar(10) DEFAULT '[1,1,1]',
  `check_hamac` int(1) NOT NULL DEFAULT 1,
  `check_ms_graph` tinyint(1) NOT NULL DEFAULT 0,
  `conges_credit` float DEFAULT NULL,
  `conges_reliquat` float DEFAULT NULL,
  `conges_anticipation` float DEFAULT NULL,
  `comp_time` float DEFAULT NULL,
  `conges_annuel` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel`
--

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `personnel` VALUES
(1,'admin','admin','sysop@biblibre.com','','','','0000-00-00','0000-00-00','[]','Inactif','[\"6\",\"9\",\"3\",\"4\",\"21\",\"1101\",\"1201\",\"22\",\"5\",\"17\",\"1301\",\"23\",\"201\",\"202\",\"203\",\"204\",\"501\",\"502\",\"503\",\"504\",\"301\",\"1001\",\"901\",\"801\",6,99,100,20]','admin','$2y$10$BtvEpITk5Dvef2EjAZrVJ.jnxlzFoMOwSrPFHpBHkbRuyxtQS7.sC','Compte cr&eacute;&eacute; lors de l&apos;installation du planning','2023-06-20 11:20:43','0',0,'','[[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"],[\"\",\"\",\"\",\"\"]]','','',0,'','','6c987624715ca2db961cac7a618935b4',NULL,'[0,0,0]',0,0,NULL,0,0,0,NULL),
(2,'Tout le monde','','','','','','0000-00-00','0000-00-00','','Actif','[99,100]','','5f4dcc3b5aa765d61d8327deb882cf99','Compte cr&eacute;&eacute; lors de l&apos;installation du planning','0000-00-00 00:00:00','',0,'','[[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"\",\"\",\"\",\"\"]]','','',0,'','',NULL,NULL,'[1,1,1]',1,0,NULL,0,0,0,NULL),
(3,'Jérôme','Jérôme','jerome@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[\"6\",\"701\",\"3\",\"4\",\"21\",\"1101\",\"1201\",\"22\",\"5\",\"17\",\"1301\",\"23\",\"201\",\"501\",\"301\",\"1001\",\"901\",\"801\",6,99,100]','jerome','$2y$10$aNQWzHwoj9HCCeSVbtmnUexCMFELECk14Ur/im4UpazwJnGGIAb6q','','2023-06-20 11:19:44','80%',37.5,'[\"3\"]','','','',0,'','','a228f0daaefdceddaf1d90f1359e9b36',NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(4,'Michaël','Michaël','michael@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','michael.michael','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','80%',37.5,'[\"4\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(5,'Virginie','Virginie','virginie@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','virginie.virginie','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','80%',37.5,'[\"1\",\"2\",\"3\",\"4\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(6,'Bianca','Bianca','bianca@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','bianca.bianca','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','80%',37.5,'[\"1\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(7,'Sophie','Sophie','sophie@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','sophie.sophie','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','80%',37.5,'[\"1\",\"2\",\"3\",\"4\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(8,'Laurence','Laurence','laurence@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','laurence.laurence','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','80%',37.5,'[\"3\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(9,'Alex','Alex','alex@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[\"6\",\"1101\",\"1201\",\"201\",\"501\",\"301\",\"1001\",6,99,100]','alex','$2y$10$5YwIuhJdQbGgi7rmLkb/eeoAdnHcT0J88dRzgvCgl1tj1oXwfM5um','','2023-06-20 11:21:14','80%',37.5,'[\"1\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(10,'Matthias','Matthias','Matthias@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','matthias.matthias','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','80%',37.5,'[\"4\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(11,'Louise','Louise','louise@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','louise.louise','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','80%',37.5,'[\"3\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(12,'Gladys','Gladys','gladys@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','gladys.gladys','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','60%',35,'[\"2\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(13,'Séverine','Séverine','severine@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"4\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','severine.severine','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','60%',35,'[\"4\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(14,'Aurélie','Aurélie','aurelie@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[\"6\",\"1101\",\"1201\",\"201\",\"501\",\"301\",\"1001\",6,99,100]','aurelie','$2y$10$sN8dsOnybhx/BfUk7/3KW.nBqo.AS3Gp6WhrgV3Gns/3/iZn6RnIe','','2023-06-20 11:21:26','60%',35,'[\"1\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(15,'Delphine','Delphine','delphine@test.com','','Titulaire','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','delphine.delphine','5f4dcc3b5aa765d61d8327deb882cf99','','0000-00-00 00:00:00','60%',35,'[\"2\"]','','','',0,'','',NULL,NULL,'[0,0,0]',0,0,NULL,0,NULL,0,NULL),
(16,'Didier','Didier','didier@example.com','','','','0000-00-00','0000-00-00','[\"1\",\"2\",\"3\",\"5\",\"6\",\"7\",\"8\",\"9\",\"10\",\"11\",\"12\"]','Actif','[99,100]','didier.didier','$2y$10$aW0ZNXobulMtGaUEpEQeqODkkYH5QSmggnD7oSYxfW49DmpUGCuiO','','0000-00-00 00:00:00','50%',37.5,'[\"2\"]','','','',0,'','','d7810f39fb9e2dca4ffb063b686d7b8a',NULL,'[0,0,0]',0,0,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `personnel` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_notes`
--

DROP TABLE IF EXISTS `pl_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `site` int(3) NOT NULL DEFAULT 1,
  `text` text DEFAULT NULL,
  `perso_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_notes`
--

LOCK TABLES `pl_notes` WRITE;
/*!40000 ALTER TABLE `pl_notes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `pl_notes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_notifications`
--

DROP TABLE IF EXISTS `pl_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `site` int(2) NOT NULL DEFAULT 1,
  `update_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `site` (`site`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_notifications`
--

LOCK TABLES `pl_notifications` WRITE;
/*!40000 ALTER TABLE `pl_notifications` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `pl_notifications` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_position_history`
--

DROP TABLE IF EXISTS `pl_position_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_position_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_ids` text NOT NULL,
  `date` date DEFAULT NULL,
  `beginning` time NOT NULL,
  `end` time NOT NULL,
  `site` int(11) NOT NULL DEFAULT 1,
  `position` int(11) NOT NULL,
  `action` varchar(20) NOT NULL,
  `undone` tinyint(4) NOT NULL DEFAULT 0,
  `archive` tinyint(4) NOT NULL DEFAULT 0,
  `play_before` tinyint(4) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=424 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_position_history`
--

LOCK TABLES `pl_position_history` WRITE;
/*!40000 ALTER TABLE `pl_position_history` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_position_history` VALUES
(1,'[\"495\"]','2023-01-03','09:30:00','11:00:00',1,52,'put',0,1,0,1,'2023-01-03 10:26:25'),
(2,'[\"495\"]','2023-01-03','09:30:00','11:00:00',1,52,'put',0,1,0,1,'2023-01-03 10:28:00'),
(3,'[\"495\"]','2023-01-03','09:30:00','11:00:00',1,52,'put',0,1,0,1,'2023-01-03 10:47:38'),
(4,'[\"495\"]','2023-01-03','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-03 10:56:24'),
(5,'[495]','2023-01-03','11:00:00','12:00:00',1,52,'delete',0,1,0,1,'2023-01-03 10:58:21'),
(6,'[\"[495]\"]','2023-01-03','11:00:00','12:00:00',1,52,'put',1,1,0,1,'2023-01-03 11:16:02'),
(7,'[\"[495]\"]','2023-01-03','12:00:00','13:00:00',1,52,'put',1,1,0,1,'2023-01-03 11:18:16'),
(8,'[\"[495]\"]','2023-01-03','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-03 11:20:20'),
(9,'[495]','2023-01-03','11:00:00','12:00:00',1,52,'delete',0,1,0,1,'2023-01-03 11:20:29'),
(10,'[\"[495]\"]','2023-01-03','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-03 11:20:30'),
(11,'[\"[495]\"]','2023-01-03','12:00:00','13:00:00',1,52,'put',1,1,0,1,'2023-01-03 11:21:10'),
(12,'[\"495\"]','2023-01-03','12:00:00','13:00:00',1,52,'put',0,1,0,1,'2023-01-03 11:21:23'),
(13,'[\"204\"]','2023-01-03','12:00:00','13:00:00',1,52,'add',0,1,0,1,'2023-01-03 11:21:28'),
(14,'[204]','2023-01-03','12:00:00','13:00:00',1,52,'cross',0,1,0,1,'2023-01-03 11:21:36'),
(15,'[495,204]','2023-01-03','12:00:00','13:00:00',1,52,'delete',1,1,0,1,'2023-01-03 11:21:44'),
(16,'[\"204\"]','2023-01-03','12:00:00','13:00:00',1,52,'disable',1,1,1,1,'2023-01-03 11:21:44'),
(17,'[495,204]','2023-01-03','12:00:00','13:00:00',1,52,'cross',1,1,0,1,'2023-01-03 11:21:53'),
(18,'[495,204]','2023-01-03','12:00:00','13:00:00',1,52,'delete',1,1,0,1,'2023-01-03 11:21:59'),
(19,'[204,495]','2023-01-03','12:00:00','13:00:00',1,52,'cross',1,1,0,1,'2023-01-03 11:22:08'),
(20,'[495]','2023-01-03','11:00:00','12:00:00',1,52,'cross',1,1,0,1,'2023-01-03 11:22:14'),
(21,'[\"262\"]','2023-01-05','09:30:00','11:00:00',1,51,'put',0,1,0,1,'2023-01-04 09:46:51'),
(22,'[\"495\"]','2023-01-05','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-04 09:46:54'),
(23,'[\"390\"]','2023-01-05','12:00:00','13:00:00',1,80,'put',0,1,0,1,'2023-01-04 09:46:57'),
(24,'[\"471\"]','2023-01-05','13:00:00','14:00:00',1,80,'put',0,1,0,1,'2023-01-04 09:47:01'),
(25,'[\"496\"]','2023-01-05','14:00:00','15:00:00',1,80,'put',0,1,0,1,'2023-01-04 09:47:04'),
(26,'[\"495\"]','2023-01-05','14:00:00','15:00:00',1,52,'put',0,1,0,1,'2023-01-04 09:48:06'),
(27,'[\"204\"]','2023-01-05','13:00:00','14:00:00',1,52,'put',0,1,0,1,'2023-01-04 09:48:08'),
(28,'[\"496\"]','2023-01-05','13:00:00','14:00:00',1,81,'put',0,1,0,1,'2023-01-04 09:48:13'),
(29,'[\"391\"]','2023-01-05','13:00:00','14:00:00',1,55,'put',0,1,0,1,'2023-01-04 09:48:15'),
(30,'[\"401\"]','2023-01-05','14:00:00','15:00:00',1,81,'put',0,1,0,1,'2023-01-04 09:48:18'),
(31,'[\"399\"]','2023-01-05','14:00:00','15:00:00',1,55,'put',0,1,0,1,'2023-01-04 09:48:21'),
(32,'[\"399\"]','2023-01-05','15:00:00','16:00:00',1,55,'put',0,1,0,1,'2023-01-04 09:49:56'),
(33,'[\"343\"]','2023-01-05','14:00:00','15:00:00',1,52,'add',0,1,0,1,'2023-01-04 09:55:28'),
(34,'[]','2023-01-09','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-04 09:56:08'),
(35,'[]','2023-01-10','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-04 09:56:08'),
(36,'[]','2023-01-11','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-04 09:56:08'),
(37,'[]','2023-01-12','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-04 09:56:08'),
(38,'[]','2023-01-13','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-04 09:56:08'),
(39,'[]','2023-01-14','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-04 09:56:08'),
(40,'[]','2023-01-15','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-04 09:56:08'),
(41,'[]','2023-01-02','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-06 13:54:10'),
(42,'[]','2023-01-03','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-06 13:54:10'),
(43,'[]','2023-01-04','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-06 13:54:10'),
(44,'[]','2023-01-05','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-06 13:54:10'),
(45,'[]','2023-01-06','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-06 13:54:10'),
(46,'[]','2023-01-07','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-06 13:54:10'),
(47,'[]','2023-01-08','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-06 13:54:10'),
(48,'[\"129\"]','2023-01-11','09:30:00','11:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:24:17'),
(49,'[\"268\"]','2023-01-11','11:00:00','12:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:24:22'),
(50,'[\"106\"]','2023-01-11','12:00:00','13:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:24:25'),
(51,'[\"503\"]','2023-01-11','09:30:00','11:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:24:30'),
(52,'[\"103\"]','2023-01-11','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:24:35'),
(53,'[\"106\"]','2023-01-11','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:26:28'),
(54,'[\"103\"]','2023-01-11','12:00:00','13:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:32:34'),
(55,'[]','2023-01-12','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-11 17:32:51'),
(56,'[\"268\"]','2023-01-12','09:30:00','11:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:33:08'),
(57,'[\"445\"]','2023-01-12','09:30:00','11:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:33:11'),
(58,'[\"445\"]','2023-01-12','11:00:00','12:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:33:17'),
(59,'[\"106\"]','2023-01-12','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:33:20'),
(60,'[\"103\"]','2023-01-12','12:00:00','13:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:33:24'),
(61,'[\"129\"]','2023-01-12','12:00:00','13:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:33:57'),
(62,'[\"503\"]','2023-01-12','13:00:00','14:00:00',1,80,'put',0,1,0,1,'2023-01-11 17:34:17'),
(63,'[\"399\"]','2023-01-12','15:00:00','16:00:00',1,80,'put',0,1,0,1,'2023-01-11 17:34:21'),
(64,'[\"103\"]','2023-01-12','16:00:00','17:00:00',1,80,'put',0,1,0,1,'2023-01-11 17:34:28'),
(65,'[\"496\"]','2023-01-12','15:00:00','16:00:00',1,80,'put',0,1,0,1,'2023-01-11 17:34:51'),
(66,'[\"391\"]','2023-01-12','14:00:00','15:00:00',1,80,'put',0,1,0,1,'2023-01-11 17:34:54'),
(67,'[\"394\"]','2023-01-12','13:00:00','14:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:34:58'),
(68,'[\"399\"]','2023-01-12','14:00:00','15:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:35:01'),
(69,'[\"204\"]','2023-01-12','15:00:00','16:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:35:05'),
(70,'[\"495\"]','2023-01-12','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:35:10'),
(71,'[\"117\"]','2023-01-12','16:00:00','17:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:39:10'),
(72,'[\"390\"]','2023-01-12','12:00:00','13:00:00',1,80,'put',0,1,0,1,'2023-01-11 17:41:14'),
(73,'[\"460\"]','2023-01-12','13:00:00','14:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:41:31'),
(74,'[\"387\"]','2023-01-12','14:00:00','15:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:41:34'),
(75,'[\"387\"]','2023-01-12','15:00:00','16:00:00',1,51,'put',0,1,0,1,'2023-01-11 17:41:37'),
(76,'[\"98\"]','2023-01-12','11:00:00','12:00:00',1,52,'put',0,1,0,1,'2023-01-11 17:41:55'),
(77,'[]','2023-01-09','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 16:37:07'),
(78,'[]','2023-01-10','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 16:37:07'),
(79,'[]','2023-01-11','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 16:37:07'),
(80,'[]','2023-01-12','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 16:37:07'),
(81,'[]','2023-01-13','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 16:37:07'),
(82,'[]','2023-01-14','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 16:37:07'),
(83,'[]','2023-01-15','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 16:37:07'),
(84,'[\"129\"]','2023-01-12','09:30:00','11:00:00',1,51,'put',0,1,0,1,'2023-01-12 16:41:06'),
(85,'[]','2023-01-16','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 17:04:20'),
(86,'[]','2023-01-17','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 17:04:20'),
(87,'[]','2023-01-18','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 17:04:20'),
(88,'[]','2023-01-19','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 17:04:20'),
(89,'[]','2023-01-20','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 17:04:21'),
(90,'[]','2023-01-21','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 17:04:21'),
(91,'[]','2023-01-22','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-12 17:04:21'),
(92,'[]','2023-01-09','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-13 13:59:41'),
(93,'[]','2023-01-10','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-13 13:59:41'),
(94,'[]','2023-01-11','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-13 13:59:41'),
(95,'[]','2023-01-12','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-13 13:59:41'),
(96,'[]','2023-01-13','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-13 13:59:41'),
(97,'[]','2023-01-14','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-13 13:59:41'),
(98,'[]','2023-01-15','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-13 13:59:41'),
(99,'[]','2023-01-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 13:57:03'),
(100,'[]','2023-01-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 13:57:03'),
(101,'[]','2023-01-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 13:57:03'),
(102,'[]','2023-01-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 13:57:03'),
(103,'[]','2023-01-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 13:57:03'),
(104,'[]','2023-01-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 13:57:03'),
(105,'[]','2023-01-29','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 13:57:03'),
(106,'[\"8\"]','2023-01-23','10:00:00','11:30:00',1,4,'put',0,1,0,1,'2023-01-23 15:47:10'),
(107,'[\"8\"]','2023-01-23','11:30:00','13:00:00',1,6,'put',0,1,0,1,'2023-01-23 15:47:12'),
(108,'[\"11\"]','2023-01-23','11:30:00','13:00:00',1,7,'put',0,1,0,1,'2023-01-23 15:47:14'),
(109,'[\"8\"]','2023-01-23','13:00:00','14:30:00',1,8,'put',0,1,0,1,'2023-01-23 15:47:15'),
(110,'[]','2023-01-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-23 15:47:23'),
(111,'[]','2023-01-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 13:50:47'),
(112,'[\"8\"]','2023-01-26','11:30:00','13:00:00',1,4,'put',0,1,0,1,'2023-01-24 13:52:09'),
(113,'[\"11\"]','2023-01-26','13:00:00','14:30:00',1,6,'put',0,1,0,1,'2023-01-24 13:52:11'),
(114,'[\"10\"]','2023-01-26','14:30:00','16:00:00',1,7,'put',0,1,0,1,'2023-01-24 13:52:14'),
(115,'[\"15\"]','2023-01-26','16:00:00','17:30:00',1,8,'put',0,1,0,1,'2023-01-24 13:52:17'),
(116,'[\"3\"]','2023-01-26','14:30:00','16:00:00',1,6,'put',0,1,0,1,'2023-01-24 13:52:28'),
(117,'[\"8\"]','2023-01-26','16:00:00','17:30:00',1,6,'put',0,1,0,1,'2023-01-24 13:53:01'),
(118,'[\"3\"]','2023-01-26','11:30:00','13:00:00',1,4,'put',0,1,0,1,'2023-01-24 13:54:11'),
(119,'[\"4\"]','2023-01-26','16:00:00','17:30:00',1,6,'put',0,1,0,1,'2023-01-24 13:54:19'),
(120,'[]','2023-01-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:11:39'),
(121,'[]','2023-01-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:11:56'),
(122,'[]','2023-01-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:12:03'),
(123,'[]','2023-01-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:12:08'),
(124,'[\"14\"]','2023-01-28','17:30:00','19:00:00',1,4,'put',0,1,0,1,'2023-01-24 14:12:42'),
(125,'[]','2023-01-30','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:01'),
(126,'[]','2023-01-31','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:01'),
(127,'[]','2023-02-01','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:01'),
(128,'[]','2023-02-02','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:01'),
(129,'[]','2023-02-03','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:01'),
(130,'[]','2023-02-04','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:01'),
(131,'[]','2023-02-05','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:01'),
(132,'[]','2023-01-30','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:10'),
(133,'[]','2023-01-31','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:10'),
(134,'[]','2023-02-01','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:10'),
(135,'[]','2023-02-02','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:10'),
(136,'[]','2023-02-03','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:10'),
(137,'[]','2023-02-04','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:10'),
(138,'[]','2023-02-05','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:10'),
(139,'[]','2023-02-06','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:17'),
(140,'[]','2023-02-07','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:17'),
(141,'[]','2023-02-08','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:17'),
(142,'[]','2023-02-09','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:17'),
(143,'[]','2023-02-10','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:17'),
(144,'[]','2023-02-11','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:17'),
(145,'[]','2023-02-12','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:13:17'),
(146,'[\"14\"]','2023-01-26','13:00:00','14:30:00',1,6,'put',0,1,0,1,'2023-01-24 14:15:29'),
(147,'[\"14\"]','2023-01-26','16:00:00','17:30:00',1,4,'put',0,1,0,1,'2023-01-24 14:15:31'),
(148,'[\"14\"]','2023-01-26','17:30:00','19:00:00',1,12,'put',0,1,0,1,'2023-01-24 14:15:35'),
(149,'[]','2023-02-13','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 14:26:34'),
(150,'[]','2023-02-14','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 14:26:34'),
(151,'[]','2023-02-15','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 14:26:34'),
(152,'[]','2023-02-16','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 14:26:34'),
(153,'[]','2023-02-17','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 14:26:34'),
(154,'[]','2023-02-18','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 14:26:34'),
(155,'[]','2023-02-19','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 14:26:34'),
(156,'[\"9\"]','2023-02-14','14:00:00','16:00:00',1,4,'put',0,1,0,1,'2023-01-24 14:28:28'),
(157,'[\"14\"]','2023-02-14','14:00:00','16:00:00',1,6,'put',0,1,0,1,'2023-01-24 14:28:30'),
(158,'[\"6\"]','2023-02-14','14:00:00','16:00:00',1,7,'put',0,1,0,1,'2023-01-24 14:28:32'),
(159,'[\"15\"]','2023-02-14','14:00:00','16:00:00',1,8,'put',0,1,0,1,'2023-01-24 14:28:34'),
(160,'[\"12\"]','2023-02-14','14:00:00','16:00:00',1,11,'put',0,1,0,1,'2023-01-24 14:28:36'),
(161,'[\"3\"]','2023-02-14','14:00:00','16:00:00',1,12,'put',0,1,0,1,'2023-01-24 14:28:38'),
(162,'[\"8\"]','2023-02-14','16:00:00','17:30:00',1,4,'put',0,1,0,1,'2023-01-24 14:28:41'),
(163,'[\"11\"]','2023-02-14','16:00:00','17:30:00',1,6,'put',0,1,0,1,'2023-01-24 14:28:43'),
(164,'[\"10\"]','2023-02-14','16:00:00','17:30:00',1,7,'put',0,1,0,1,'2023-01-24 14:28:45'),
(165,'[\"4\"]','2023-02-14','16:00:00','17:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:28:47'),
(166,'[\"7\"]','2023-02-14','16:00:00','17:30:00',1,11,'put',0,1,0,1,'2023-01-24 14:28:49'),
(167,'[\"5\"]','2023-02-14','16:00:00','17:30:00',1,12,'put',0,1,0,1,'2023-01-24 14:28:52'),
(168,'[\"14\"]','2023-02-14','17:30:00','19:00:00',1,4,'put',0,1,0,1,'2023-01-24 14:28:55'),
(169,'[\"6\"]','2023-02-14','17:30:00','19:00:00',1,6,'put',0,1,0,1,'2023-01-24 14:28:58'),
(170,'[\"15\"]','2023-02-14','17:30:00','19:00:00',1,7,'put',0,1,0,1,'2023-01-24 14:29:01'),
(171,'[\"12\"]','2023-02-14','17:30:00','19:00:00',1,8,'put',0,1,0,1,'2023-01-24 14:29:03'),
(172,'[\"3\"]','2023-02-14','17:30:00','19:00:00',1,11,'put',0,1,0,1,'2023-01-24 14:29:05'),
(173,'[\"9\"]','2023-02-14','17:30:00','19:00:00',1,12,'put',0,1,0,1,'2023-01-24 14:29:07'),
(174,'[\"13\"]','2023-02-14','14:00:00','16:00:00',1,23,'put',0,1,0,1,'2023-01-24 14:29:15'),
(175,'[\"13\"]','2023-02-14','16:00:00','18:00:00',1,23,'put',0,1,0,1,'2023-01-24 14:29:23'),
(176,'[\"8\"]','2023-02-14','14:00:00','15:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:29:27'),
(177,'[\"11\"]','2023-02-14','14:00:00','15:00:00',1,25,'put',0,1,0,1,'2023-01-24 14:29:30'),
(178,'[\"10\"]','2023-02-14','14:00:00','15:00:00',1,26,'put',0,1,0,1,'2023-01-24 14:29:31'),
(179,'[\"4\"]','2023-02-14','14:00:00','15:00:00',1,27,'put',0,1,0,1,'2023-01-24 14:29:34'),
(180,'[\"11\"]','2023-02-14','15:00:00','16:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:29:42'),
(181,'[\"10\"]','2023-02-14','15:00:00','16:00:00',1,25,'put',0,1,0,1,'2023-01-24 14:29:45'),
(182,'[\"4\"]','2023-02-14','15:00:00','16:00:00',1,26,'put',0,1,0,1,'2023-01-24 14:29:48'),
(183,'[\"8\"]','2023-02-14','15:00:00','16:00:00',1,27,'put',0,1,0,1,'2023-01-24 14:29:50'),
(184,'[\"9\"]','2023-02-14','16:00:00','17:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:29:54'),
(185,'[\"14\"]','2023-02-14','16:00:00','17:00:00',1,25,'put',0,1,0,1,'2023-01-24 14:29:56'),
(186,'[\"6\"]','2023-02-14','16:00:00','17:00:00',1,26,'put',0,1,0,1,'2023-01-24 14:29:58'),
(187,'[\"15\"]','2023-02-14','16:00:00','17:00:00',1,27,'put',0,1,0,1,'2023-01-24 14:30:00'),
(188,'[\"11\"]','2023-02-14','19:00:00','20:00:00',1,4,'put',0,1,0,1,'2023-01-24 14:30:12'),
(189,'[\"8\"]','2023-02-14','19:00:00','20:00:00',1,6,'put',0,1,0,1,'2023-01-24 14:30:15'),
(190,'[\"4\"]','2023-02-14','19:00:00','20:00:00',1,7,'put',0,1,0,1,'2023-01-24 14:30:18'),
(191,'[\"10\"]','2023-02-14','19:00:00','20:00:00',1,8,'put',0,1,0,1,'2023-01-24 14:30:21'),
(192,'[\"13\"]','2023-02-14','19:00:00','20:00:00',1,11,'put',0,1,0,1,'2023-01-24 14:30:24'),
(193,'[\"7\"]','2023-02-14','19:00:00','20:00:00',1,12,'put',0,1,0,1,'2023-01-24 14:30:28'),
(194,'[12]','2023-02-14','17:30:00','19:00:00',1,8,'delete',0,1,0,1,'2023-01-24 14:30:58'),
(195,'[\"12\"]','2023-02-14','17:00:00','18:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:31:00'),
(196,'[\"3\"]','2023-02-14','16:00:00','17:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:31:17'),
(197,'[\"4\"]','2023-02-14','17:00:00','18:00:00',1,25,'put',0,1,0,1,'2023-01-24 14:31:21'),
(198,'[]','2023-02-16','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:31:47'),
(199,'[]','2023-02-17','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:31:53'),
(200,'[\"9\"]','2023-02-15','10:00:00','11:30:00',1,4,'put',0,1,0,1,'2023-01-24 14:32:02'),
(201,'[\"5\"]','2023-02-15','10:00:00','11:30:00',1,4,'put',0,1,0,1,'2023-01-24 14:32:08'),
(202,'[\"7\"]','2023-02-15','10:00:00','11:30:00',1,6,'put',0,1,0,1,'2023-01-24 14:32:11'),
(203,'[\"13\"]','2023-02-15','10:00:00','11:30:00',1,7,'put',0,1,0,1,'2023-01-24 14:32:15'),
(204,'[\"4\"]','2023-02-15','10:00:00','11:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:32:17'),
(205,'[\"10\"]','2023-02-15','10:00:00','11:30:00',1,11,'put',0,1,0,1,'2023-01-24 14:32:19'),
(206,'[\"11\"]','2023-02-15','10:00:00','11:30:00',1,12,'put',0,1,0,1,'2023-01-24 14:32:21'),
(207,'[\"8\"]','2023-02-15','11:30:00','13:00:00',1,4,'put',0,1,0,1,'2023-01-24 14:32:24'),
(208,'[\"3\"]','2023-02-15','11:30:00','13:00:00',1,6,'put',0,1,0,1,'2023-01-24 14:32:26'),
(209,'[\"12\"]','2023-02-15','11:30:00','13:00:00',1,7,'put',0,1,0,1,'2023-01-24 14:32:27'),
(210,'[\"15\"]','2023-02-15','11:30:00','13:00:00',1,8,'put',0,1,0,1,'2023-01-24 14:32:29'),
(211,'[\"6\"]','2023-02-15','11:30:00','13:00:00',1,11,'put',0,1,0,1,'2023-01-24 14:32:31'),
(212,'[\"14\"]','2023-02-15','11:30:00','13:00:00',1,12,'put',0,1,0,1,'2023-01-24 14:32:33'),
(213,'[\"9\"]','2023-02-15','13:00:00','14:30:00',1,4,'put',0,1,0,1,'2023-01-24 14:32:36'),
(214,'[\"5\"]','2023-02-15','13:00:00','14:30:00',1,6,'put',0,1,0,1,'2023-01-24 14:32:38'),
(215,'[\"7\"]','2023-02-15','13:00:00','14:30:00',1,7,'put',0,1,0,1,'2023-01-24 14:32:40'),
(216,'[\"10\"]','2023-02-15','13:00:00','14:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:32:42'),
(217,'[\"13\"]','2023-02-15','13:00:00','14:30:00',1,11,'put',0,1,0,1,'2023-01-24 14:32:47'),
(218,'[\"4\"]','2023-02-15','13:00:00','14:30:00',1,12,'put',0,1,0,1,'2023-01-24 14:32:49'),
(219,'[\"9\"]','2023-02-15','09:00:00','10:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:32:56'),
(220,'[\"14\"]','2023-02-15','09:00:00','10:00:00',1,28,'add',0,1,0,1,'2023-01-24 14:32:58'),
(221,'[\"6\"]','2023-02-15','09:00:00','10:00:00',1,25,'put',0,1,0,1,'2023-01-24 14:33:01'),
(222,'[\"15\"]','2023-02-15','09:00:00','10:00:00',1,25,'add',0,1,0,1,'2023-01-24 14:33:08'),
(223,'[\"12\"]','2023-02-15','09:00:00','10:00:00',1,26,'put',0,1,0,1,'2023-01-24 14:33:10'),
(224,'[\"3\"]','2023-02-15','09:00:00','10:00:00',1,26,'add',0,1,0,1,'2023-01-24 14:33:16'),
(225,'[\"8\"]','2023-02-15','09:00:00','10:00:00',1,27,'put',0,1,0,1,'2023-01-24 14:33:18'),
(226,'[\"[9,14]\"]','2023-02-15','10:00:00','11:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:33:20'),
(227,'[\"[6,15]\"]','2023-02-15','10:00:00','11:00:00',1,25,'put',0,1,0,1,'2023-01-24 14:33:21'),
(228,'[\"[12,3]\"]','2023-02-15','10:00:00','11:00:00',1,26,'put',0,1,0,1,'2023-01-24 14:33:22'),
(229,'[\"[8]\"]','2023-02-15','10:00:00','11:00:00',1,27,'put',0,1,0,1,'2023-01-24 14:33:23'),
(230,'[\"3\"]','2023-02-15','14:30:00','16:00:00',1,4,'put',0,1,0,1,'2023-01-24 14:33:31'),
(231,'[\"11\"]','2023-02-15','14:30:00','16:00:00',1,6,'put',0,1,0,1,'2023-01-24 14:33:33'),
(232,'[\"8\"]','2023-02-15','14:30:00','16:00:00',1,7,'put',0,1,0,1,'2023-01-24 14:33:35'),
(233,'[\"12\"]','2023-02-15','14:30:00','16:00:00',1,8,'put',0,1,0,1,'2023-01-24 14:33:37'),
(234,'[\"15\"]','2023-02-15','14:30:00','16:00:00',1,11,'put',0,1,0,1,'2023-01-24 14:33:39'),
(235,'[\"6\"]','2023-02-15','14:30:00','16:00:00',1,12,'put',0,1,0,1,'2023-01-24 14:33:41'),
(236,'[\"14\"]','2023-02-15','16:00:00','17:30:00',1,4,'put',0,1,0,1,'2023-01-24 14:33:44'),
(237,'[\"13\"]','2023-02-15','16:00:00','17:30:00',1,6,'put',0,1,0,1,'2023-01-24 14:33:48'),
(238,'[\"4\"]','2023-02-15','16:00:00','17:30:00',1,7,'put',0,1,0,1,'2023-01-24 14:33:50'),
(239,'[\"5\"]','2023-02-15','16:00:00','17:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:33:52'),
(240,'[\"7\"]','2023-02-15','16:00:00','17:30:00',1,11,'put',0,1,0,1,'2023-01-24 14:33:55'),
(241,'[\"9\"]','2023-02-15','16:00:00','17:30:00',1,12,'put',0,1,0,1,'2023-01-24 14:33:57'),
(242,'[\"12\"]','2023-02-15','17:30:00','19:00:00',1,4,'put',0,1,0,1,'2023-01-24 14:34:00'),
(243,'[\"10\"]','2023-02-15','17:30:00','19:00:00',1,6,'put',0,1,0,1,'2023-01-24 14:34:03'),
(244,'[\"11\"]','2023-02-15','17:30:00','19:00:00',1,7,'put',0,1,0,1,'2023-01-24 14:34:05'),
(245,'[\"8\"]','2023-02-15','17:30:00','19:00:00',1,8,'put',0,1,0,1,'2023-01-24 14:34:07'),
(246,'[\"3\"]','2023-02-15','17:30:00','19:00:00',1,11,'put',0,1,0,1,'2023-01-24 14:34:09'),
(247,'[\"15\"]','2023-02-15','17:30:00','19:00:00',1,12,'put',0,1,0,1,'2023-01-24 14:34:11'),
(248,'[\"11\"]','2023-02-15','12:00:00','13:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:34:22'),
(249,'[\"10\"]','2023-02-15','15:00:00','16:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:34:33'),
(250,'[\"6\"]','2023-02-15','17:00:00','18:00:00',1,28,'put',0,1,0,1,'2023-01-24 14:34:39'),
(251,'[]','2023-02-18','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:34:58'),
(252,'[]','2023-02-20','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:35:34'),
(253,'[]','2023-02-21','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:35:34'),
(254,'[]','2023-02-22','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:35:34'),
(255,'[]','2023-02-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:35:34'),
(256,'[]','2023-02-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:35:34'),
(257,'[]','2023-02-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:35:34'),
(258,'[]','2023-02-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:35:34'),
(259,'[]','2023-02-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:14'),
(260,'[]','2023-02-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:14'),
(261,'[]','2023-03-01','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:14'),
(262,'[]','2023-03-02','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:14'),
(263,'[]','2023-03-03','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:14'),
(264,'[]','2023-03-04','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:14'),
(265,'[]','2023-03-05','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:14'),
(266,'[]','2023-02-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:26'),
(267,'[]','2023-02-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:26'),
(268,'[]','2023-03-01','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:26'),
(269,'[]','2023-03-02','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:26'),
(270,'[]','2023-03-03','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:26'),
(271,'[]','2023-03-04','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:26'),
(272,'[]','2023-03-05','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:26'),
(273,'[]','2023-02-20','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:48'),
(274,'[]','2023-02-21','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:48'),
(275,'[]','2023-02-22','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:48'),
(276,'[]','2023-02-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:48'),
(277,'[]','2023-02-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:48'),
(278,'[]','2023-02-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:48'),
(279,'[]','2023-02-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:37:48'),
(280,'[]','2023-01-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:38:13'),
(281,'[]','2023-01-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:38:13'),
(282,'[]','2023-01-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:38:13'),
(283,'[]','2023-01-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:38:13'),
(284,'[]','2023-01-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:38:13'),
(285,'[]','2023-01-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:38:13'),
(286,'[]','2023-01-29','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 14:38:13'),
(287,'[\"12\"]','2023-01-25','13:00:00','14:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:38:35'),
(288,'[\"3\"]','2023-01-25','13:00:00','14:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:38:38'),
(289,'[\"10\"]','2023-01-25','13:00:00','14:30:00',1,8,'put',0,1,0,1,'2023-01-24 14:38:42'),
(290,'[\"4\"]','2023-01-26','16:00:00','17:00:00',1,27,'put',0,1,0,1,'2023-01-24 15:18:48'),
(291,'[\"15\"]','2023-01-26','16:00:00','17:30:00',1,4,'put',0,1,0,1,'2023-01-24 15:18:50'),
(292,'[10]','2023-01-26','19:00:00','20:00:00',1,8,'delete',0,1,0,1,'2023-01-24 15:18:59'),
(293,'[]','2023-01-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:19:21'),
(294,'[]','2023-01-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:19:21'),
(295,'[]','2023-01-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:19:21'),
(296,'[]','2023-01-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:19:21'),
(297,'[]','2023-01-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:19:21'),
(298,'[]','2023-01-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:19:21'),
(299,'[]','2023-01-29','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:19:21'),
(300,'[\"16\"]','2023-01-26','16:00:00','17:30:00',1,4,'put',0,1,0,1,'2023-01-24 15:20:34'),
(301,'[\"16\"]','2023-01-26','19:00:00','20:00:00',1,6,'put',0,1,0,1,'2023-01-24 15:20:36'),
(302,'[\"16\"]','2023-01-26','14:00:00','15:00:00',1,28,'put',0,1,0,1,'2023-01-24 15:20:38'),
(303,'[\"16\"]','2023-01-26','15:00:00','16:00:00',1,27,'put',0,1,0,1,'2023-01-24 15:20:40'),
(304,'[]','2023-01-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:21:27'),
(305,'[]','2023-01-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:21:27'),
(306,'[]','2023-01-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:21:27'),
(307,'[]','2023-01-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:21:27'),
(308,'[]','2023-01-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:21:27'),
(309,'[]','2023-01-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:21:27'),
(310,'[]','2023-01-29','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:21:27'),
(311,'[]','2023-02-13','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:22:00'),
(312,'[]','2023-02-14','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:22:00'),
(313,'[]','2023-02-15','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:22:00'),
(314,'[]','2023-02-16','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:22:00'),
(315,'[]','2023-02-17','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:22:00'),
(316,'[]','2023-02-18','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:22:00'),
(317,'[]','2023-02-19','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:22:00'),
(318,'[\"3\"]','2023-02-15','10:00:00','11:30:00',1,4,'put',0,1,0,1,'2023-01-24 15:23:44'),
(319,'[\"8\"]','2023-02-15','10:00:00','11:30:00',1,6,'put',0,1,0,1,'2023-01-24 15:23:46'),
(320,'[\"11\"]','2023-02-15','10:00:00','11:30:00',1,7,'put',0,1,0,1,'2023-01-24 15:23:48'),
(321,'[\"10\"]','2023-02-15','10:00:00','11:30:00',1,8,'put',0,1,0,1,'2023-01-24 15:23:50'),
(322,'[\"16\"]','2023-02-15','10:00:00','11:30:00',1,11,'put',0,1,0,1,'2023-01-24 15:24:09'),
(323,'[\"[8]\"]','2023-02-15','11:30:00','13:00:00',1,6,'put',0,1,0,1,'2023-01-24 15:24:22'),
(324,'[\"12\"]','2023-02-15','14:00:00','16:00:00',1,23,'put',0,1,0,1,'2023-01-24 15:25:48'),
(325,'[\"16\"]','2023-01-26','16:00:00','17:30:00',1,4,'put',0,1,0,1,'2023-01-24 15:33:58'),
(326,'[\"16\"]','2023-01-26','19:00:00','20:00:00',1,6,'put',0,1,0,1,'2023-01-24 15:34:01'),
(327,'[\"16\"]','2023-01-26','14:00:00','15:00:00',1,28,'put',0,1,0,1,'2023-01-24 15:34:04'),
(328,'[\"16\"]','2023-01-26','15:00:00','16:00:00',1,27,'put',0,1,0,1,'2023-01-24 15:34:07'),
(329,'[]','2023-01-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:34:17'),
(330,'[]','2023-01-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:34:17'),
(331,'[]','2023-01-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:34:17'),
(332,'[]','2023-01-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:34:17'),
(333,'[]','2023-01-27','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:34:17'),
(334,'[]','2023-01-28','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:34:17'),
(335,'[]','2023-01-29','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:34:17'),
(336,'[]','2023-02-13','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:34:42'),
(337,'[]','2023-02-14','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:34:42'),
(338,'[]','2023-02-15','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:34:42'),
(339,'[]','2023-02-16','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:34:42'),
(340,'[]','2023-02-17','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:34:42'),
(341,'[]','2023-02-18','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:34:42'),
(342,'[]','2023-02-19','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:34:42'),
(343,'[\"9\"]','2023-02-14','14:00:00','16:00:00',1,4,'put',0,1,0,1,'2023-01-24 15:36:31'),
(344,'[\"14\"]','2023-02-14','14:00:00','16:00:00',1,6,'put',0,1,0,1,'2023-01-24 15:36:33'),
(345,'[\"6\"]','2023-02-14','14:00:00','16:00:00',1,7,'put',0,1,0,1,'2023-01-24 15:36:36'),
(346,'[\"15\"]','2023-02-14','14:00:00','16:00:00',1,8,'put',0,1,0,1,'2023-01-24 15:36:39'),
(347,'[\"16\"]','2023-02-14','14:00:00','16:00:00',1,11,'put',0,1,0,1,'2023-01-24 15:36:41'),
(348,'[\"12\"]','2023-02-14','14:00:00','16:00:00',1,12,'put',0,1,0,1,'2023-01-24 15:37:01'),
(349,'[\"11\"]','2023-02-14','16:00:00','18:00:00',1,23,'put',0,1,0,1,'2023-01-24 15:37:56'),
(350,'[\"13\"]','2023-02-14','16:00:00','18:00:00',1,23,'add',0,1,0,1,'2023-01-24 15:37:58'),
(351,'[\"10\"]','2023-02-14','16:00:00','17:30:00',1,12,'put',0,1,0,1,'2023-01-24 15:38:05'),
(352,'[\"3\"]','2023-02-14','16:00:00','17:30:00',1,11,'put',0,1,0,1,'2023-01-24 15:38:07'),
(353,'[\"3\"]','2023-02-14','17:30:00','19:00:00',1,7,'put',0,1,0,1,'2023-01-24 15:40:41'),
(354,'[\"3\"]','2023-02-15','11:30:00','13:00:00',1,4,'put',0,1,0,1,'2023-01-24 15:41:21'),
(355,'[\"3\"]','2023-02-15','14:30:00','16:00:00',1,7,'put',0,1,0,1,'2023-01-24 15:41:24'),
(356,'[\"3\"]','2023-02-15','13:00:00','14:30:00',1,6,'put',0,1,0,1,'2023-01-24 15:41:32'),
(357,'[\"8\"]','2023-02-15','11:30:00','13:00:00',1,6,'put',0,1,0,1,'2023-01-24 15:41:58'),
(358,'[\"11\"]','2023-02-15','11:30:00','13:00:00',1,7,'put',0,1,0,1,'2023-01-24 15:42:00'),
(359,'[\"10\"]','2023-02-15','11:30:00','13:00:00',1,4,'put',0,1,0,1,'2023-01-24 15:42:07'),
(360,'[\"8\"]','2023-02-14','16:00:00','17:30:00',1,4,'put',0,1,0,1,'2023-01-24 15:43:01'),
(361,'[]','2023-03-13','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:43:49'),
(362,'[]','2023-03-14','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:43:49'),
(363,'[]','2023-03-15','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:43:49'),
(364,'[]','2023-03-16','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:43:49'),
(365,'[]','2023-03-17','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:43:49'),
(366,'[]','2023-03-18','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:43:49'),
(367,'[]','2023-03-19','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:43:49'),
(368,'[]','2023-03-13','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:43:57'),
(369,'[]','2023-03-14','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:43:57'),
(370,'[]','2023-03-15','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:43:57'),
(371,'[]','2023-03-16','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:43:57'),
(372,'[]','2023-03-17','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:43:57'),
(373,'[]','2023-03-18','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:43:57'),
(374,'[]','2023-03-19','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:43:57'),
(375,'[]','2023-02-27','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:06'),
(376,'[]','2023-02-28','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:06'),
(377,'[]','2023-03-01','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:06'),
(378,'[]','2023-03-02','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:06'),
(379,'[]','2023-03-03','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:06'),
(380,'[]','2023-03-04','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:06'),
(381,'[]','2023-03-05','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:06'),
(382,'[]','2023-02-20','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:20'),
(383,'[]','2023-02-21','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:20'),
(384,'[]','2023-02-22','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:20'),
(385,'[]','2023-02-23','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:20'),
(386,'[]','2023-02-24','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:20'),
(387,'[]','2023-02-25','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:20'),
(388,'[]','2023-02-26','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 15:44:20'),
(389,'[]','2023-02-20','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:44:51'),
(390,'[]','2023-02-21','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:44:51'),
(391,'[]','2023-02-22','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:44:51'),
(392,'[]','2023-02-23','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:44:51'),
(393,'[]','2023-02-24','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:44:51'),
(394,'[]','2023-02-25','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:44:51'),
(395,'[]','2023-02-26','00:00:00','23:59:59',1,0,'import-model',0,1,0,1,'2023-01-24 15:44:52'),
(396,'[\"16\"]','2023-02-22','10:00:00','11:30:00',1,6,'put',0,1,0,1,'2023-01-24 15:45:49'),
(397,'[\"16\"]','2023-02-22','13:00:00','14:30:00',1,7,'put',0,1,0,1,'2023-01-24 15:45:53'),
(398,'[\"16\"]','2023-02-22','16:00:00','17:30:00',1,11,'put',0,1,0,1,'2023-01-24 15:45:55'),
(399,'[\"3\"]','2023-02-28','14:00:00','16:00:00',1,37,'put',0,1,0,1,'2023-01-24 16:00:39'),
(400,'[\"12\"]','2023-02-28','14:00:00','16:00:00',1,4,'put',0,1,0,1,'2023-01-24 16:00:40'),
(401,'[\"8\"]','2023-02-28','14:00:00','16:00:00',1,6,'put',0,1,0,1,'2023-01-24 16:00:42'),
(402,'[\"11\"]','2023-02-28','16:00:00','17:30:00',1,37,'put',0,1,0,1,'2023-01-24 16:00:46'),
(403,'[]','2023-02-27','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:52'),
(404,'[]','2023-02-28','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:52'),
(405,'[]','2023-03-01','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:52'),
(406,'[]','2023-03-02','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:52'),
(407,'[]','2023-03-03','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:52'),
(408,'[]','2023-03-04','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:52'),
(409,'[]','2023-03-05','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:52'),
(410,'[]','2023-02-20','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:59'),
(411,'[]','2023-02-21','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:59'),
(412,'[]','2023-02-22','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:59'),
(413,'[]','2023-02-23','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:59'),
(414,'[]','2023-02-24','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:59'),
(415,'[]','2023-02-25','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:59'),
(416,'[]','2023-02-26','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:00:59'),
(417,'[]','2023-02-13','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:01:08'),
(418,'[]','2023-02-14','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:01:08'),
(419,'[]','2023-02-15','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:01:08'),
(420,'[]','2023-02-16','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:01:08'),
(421,'[]','2023-02-17','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:01:08'),
(422,'[]','2023-02-18','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:01:08'),
(423,'[]','2023-02-19','00:00:00','23:59:59',1,0,'delete-planning',0,1,0,1,'2023-01-24 16:01:08');
/*!40000 ALTER TABLE `pl_position_history` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste`
--

DROP TABLE IF EXISTS `pl_poste`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `poste` int(11) NOT NULL DEFAULT 0,
  `absent` tinyint(1) NOT NULL DEFAULT 0,
  `chgt_login` int(4) DEFAULT NULL,
  `chgt_time` datetime DEFAULT NULL,
  `debut` time NOT NULL,
  `fin` time NOT NULL,
  `supprime` tinyint(1) NOT NULL DEFAULT 0,
  `site` int(3) DEFAULT 1,
  `grise` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `site` (`site`)
) ENGINE=MyISAM AUTO_INCREMENT=3306 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste`
--

LOCK TABLES `pl_poste` WRITE;
/*!40000 ALTER TABLE `pl_poste` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste` VALUES
(1,9,'2022-09-27',4,0,1,'2022-09-27 15:43:43','10:00:00','11:30:00',0,1,0),
(2,9,'2022-09-27',6,0,1,'2022-09-27 15:43:45','11:30:00','13:00:00',0,1,0),
(9,10,'2022-09-27',7,0,1,'2022-09-27 15:44:04','13:00:00','14:30:00',0,1,0),
(4,6,'2022-09-27',6,0,1,'2022-09-27 15:43:50','10:00:00','11:30:00',0,1,0),
(5,6,'2022-09-27',7,0,1,'2022-09-27 15:43:52','11:30:00','13:00:00',0,1,0),
(7,3,'2022-09-27',4,0,1,'2022-09-27 15:44:00','13:00:00','14:30:00',0,1,0),
(8,8,'2022-09-27',6,0,1,'2022-09-27 15:44:02','13:00:00','14:30:00',0,1,0),
(10,11,'2022-09-27',8,0,1,'2022-09-27 15:44:06','13:00:00','14:30:00',0,1,0),
(11,4,'2022-09-27',7,0,1,'2022-09-27 15:44:09','10:00:00','11:30:00',0,1,0),
(12,7,'2022-09-27',4,0,1,'2022-09-27 15:44:11','11:30:00','13:00:00',0,1,0),
(13,10,'2022-09-27',8,0,1,'2022-09-27 15:44:13','10:00:00','11:30:00',0,1,0),
(14,5,'2022-09-27',8,0,1,'2022-09-27 15:44:16','11:30:00','13:00:00',0,1,0),
(15,6,'2022-09-27',4,0,1,'2022-09-27 15:44:21','14:30:00','16:00:00',0,1,0),
(16,4,'2022-09-27',6,0,1,'2022-09-27 15:44:24','14:30:00','16:00:00',0,1,0),
(17,7,'2022-09-27',7,0,1,'2022-09-27 15:44:26','14:30:00','16:00:00',0,1,0),
(18,9,'2022-09-27',8,0,1,'2022-09-27 15:44:28','14:30:00','16:00:00',0,1,0),
(19,5,'2022-09-27',11,0,1,'2022-09-27 15:44:34','14:30:00','16:00:00',0,1,0),
(20,3,'2022-09-27',11,0,1,'2022-09-27 15:44:42','10:00:00','11:30:00',0,1,0),
(21,8,'2022-09-27',4,0,1,'2022-09-27 15:44:55','16:00:00','17:30:00',0,1,0),
(22,3,'2022-09-27',6,0,1,'2022-09-27 15:44:57','16:00:00','17:30:00',0,1,0),
(23,11,'2022-09-27',7,0,1,'2022-09-27 15:45:00','16:00:00','17:30:00',0,1,0),
(24,4,'2022-09-27',8,0,1,'2022-09-27 15:45:06','16:00:00','17:30:00',0,1,0),
(25,10,'2022-09-27',11,0,1,'2022-09-27 15:45:09','16:00:00','17:30:00',0,1,0),
(26,3,'2022-09-27',7,0,1,'2022-09-27 15:45:17','17:30:00','19:00:00',0,1,0),
(27,6,'2022-09-27',8,0,1,'2022-09-27 15:45:20','17:30:00','19:00:00',0,1,0),
(28,9,'2022-09-27',11,0,1,'2022-09-27 15:45:23','17:30:00','19:00:00',0,1,0),
(29,5,'2022-09-27',4,0,1,'2022-09-27 15:45:26','17:30:00','19:00:00',0,1,0),
(30,7,'2022-09-27',6,0,1,'2022-09-27 15:45:29','17:30:00','19:00:00',0,1,0),
(31,8,'2022-09-27',12,0,1,'2022-09-27 15:45:33','17:30:00','19:00:00',0,1,0),
(32,5,'2022-09-27',12,0,1,'2022-09-27 15:45:38','16:00:00','17:30:00',0,1,0),
(33,11,'2022-09-27',12,0,1,'2022-09-27 15:45:44','10:00:00','11:30:00',0,1,0),
(34,4,'2022-09-27',11,0,1,'2022-09-27 15:45:49','11:30:00','13:00:00',0,1,0),
(130,4,'2022-10-13',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(129,11,'2022-10-13',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(128,5,'2022-10-13',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(127,8,'2022-10-13',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(171,3,'2022-10-13',6,0,3,'2022-09-27 15:53:35','17:30:00','19:00:00',0,1,0),
(125,5,'2022-10-13',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(124,9,'2022-10-13',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(123,6,'2022-10-13',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(170,10,'2022-10-13',7,0,3,'2022-09-27 15:53:33','17:30:00','19:00:00',0,1,0),
(121,10,'2022-10-13',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(120,4,'2022-10-13',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(119,11,'2022-10-13',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(169,7,'2022-10-13',6,0,3,'2022-09-27 15:53:24','16:00:00','17:30:00',0,1,0),
(117,8,'2022-10-13',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(163,7,'2022-10-13',11,0,3,'2022-09-27 15:53:07','10:00:00','11:30:00',0,1,0),
(115,5,'2022-10-13',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(114,9,'2022-10-13',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(167,3,'2022-10-13',7,0,3,'2022-09-27 15:53:19','14:30:00','16:00:00',0,1,0),
(112,4,'2022-10-13',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(111,6,'2022-10-13',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(110,5,'2022-10-13',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(164,3,'2022-10-13',8,0,3,'2022-09-27 15:53:09','10:00:00','11:30:00',0,1,0),
(165,3,'2022-10-13',4,0,3,'2022-09-27 15:53:14','11:30:00','13:00:00',0,1,0),
(107,4,'2022-10-13',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(106,11,'2022-10-13',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(105,8,'2022-10-13',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(166,7,'2022-10-13',4,0,3,'2022-09-27 15:53:16','13:00:00','14:30:00',0,1,0),
(103,6,'2022-10-13',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(102,6,'2022-10-13',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(101,10,'2022-10-13',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(100,9,'2022-10-13',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(99,9,'2022-10-13',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(131,9,'2022-10-14',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(132,9,'2022-10-14',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(133,10,'2022-10-14',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(134,6,'2022-10-14',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(135,6,'2022-10-14',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(136,3,'2022-10-14',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(137,8,'2022-10-14',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(138,11,'2022-10-14',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(139,4,'2022-10-14',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(140,7,'2022-10-14',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(141,10,'2022-10-14',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(142,5,'2022-10-14',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(143,6,'2022-10-14',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(144,4,'2022-10-14',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(145,7,'2022-10-14',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(146,9,'2022-10-14',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(147,5,'2022-10-14',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(148,3,'2022-10-14',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(149,8,'2022-10-14',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(150,3,'2022-10-14',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(151,11,'2022-10-14',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(152,4,'2022-10-14',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(153,10,'2022-10-14',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(154,3,'2022-10-14',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(155,6,'2022-10-14',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(156,9,'2022-10-14',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(157,5,'2022-10-14',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(158,7,'2022-10-14',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(159,8,'2022-10-14',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(160,5,'2022-10-14',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(161,11,'2022-10-14',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(162,4,'2022-10-14',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(168,7,'2022-10-13',12,0,3,'2022-09-27 15:53:22','14:30:00','16:00:00',0,1,0),
(213,8,'2022-10-11',4,0,3,'2022-09-27 15:58:11','10:00:00','11:30:00',0,1,0),
(217,9,'2022-09-28',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(174,10,'2022-10-11',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(175,6,'2022-10-11',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(176,6,'2022-10-11',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(206,9,'2022-10-11',4,0,3,'2022-09-27 15:57:19','13:00:00','14:30:00',0,1,0),
(178,8,'2022-10-11',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(179,11,'2022-10-11',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(180,4,'2022-10-11',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(181,7,'2022-10-11',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(182,10,'2022-10-11',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(183,5,'2022-10-11',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(184,6,'2022-10-11',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(185,4,'2022-10-11',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(186,7,'2022-10-11',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(208,3,'2022-10-11',8,0,3,'2022-09-27 15:57:26','14:30:00','16:00:00',0,1,0),
(188,5,'2022-10-11',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(204,7,'2022-10-11',11,0,3,'2022-09-27 15:57:14','10:00:00','11:30:00',0,1,0),
(190,8,'2022-10-11',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(209,9,'2022-10-11',6,0,3,'2022-09-27 15:57:29','16:00:00','17:30:00',0,1,0),
(192,11,'2022-10-11',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(215,3,'2022-10-11',8,0,3,'2022-09-27 15:58:47','16:00:00','17:30:00',0,1,0),
(194,10,'2022-10-11',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(211,9,'2022-10-11',7,0,3,'2022-09-27 15:57:37','17:30:00','19:00:00',0,1,0),
(216,4,'2022-10-11',8,0,3,'2022-09-27 15:58:50','17:30:00','19:00:00',0,1,0),
(210,11,'2022-10-11',11,0,3,'2022-09-27 15:57:33','17:30:00','19:00:00',0,1,0),
(198,5,'2022-10-11',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(199,7,'2022-10-11',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(200,8,'2022-10-11',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(201,5,'2022-10-11',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(202,11,'2022-10-11',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(203,4,'2022-10-11',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(218,9,'2022-09-28',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(219,10,'2022-09-28',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(220,6,'2022-09-28',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(221,6,'2022-09-28',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(222,3,'2022-09-28',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(223,8,'2022-09-28',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(224,11,'2022-09-28',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(225,4,'2022-09-28',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(226,7,'2022-09-28',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(227,10,'2022-09-28',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(228,5,'2022-09-28',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(229,6,'2022-09-28',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(230,4,'2022-09-28',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(231,7,'2022-09-28',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(232,9,'2022-09-28',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(233,5,'2022-09-28',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(234,3,'2022-09-28',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(235,8,'2022-09-28',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(236,3,'2022-09-28',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(237,11,'2022-09-28',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(238,4,'2022-09-28',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(239,10,'2022-09-28',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(240,3,'2022-09-28',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(241,6,'2022-09-28',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(242,9,'2022-09-28',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(243,5,'2022-09-28',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(244,7,'2022-09-28',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(245,8,'2022-09-28',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(246,5,'2022-09-28',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(247,11,'2022-09-28',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(248,4,'2022-09-28',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(249,12,'2022-09-28',12,0,1,'2022-09-28 12:15:18','11:30:00','13:00:00',0,1,0),
(250,12,'2022-09-28',28,0,1,'2022-09-28 12:15:21','10:00:00','11:00:00',0,1,0),
(251,15,'2022-09-28',25,0,1,'2022-09-28 12:15:24','10:00:00','11:00:00',0,1,0),
(252,14,'2022-09-28',28,0,1,'2022-09-28 12:15:27','11:00:00','12:00:00',0,1,0),
(253,13,'2022-09-28',25,0,1,'2022-09-28 12:15:29','11:00:00','12:00:00',0,1,0),
(255,14,'2022-09-28',25,0,1,'2022-09-28 12:15:36','12:00:00','13:00:00',0,1,0),
(256,15,'2022-09-28',28,0,1,'2022-09-28 12:15:40','12:00:00','13:00:00',0,1,0),
(257,15,'2022-09-28',6,0,3,'2022-09-28 12:18:56','19:00:00','20:00:00',0,1,0),
(265,6,'2023-01-23',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(264,10,'2023-01-23',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(263,9,'2023-01-23',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(262,9,'2023-01-23',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(266,6,'2023-01-23',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(267,3,'2023-01-23',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(268,8,'2023-01-23',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(269,11,'2023-01-23',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(270,4,'2023-01-23',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(271,7,'2023-01-23',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(272,10,'2023-01-23',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(273,5,'2023-01-23',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(274,6,'2023-01-23',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(275,4,'2023-01-23',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(276,7,'2023-01-23',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(277,9,'2023-01-23',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(278,5,'2023-01-23',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(279,3,'2023-01-23',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(280,8,'2023-01-23',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(281,3,'2023-01-23',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(282,11,'2023-01-23',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(283,4,'2023-01-23',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(284,10,'2023-01-23',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(285,3,'2023-01-23',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(286,6,'2023-01-23',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(287,9,'2023-01-23',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(288,5,'2023-01-23',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(289,7,'2023-01-23',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(290,8,'2023-01-23',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(291,5,'2023-01-23',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(292,11,'2023-01-23',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(293,4,'2023-01-23',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(2856,9,'2023-01-24',4,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2855,14,'2023-01-24',6,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2854,6,'2023-01-24',7,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2853,15,'2023-01-24',8,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2852,12,'2023-01-24',11,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2851,3,'2023-01-24',12,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2850,8,'2023-01-24',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2849,11,'2023-01-24',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2848,10,'2023-01-24',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2847,3,'2023-01-24',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2846,7,'2023-01-24',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2845,5,'2023-01-24',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2844,14,'2023-01-24',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2843,6,'2023-01-24',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2842,15,'2023-01-24',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2841,12,'2023-01-24',28,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(2840,3,'2023-01-24',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2839,9,'2023-01-24',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2838,13,'2023-01-24',23,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2837,13,'2023-01-24',23,0,NULL,'0000-00-00 00:00:00','16:00:00','18:00:00',0,1,0),
(2836,8,'2023-01-24',28,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2835,11,'2023-01-24',25,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2834,10,'2023-01-24',26,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2833,4,'2023-01-24',27,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2832,11,'2023-01-24',28,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2831,10,'2023-01-24',25,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2830,4,'2023-01-24',26,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2829,8,'2023-01-24',27,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2828,9,'2023-01-24',28,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2827,14,'2023-01-24',25,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2826,6,'2023-01-24',26,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2825,15,'2023-01-24',27,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2948,9,'2023-01-26',4,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2947,14,'2023-01-26',6,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2946,6,'2023-01-26',7,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2945,15,'2023-01-26',8,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2944,12,'2023-01-26',11,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2943,3,'2023-01-26',12,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2942,8,'2023-01-26',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2941,11,'2023-01-26',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2940,10,'2023-01-26',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2939,3,'2023-01-26',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2938,7,'2023-01-26',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2937,5,'2023-01-26',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2936,14,'2023-01-26',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2935,6,'2023-01-26',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2934,15,'2023-01-26',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2933,12,'2023-01-26',28,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(2932,3,'2023-01-26',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2931,9,'2023-01-26',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2930,13,'2023-01-26',23,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2929,13,'2023-01-26',23,0,NULL,'0000-00-00 00:00:00','16:00:00','18:00:00',0,1,0),
(2928,8,'2023-01-26',28,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2927,11,'2023-01-26',25,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2926,10,'2023-01-26',26,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2925,4,'2023-01-26',27,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2924,11,'2023-01-26',28,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2923,10,'2023-01-26',25,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2922,4,'2023-01-26',26,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2921,8,'2023-01-26',27,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2920,9,'2023-01-26',28,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2919,14,'2023-01-26',25,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2918,6,'2023-01-26',26,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2917,15,'2023-01-26',27,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2909,5,'2023-01-25',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(2908,7,'2023-01-25',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(2907,13,'2023-01-25',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(2906,4,'2023-01-25',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(2905,10,'2023-01-25',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(2904,11,'2023-01-25',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(2903,8,'2023-01-25',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(2902,3,'2023-01-25',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(2901,12,'2023-01-25',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(2900,15,'2023-01-25',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(2899,6,'2023-01-25',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(2898,14,'2023-01-25',12,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(2897,9,'2023-01-25',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(2896,5,'2023-01-25',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(2895,7,'2023-01-25',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(2894,10,'2023-01-25',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(2893,13,'2023-01-25',11,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(2892,4,'2023-01-25',12,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(2891,9,'2023-01-25',28,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(2890,14,'2023-01-25',28,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(2889,6,'2023-01-25',25,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(2888,15,'2023-01-25',25,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(2887,12,'2023-01-25',26,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(2886,3,'2023-01-25',26,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(2885,8,'2023-01-25',27,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(2884,9,'2023-01-25',28,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(2883,14,'2023-01-25',28,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(2882,6,'2023-01-25',25,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(2881,15,'2023-01-25',25,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(2880,12,'2023-01-25',26,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(2879,3,'2023-01-25',26,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(2878,8,'2023-01-25',27,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(2987,9,'2023-01-27',4,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2986,14,'2023-01-27',6,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2985,6,'2023-01-27',7,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2984,15,'2023-01-27',8,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2983,12,'2023-01-27',11,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2982,3,'2023-01-27',12,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2981,8,'2023-01-27',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2980,11,'2023-01-27',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2979,10,'2023-01-27',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2978,3,'2023-01-27',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2977,7,'2023-01-27',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2976,5,'2023-01-27',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2975,14,'2023-01-27',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2974,6,'2023-01-27',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2973,15,'2023-01-27',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2972,12,'2023-01-27',28,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(2971,3,'2023-01-27',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2970,9,'2023-01-27',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2969,13,'2023-01-27',23,0,NULL,'0000-00-00 00:00:00','14:00:00','16:00:00',0,1,0),
(2968,13,'2023-01-27',23,0,NULL,'0000-00-00 00:00:00','16:00:00','18:00:00',0,1,0),
(2967,8,'2023-01-27',28,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2966,11,'2023-01-27',25,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2965,10,'2023-01-27',26,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2964,4,'2023-01-27',27,0,NULL,'0000-00-00 00:00:00','14:00:00','15:00:00',0,1,0),
(2963,11,'2023-01-27',28,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2962,10,'2023-01-27',25,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2961,4,'2023-01-27',26,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2960,8,'2023-01-27',27,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2959,9,'2023-01-27',28,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2958,14,'2023-01-27',25,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2957,6,'2023-01-27',26,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(2956,15,'2023-01-27',27,0,NULL,'0000-00-00 00:00:00','16:00:00','17:00:00',0,1,0),
(3040,15,'2023-01-28',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(3039,11,'2023-01-28',28,0,NULL,'0000-00-00 00:00:00','12:00:00','13:00:00',0,1,0),
(3038,10,'2023-01-28',28,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(3037,6,'2023-01-28',28,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(3036,7,'2023-01-28',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(3035,10,'2023-01-28',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(3034,13,'2023-01-28',11,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(3033,4,'2023-01-28',12,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(3032,9,'2023-01-28',28,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(3031,14,'2023-01-28',28,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(3030,6,'2023-01-28',25,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(3029,15,'2023-01-28',25,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(3028,12,'2023-01-28',26,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(3027,3,'2023-01-28',26,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(3026,8,'2023-01-28',27,0,NULL,'0000-00-00 00:00:00','09:00:00','10:00:00',0,1,0),
(3025,9,'2023-01-28',28,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(3024,14,'2023-01-28',28,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(3023,6,'2023-01-28',25,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(3022,15,'2023-01-28',25,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(3021,12,'2023-01-28',26,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(3020,3,'2023-01-28',26,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(3019,8,'2023-01-28',27,0,NULL,'0000-00-00 00:00:00','10:00:00','11:00:00',0,1,0),
(3018,3,'2023-01-28',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(3017,11,'2023-01-28',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(3016,5,'2023-01-28',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(3015,7,'2023-01-28',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(3014,13,'2023-01-28',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(3013,4,'2023-01-28',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(3012,10,'2023-01-28',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(3011,11,'2023-01-28',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(3010,8,'2023-01-28',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(3009,3,'2023-01-28',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(686,4,'2023-01-30',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(685,11,'2023-01-30',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(684,5,'2023-01-30',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(683,8,'2023-01-30',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(682,7,'2023-01-30',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(681,5,'2023-01-30',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(680,9,'2023-01-30',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(679,6,'2023-01-30',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(678,3,'2023-01-30',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(677,10,'2023-01-30',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(676,4,'2023-01-30',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(675,11,'2023-01-30',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(674,3,'2023-01-30',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(673,8,'2023-01-30',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(672,3,'2023-01-30',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(671,5,'2023-01-30',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(670,9,'2023-01-30',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(669,7,'2023-01-30',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(668,4,'2023-01-30',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(667,6,'2023-01-30',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(666,5,'2023-01-30',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(665,10,'2023-01-30',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(664,7,'2023-01-30',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(663,4,'2023-01-30',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(662,11,'2023-01-30',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(661,8,'2023-01-30',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(660,3,'2023-01-30',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(659,6,'2023-01-30',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(658,9,'2023-01-30',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(657,9,'2023-01-30',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(656,10,'2023-01-30',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(655,6,'2023-01-30',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(718,4,'2023-01-31',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(717,11,'2023-01-31',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(716,5,'2023-01-31',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(715,8,'2023-01-31',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(714,7,'2023-01-31',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(713,5,'2023-01-31',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(712,9,'2023-01-31',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(711,6,'2023-01-31',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(710,3,'2023-01-31',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(709,10,'2023-01-31',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(708,4,'2023-01-31',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(707,11,'2023-01-31',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(706,3,'2023-01-31',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(705,8,'2023-01-31',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(704,3,'2023-01-31',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(703,5,'2023-01-31',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(702,9,'2023-01-31',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(701,7,'2023-01-31',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(700,4,'2023-01-31',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(699,6,'2023-01-31',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(698,5,'2023-01-31',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(697,10,'2023-01-31',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(696,7,'2023-01-31',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(695,4,'2023-01-31',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(694,11,'2023-01-31',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(693,8,'2023-01-31',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(692,3,'2023-01-31',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(691,6,'2023-01-31',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(690,6,'2023-01-31',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(689,10,'2023-01-31',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(688,9,'2023-01-31',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(687,9,'2023-01-31',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(750,4,'2023-02-01',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(749,11,'2023-02-01',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(748,5,'2023-02-01',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(747,8,'2023-02-01',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(746,7,'2023-02-01',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(745,5,'2023-02-01',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(744,9,'2023-02-01',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(743,6,'2023-02-01',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(742,3,'2023-02-01',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(741,10,'2023-02-01',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(740,4,'2023-02-01',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(739,11,'2023-02-01',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(738,3,'2023-02-01',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(737,8,'2023-02-01',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(736,3,'2023-02-01',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(735,5,'2023-02-01',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(734,9,'2023-02-01',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(733,7,'2023-02-01',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(732,4,'2023-02-01',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(731,6,'2023-02-01',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(730,5,'2023-02-01',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(729,10,'2023-02-01',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(728,7,'2023-02-01',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(727,4,'2023-02-01',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(726,11,'2023-02-01',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(725,8,'2023-02-01',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(724,3,'2023-02-01',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(723,6,'2023-02-01',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(722,6,'2023-02-01',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(721,10,'2023-02-01',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(720,9,'2023-02-01',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(719,9,'2023-02-01',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(782,4,'2023-02-02',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(781,11,'2023-02-02',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(780,5,'2023-02-02',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(779,8,'2023-02-02',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(778,7,'2023-02-02',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(777,5,'2023-02-02',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(776,9,'2023-02-02',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(775,6,'2023-02-02',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(774,3,'2023-02-02',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(773,10,'2023-02-02',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(772,4,'2023-02-02',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(771,11,'2023-02-02',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(770,3,'2023-02-02',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(769,8,'2023-02-02',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(768,3,'2023-02-02',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(767,5,'2023-02-02',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(766,9,'2023-02-02',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(765,7,'2023-02-02',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(764,4,'2023-02-02',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(763,6,'2023-02-02',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(762,5,'2023-02-02',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(761,10,'2023-02-02',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(760,7,'2023-02-02',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(759,4,'2023-02-02',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(758,11,'2023-02-02',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(757,8,'2023-02-02',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(756,9,'2023-02-02',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(755,9,'2023-02-02',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(754,10,'2023-02-02',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(753,6,'2023-02-02',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(752,6,'2023-02-02',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(751,3,'2023-02-02',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(814,4,'2023-02-03',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(813,11,'2023-02-03',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(812,5,'2023-02-03',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(811,8,'2023-02-03',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(810,7,'2023-02-03',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(809,5,'2023-02-03',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(808,9,'2023-02-03',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(807,6,'2023-02-03',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(806,3,'2023-02-03',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(805,10,'2023-02-03',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(804,4,'2023-02-03',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(803,11,'2023-02-03',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(802,3,'2023-02-03',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(801,8,'2023-02-03',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(800,3,'2023-02-03',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(799,5,'2023-02-03',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(798,9,'2023-02-03',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(797,7,'2023-02-03',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(796,4,'2023-02-03',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(795,6,'2023-02-03',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(794,5,'2023-02-03',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(793,10,'2023-02-03',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(792,7,'2023-02-03',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(791,4,'2023-02-03',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(790,11,'2023-02-03',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(789,8,'2023-02-03',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(788,3,'2023-02-03',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(787,6,'2023-02-03',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(786,6,'2023-02-03',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(785,10,'2023-02-03',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(784,9,'2023-02-03',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(783,9,'2023-02-03',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(846,4,'2023-02-04',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(845,11,'2023-02-04',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(844,5,'2023-02-04',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(843,8,'2023-02-04',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(842,7,'2023-02-04',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(841,5,'2023-02-04',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(840,9,'2023-02-04',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(839,6,'2023-02-04',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(838,3,'2023-02-04',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(837,10,'2023-02-04',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(836,4,'2023-02-04',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(835,11,'2023-02-04',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(834,3,'2023-02-04',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(833,8,'2023-02-04',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(832,3,'2023-02-04',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(831,5,'2023-02-04',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(830,9,'2023-02-04',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(829,7,'2023-02-04',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(828,4,'2023-02-04',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(827,6,'2023-02-04',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(826,5,'2023-02-04',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(825,10,'2023-02-04',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(824,7,'2023-02-04',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(823,4,'2023-02-04',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(822,11,'2023-02-04',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(821,8,'2023-02-04',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(820,3,'2023-02-04',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(819,6,'2023-02-04',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(818,6,'2023-02-04',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(817,10,'2023-02-04',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(816,9,'2023-02-04',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(815,9,'2023-02-04',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(847,4,'2023-02-06',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(848,11,'2023-02-06',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(849,5,'2023-02-06',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(850,8,'2023-02-06',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(851,7,'2023-02-06',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(852,5,'2023-02-06',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(853,9,'2023-02-06',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(854,6,'2023-02-06',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(855,3,'2023-02-06',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(856,10,'2023-02-06',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(857,4,'2023-02-06',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(858,11,'2023-02-06',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(859,3,'2023-02-06',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(860,8,'2023-02-06',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(861,3,'2023-02-06',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(862,5,'2023-02-06',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(863,9,'2023-02-06',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(864,7,'2023-02-06',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(865,4,'2023-02-06',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(866,6,'2023-02-06',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(867,5,'2023-02-06',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(868,10,'2023-02-06',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(869,7,'2023-02-06',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(870,4,'2023-02-06',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(871,11,'2023-02-06',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(872,8,'2023-02-06',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(873,3,'2023-02-06',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(874,6,'2023-02-06',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(875,9,'2023-02-06',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(876,9,'2023-02-06',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(877,10,'2023-02-06',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(878,6,'2023-02-06',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(879,4,'2023-02-07',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(880,11,'2023-02-07',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(881,5,'2023-02-07',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(882,8,'2023-02-07',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(883,7,'2023-02-07',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(884,5,'2023-02-07',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(885,9,'2023-02-07',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(886,6,'2023-02-07',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(887,3,'2023-02-07',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(888,10,'2023-02-07',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(889,4,'2023-02-07',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(890,11,'2023-02-07',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(891,3,'2023-02-07',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(892,8,'2023-02-07',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(893,3,'2023-02-07',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(894,5,'2023-02-07',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(895,9,'2023-02-07',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(896,7,'2023-02-07',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(897,4,'2023-02-07',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(898,6,'2023-02-07',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(899,5,'2023-02-07',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(900,10,'2023-02-07',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(901,7,'2023-02-07',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(902,4,'2023-02-07',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(903,11,'2023-02-07',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(904,8,'2023-02-07',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(905,3,'2023-02-07',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(906,6,'2023-02-07',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(907,6,'2023-02-07',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(908,10,'2023-02-07',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(909,9,'2023-02-07',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(910,9,'2023-02-07',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(911,4,'2023-02-08',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(912,11,'2023-02-08',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(913,5,'2023-02-08',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(914,8,'2023-02-08',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(915,7,'2023-02-08',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(916,5,'2023-02-08',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(917,9,'2023-02-08',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(918,6,'2023-02-08',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(919,3,'2023-02-08',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(920,10,'2023-02-08',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(921,4,'2023-02-08',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(922,11,'2023-02-08',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(923,3,'2023-02-08',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(924,8,'2023-02-08',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(925,3,'2023-02-08',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(926,5,'2023-02-08',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(927,9,'2023-02-08',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(928,7,'2023-02-08',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(929,4,'2023-02-08',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(930,6,'2023-02-08',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(931,5,'2023-02-08',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(932,10,'2023-02-08',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(933,7,'2023-02-08',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(934,4,'2023-02-08',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(935,11,'2023-02-08',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(936,8,'2023-02-08',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(937,3,'2023-02-08',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(938,6,'2023-02-08',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(939,6,'2023-02-08',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(940,10,'2023-02-08',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(941,9,'2023-02-08',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(942,9,'2023-02-08',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(943,4,'2023-02-09',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(944,11,'2023-02-09',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(945,5,'2023-02-09',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(946,8,'2023-02-09',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(947,7,'2023-02-09',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(948,5,'2023-02-09',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(949,9,'2023-02-09',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(950,6,'2023-02-09',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(951,3,'2023-02-09',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(952,10,'2023-02-09',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(953,4,'2023-02-09',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(954,11,'2023-02-09',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(955,3,'2023-02-09',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(956,8,'2023-02-09',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(957,3,'2023-02-09',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(958,5,'2023-02-09',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(959,9,'2023-02-09',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(960,7,'2023-02-09',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(961,4,'2023-02-09',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(962,6,'2023-02-09',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(963,5,'2023-02-09',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(964,10,'2023-02-09',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(965,7,'2023-02-09',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(966,4,'2023-02-09',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(967,11,'2023-02-09',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(968,8,'2023-02-09',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(969,9,'2023-02-09',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(970,9,'2023-02-09',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(971,10,'2023-02-09',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(972,6,'2023-02-09',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(973,6,'2023-02-09',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(974,3,'2023-02-09',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(975,4,'2023-02-10',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(976,11,'2023-02-10',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(977,5,'2023-02-10',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(978,8,'2023-02-10',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(979,7,'2023-02-10',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(980,5,'2023-02-10',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(981,9,'2023-02-10',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(982,6,'2023-02-10',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(983,3,'2023-02-10',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(984,10,'2023-02-10',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(985,4,'2023-02-10',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(986,11,'2023-02-10',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(987,3,'2023-02-10',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(988,8,'2023-02-10',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(989,3,'2023-02-10',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(990,5,'2023-02-10',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(991,9,'2023-02-10',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(992,7,'2023-02-10',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(993,4,'2023-02-10',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(994,6,'2023-02-10',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(995,5,'2023-02-10',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(996,10,'2023-02-10',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(997,7,'2023-02-10',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(998,4,'2023-02-10',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(999,11,'2023-02-10',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1000,8,'2023-02-10',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1001,3,'2023-02-10',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1002,6,'2023-02-10',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(1003,6,'2023-02-10',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(1004,10,'2023-02-10',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1005,9,'2023-02-10',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(1006,9,'2023-02-10',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(1007,4,'2023-02-11',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(1008,11,'2023-02-11',12,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(1009,5,'2023-02-11',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(1010,8,'2023-02-11',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(1011,7,'2023-02-11',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(1012,14,'2023-02-11',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(1013,9,'2023-02-11',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(1014,6,'2023-02-11',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(1015,3,'2023-02-11',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(1016,10,'2023-02-11',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(1017,4,'2023-02-11',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(1018,11,'2023-02-11',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(1019,3,'2023-02-11',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(1020,8,'2023-02-11',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(1021,3,'2023-02-11',11,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(1022,5,'2023-02-11',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(1023,9,'2023-02-11',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(1024,7,'2023-02-11',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(1025,4,'2023-02-11',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(1026,6,'2023-02-11',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(1027,5,'2023-02-11',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(1028,10,'2023-02-11',8,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(1029,7,'2023-02-11',4,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(1030,4,'2023-02-11',7,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(1031,11,'2023-02-11',8,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1032,8,'2023-02-11',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1033,3,'2023-02-11',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1034,6,'2023-02-11',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(1035,6,'2023-02-11',6,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(1036,10,'2023-02-11',7,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(1037,9,'2023-02-11',6,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(1038,9,'2023-02-11',4,0,NULL,'0000-00-00 00:00:00','10:00:00','11:30:00',0,1,0),
(2824,11,'2023-01-24',4,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2823,8,'2023-01-24',6,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2822,4,'2023-01-24',7,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2821,10,'2023-01-24',8,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2820,13,'2023-01-24',11,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2819,7,'2023-01-24',12,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2818,4,'2023-01-24',25,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(2877,3,'2023-01-25',4,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2876,11,'2023-01-25',6,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2875,8,'2023-01-25',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2874,12,'2023-01-25',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2873,15,'2023-01-25',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2872,6,'2023-01-25',12,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2871,14,'2023-01-25',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2870,13,'2023-01-25',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2869,4,'2023-01-25',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2868,5,'2023-01-25',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2867,7,'2023-01-25',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2866,9,'2023-01-25',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2865,12,'2023-01-25',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2864,10,'2023-01-25',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2863,11,'2023-01-25',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2862,8,'2023-01-25',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2861,3,'2023-01-25',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2860,15,'2023-01-25',12,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2859,11,'2023-01-25',28,0,NULL,'0000-00-00 00:00:00','12:00:00','13:00:00',0,1,0),
(2858,10,'2023-01-25',28,0,NULL,'0000-00-00 00:00:00','15:00:00','16:00:00',0,1,0),
(2857,6,'2023-01-25',28,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(2916,11,'2023-01-26',4,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2915,8,'2023-01-26',6,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2914,4,'2023-01-26',7,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2913,10,'2023-01-26',8,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2912,13,'2023-01-26',11,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2911,7,'2023-01-26',12,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2910,4,'2023-01-26',25,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(2955,11,'2023-01-27',4,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2954,8,'2023-01-27',6,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2953,4,'2023-01-27',7,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2952,10,'2023-01-27',8,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2951,13,'2023-01-27',11,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2950,7,'2023-01-27',12,2,NULL,'0000-00-00 00:00:00','19:00:00','20:00:00',0,1,0),
(2949,4,'2023-01-27',25,0,NULL,'0000-00-00 00:00:00','17:00:00','18:00:00',0,1,0),
(3008,12,'2023-01-28',7,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(3007,15,'2023-01-28',8,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(3006,6,'2023-01-28',11,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(3005,14,'2023-01-28',12,0,NULL,'0000-00-00 00:00:00','11:30:00','13:00:00',0,1,0),
(3004,9,'2023-01-28',4,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(3003,5,'2023-01-28',6,0,NULL,'0000-00-00 00:00:00','13:00:00','14:30:00',0,1,0),
(3002,8,'2023-01-28',7,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(3001,12,'2023-01-28',8,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(3000,15,'2023-01-28',11,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2999,6,'2023-01-28',12,0,NULL,'0000-00-00 00:00:00','14:30:00','16:00:00',0,1,0),
(2998,14,'2023-01-28',4,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2997,13,'2023-01-28',6,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2996,4,'2023-01-28',7,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2995,5,'2023-01-28',8,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2994,7,'2023-01-28',11,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2993,9,'2023-01-28',12,0,NULL,'0000-00-00 00:00:00','16:00:00','17:30:00',0,1,0),
(2992,12,'2023-01-28',4,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2991,10,'2023-01-28',6,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2990,11,'2023-01-28',7,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2989,8,'2023-01-28',8,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0),
(2988,3,'2023-01-28',11,0,NULL,'0000-00-00 00:00:00','17:30:00','19:00:00',0,1,0);
/*!40000 ALTER TABLE `pl_poste` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_cellules`
--

DROP TABLE IF EXISTS `pl_poste_cellules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_cellules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `tableau` int(11) NOT NULL,
  `ligne` int(11) NOT NULL,
  `colonne` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_cellules`
--

LOCK TABLES `pl_poste_cellules` WRITE;
/*!40000 ALTER TABLE `pl_poste_cellules` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_cellules` VALUES
(66,1,3,3,12),
(65,1,3,2,12),
(64,1,3,1,12),
(63,1,3,0,12),
(62,1,2,0,4),
(61,1,2,0,1),
(60,1,1,11,9),
(59,1,1,11,1),
(58,1,1,10,9),
(57,1,1,10,1),
(56,1,1,7,9),
(55,1,1,7,1),
(54,1,1,6,1),
(53,1,1,5,1),
(52,1,1,3,1);
/*!40000 ALTER TABLE `pl_poste_cellules` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_horaires`
--

DROP TABLE IF EXISTS `pl_poste_horaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_horaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debut` time NOT NULL DEFAULT '00:00:00',
  `fin` time NOT NULL DEFAULT '00:00:00',
  `tableau` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_horaires`
--

LOCK TABLES `pl_poste_horaires` WRITE;
/*!40000 ALTER TABLE `pl_poste_horaires` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_horaires` VALUES
(1,'09:00:00','10:00:00',1,1),
(2,'10:00:00','11:30:00',1,1),
(3,'11:30:00','13:00:00',1,1),
(4,'13:00:00','14:30:00',1,1),
(5,'14:30:00','16:00:00',1,1),
(6,'16:00:00','17:30:00',1,1),
(7,'17:30:00','19:00:00',1,1),
(8,'19:00:00','20:00:00',1,1),
(9,'20:00:00','22:00:00',1,1),
(10,'09:00:00','14:00:00',2,1),
(11,'14:00:00','16:00:00',2,1),
(12,'16:00:00','18:00:00',2,1),
(13,'18:00:00','22:00:00',2,1),
(14,'09:00:00','10:00:00',3,1),
(15,'10:00:00','11:00:00',3,1),
(16,'11:00:00','12:00:00',3,1),
(17,'12:00:00','13:00:00',3,1),
(18,'13:00:00','14:00:00',3,1),
(19,'14:00:00','15:00:00',3,1),
(20,'15:00:00','16:00:00',3,1),
(21,'16:00:00','17:00:00',3,1),
(22,'17:00:00','18:00:00',3,1),
(23,'18:00:00','19:00:00',3,1),
(24,'19:00:00','20:00:00',3,1),
(25,'20:00:00','22:00:00',3,1),
(27,'09:00:00','17:00:00',0,2),
(122,'20:00:00','22:00:00',3,3),
(121,'19:00:00','20:00:00',3,3),
(120,'18:00:00','19:00:00',3,3),
(119,'17:00:00','18:00:00',3,3),
(118,'16:00:00','17:00:00',3,3),
(117,'15:00:00','16:00:00',3,3),
(116,'14:00:00','15:00:00',3,3),
(115,'18:00:00','22:00:00',2,3),
(114,'16:00:00','18:00:00',2,3),
(113,'14:00:00','16:00:00',2,3),
(112,'20:00:00','22:00:00',1,3),
(111,'19:00:00','20:00:00',1,3),
(110,'17:30:00','19:00:00',1,3),
(109,'16:00:00','17:30:00',1,3),
(108,'14:00:00','16:00:00',1,3),
(155,'13:00:00','14:00:00',3,5),
(154,'20:00:00','22:00:00',3,5),
(153,'19:00:00','20:00:00',3,5),
(152,'18:00:00','19:00:00',3,5),
(151,'17:00:00','18:00:00',3,5),
(150,'16:00:00','17:00:00',3,5),
(149,'15:00:00','16:00:00',3,5),
(148,'14:00:00','15:00:00',3,5),
(147,'13:00:00','14:00:00',2,5),
(146,'18:00:00','22:00:00',2,5),
(145,'16:00:00','18:00:00',2,5),
(144,'14:00:00','16:00:00',2,5),
(143,'13:00:00','14:00:00',1,5),
(142,'20:00:00','22:00:00',1,5),
(141,'19:00:00','20:00:00',1,5),
(140,'17:30:00','19:00:00',1,5),
(139,'16:00:00','17:30:00',1,5),
(138,'14:00:00','16:00:00',1,5),
(97,'20:00:00','22:00:00',1,4),
(96,'19:00:00','20:00:00',1,4),
(95,'17:30:00','19:00:00',1,4),
(94,'16:00:00','17:30:00',1,4),
(93,'14:00:00','16:00:00',1,4);
/*!40000 ALTER TABLE `pl_poste_horaires` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_lignes`
--

DROP TABLE IF EXISTS `pl_poste_lignes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `tableau` int(11) NOT NULL,
  `ligne` int(11) NOT NULL,
  `poste` varchar(30) NOT NULL,
  `type` varchar(6) NOT NULL DEFAULT 'poste',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_lignes`
--

LOCK TABLES `pl_poste_lignes` WRITE;
/*!40000 ALTER TABLE `pl_poste_lignes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_lignes` VALUES
(54,1,3,3,'27','poste'),
(53,1,3,2,'26','poste'),
(52,1,3,1,'25','poste'),
(51,1,3,0,'28','poste'),
(50,1,2,0,'23','poste'),
(49,1,1,11,'12','poste'),
(48,1,1,10,'11','poste'),
(47,1,1,7,'8','poste'),
(46,1,1,6,'7','poste'),
(45,1,1,5,'6','poste'),
(44,1,1,3,'4','poste'),
(43,1,3,0,'vert','classe'),
(42,1,3,0,'Rangement','titre'),
(41,1,2,0,'rouge','classe'),
(40,1,2,0,'Réserve','titre'),
(39,1,1,0,'jaune','classe'),
(38,1,1,0,'Mezzanine','titre'),
(55,2,0,0,'Fermeture','titre'),
(56,2,0,0,'rouge','classe'),
(103,3,2,0,'23','poste'),
(102,3,1,5,'12','poste'),
(101,3,1,4,'11','poste'),
(100,3,1,3,'8','poste'),
(99,3,1,2,'7','poste'),
(98,3,1,1,'6','poste'),
(97,3,1,0,'4','poste'),
(96,3,3,0,'vert','classe'),
(95,3,3,0,'Rangement','titre'),
(94,3,2,0,'rouge','classe'),
(93,3,2,0,'Réserve','titre'),
(92,3,1,0,'jaune','classe'),
(91,3,1,0,'Mezzanine','titre'),
(183,5,3,2,'26','poste'),
(132,4,1,5,'12','poste'),
(131,4,1,4,'11','poste'),
(130,4,1,3,'8','poste'),
(129,4,1,2,'7','poste'),
(128,4,1,1,'6','poste'),
(127,4,1,0,'4','poste'),
(182,5,3,1,'25','poste'),
(181,5,3,0,'28','poste'),
(180,5,2,0,'23','poste'),
(126,4,1,0,'jaune','classe'),
(125,4,1,0,'Mezzanine','titre'),
(104,3,3,0,'28','poste'),
(105,3,3,1,'25','poste'),
(106,3,3,2,'26','poste'),
(107,3,3,3,'27','poste'),
(179,5,1,6,'12','poste'),
(178,5,1,5,'11','poste'),
(177,5,1,4,'8','poste'),
(176,5,1,3,'7','poste'),
(175,5,1,2,'6','poste'),
(174,5,1,1,'4','poste'),
(173,5,3,0,'vert','classe'),
(172,5,3,0,'Rangement','titre'),
(171,5,2,0,'rouge','classe'),
(170,5,2,0,'Réserve','titre'),
(169,5,1,0,'bleu','classe'),
(168,5,1,0,'Service Public','titre'),
(184,5,3,3,'27','poste');
/*!40000 ALTER TABLE `pl_poste_lignes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_modeles`
--

DROP TABLE IF EXISTS `pl_poste_modeles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_modeles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL DEFAULT 0,
  `perso_id` int(11) NOT NULL,
  `poste` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `debut` time NOT NULL,
  `fin` time NOT NULL,
  `tableau` varchar(20) NOT NULL,
  `jour` varchar(10) NOT NULL,
  `site` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1479 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_modeles`
--

LOCK TABLES `pl_poste_modeles` WRITE;
/*!40000 ALTER TABLE `pl_poste_modeles` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_modeles` VALUES
(1,1,9,4,'','10:00:00','11:30:00','','',1),
(2,1,9,6,'','11:30:00','13:00:00','','',1),
(3,1,10,7,'','13:00:00','14:30:00','','',1),
(4,1,6,6,'','10:00:00','11:30:00','','',1),
(5,1,6,7,'','11:30:00','13:00:00','','',1),
(6,1,3,4,'','13:00:00','14:30:00','','',1),
(7,1,8,6,'','13:00:00','14:30:00','','',1),
(8,1,11,8,'','13:00:00','14:30:00','','',1),
(9,1,4,7,'','10:00:00','11:30:00','','',1),
(10,1,7,4,'','11:30:00','13:00:00','','',1),
(11,1,10,8,'','10:00:00','11:30:00','','',1),
(12,1,5,8,'','11:30:00','13:00:00','','',1),
(13,1,6,4,'','14:30:00','16:00:00','','',1),
(14,1,4,6,'','14:30:00','16:00:00','','',1),
(15,1,7,7,'','14:30:00','16:00:00','','',1),
(16,1,9,8,'','14:30:00','16:00:00','','',1),
(17,1,5,11,'','14:30:00','16:00:00','','',1),
(18,1,3,11,'','10:00:00','11:30:00','','',1),
(19,1,8,4,'','16:00:00','17:30:00','','',1),
(20,1,3,6,'','16:00:00','17:30:00','','',1),
(21,1,11,7,'','16:00:00','17:30:00','','',1),
(22,1,4,8,'','16:00:00','17:30:00','','',1),
(23,1,10,11,'','16:00:00','17:30:00','','',1),
(24,1,3,7,'','17:30:00','19:00:00','','',1),
(25,1,6,8,'','17:30:00','19:00:00','','',1),
(26,1,9,11,'','17:30:00','19:00:00','','',1),
(27,1,5,4,'','17:30:00','19:00:00','','',1),
(28,1,7,6,'','17:30:00','19:00:00','','',1),
(29,1,8,12,'','17:30:00','19:00:00','','',1),
(30,1,5,12,'','16:00:00','17:30:00','','',1),
(31,1,11,12,'','10:00:00','11:30:00','','',1),
(32,1,4,11,'','11:30:00','13:00:00','','',1),
(1478,5,10,4,'','11:30:00','13:00:00','','3',1),
(1477,5,3,7,'','14:30:00','16:00:00','','3',1),
(1476,5,3,6,'','13:00:00','14:30:00','','3',1),
(1475,5,8,6,'','11:30:00','13:00:00','','3',1),
(1474,5,11,7,'','11:30:00','13:00:00','','3',1),
(1473,5,9,4,'','14:00:00','16:00:00','','2',1),
(1472,5,14,6,'','14:00:00','16:00:00','','2',1),
(1471,5,6,7,'','14:00:00','16:00:00','','2',1),
(1470,5,15,8,'','14:00:00','16:00:00','','2',1),
(1469,5,16,11,'','14:00:00','16:00:00','','2',1),
(1468,5,12,12,'','14:00:00','16:00:00','','2',1),
(1467,5,11,23,'','16:00:00','18:00:00','','2',1),
(1466,5,13,23,'','16:00:00','18:00:00','','2',1),
(1465,5,10,12,'','16:00:00','17:30:00','','2',1),
(1464,5,3,11,'','16:00:00','17:30:00','','2',1),
(1437,4,3,11,'','17:30:00','19:00:00','','6',1),
(1436,4,8,8,'','17:30:00','19:00:00','','6',1),
(1435,4,11,7,'','17:30:00','19:00:00','','6',1),
(1434,4,10,6,'','17:30:00','19:00:00','','6',1),
(1433,4,12,4,'','17:30:00','19:00:00','','6',1),
(1432,4,9,12,'','16:00:00','17:30:00','','6',1),
(1431,4,7,11,'','16:00:00','17:30:00','','6',1),
(1430,4,5,8,'','16:00:00','17:30:00','','6',1),
(1429,4,4,7,'','16:00:00','17:30:00','','6',1),
(1428,4,13,6,'','16:00:00','17:30:00','','6',1),
(1427,4,14,4,'','16:00:00','17:30:00','','6',1),
(1426,4,6,12,'','14:30:00','16:00:00','','6',1),
(1425,4,15,11,'','14:30:00','16:00:00','','6',1),
(1424,4,12,8,'','14:30:00','16:00:00','','6',1),
(1423,4,8,7,'','14:30:00','16:00:00','','6',1),
(1422,4,5,6,'','13:00:00','14:30:00','','6',1),
(1421,4,9,4,'','13:00:00','14:30:00','','6',1),
(1420,4,14,12,'','11:30:00','13:00:00','','6',1),
(1419,4,6,11,'','11:30:00','13:00:00','','6',1),
(1418,4,15,8,'','11:30:00','13:00:00','','6',1),
(1417,4,12,7,'','11:30:00','13:00:00','','6',1),
(1416,4,3,6,'','11:30:00','13:00:00','','6',1),
(1415,4,8,4,'','11:30:00','13:00:00','','6',1),
(1414,4,11,12,'','10:00:00','11:30:00','','6',1),
(1413,4,10,11,'','10:00:00','11:30:00','','6',1),
(1412,4,4,8,'','10:00:00','11:30:00','','6',1),
(1411,4,13,7,'','10:00:00','11:30:00','','6',1),
(1410,4,7,6,'','10:00:00','11:30:00','','6',1),
(1409,4,5,4,'','10:00:00','11:30:00','','6',1),
(1408,4,4,25,'','17:00:00','18:00:00','','5',1),
(1407,4,7,12,'','19:00:00','20:00:00','','5',1),
(1406,4,13,11,'','19:00:00','20:00:00','','5',1),
(1405,4,10,8,'','19:00:00','20:00:00','','5',1),
(1404,4,4,7,'','19:00:00','20:00:00','','5',1),
(1403,4,8,6,'','19:00:00','20:00:00','','5',1),
(1402,4,11,4,'','19:00:00','20:00:00','','5',1),
(1401,4,15,27,'','16:00:00','17:00:00','','5',1),
(1400,4,6,26,'','16:00:00','17:00:00','','5',1),
(1399,4,14,25,'','16:00:00','17:00:00','','5',1),
(1398,4,9,28,'','16:00:00','17:00:00','','5',1),
(1397,4,8,27,'','15:00:00','16:00:00','','5',1),
(1396,4,4,26,'','15:00:00','16:00:00','','5',1),
(1395,4,10,25,'','15:00:00','16:00:00','','5',1),
(1394,4,11,28,'','15:00:00','16:00:00','','5',1),
(1393,4,4,27,'','14:00:00','15:00:00','','5',1),
(1392,4,10,26,'','14:00:00','15:00:00','','5',1),
(1391,4,11,25,'','14:00:00','15:00:00','','5',1),
(1390,4,8,28,'','14:00:00','15:00:00','','5',1),
(1389,4,13,23,'','16:00:00','18:00:00','','5',1),
(1388,4,13,23,'','14:00:00','16:00:00','','5',1),
(1387,4,9,12,'','17:30:00','19:00:00','','5',1),
(1386,4,3,11,'','17:30:00','19:00:00','','5',1),
(1385,4,12,28,'','17:00:00','18:00:00','','5',1),
(1384,4,15,7,'','17:30:00','19:00:00','','5',1),
(1383,4,6,6,'','17:30:00','19:00:00','','5',1),
(1382,4,14,4,'','17:30:00','19:00:00','','5',1),
(1381,4,5,12,'','16:00:00','17:30:00','','5',1),
(1380,4,7,11,'','16:00:00','17:30:00','','5',1),
(1379,4,3,8,'','16:00:00','17:30:00','','5',1),
(1378,4,10,7,'','16:00:00','17:30:00','','5',1),
(1377,4,11,6,'','16:00:00','17:30:00','','5',1),
(1376,4,8,4,'','16:00:00','17:30:00','','5',1),
(1375,4,3,12,'','14:00:00','16:00:00','','5',1),
(1374,4,12,11,'','14:00:00','16:00:00','','5',1),
(1373,4,15,8,'','14:00:00','16:00:00','','5',1),
(1372,4,6,7,'','14:00:00','16:00:00','','5',1),
(1371,4,14,6,'','14:00:00','16:00:00','','5',1),
(1370,4,9,4,'','14:00:00','16:00:00','','5',1),
(1369,4,4,25,'','17:00:00','18:00:00','','4',1),
(1368,4,7,12,'','19:00:00','20:00:00','','4',1),
(1367,4,13,11,'','19:00:00','20:00:00','','4',1),
(1366,4,10,8,'','19:00:00','20:00:00','','4',1),
(1365,4,4,7,'','19:00:00','20:00:00','','4',1),
(1364,4,8,6,'','19:00:00','20:00:00','','4',1),
(1363,4,11,4,'','19:00:00','20:00:00','','4',1),
(1362,4,15,27,'','16:00:00','17:00:00','','4',1),
(1361,4,6,26,'','16:00:00','17:00:00','','4',1),
(1360,4,14,25,'','16:00:00','17:00:00','','4',1),
(1359,4,9,28,'','16:00:00','17:00:00','','4',1),
(1358,4,8,27,'','15:00:00','16:00:00','','4',1),
(1357,4,4,26,'','15:00:00','16:00:00','','4',1),
(1356,4,10,25,'','15:00:00','16:00:00','','4',1),
(1355,4,11,28,'','15:00:00','16:00:00','','4',1),
(1354,4,4,27,'','14:00:00','15:00:00','','4',1),
(1353,4,10,26,'','14:00:00','15:00:00','','4',1),
(1352,4,11,25,'','14:00:00','15:00:00','','4',1),
(1351,4,8,28,'','14:00:00','15:00:00','','4',1),
(1350,4,13,23,'','16:00:00','18:00:00','','4',1),
(1349,4,13,23,'','14:00:00','16:00:00','','4',1),
(1348,4,9,12,'','17:30:00','19:00:00','','4',1),
(1347,4,3,11,'','17:30:00','19:00:00','','4',1),
(1346,4,12,28,'','17:00:00','18:00:00','','4',1),
(1345,4,15,7,'','17:30:00','19:00:00','','4',1),
(1344,4,6,6,'','17:30:00','19:00:00','','4',1),
(1343,4,14,4,'','17:30:00','19:00:00','','4',1),
(1342,4,5,12,'','16:00:00','17:30:00','','4',1),
(1341,4,7,11,'','16:00:00','17:30:00','','4',1),
(1340,4,3,8,'','16:00:00','17:30:00','','4',1),
(1339,4,10,7,'','16:00:00','17:30:00','','4',1),
(1338,4,11,6,'','16:00:00','17:30:00','','4',1),
(1337,4,8,4,'','16:00:00','17:30:00','','4',1),
(1336,4,3,12,'','14:00:00','16:00:00','','4',1),
(1335,4,12,11,'','14:00:00','16:00:00','','4',1),
(1334,4,15,8,'','14:00:00','16:00:00','','4',1),
(1333,4,6,7,'','14:00:00','16:00:00','','4',1),
(1332,4,14,6,'','14:00:00','16:00:00','','4',1),
(1331,4,9,4,'','14:00:00','16:00:00','','4',1),
(1330,4,6,28,'','17:00:00','18:00:00','','3',1),
(1329,4,10,28,'','15:00:00','16:00:00','','3',1),
(1328,4,11,28,'','12:00:00','13:00:00','','3',1),
(1327,4,15,12,'','17:30:00','19:00:00','','3',1),
(1326,4,3,11,'','17:30:00','19:00:00','','3',1),
(1325,4,8,8,'','17:30:00','19:00:00','','3',1),
(1324,4,11,7,'','17:30:00','19:00:00','','3',1),
(1323,4,10,6,'','17:30:00','19:00:00','','3',1),
(1322,4,12,4,'','17:30:00','19:00:00','','3',1),
(1321,4,9,12,'','16:00:00','17:30:00','','3',1),
(1320,4,7,11,'','16:00:00','17:30:00','','3',1),
(1319,4,5,8,'','16:00:00','17:30:00','','3',1),
(1318,4,4,7,'','16:00:00','17:30:00','','3',1),
(1317,4,13,6,'','16:00:00','17:30:00','','3',1),
(1316,4,14,4,'','16:00:00','17:30:00','','3',1),
(1315,4,6,12,'','14:30:00','16:00:00','','3',1),
(1314,4,15,11,'','14:30:00','16:00:00','','3',1),
(1313,4,12,8,'','14:30:00','16:00:00','','3',1),
(1312,4,8,7,'','14:30:00','16:00:00','','3',1),
(1311,4,11,6,'','14:30:00','16:00:00','','3',1),
(1310,4,3,4,'','14:30:00','16:00:00','','3',1),
(1309,4,8,27,'','10:00:00','11:00:00','','3',1),
(1308,4,3,26,'','10:00:00','11:00:00','','3',1),
(1307,4,12,26,'','10:00:00','11:00:00','','3',1),
(1306,4,15,25,'','10:00:00','11:00:00','','3',1),
(1305,4,6,25,'','10:00:00','11:00:00','','3',1),
(1304,4,14,28,'','10:00:00','11:00:00','','3',1),
(1303,4,9,28,'','10:00:00','11:00:00','','3',1),
(1302,4,8,27,'','09:00:00','10:00:00','','3',1),
(1301,4,3,26,'','09:00:00','10:00:00','','3',1),
(1300,4,12,26,'','09:00:00','10:00:00','','3',1),
(1299,4,15,25,'','09:00:00','10:00:00','','3',1),
(1298,4,6,25,'','09:00:00','10:00:00','','3',1),
(1297,4,14,28,'','09:00:00','10:00:00','','3',1),
(1296,4,9,28,'','09:00:00','10:00:00','','3',1),
(1295,4,4,12,'','13:00:00','14:30:00','','3',1),
(1294,4,13,11,'','13:00:00','14:30:00','','3',1),
(1293,4,10,8,'','13:00:00','14:30:00','','3',1),
(1292,4,7,7,'','13:00:00','14:30:00','','3',1),
(1291,4,5,6,'','13:00:00','14:30:00','','3',1),
(1290,4,9,4,'','13:00:00','14:30:00','','3',1),
(1289,4,14,12,'','11:30:00','13:00:00','','3',1),
(1288,4,6,11,'','11:30:00','13:00:00','','3',1),
(1287,4,15,8,'','11:30:00','13:00:00','','3',1),
(1286,4,12,7,'','11:30:00','13:00:00','','3',1),
(1285,4,3,6,'','11:30:00','13:00:00','','3',1),
(1284,4,8,4,'','11:30:00','13:00:00','','3',1),
(1283,4,11,12,'','10:00:00','11:30:00','','3',1),
(1282,4,10,11,'','10:00:00','11:30:00','','3',1),
(1281,4,4,8,'','10:00:00','11:30:00','','3',1),
(1280,4,13,7,'','10:00:00','11:30:00','','3',1),
(1279,4,7,6,'','10:00:00','11:30:00','','3',1),
(1278,4,5,4,'','10:00:00','11:30:00','','3',1),
(1277,4,4,25,'','17:00:00','18:00:00','','2',1),
(1276,4,7,12,'','19:00:00','20:00:00','','2',1),
(1275,4,13,11,'','19:00:00','20:00:00','','2',1),
(1274,4,10,8,'','19:00:00','20:00:00','','2',1),
(1273,4,4,7,'','19:00:00','20:00:00','','2',1),
(1272,4,8,6,'','19:00:00','20:00:00','','2',1),
(1271,4,11,4,'','19:00:00','20:00:00','','2',1),
(1270,4,15,27,'','16:00:00','17:00:00','','2',1),
(1269,4,6,26,'','16:00:00','17:00:00','','2',1),
(1268,4,14,25,'','16:00:00','17:00:00','','2',1),
(1267,4,9,28,'','16:00:00','17:00:00','','2',1),
(1266,4,8,27,'','15:00:00','16:00:00','','2',1),
(1265,4,4,26,'','15:00:00','16:00:00','','2',1),
(1264,4,10,25,'','15:00:00','16:00:00','','2',1),
(1263,4,11,28,'','15:00:00','16:00:00','','2',1),
(1262,4,4,27,'','14:00:00','15:00:00','','2',1),
(1261,4,10,26,'','14:00:00','15:00:00','','2',1),
(1260,4,11,25,'','14:00:00','15:00:00','','2',1),
(1259,4,8,28,'','14:00:00','15:00:00','','2',1),
(1258,4,13,23,'','16:00:00','18:00:00','','2',1),
(1257,4,13,23,'','14:00:00','16:00:00','','2',1),
(1256,4,9,12,'','17:30:00','19:00:00','','2',1),
(1255,4,3,11,'','17:30:00','19:00:00','','2',1),
(1254,4,12,28,'','17:00:00','18:00:00','','2',1),
(1253,4,15,7,'','17:30:00','19:00:00','','2',1),
(1252,4,6,6,'','17:30:00','19:00:00','','2',1),
(1251,4,14,4,'','17:30:00','19:00:00','','2',1),
(1250,4,5,12,'','16:00:00','17:30:00','','2',1),
(1249,4,7,11,'','16:00:00','17:30:00','','2',1),
(1248,4,3,8,'','16:00:00','17:30:00','','2',1),
(1247,4,10,7,'','16:00:00','17:30:00','','2',1),
(1219,3,11,6,'','14:30:00','16:00:00','','6',1),
(1218,3,3,4,'','14:30:00','16:00:00','','6',1),
(1217,3,8,27,'','10:00:00','11:00:00','','6',1),
(1216,3,3,26,'','10:00:00','11:00:00','','6',1),
(1215,3,12,26,'','10:00:00','11:00:00','','6',1),
(1214,3,15,25,'','10:00:00','11:00:00','','6',1),
(1213,3,6,25,'','10:00:00','11:00:00','','6',1),
(1212,3,14,28,'','10:00:00','11:00:00','','6',1),
(1211,3,9,28,'','10:00:00','11:00:00','','6',1),
(1210,3,8,27,'','09:00:00','10:00:00','','6',1),
(1209,3,3,26,'','09:00:00','10:00:00','','6',1),
(1208,3,12,26,'','09:00:00','10:00:00','','6',1),
(1207,3,15,25,'','09:00:00','10:00:00','','6',1),
(1206,3,6,25,'','09:00:00','10:00:00','','6',1),
(1205,3,14,28,'','09:00:00','10:00:00','','6',1),
(1204,3,9,28,'','09:00:00','10:00:00','','6',1),
(1203,3,4,12,'','13:00:00','14:30:00','','6',1),
(1202,3,13,11,'','13:00:00','14:30:00','','6',1),
(1201,3,10,8,'','13:00:00','14:30:00','','6',1),
(1200,3,7,7,'','13:00:00','14:30:00','','6',1),
(1246,4,11,6,'','16:00:00','17:30:00','','2',1),
(1245,4,8,4,'','16:00:00','17:30:00','','2',1),
(1244,4,3,12,'','14:00:00','16:00:00','','2',1),
(1243,4,12,11,'','14:00:00','16:00:00','','2',1),
(1242,4,15,8,'','14:00:00','16:00:00','','2',1),
(1241,4,6,7,'','14:00:00','16:00:00','','2',1),
(1240,4,14,6,'','14:00:00','16:00:00','','2',1),
(1239,4,9,4,'','14:00:00','16:00:00','','2',1),
(1238,3,6,28,'','17:00:00','18:00:00','','6',1),
(1237,3,10,28,'','15:00:00','16:00:00','','6',1),
(1236,3,11,28,'','12:00:00','13:00:00','','6',1),
(1235,3,15,12,'','17:30:00','19:00:00','','6',1),
(1234,3,3,11,'','17:30:00','19:00:00','','6',1),
(1233,3,8,8,'','17:30:00','19:00:00','','6',1),
(1232,3,11,7,'','17:30:00','19:00:00','','6',1),
(1231,3,10,6,'','17:30:00','19:00:00','','6',1),
(1230,3,12,4,'','17:30:00','19:00:00','','6',1),
(1229,3,9,12,'','16:00:00','17:30:00','','6',1),
(1228,3,7,11,'','16:00:00','17:30:00','','6',1),
(1227,3,5,8,'','16:00:00','17:30:00','','6',1),
(1226,3,4,7,'','16:00:00','17:30:00','','6',1),
(1225,3,13,6,'','16:00:00','17:30:00','','6',1),
(1224,3,14,4,'','16:00:00','17:30:00','','6',1),
(1223,3,6,12,'','14:30:00','16:00:00','','6',1),
(1222,3,15,11,'','14:30:00','16:00:00','','6',1),
(1221,3,12,8,'','14:30:00','16:00:00','','6',1),
(1220,3,8,7,'','14:30:00','16:00:00','','6',1),
(1463,5,3,7,'','17:30:00','19:00:00','','2',1),
(1462,5,8,4,'','16:00:00','17:30:00','','2',1),
(1461,4,11,6,'','14:30:00','16:00:00','','6',1),
(1460,4,3,4,'','14:30:00','16:00:00','','6',1),
(1459,4,8,27,'','10:00:00','11:00:00','','6',1),
(1458,4,3,26,'','10:00:00','11:00:00','','6',1),
(1457,4,12,26,'','10:00:00','11:00:00','','6',1),
(1456,4,15,25,'','10:00:00','11:00:00','','6',1),
(1455,4,6,25,'','10:00:00','11:00:00','','6',1),
(1454,4,14,28,'','10:00:00','11:00:00','','6',1),
(1453,4,9,28,'','10:00:00','11:00:00','','6',1),
(1452,4,8,27,'','09:00:00','10:00:00','','6',1),
(1451,4,3,26,'','09:00:00','10:00:00','','6',1),
(1450,4,12,26,'','09:00:00','10:00:00','','6',1),
(1449,4,15,25,'','09:00:00','10:00:00','','6',1),
(1448,4,6,25,'','09:00:00','10:00:00','','6',1),
(1447,4,14,28,'','09:00:00','10:00:00','','6',1),
(1446,4,9,28,'','09:00:00','10:00:00','','6',1),
(1445,4,4,12,'','13:00:00','14:30:00','','6',1),
(1444,4,13,11,'','13:00:00','14:30:00','','6',1),
(1443,4,10,8,'','13:00:00','14:30:00','','6',1),
(1442,4,7,7,'','13:00:00','14:30:00','','6',1),
(1441,4,6,28,'','17:00:00','18:00:00','','6',1),
(1440,4,10,28,'','15:00:00','16:00:00','','6',1),
(1439,4,11,28,'','12:00:00','13:00:00','','6',1),
(1438,4,15,12,'','17:30:00','19:00:00','','6',1),
(1199,3,5,6,'','13:00:00','14:30:00','','6',1),
(1198,3,9,4,'','13:00:00','14:30:00','','6',1),
(1197,3,14,12,'','11:30:00','13:00:00','','6',1),
(1196,3,6,11,'','11:30:00','13:00:00','','6',1),
(1195,3,15,8,'','11:30:00','13:00:00','','6',1),
(1194,3,12,7,'','11:30:00','13:00:00','','6',1),
(1193,3,3,6,'','11:30:00','13:00:00','','6',1),
(1192,3,8,4,'','11:30:00','13:00:00','','6',1),
(1191,3,11,12,'','10:00:00','11:30:00','','6',1),
(1190,3,10,11,'','10:00:00','11:30:00','','6',1),
(1189,3,4,8,'','10:00:00','11:30:00','','6',1),
(1188,3,13,7,'','10:00:00','11:30:00','','6',1),
(1187,3,7,6,'','10:00:00','11:30:00','','6',1),
(1186,3,5,4,'','10:00:00','11:30:00','','6',1),
(1185,3,6,28,'','17:00:00','18:00:00','','3',1),
(1184,3,10,28,'','15:00:00','16:00:00','','3',1),
(1183,3,11,28,'','12:00:00','13:00:00','','3',1),
(1182,3,15,12,'','17:30:00','19:00:00','','3',1),
(1181,3,3,11,'','17:30:00','19:00:00','','3',1),
(1180,3,8,8,'','17:30:00','19:00:00','','3',1),
(1179,3,11,7,'','17:30:00','19:00:00','','3',1),
(1178,3,10,6,'','17:30:00','19:00:00','','3',1),
(1177,3,12,4,'','17:30:00','19:00:00','','3',1),
(1176,3,9,12,'','16:00:00','17:30:00','','3',1),
(1175,3,7,11,'','16:00:00','17:30:00','','3',1),
(1174,3,5,8,'','16:00:00','17:30:00','','3',1),
(1173,3,4,7,'','16:00:00','17:30:00','','3',1),
(1172,3,13,6,'','16:00:00','17:30:00','','3',1),
(1171,3,14,4,'','16:00:00','17:30:00','','3',1),
(1170,3,6,12,'','14:30:00','16:00:00','','3',1),
(1169,3,15,11,'','14:30:00','16:00:00','','3',1),
(1168,3,12,8,'','14:30:00','16:00:00','','3',1),
(1167,3,8,7,'','14:30:00','16:00:00','','3',1),
(1166,3,11,6,'','14:30:00','16:00:00','','3',1),
(1165,3,3,4,'','14:30:00','16:00:00','','3',1),
(1164,3,8,27,'','10:00:00','11:00:00','','3',1),
(1163,3,3,26,'','10:00:00','11:00:00','','3',1),
(1162,3,12,26,'','10:00:00','11:00:00','','3',1),
(1161,3,15,25,'','10:00:00','11:00:00','','3',1),
(1160,3,6,25,'','10:00:00','11:00:00','','3',1),
(1159,3,14,28,'','10:00:00','11:00:00','','3',1),
(1158,3,9,28,'','10:00:00','11:00:00','','3',1),
(1157,3,8,27,'','09:00:00','10:00:00','','3',1),
(1156,3,3,26,'','09:00:00','10:00:00','','3',1),
(1155,3,12,26,'','09:00:00','10:00:00','','3',1),
(1154,3,15,25,'','09:00:00','10:00:00','','3',1),
(1153,3,6,25,'','09:00:00','10:00:00','','3',1),
(1152,3,14,28,'','09:00:00','10:00:00','','3',1),
(1151,3,9,28,'','09:00:00','10:00:00','','3',1),
(1150,3,4,12,'','13:00:00','14:30:00','','3',1),
(1149,3,13,11,'','13:00:00','14:30:00','','3',1),
(1148,3,10,8,'','13:00:00','14:30:00','','3',1),
(1147,3,7,7,'','13:00:00','14:30:00','','3',1),
(1146,3,5,6,'','13:00:00','14:30:00','','3',1),
(1145,3,9,4,'','13:00:00','14:30:00','','3',1),
(1144,3,14,12,'','11:30:00','13:00:00','','3',1),
(1143,3,6,11,'','11:30:00','13:00:00','','3',1),
(1142,3,15,8,'','11:30:00','13:00:00','','3',1),
(1141,3,12,7,'','11:30:00','13:00:00','','3',1),
(1140,3,3,6,'','11:30:00','13:00:00','','3',1),
(1139,3,8,4,'','11:30:00','13:00:00','','3',1),
(1138,3,11,12,'','10:00:00','11:30:00','','3',1),
(1137,3,10,11,'','10:00:00','11:30:00','','3',1),
(1136,3,4,8,'','10:00:00','11:30:00','','3',1),
(1135,3,13,7,'','10:00:00','11:30:00','','3',1),
(1134,3,7,6,'','10:00:00','11:30:00','','3',1),
(1133,3,5,4,'','10:00:00','11:30:00','','3',1),
(1132,3,4,25,'','17:00:00','18:00:00','','5',1),
(1131,3,7,12,'','19:00:00','20:00:00','','5',1),
(1130,3,13,11,'','19:00:00','20:00:00','','5',1),
(1129,3,10,8,'','19:00:00','20:00:00','','5',1),
(1128,3,4,7,'','19:00:00','20:00:00','','5',1),
(1127,3,8,6,'','19:00:00','20:00:00','','5',1),
(1126,3,11,4,'','19:00:00','20:00:00','','5',1),
(1125,3,15,27,'','16:00:00','17:00:00','','5',1),
(1124,3,6,26,'','16:00:00','17:00:00','','5',1),
(1123,3,14,25,'','16:00:00','17:00:00','','5',1),
(1122,3,9,28,'','16:00:00','17:00:00','','5',1),
(1121,3,8,27,'','15:00:00','16:00:00','','5',1),
(1120,3,4,26,'','15:00:00','16:00:00','','5',1),
(1119,3,10,25,'','15:00:00','16:00:00','','5',1),
(1118,3,11,28,'','15:00:00','16:00:00','','5',1),
(1117,3,4,27,'','14:00:00','15:00:00','','5',1),
(1116,3,10,26,'','14:00:00','15:00:00','','5',1),
(1115,3,11,25,'','14:00:00','15:00:00','','5',1),
(1114,3,8,28,'','14:00:00','15:00:00','','5',1),
(1113,3,13,23,'','16:00:00','18:00:00','','5',1),
(1112,3,13,23,'','14:00:00','16:00:00','','5',1),
(1111,3,9,12,'','17:30:00','19:00:00','','5',1),
(1110,3,3,11,'','17:30:00','19:00:00','','5',1),
(1109,3,12,28,'','17:00:00','18:00:00','','5',1),
(1108,3,15,7,'','17:30:00','19:00:00','','5',1),
(1107,3,6,6,'','17:30:00','19:00:00','','5',1),
(1106,3,14,4,'','17:30:00','19:00:00','','5',1),
(1105,3,5,12,'','16:00:00','17:30:00','','5',1),
(1104,3,7,11,'','16:00:00','17:30:00','','5',1),
(1103,3,3,8,'','16:00:00','17:30:00','','5',1),
(1102,3,10,7,'','16:00:00','17:30:00','','5',1),
(1101,3,11,6,'','16:00:00','17:30:00','','5',1),
(1100,3,8,4,'','16:00:00','17:30:00','','5',1),
(1099,3,3,12,'','14:00:00','16:00:00','','5',1),
(1098,3,12,11,'','14:00:00','16:00:00','','5',1),
(1097,3,15,8,'','14:00:00','16:00:00','','5',1),
(1096,3,6,7,'','14:00:00','16:00:00','','5',1),
(1095,3,14,6,'','14:00:00','16:00:00','','5',1),
(1094,3,9,4,'','14:00:00','16:00:00','','5',1),
(1093,3,4,25,'','17:00:00','18:00:00','','4',1),
(1092,3,7,12,'','19:00:00','20:00:00','','4',1),
(1091,3,13,11,'','19:00:00','20:00:00','','4',1),
(1090,3,10,8,'','19:00:00','20:00:00','','4',1),
(1089,3,4,7,'','19:00:00','20:00:00','','4',1),
(1088,3,8,6,'','19:00:00','20:00:00','','4',1),
(1087,3,11,4,'','19:00:00','20:00:00','','4',1),
(1086,3,15,27,'','16:00:00','17:00:00','','4',1),
(1085,3,6,26,'','16:00:00','17:00:00','','4',1),
(1084,3,14,25,'','16:00:00','17:00:00','','4',1),
(1083,3,9,28,'','16:00:00','17:00:00','','4',1),
(1082,3,8,27,'','15:00:00','16:00:00','','4',1),
(1081,3,4,26,'','15:00:00','16:00:00','','4',1),
(1080,3,10,25,'','15:00:00','16:00:00','','4',1),
(1079,3,11,28,'','15:00:00','16:00:00','','4',1),
(1078,3,4,27,'','14:00:00','15:00:00','','4',1),
(1077,3,10,26,'','14:00:00','15:00:00','','4',1),
(1076,3,11,25,'','14:00:00','15:00:00','','4',1),
(1075,3,8,28,'','14:00:00','15:00:00','','4',1),
(1074,3,13,23,'','16:00:00','18:00:00','','4',1),
(1073,3,13,23,'','14:00:00','16:00:00','','4',1),
(1072,3,9,12,'','17:30:00','19:00:00','','4',1),
(1071,3,3,11,'','17:30:00','19:00:00','','4',1),
(1070,3,12,28,'','17:00:00','18:00:00','','4',1),
(1069,3,15,7,'','17:30:00','19:00:00','','4',1),
(1068,3,6,6,'','17:30:00','19:00:00','','4',1),
(1067,3,14,4,'','17:30:00','19:00:00','','4',1),
(1066,3,5,12,'','16:00:00','17:30:00','','4',1),
(1065,3,7,11,'','16:00:00','17:30:00','','4',1),
(1064,3,3,8,'','16:00:00','17:30:00','','4',1),
(1063,3,10,7,'','16:00:00','17:30:00','','4',1),
(1062,3,11,6,'','16:00:00','17:30:00','','4',1),
(1061,3,8,4,'','16:00:00','17:30:00','','4',1),
(1060,3,3,12,'','14:00:00','16:00:00','','4',1),
(1059,3,12,11,'','14:00:00','16:00:00','','4',1),
(1058,3,15,8,'','14:00:00','16:00:00','','4',1),
(1057,3,6,7,'','14:00:00','16:00:00','','4',1),
(1056,3,14,6,'','14:00:00','16:00:00','','4',1),
(1055,3,9,4,'','14:00:00','16:00:00','','4',1),
(1054,3,4,25,'','17:00:00','18:00:00','','2',1),
(1053,3,7,12,'','19:00:00','20:00:00','','2',1),
(1052,3,13,11,'','19:00:00','20:00:00','','2',1),
(1051,3,10,8,'','19:00:00','20:00:00','','2',1),
(1050,3,4,7,'','19:00:00','20:00:00','','2',1),
(1049,3,8,6,'','19:00:00','20:00:00','','2',1),
(1048,3,11,4,'','19:00:00','20:00:00','','2',1),
(1047,3,15,27,'','16:00:00','17:00:00','','2',1),
(1046,3,6,26,'','16:00:00','17:00:00','','2',1),
(1045,3,14,25,'','16:00:00','17:00:00','','2',1),
(1044,3,9,28,'','16:00:00','17:00:00','','2',1),
(1043,3,8,27,'','15:00:00','16:00:00','','2',1),
(1042,3,4,26,'','15:00:00','16:00:00','','2',1),
(1041,3,10,25,'','15:00:00','16:00:00','','2',1),
(1040,3,11,28,'','15:00:00','16:00:00','','2',1),
(1039,3,4,27,'','14:00:00','15:00:00','','2',1),
(1038,3,10,26,'','14:00:00','15:00:00','','2',1),
(1037,3,11,25,'','14:00:00','15:00:00','','2',1),
(1036,3,8,28,'','14:00:00','15:00:00','','2',1),
(1035,3,13,23,'','16:00:00','18:00:00','','2',1),
(1034,3,13,23,'','14:00:00','16:00:00','','2',1),
(1033,3,9,12,'','17:30:00','19:00:00','','2',1),
(1032,3,3,11,'','17:30:00','19:00:00','','2',1),
(1031,3,12,28,'','17:00:00','18:00:00','','2',1),
(1030,3,15,7,'','17:30:00','19:00:00','','2',1),
(1029,3,6,6,'','17:30:00','19:00:00','','2',1),
(1028,3,14,4,'','17:30:00','19:00:00','','2',1),
(1027,3,5,12,'','16:00:00','17:30:00','','2',1),
(1026,3,7,11,'','16:00:00','17:30:00','','2',1),
(1025,3,3,8,'','16:00:00','17:30:00','','2',1),
(1024,3,10,7,'','16:00:00','17:30:00','','2',1),
(1023,3,11,6,'','16:00:00','17:30:00','','2',1),
(1022,3,8,4,'','16:00:00','17:30:00','','2',1),
(1021,3,3,12,'','14:00:00','16:00:00','','2',1),
(1020,3,12,11,'','14:00:00','16:00:00','','2',1),
(1019,3,15,8,'','14:00:00','16:00:00','','2',1),
(1018,3,6,7,'','14:00:00','16:00:00','','2',1),
(1017,3,14,6,'','14:00:00','16:00:00','','2',1),
(1016,3,9,4,'','14:00:00','16:00:00','','2',1),
(1015,2,4,25,'','17:00:00','18:00:00','','',1),
(1014,2,7,12,'','19:00:00','20:00:00','','',1),
(1013,2,13,11,'','19:00:00','20:00:00','','',1),
(1012,2,10,8,'','19:00:00','20:00:00','','',1),
(1011,2,4,7,'','19:00:00','20:00:00','','',1),
(1010,2,8,6,'','19:00:00','20:00:00','','',1),
(1009,2,11,4,'','19:00:00','20:00:00','','',1),
(1008,2,15,27,'','16:00:00','17:00:00','','',1),
(1007,2,6,26,'','16:00:00','17:00:00','','',1),
(1006,2,14,25,'','16:00:00','17:00:00','','',1),
(1005,2,9,28,'','16:00:00','17:00:00','','',1),
(1004,2,8,27,'','15:00:00','16:00:00','','',1),
(1003,2,4,26,'','15:00:00','16:00:00','','',1),
(1002,2,10,25,'','15:00:00','16:00:00','','',1),
(1001,2,11,28,'','15:00:00','16:00:00','','',1),
(1000,2,4,27,'','14:00:00','15:00:00','','',1),
(999,2,10,26,'','14:00:00','15:00:00','','',1),
(998,2,11,25,'','14:00:00','15:00:00','','',1),
(997,2,8,28,'','14:00:00','15:00:00','','',1),
(996,2,13,23,'','16:00:00','18:00:00','','',1),
(995,2,13,23,'','14:00:00','16:00:00','','',1),
(994,2,9,12,'','17:30:00','19:00:00','','',1),
(993,2,3,11,'','17:30:00','19:00:00','','',1),
(992,2,12,28,'','17:00:00','18:00:00','','',1),
(991,2,15,7,'','17:30:00','19:00:00','','',1),
(990,2,6,6,'','17:30:00','19:00:00','','',1),
(989,2,14,4,'','17:30:00','19:00:00','','',1),
(988,2,5,12,'','16:00:00','17:30:00','','',1),
(987,2,7,11,'','16:00:00','17:30:00','','',1),
(986,2,3,8,'','16:00:00','17:30:00','','',1),
(985,2,10,7,'','16:00:00','17:30:00','','',1),
(984,2,11,6,'','16:00:00','17:30:00','','',1),
(983,2,8,4,'','16:00:00','17:30:00','','',1),
(982,2,3,12,'','14:00:00','16:00:00','','',1),
(981,2,12,11,'','14:00:00','16:00:00','','',1),
(980,2,15,8,'','14:00:00','16:00:00','','',1),
(979,2,6,7,'','14:00:00','16:00:00','','',1),
(978,2,14,6,'','14:00:00','16:00:00','','',1),
(977,2,9,4,'','14:00:00','16:00:00','','',1);
/*!40000 ALTER TABLE `pl_poste_modeles` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_modeles_tab`
--

DROP TABLE IF EXISTS `pl_poste_modeles_tab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_modeles_tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `model_id` int(11) NOT NULL DEFAULT 0,
  `jour` int(11) NOT NULL,
  `tableau` int(11) NOT NULL,
  `site` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_modeles_tab`
--

LOCK TABLES `pl_poste_modeles_tab` WRITE;
/*!40000 ALTER TABLE `pl_poste_modeles_tab` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_modeles_tab` VALUES
(1,'webinaire 13/10',1,9,1,1),
(50,'test webinaire',5,2,3,1),
(49,'test webinaire',5,1,2,1),
(48,'test webinaire',5,4,3,1),
(47,'test webinaire',5,5,3,1),
(46,'test webinaire',5,6,1,1),
(45,'test webinaire',5,7,4,1),
(44,'semaine paire',4,7,4,1),
(43,'semaine paire',4,6,1,1),
(42,'semaine paire',4,5,3,1),
(41,'semaine paire',4,4,3,1),
(40,'semaine paire',4,3,1,1),
(39,'semaine paire',4,2,3,1),
(37,'semaine impaire',3,7,4,1),
(38,'semaine paire',4,1,2,1),
(36,'semaine impaire',3,6,1,1),
(35,'semaine impaire',3,5,3,1),
(34,'semaine impaire',3,4,3,1),
(33,'semaine impaire',3,3,1,1),
(32,'semaine impaire',3,2,3,1),
(31,'semaine impaire',3,1,2,1),
(30,'semaine impaire',2,9,3,1),
(51,'test webinaire',5,3,1,1);
/*!40000 ALTER TABLE `pl_poste_modeles_tab` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_tab`
--

DROP TABLE IF EXISTS `pl_poste_tab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_tab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tableau` int(20) NOT NULL,
  `nom` text NOT NULL,
  `site` int(2) NOT NULL DEFAULT 1,
  `supprime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_tab`
--

LOCK TABLES `pl_poste_tab` WRITE;
/*!40000 ALTER TABLE `pl_poste_tab` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_tab` VALUES
(1,1,'Scolaire : Mercredi - Samedi',1,NULL),
(2,2,'Fermeture',1,NULL),
(3,3,'Scolaire : Mardi - Jeudi - Vendredi',1,NULL),
(4,4,'Scolaire : Dimanche',1,NULL),
(5,5,'Nouveau tableau du mardi',1,NULL);
/*!40000 ALTER TABLE `pl_poste_tab` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_tab_affect`
--

DROP TABLE IF EXISTS `pl_poste_tab_affect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_tab_affect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `tableau` int(11) NOT NULL,
  `site` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_tab_affect`
--

LOCK TABLES `pl_poste_tab_affect` WRITE;
/*!40000 ALTER TABLE `pl_poste_tab_affect` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_tab_affect` VALUES
(1,'2022-09-27',1,1),
(4,'2022-10-13',1,1),
(5,'2022-10-14',1,1),
(6,'2022-10-11',1,1),
(7,'2022-09-28',1,1),
(102,'2023-01-23',2,1),
(103,'2023-01-24',3,1),
(105,'2023-01-26',3,1),
(104,'2023-01-25',1,1),
(106,'2023-01-27',3,1),
(107,'2023-01-28',1,1),
(22,'2023-01-30',1,1),
(23,'2023-01-31',1,1),
(24,'2023-02-01',1,1),
(25,'2023-02-02',1,1),
(26,'2023-02-03',1,1),
(27,'2023-02-04',1,1),
(28,'2023-02-06',1,1),
(29,'2023-02-07',1,1),
(30,'2023-02-08',1,1),
(31,'2023-02-09',1,1),
(32,'2023-02-10',1,1),
(33,'2023-02-11',1,1),
(108,'2023-01-29',4,1);
/*!40000 ALTER TABLE `pl_poste_tab_affect` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_tab_grp`
--

DROP TABLE IF EXISTS `pl_poste_tab_grp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_tab_grp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` text DEFAULT NULL,
  `lundi` int(11) DEFAULT NULL,
  `mardi` int(11) DEFAULT NULL,
  `mercredi` int(11) DEFAULT NULL,
  `jeudi` int(11) DEFAULT NULL,
  `vendredi` int(11) DEFAULT NULL,
  `samedi` int(11) DEFAULT NULL,
  `dimanche` int(11) DEFAULT NULL,
  `site` int(2) NOT NULL DEFAULT 1,
  `supprime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_tab_grp`
--

LOCK TABLES `pl_poste_tab_grp` WRITE;
/*!40000 ALTER TABLE `pl_poste_tab_grp` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_tab_grp` VALUES
(1,'Semaine scolaire',2,3,1,3,3,1,4,1,NULL);
/*!40000 ALTER TABLE `pl_poste_tab_grp` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pl_poste_verrou`
--

DROP TABLE IF EXISTS `pl_poste_verrou`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pl_poste_verrou` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `verrou` int(1) NOT NULL DEFAULT 0,
  `validation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `perso` int(11) NOT NULL DEFAULT 0,
  `verrou2` int(1) NOT NULL DEFAULT 0,
  `validation2` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `perso2` int(11) NOT NULL DEFAULT 0,
  `vivier` int(1) NOT NULL DEFAULT 0,
  `site` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_verrou`
--

LOCK TABLES `pl_poste_verrou` WRITE;
/*!40000 ALTER TABLE `pl_poste_verrou` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_verrou` VALUES
(1,'2022-10-14',0,'0000-00-00 00:00:00',0,1,'2022-09-27 15:49:02',1,0,1),
(2,'2022-10-13',0,'0000-00-00 00:00:00',0,1,'2022-09-27 15:53:37',3,0,1),
(3,'2022-10-11',0,'0000-00-00 00:00:00',0,1,'2022-09-27 15:58:51',3,0,1),
(4,'2022-09-28',0,'0000-00-00 00:00:00',0,1,'2022-09-28 12:18:58',3,0,1),
(5,'2023-01-26',0,'0000-00-00 00:00:00',0,1,'2023-01-24 15:34:21',1,0,1),
(6,'2023-02-02',0,'0000-00-00 00:00:00',0,0,'2023-01-24 15:33:06',1,0,1),
(7,'2023-01-24',0,'0000-00-00 00:00:00',0,1,'2023-01-24 15:48:12',1,0,1),
(8,'2023-01-25',0,'0000-00-00 00:00:00',0,1,'2023-01-24 15:48:16',1,0,1),
(9,'2023-01-27',0,'0000-00-00 00:00:00',0,1,'2023-01-24 15:48:19',1,0,1),
(10,'2023-01-28',0,'0000-00-00 00:00:00',0,1,'2023-01-24 15:48:24',1,0,1);
/*!40000 ALTER TABLE `pl_poste_verrou` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `planning_hebdo`
--

DROP TABLE IF EXISTS `planning_hebdo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `planning_hebdo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL,
  `debut` date NOT NULL,
  `fin` date NOT NULL,
  `temps` text NOT NULL,
  `breaktime` text NOT NULL,
  `saisie` timestamp NOT NULL DEFAULT current_timestamp(),
  `modif` int(11) NOT NULL DEFAULT 0,
  `modification` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valide_n1` int(11) NOT NULL DEFAULT 0,
  `validation_n1` timestamp NULL DEFAULT NULL,
  `valide` int(11) NOT NULL DEFAULT 0,
  `validation` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `actuel` int(1) NOT NULL DEFAULT 0,
  `remplace` int(11) NOT NULL DEFAULT 0,
  `cle` varchar(100) DEFAULT NULL,
  `exception` int(11) NOT NULL DEFAULT 0,
  `nb_semaine` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cle` (`cle`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planning_hebdo`
--

LOCK TABLES `planning_hebdo` WRITE;
/*!40000 ALTER TABLE `planning_hebdo` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `planning_hebdo` VALUES
(1,9,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:41:15',1,'2023-06-20 09:14:12',0,'0000-00-00 00:00:00',1,'2023-06-20 09:14:12',1,0,NULL,0,1),
(2,6,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:41:27',1,'2023-06-20 09:14:36',0,'0000-00-00 00:00:00',1,'2023-06-20 09:14:36',1,0,NULL,0,1),
(3,3,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:41:37',1,'2023-06-20 09:15:31',0,'0000-00-00 00:00:00',1,'2023-06-20 09:15:31',1,0,NULL,0,1),
(4,8,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:41:44',1,'2023-06-20 09:15:43',0,'0000-00-00 00:00:00',1,'2023-06-20 09:15:43',1,0,NULL,0,1),
(5,11,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"3\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:41:51',1,'2023-06-20 09:15:55',0,'0000-00-00 00:00:00',1,'2023-06-20 09:15:55',1,0,NULL,0,1),
(6,10,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:41:58',1,'2023-06-20 09:16:07',0,'0000-00-00 00:00:00',1,'2023-06-20 09:16:07',1,0,NULL,0,1),
(7,4,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:42:05',1,'2023-06-20 09:16:19',0,'0000-00-00 00:00:00',1,'2023-06-20 09:16:19',1,0,NULL,0,1),
(8,7,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:42:12',1,'2023-06-20 09:16:38',0,'0000-00-00 00:00:00',1,'2023-06-20 09:16:38',1,0,NULL,0,1),
(9,5,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"-1\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-27 13:42:19',1,'2023-06-20 09:17:14',0,'0000-00-00 00:00:00',1,'2023-06-20 09:17:14',1,0,NULL,0,1),
(10,14,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"1\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-28 10:14:18',1,'2023-06-20 09:14:24',0,'0000-00-00 00:00:00',1,'2023-06-20 09:14:24',1,0,NULL,0,1),
(11,15,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-28 10:14:29',1,'2023-06-20 09:14:49',0,'0000-00-00 00:00:00',1,'2023-06-20 09:14:49',1,0,NULL,0,1),
(12,12,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"2\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-28 10:14:42',1,'2023-06-20 09:15:16',0,'0000-00-00 00:00:00',1,'2023-06-20 09:15:16',1,0,NULL,0,1),
(13,13,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"09:00:00\",\"\",\"\",\"19:00:00\",\"4\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2022-09-28 10:14:56',1,'2023-06-20 09:16:53',0,'0000-00-00 00:00:00',1,'2023-06-20 09:16:53',1,0,NULL,0,1),
(14,16,'2022-09-01','2026-12-31','[[\"09:00:00\",\"\",\"\",\"22:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"22:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"22:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"22:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"22:00:00\",\"2\"],[\"09:00:00\",\"\",\"\",\"22:00:00\",\"2\"],[\"\",\"\",\"\",\"\",\"\"]]','[1,1,1,1,1,1,0]','2023-01-24 14:20:08',1,'2023-06-20 09:15:04',0,'0000-00-00 00:00:00',1,'2023-06-20 09:15:04',1,0,NULL,0,1);
/*!40000 ALTER TABLE `planning_hebdo` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `postes`
--

DROP TABLE IF EXISTS `postes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `postes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `groupe` text NOT NULL,
  `groupe_id` int(11) NOT NULL DEFAULT 0,
  `obligatoire` varchar(15) NOT NULL,
  `etage` text NOT NULL,
  `activites` text NOT NULL,
  `statistiques` tinyint(1) NOT NULL DEFAULT 1,
  `teleworking` tinyint(1) NOT NULL DEFAULT 0,
  `bloquant` tinyint(1) NOT NULL DEFAULT 1,
  `lunch` tinyint(1) NOT NULL DEFAULT 0,
  `site` int(1) DEFAULT 1,
  `categories` text DEFAULT NULL,
  `supprime` datetime DEFAULT NULL,
  `quota_sp` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postes`
--

LOCK TABLES `postes` WRITE;
/*!40000 ALTER TABLE `postes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `postes` VALUES
(4,'Inscription 1','',0,'Obligatoire','2','[5,9]',1,0,1,0,1,NULL,NULL,1),
(5,'Retour','',0,'Obligatoire','2','[6,9]',1,0,1,0,1,NULL,NULL,1),
(6,'Prêt / retour 1','',0,'Obligatoire','2','[7,6,9]',1,0,1,0,1,NULL,NULL,1),
(7,'Prêt / retour 2','',0,'Renfort','2','[7,6,9]',1,0,1,0,1,NULL,NULL,1),
(8,'Prêt / retour 3','',0,'Renfort','2','[5,7,6,9]',1,0,1,0,1,NULL,NULL,1),
(9,'Prêt / retour 4','',0,'Renfort','2','[7,6,9]',1,0,1,0,1,NULL,NULL,1),
(10,'Inscription 2','',0,'Renfort','2','[5]',1,0,1,0,1,NULL,NULL,1),
(11,'Communication RDC','',0,'Renfort','2','[3,7,9]',1,0,1,0,1,NULL,NULL,1),
(12,'Renseignement RDC','',0,'Obligatoire','2','[9,10]',1,0,1,0,1,NULL,NULL,1),
(13,'Renseignement spécialisé 1','',0,'Obligatoire','3','[9,10,12]',1,0,1,0,1,NULL,NULL,1),
(14,'Renseignement spécialisé 2','',0,'Renfort','3','[9,10,12]',1,0,1,0,1,NULL,NULL,1),
(15,'Renseignement spécialisé 3','',0,'Renfort','3','[9,10,12]',1,0,1,0,1,NULL,NULL,1),
(16,'Communication (banque 1)','',0,'Obligatoire','3','[3,7,6,9]',1,0,1,0,1,NULL,NULL,1),
(17,'Communication (banque 2)','',0,'Renfort','3','[3,9,10]',1,0,1,0,1,NULL,NULL,1),
(19,'Communication (coordination)','',0,'Obligatoire','3','[3]',1,0,1,0,1,NULL,NULL,1),
(20,'Communication (magasin 1)','',0,'Obligatoire','3','[3]',1,0,1,0,1,NULL,NULL,1),
(21,'Communication (magasin 2)','',0,'Obligatoire','3','[11]',1,0,1,0,1,NULL,NULL,1),
(22,'Communication (magasin 3)','',0,'Renfort','3','[3]',1,0,1,0,1,NULL,NULL,1),
(23,'Consultation de la réserve','',0,'Obligatoire','3','[4,9]',1,0,1,0,1,NULL,NULL,1),
(24,'Audiovisuel et autoformation','',0,'Obligatoire','1','[1,2,7,9]',1,0,1,0,1,NULL,NULL,1),
(25,'Rangement 2','',0,'Obligatoire','2','[8]',1,0,1,0,1,NULL,NULL,1),
(26,'Rangement 3','',0,'Obligatoire','2','[8]',1,0,1,0,1,NULL,NULL,1),
(27,'Rangement 4','',0,'Renfort','2','[8]',1,0,1,0,1,NULL,NULL,1),
(28,'Rangement 1','',0,'Obligatoire','1','[8]',1,0,1,0,1,NULL,NULL,1),
(29,'Rangement 5','',0,'Obligatoire','3','[8]',1,0,1,0,1,NULL,NULL,1),
(30,'Rangement 6','',0,'Obligatoire','3','[8]',1,0,1,0,1,NULL,NULL,1),
(31,'Rangement 7','',0,'Renfort','3','[8]',1,0,1,0,1,NULL,NULL,1),
(32,'Rangement 8','',0,'Renfort','3','[8]',1,0,1,0,1,NULL,NULL,1),
(33,'Rangement 9','',0,'Renfort','3','[8]',1,0,1,0,1,NULL,NULL,1),
(34,'Rangement 10','',0,'Obligatoire','4','[8]',1,0,1,0,1,NULL,NULL,1),
(35,'Rangement 11','',0,'Obligatoire','4','[8]',1,0,1,0,1,NULL,NULL,1),
(36,'Renseignement kiosque','',0,'Renfort','1','[9,10]',1,0,1,0,1,NULL,NULL,1),
(37,'Accueil','',0,'Obligatoire','2','[\"5\",\"9\"]',1,0,1,0,0,'[]','2023-01-24 16:02:16',1);
/*!40000 ALTER TABLE `postes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `recuperations`
--

DROP TABLE IF EXISTS `recuperations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `recuperations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `date2` date DEFAULT NULL,
  `heures` float DEFAULT NULL,
  `etat` varchar(20) DEFAULT NULL,
  `commentaires` text DEFAULT NULL,
  `saisie` timestamp NOT NULL DEFAULT current_timestamp(),
  `saisie_par` int(11) NOT NULL,
  `modif` int(11) NOT NULL DEFAULT 0,
  `modification` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valide_n1` int(11) NOT NULL DEFAULT 0,
  `validation_n1` datetime DEFAULT NULL,
  `valide` int(11) NOT NULL DEFAULT 0,
  `validation` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `refus` text DEFAULT NULL,
  `solde_prec` float DEFAULT NULL,
  `solde_actuel` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperations`
--

LOCK TABLES `recuperations` WRITE;
/*!40000 ALTER TABLE `recuperations` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `recuperations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `responsables`
--

DROP TABLE IF EXISTS `responsables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `responsables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT 0,
  `responsable` int(11) NOT NULL DEFAULT 0,
  `level1` int(1) NOT NULL DEFAULT 1,
  `level2` int(1) NOT NULL DEFAULT 0,
  `notification_level1` int(1) NOT NULL DEFAULT 0,
  `notification_level2` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=205 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responsables`
--

LOCK TABLES `responsables` WRITE;
/*!40000 ALTER TABLE `responsables` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `responsables` VALUES
(182,16,14,1,1,0,1),
(181,16,9,1,0,1,0),
(198,13,3,1,0,1,0),
(184,12,14,1,1,0,1),
(204,5,14,1,1,1,1),
(183,12,9,1,0,1,0),
(203,7,14,1,1,1,1),
(177,6,9,1,0,1,0),
(175,14,9,1,0,1,0),
(194,4,3,1,0,1,0),
(185,9,14,1,1,1,1),
(192,10,3,1,0,1,0),
(190,11,3,1,0,1,0),
(188,8,3,1,0,1,0),
(189,8,14,1,1,0,1),
(180,15,14,1,1,0,1),
(199,13,14,1,1,0,1),
(195,4,14,1,1,0,1),
(178,6,14,1,1,0,1),
(176,14,14,1,1,0,1),
(193,10,14,1,1,0,1),
(191,11,14,1,1,0,1),
(202,3,14,1,1,1,1),
(179,15,9,1,0,1,0);
/*!40000 ALTER TABLE `responsables` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `select_abs`
--

DROP TABLE IF EXISTS `select_abs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `select_abs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL DEFAULT 0,
  `type` int(1) NOT NULL DEFAULT 0,
  `notification_workflow` char(1) DEFAULT NULL,
  `teleworking` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `select_abs`
--

LOCK TABLES `select_abs` WRITE;
/*!40000 ALTER TABLE `select_abs` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `select_abs` VALUES
(1,'Non justifiée',1,0,'A',0),
(2,'Congés payés',2,0,'A',0),
(3,'Maladie',3,0,'A',0),
(4,'Congé maternité',4,0,'A',0),
(5,'Réunion syndicale',5,0,'A',0),
(6,'Grève',6,0,'A',0),
(7,'Formation',7,0,'A',0),
(8,'Concours',8,0,'A',0),
(9,'Stage',9,0,'A',0),
(10,'Réunion',10,0,'A',0),
(11,'Entretien',11,0,'A',0),
(12,'Autre',12,0,'A',0);
/*!40000 ALTER TABLE `select_abs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `select_categories`
--

DROP TABLE IF EXISTS `select_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `select_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `select_categories`
--

LOCK TABLES `select_categories` WRITE;
/*!40000 ALTER TABLE `select_categories` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `select_categories` VALUES
(1,'Cat&eacute;gorie A',10),
(2,'Cat&eacute;gorie B',20),
(3,'Cat&eacute;gorie C',30);
/*!40000 ALTER TABLE `select_categories` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `select_etages`
--

DROP TABLE IF EXISTS `select_etages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `select_etages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `select_etages`
--

LOCK TABLES `select_etages` WRITE;
/*!40000 ALTER TABLE `select_etages` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `select_etages` VALUES
(1,'Mezzanine',1),
(2,'RDC',2),
(3,'RDJ',3),
(4,'Magasins',4);
/*!40000 ALTER TABLE `select_etages` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `select_groupes`
--

DROP TABLE IF EXISTS `select_groupes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `select_groupes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `select_groupes`
--

LOCK TABLES `select_groupes` WRITE;
/*!40000 ALTER TABLE `select_groupes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `select_groupes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `select_services`
--

DROP TABLE IF EXISTS `select_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `select_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL,
  `couleur` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `select_services`
--

LOCK TABLES `select_services` WRITE;
/*!40000 ALTER TABLE `select_services` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `select_services` VALUES
(1,'Pôle public',1,''),
(2,'Pôle conservation',2,''),
(3,'Pôle collection',3,''),
(4,'Pôle informatique',4,''),
(5,'Pôle administratif',5,''),
(6,'Direction',6,'');
/*!40000 ALTER TABLE `select_services` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `select_statuts`
--

DROP TABLE IF EXISTS `select_statuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `select_statuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` text NOT NULL,
  `rang` int(11) NOT NULL DEFAULT 0,
  `couleur` varchar(7) NOT NULL,
  `categorie` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `select_statuts`
--

LOCK TABLES `select_statuts` WRITE;
/*!40000 ALTER TABLE `select_statuts` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `select_statuts` VALUES
(1,'Conservateur',1,'',1),
(2,'Bibliothécaire',2,'',1),
(3,'AB',3,'',0),
(4,'BAS',4,'',2),
(5,'Magasinier',5,'',3),
(6,'Etudiant',6,'',3),
(7,'Garde de nuit',7,'',0),
(8,'Autre',8,'',0);
/*!40000 ALTER TABLE `select_statuts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `volants`
--

DROP TABLE IF EXISTS `volants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `volants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `perso_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `volants`
--

LOCK TABLES `volants` WRITE;
/*!40000 ALTER TABLE `volants` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `volants` VALUES
(5,'2022-09-26',15),
(3,'2022-10-03',3),
(4,'2022-10-10',3);
/*!40000 ALTER TABLE `volants` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `working_hour_cycles`
--

DROP TABLE IF EXISTS `working_hour_cycles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `working_hour_cycles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT curdate(),
  `week` int(11) NOT NULL DEFAULT 0,
  `sites` mediumtext NOT NULL DEFAULT '[]',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `working_hour_cycles`
--

LOCK TABLES `working_hour_cycles` WRITE;
/*!40000 ALTER TABLE `working_hour_cycles` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `working_hour_cycles` VALUES
(1,'2025-12-22',1,'[1]'),
(2,'2025-12-29',1,'[]'),
(3,'2026-01-05',1,'[]'),
(4,'2026-06-29',1,'[]'),
(5,'2026-07-13',1,'[]'),
(6,'2026-07-27',1,'[]'),
(7,'2026-08-10',1,'[]'),
(8,'2026-08-24',1,'[]'),
(9,'2026-08-31',3,'[]');
/*!40000 ALTER TABLE `working_hour_cycles` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-12-09 14:17:09
