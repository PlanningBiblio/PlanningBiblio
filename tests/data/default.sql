/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: planno-mariadb    Database: planno_test
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absences`
--

LOCK TABLES `absences` WRITE;
/*!40000 ALTER TABLE `absences` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `debut` date NOT NULL DEFAULT '0000-00-00',
  `fin` date NOT NULL DEFAULT '0000-00-00',
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
  `last_update` timestamp NULL DEFAULT NULL,
  `last_check` timestamp NULL DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acces`
--

LOCK TABLES `acces` WRITE;
/*!40000 ALTER TABLE `acces` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `acces` VALUES
(1,'Personnel - Password',100,'','personnel/password.php',0,''),
(2,'Absences - Infos',201,'Gestion des absences, validation niveau 1','',30,'Absences'),
(3,'Enregistrement d\'absences pour plusieurs agents',9,'Enregistrement d\'absences pour plusieurs agents','',25,'Absences'),
(4,'Personnel - Index',4,'Voir les fiches des agents','',60,'Agents'),
(5,'Postes et activités',5,'Gestion des postes','',160,'Postes'),
(6,'Statistiques',17,'Accès aux statistiques','',170,'Statistiques'),
(7,'Liste des agents présents et absents',1301,'Accès aux statistiques Présents / Absents','',171,'Statistiques'),
(8,'Configuration avancée',20,'Configuration avancée','',0,''),
(9,'Personnel - Suppression',21,'Gestion des agents','personnel/suppression.php',70,'Agents'),
(10,'Personnel - Valid',21,'Gestion des agents','',70,'Agents'),
(11,'Gestion du personnel',21,'Gestion des agents','',70,'Agents'),
(12,'Configuration des horaires des tableaux',22,'Configuration des tableaux','',140,'Planning'),
(13,'Configuration des horaires des tableaux',22,'Configuration des tableaux','',140,'Planning'),
(14,'Configuration des lignes des tableaux',22,'Configuration des tableaux','planning/postes_cfg/lignes.php',140,'Planning'),
(15,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(16,'Afficher les informations',23,'Informations','',0,'Informations'),
(17,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(18,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(19,'Configuration des tableaux - Modif',22,'Configuration des tableaux','',140,'Planning'),
(20,'Modification des plannings - menudiv',1001,'Modification des plannings','planning/poste/menudiv.php',120,'Planning'),
(21,'Modification des plannings - majdb',1001,'Modification des plannings','planning/poste/majdb.php',120,'Planning'),
(22,'Jours fériés',25,'Gestion des jours fériés','',0,''),
(23,'Voir les agendas de tous',3,'Voir les agendas de tous','',55,'Agendas'),
(24,'Modifier ses propres absences',6,'Modifier ses propres absences','',20,'Absences'),
(25,'Gestion des absences, validation niveau 2',501,'Gestion des absences, validation niveau 2','',40,'Absences'),
(26,'Gestion des absences, pièces justificatives',701,'Gestion des absences, pièces justificatives','',50,'Absences'),
(27,'Planning Hebdo - Admin N1',1101,'Gestion des heures de présence, validation niveau 1','',90,'Heures de présence'),
(28,'Planning Hebdo - Admin N2',1201,'Gestion des heures de présence, validation niveau 2','',90,'Heures de présence'),
(29,'Modification des commentaires des plannings',801,'Modification des commentaires des plannings','',130,'Planning'),
(30,'Griser les cellules des plannings',901,'Griser les cellules des plannings','',125,'Planning'),
(31,'Congés - Index',100,'','conges/index.php',0,''),
(32,'Planning Poste',301,'Création / modification des plannings, utilisation et gestion des modèles','',110,'Planning'),
(33,'Gestion des congés, validation niveau 2',601,'Gestion des congés, validation niveau 2','',76,'Congés'),
(34,'Gestion des congés, validation niveau 1',401,'Gestion des congés, validation niveau 1','',75,'Congés');
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
) ENGINE=MyISAM AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `config` VALUES
(1,'Version','info','25.11.07','Version de l\'application',' Divers','',0,NULL,0),
(2,'URL','info','','URL de l\'application',' Divers','',0,NULL,10),
(3,'toutlemonde','boolean','0','Affiche ou non l\'utilisateur \"tout le monde\" dans le menu.','Planning','',0,NULL,5),
(4,'Mail-IsEnabled','boolean','0','Active ou désactive l\'envoi des e-mails.','Messagerie','',0,NULL,10),
(5,'Mail-IsMail-IsSMTP','enum','IsSMTP','Utiliser un relais SMTP (IsSMTP) ou le programme \"mail\" du serveur (IsMail).','Messagerie','IsSMTP,IsMail',0,'onchange=\'mail_config();\'',20),
(6,'Mail-Hostname','','','Nom d\'hôte du serveur pour l\'envoi des e-mails.','Messagerie','',0,NULL,30),
(7,'Mail-Host','','','Nom FQDN ou IP du serveur SMTP.','Messagerie','',0,NULL,40),
(8,'Mail-Port','','25','Port du serveur SMTP','Messagerie','',0,NULL,50),
(9,'Mail-SMTPSecure','enum','','Cryptage utilisé par le serveur STMP.','Messagerie',',ssl,tls',0,NULL,60),
(10,'Mail-SMTPAutoTLS','boolean','1','Activer ou désactiver le mode Auto TLS','Messagerie','',1,NULL,70),
(11,'Mail-SMTPAuth','boolean','0','Le serveur SMTP requiert-il une authentification ?','Messagerie','',0,NULL,80),
(12,'Mail-Username','','','Nom d\'utilisateur pour le serveur SMTP.','Messagerie','',0,NULL,90),
(13,'Mail-Password','password','','Mot de passe pour le serveur SMTP.','Messagerie','',0,NULL,100),
(14,'Mail-From','','no-reply@planno.fr','Adresse e-mail de l\'expediteur.','Messagerie','',0,NULL,110),
(15,'Mail-FromName','','Planno','Nom de l\'expediteur.','Messagerie','',0,NULL,120),
(16,'Mail-Signature','textarea','Ce message a été envoyé par Planno.\nMerci de ne pas y répondre.','Signature des e-mails.','Messagerie','',0,NULL,130),
(17,'Mail-Planning','textarea','','Adresses e-mails de la cellule planning, séparées par des ;','Messagerie','',0,NULL,140),
(18,'Dimanche','boolean','0','Utiliser le planning le dimanche',' Divers','',0,NULL,20),
(19,'nb_semaine','enum','1','Nombre de semaine pour la rotation des heures de présence. Les valeurs supérieures à 3 ne peuvent être utilisées que si le paramètre PlanningHebdo est coché','Heures de présence','1,2,3,4,5,6,7,8,9,10',0,NULL,0),
(20,'dateDebutPlHebdo','date','','Date de début permettant la rotation des heures de présence (pour l\'utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)','Heures de présence','',0,NULL,0),
(21,'Planning-IgnoreBreaks','boolean','0','Si cette case est cochée, les périodes de pauses (ex: pause déjeuner) définies dans les heures de présence seront ignorées dans le menu permettant d\'ajouter les agents dans le planning et lors de l\'importation des modèles.','Planning','',0,NULL,0),
(22,'ctrlHresAgents','boolean','1','Contrôle des heures des agents le samedi et le dimanche','Planning','',0,NULL,1),
(23,'agentsIndispo','boolean','1','Afficher les agents indisponibles','Planning','',0,NULL,5),
(24,'Granularite','enum2','1','Granularité des champs horaires.',' Divers','[[1, \"Libre\"],[60,\"Heure\"],[30,\"Demi-heure\"],[15,\"Quart d\'heure\"],[5,\"5 minutes\"]]',0,NULL,30),
(25,'Absences-planning','enum2','','Choix des listes de présence et d\'absences à afficher sous les plannings','Absences','[[0,\"\"],[1,\"simple\"],[2,\"détaillé\"],[3,\"absents et présents\"],[4,\"absents et présents filtrés par site\"]]',0,NULL,25),
(26,'Auth-Mode','enum','SQL','Méthode d\'authentification','Authentification','SQL,LDAP,LDAP-SQL,CAS,CAS-SQL,OpenIDConnect',0,NULL,5),
(27,'Auth-LoginLayout','enum','firstname.lastname','Schéma à utiliser pour la construction des logins','Authentification','firstname.lastname,lastname.firstname,mail,mailPrefix',0,NULL,10),
(28,'Auth-PasswordLength','text','8','Nombre minimum de caractères obligatoires pour le changement de mot de passe.','Authentification','',0,NULL,20),
(29,'Absences-apresValidation','boolean','1','Autoriser l\'enregistrement d\'absences après validation des plannings','Absences','',0,NULL,10),
(30,'Absences-planningVide','boolean','1','Autoriser l\'enregistrement d\'absences sur des plannings en cours d\'élaboration','Absences','',0,NULL,8),
(31,'Multisites-nombre','enum','1','Nombre de sites','Multisites','1,2,3,4,5,6,7,8,9,10',0,NULL,10),
(32,'Multisites-site1','text','','Nom du site N°1','Multisites','',0,NULL,20),
(33,'Multisites-site1-mail','text','','Adresses e-mails de la cellule planning du site N°1, séparées par des ;','Multisites','',0,NULL,25),
(34,'Multisites-site2','text','','Nom du site N°2','Multisites','',0,NULL,30),
(35,'Multisites-site2-mail','text','','Adresses e-mails de la cellule planning du site N°2, séparées par des ;','Multisites','',0,NULL,35),
(36,'Multisites-site3','text','','Nom du site N°3','Multisites','',0,NULL,40),
(37,'Multisites-site3-mail','text','','Adresses e-mails de la cellule planning du site N°3, séparées par des ;','Multisites','',0,NULL,45),
(38,'Multisites-site4','text','','Nom du site N°4','Multisites','',0,NULL,50),
(39,'Multisites-site4-mail','text','','Adresses e-mails de la cellule planning du site N°4, séparées par des ;','Multisites','',0,NULL,55),
(40,'Multisites-site5','text','','Nom du site N°5','Multisites','',0,NULL,60),
(41,'Multisites-site5-mail','text','','Adresses e-mails de la cellule planning du site N°5, séparées par des ;','Multisites','',0,NULL,65),
(42,'Multisites-site6','text','','Nom du site N°6','Multisites','',0,NULL,70),
(43,'Multisites-site6-mail','text','','Adresses e-mails de la cellule planning du site N°6, séparées par des ;','Multisites','',0,NULL,75),
(44,'Multisites-site7','text','','Nom du site N°7','Multisites','',0,NULL,80),
(45,'Multisites-site7-mail','text','','Adresses e-mails de la cellule planning du site N°7, séparées par des ;','Multisites','',0,NULL,85),
(46,'Multisites-site8','text','','Nom du site N°8','Multisites','',0,NULL,90),
(47,'Multisites-site8-mail','text','','Adresses e-mails de la cellule planning du site N°8, séparées par des ;','Multisites','',0,NULL,95),
(48,'Multisites-site9','text','','Nom du site N°9','Multisites','',0,NULL,100),
(49,'Multisites-site9-mail','text','','Adresses e-mails de la cellule planning du site N°9, séparées par des ;','Multisites','',0,NULL,105),
(50,'Multisites-site10','text','','Nom du site N°10','Multisites','',0,NULL,110),
(51,'Multisites-site10-mail','text','','Adresses e-mails de la cellule planning du site N°10, séparées par des ;','Multisites','',0,NULL,115),
(52,'hres4semaines','boolean','0','Afficher le total d\'heures des 4 dernières semaine dans le menu','Planning','',0,NULL,5),
(53,'Auth-Anonyme','boolean','0','Autoriser les logins anonymes','Authentification','',0,NULL,7),
(54,'EDTSamedi','enum2','0','Horaires différents les semaines avec samedi travaillé et semaines à ouverture restreinte. Ce paramètre est ignoré si PlanningHebdo est activé.','Heures de présence','[[0, \"Désactivé\"], [1, \"Horaires différents les semaines avec samedi travaillé\"], [2, \"Horaires différents les semaines avec samedi travaillé et les semaines à ouverture restreinte\"]]',0,NULL,0),
(55,'ClasseParService','boolean','1','Classer les agents par service dans le menu d&eacute;roulant du planning','Planning','',0,NULL,5),
(56,'Alerte2SP','boolean','0','Alerter si l&apos;agent fera 2 plages de service public de suite','Planning','',0,NULL,5),
(57,'CatAFinDeService','boolean','0','Alerter si aucun agent de catégorie A n\'est placé en fin de service','Planning','',0,NULL,2),
(58,'Conges-Recuperations','enum2','0','Traiter les r&eacute;cup&eacute;rations comme les cong&eacute;s (Assembler), ou les traiter s&eacute;par&eacute;ment (Dissocier)','Congés','[[0,\"Assembler\"],[1,\"Dissocier\"]]',0,NULL,3),
(59,'Conges-tous','boolean','0','Autoriser l\'enregistrement de congés pour tous les agents en une fois','Congés','',0,NULL,6),
(60,'Conges-Heures','enum2','0','Permettre la saisie de congés sur quelques heures ou forcer la saisie de congés sur des journées complètes. Paramètre actif avec les options Conges-Mode=Heures et Conges-Recuperations=Dissocier','Congés','[[0,\"Forcer la saisie de congés sur journées entières\"],[1,\"Permettre la saisie de congés sur quelques heures\"]]',0,NULL,3),
(61,'Recup-Agent','enum2','Texte','Type de champ pour la r&eacute;cup&eacute;ration des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu d&eacute;roulant</b>','Congés','[[0,\"\"],[1,\"Texte\"],[2,\"Menu déroulant\"]]',0,NULL,40),
(62,'Recup-SamediSeulement','boolean','0','Autoriser les demandes de récupération des samedis seulement','Congés','',0,NULL,20),
(63,'Recup-Uneparjour','boolean','1','Autoriser une seule demande de r&eacute;cup&eacute;ration par jour','Congés','',0,NULL,19),
(64,'Recup-DeuxSamedis','boolean','0','Autoriser les demandes de récupération pour 2 samedis','Congés','',0,NULL,30),
(65,'Recup-DelaiDefaut','text','7','Delai pour les demandes de récupération par d&eacute;faut (en jours)','Congés','',0,NULL,40),
(66,'Recup-DelaiTitulaire1','enum2','0','Delai pour les demandes de récupération des titulaires pour 1 samedi (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,50),
(67,'Recup-DelaiTitulaire2','enum2','0','Delai pour les demandes de récupération des titulaires pour 2 samedis (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,60),
(68,'Recup-DelaiContractuel1','enum2','0','Delai pour les demandes de récupération des contractuels pour 1 samedi (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,70),
(69,'Recup-DelaiContractuel2','enum2','0','Delai pour les demandes de récupération des contractuels pour 2 samedis (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]',0,NULL,80),
(70,'Recup-notifications1','checkboxes','[2]','Destinataires des notifications de nouvelles demandes de crédit de récupérations','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(71,'Recup-notifications2','checkboxes','[2]','Destinataires des notifications de modification de crédit de récupérations','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(72,'Recup-notifications3','checkboxes','[1]','Destinataires des notifications des validations de crédit de récupérations niveau 1','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(73,'Recup-notifications4','checkboxes','[3]','Destinataires des notifications des validations de crédit de récupérations niveau 2','Congés','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]',0,NULL,100),
(74,'Conges-planningVide','boolean','1','Autoriser l\'enregistrement de congés sur des plannings en cours d\'élaboration','Congés','',0,NULL,11),
(75,'Conges-apresValidation','boolean','1','Autoriser l\'enregistrement de congés après validation des plannings','Congés','',0,NULL,12),
(76,'Conges-Rappels','boolean','0','Activer / D&eacute;sactiver l&apos;envoi de rappels s&apos;il y a des cong&eacute;s non-valid&eacute;s','Congés','',0,NULL,6),
(77,'Conges-Rappels-Jours','text','14','Nombre de jours &agrave; contr&ocirc;ler pour l&apos;envoi de rappels sur les cong&eacute;s non-valid&eacute;s','Congés','',0,NULL,7),
(78,'Conges-demi-journees','boolean','0','Autorise la saisie de congés en demi-journée. Fonctionne uniquement avec le mode de saisie en jour','Congés','',0,NULL,8),
(79,'Conges-fullday-switching-time','text','4','Temps définissant la bascule entre une demi-journée et une journée complète lorsque les crédits de congés sont comptés en jours. Format : entier ou décimal. Exemple : pour 3h30, tapez 3.5','Congés','',0,NULL,9),
(80,'Conges-fullday-reference-time','text','','Temps de référence (en heures) pour une journée complète. Si ce champ est renseigné et que les crédits de congés sont gérés en jours, la différence de temps de chaque journée sera créditée ou débitée du solde des récupérations. Format : entier ou décimal. Exemple : pour 7h30, tapez 7.5','Congés','',0,NULL,10),
(81,'Conges-Rappels-N1','checkboxes','[\"Mail-Planning\"]','A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 1','Congés','[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]',0,NULL,14),
(82,'Conges-Rappels-N2','checkboxes','[\"mails_responsables\"]','A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 2','Congés','[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]',0,NULL,15),
(83,'Conges-validation','boolean','1','Les congés doivent être validés par un administrateur avant d\'être pris en compte','Congés','',0,NULL,4),
(84,'Conges-Validation-N2','enum2','0','La validation niveau 2 des cong&eacute;s peut se faire directement ou doit attendre la validation niveau 1','Congés','[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le cong&eacute; doit &ecirc;tre valid&eacute; au niveau 1\"]]',0,NULL,5),
(85,'Absences-Validation-N2','enum2','0','La validation niveau 2 des absences peut se faire directement ou doit attendre la validation niveau 1','Absences','[[0,\"Validation directe autoris&eacute;e\"],[1,\"L\'absence doit &ecirc;tre valid&eacute; au niveau 1\"]]',0,NULL,31),
(86,'Conges-Enable','boolean','0','Activer le module Congés','Congés','',0,NULL,1),
(87,'Conges-Mode','enum2','heures','Décompte des congés en heures ou en jours','Congés','[[\"heures\",\"Heures\"],[\"jours\",\"Jours\"]]',0,NULL,2),
(88,'Conges-transfer-comp-time','boolean','0','Transférer les récupérations restantes sur le reliquat','Congés','',0,NULL,16),
(89,'Absences-validation','boolean','0','Les absences doivent &ecirc;tre valid&eacute;es par un administrateur avant d&apos;&ecirc;tre prises en compte','Absences','',0,NULL,30),
(90,'Absences-blocage','boolean','0','Permettre le blocage des absences et congés sur une période définie par les gestionnaires. Ce paramètre empêchera les agents qui n\'ont pas le droits de gérer les absences d\'enregistrer absences et congés sur les périodes définies. En configuration multi-sites, les agents de tous les sites seront bloqués sans distinction.','Absences','',0,NULL,5),
(91,'Absences-non-validees','boolean','1','Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e','Absences','',0,NULL,35),
(92,'Absences-agent-preselection','boolean','1','Présélectionner l&apos;agent connecté lors de l&apos;ajout d&apos;une nouvelle absence.','Absences','',0,NULL,36),
(93,'Absences-journeeEntiere','boolean','1','Le paramètre \"Journée(s) entière(s)\" est coché par défaut lors de la saisie d\'une absence.','Absences','',0,NULL,38),
(94,'Absences-tous','boolean','0','Autoriser l&apos;enregistrement d&apos;absences pour tous les agents en une fois','Absences','',0,NULL,37),
(95,'Absences-adminSeulement','boolean','0','Autoriser la saisie des absences aux administrateurs seulement.','Absences','',0,NULL,20),
(96,'Planning-sansRepas','boolean','1','Afficher une notification pour les Sans Repas dans le menu d&eacute;roulant et dans le planning','Planning','',0,NULL,10),
(97,'Planning-dejaPlace','boolean','1','Afficher une notification pour les agents d&eacute;j&agrave; plac&eacute; sur un poste dans le menu d&eacute;roulant du planning','Planning','',0,NULL,20),
(98,'Planning-Heures','boolean','1','Afficher les heures &agrave; c&ocirc;t&eacute; du nom des agents dans le menu du planning','Planning','',0,NULL,25),
(99,'Planning-CommentairesToujoursActifs','boolean','0','Afficher la zone de commentaire m&ecirc;me si le planning n\'est pas encore commenc&eacute;.','Planning','',0,NULL,100),
(100,'Absences-notifications-A1','checkboxes','[0,1,2,3]','Destinataires des notifications de nouvelles absences (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,40),
(101,'Absences-notifications-A2','checkboxes','[0,1,2,3]','Destinataires des notifications de modification d&apos;absences (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,50),
(102,'Absences-notifications-A3','checkboxes','[1]','Destinataires des notifications des validations niveau 1 (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,60),
(103,'Absences-notifications-A4','checkboxes','[3]','Destinataires des notifications des validations niveau 2 (Circuit A)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,70),
(104,'Absences-notifications-agent-par-agent','boolean','0','Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres Absences-notifications-A1, A2, A3 et A4 ou B1, B2, B3 et B4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','Absences','',0,NULL,120),
(105,'Absences-notifications-B1','checkboxes','[0,1,2,3]','Destinataires des notifications de nouvelles absences (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,80),
(106,'Absences-notifications-B2','checkboxes','[0,1,2,3]','Destinataires des notifications de modification d&apos;absences (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,90),
(107,'Absences-notifications-B3','checkboxes','[1]','Destinataires des notifications des validations niveau 1 (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,100),
(108,'Absences-notifications-B4','checkboxes','[3]','Destinataires des notifications des validations niveau 2 (Circuit B)','Absences','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]',0,NULL,110),
(109,'Absences-notifications-titre','text','','Titre personnalis&eacute; pour les notifications de nouvelles absences','Absences','',0,NULL,130),
(110,'Absences-notifications-message','textarea','','Message personnalis&eacute; pour les notifications de nouvelles absences','Absences','',0,NULL,140),
(111,'Absences-DelaiSuppressionDocuments','text','365','Les documents associ&eacute;s aux absences sont supprim&eacute;s au-del&agrave; du nombre de jours d&eacute;finis par ce param&egrave;tre.','Absences','',0,NULL,150),
(112,'Absences-Exclusion','enum2','0','Autoriser l\'affectation au planning des agents absents.','Absences','[[0, \"Les agents ayant une absence validée sont exclus des plannings.\"],[1,\"Les agents ayant des absences importées validées peuvent être ajoutés au planning.\"],[2,\"Les agents ayant des absences validées, importées ou non, peuvent être ajoutés au planning.\"]]',0,NULL,160),
(113,'Statistiques-Heures','textarea','','Afficher des statistiques sur les cr&eacute;neaux horaires voulus. Les cr&eacute;neaux doivent &ecirc;tre au format 00h00-00h00 et s&eacute;par&eacute;s par des ; Exemple : 19h00-20h00; 20h00-21h00; 21h00-22h00','Statistiques','',0,NULL,10),
(114,'Affichage-theme','text','default','Th&egrave;me de l&apos;application.','Affichage','',0,NULL,10),
(115,'Affichage-titre','text','','Titre affich&eacute; sur la page d&apos;accueil','Affichage','',0,NULL,20),
(116,'Affichage-etages','boolean','0','Afficher les &eacute;tages des postes dans le planning','Affichage','',0,NULL,30),
(117,'Affichage-Agent','color','#FFF3B3','Couleur des cellules de l\'agent connecté','Affichage','',0,NULL,40),
(118,'Planning-NbAgentsCellule','enum','4','Nombre maximum d\'agents par cellule','Planning','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20',0,NULL,3),
(119,'Planning-lignesVides','boolean','1','Afficher ou non les lignes vides dans les plannings validés','Planning','',0,NULL,4),
(120,'Planning-SR-debut','enum2','11:30:00','Heure de d&eacute;but pour la v&eacute;rification des sans repas','Planning','[[\"11:00:00\",\"11h00\"],[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"]]',0,NULL,11),
(121,'Planning-SR-fin','enum2','14:30:00','Heure de fin pour la v&eacute;rification des sans repas','Planning','[[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"],[\"15:00:00\",\"15h00\"]]',0,NULL,12),
(122,'Planning-Absences-Heures-Hebdo','boolean','0','Prendre en compte les absences pour calculer le nombre d&apos;heures de SP &agrave; effectuer. (Module PlanningHebdo requis)','Planning','',0,NULL,30),
(123,'CAS-Debug','boolean','0','Activer le débogage pour CAS. Créé un fichier \"cas_debug.txt\" dans le dossier \"[TEMP]\"','CAS','',0,NULL,50),
(124,'Planook','hidden','0','Version Lite Planook',' Divers','',0,NULL,0),
(125,'PlanningHebdo','boolean','0','Utiliser le module “Planning Hebdo”. Ce module permet d\'enregistrer plusieurs horaires de présence par agent en définissant des périodes d\'utilisation. (Incompatible avec l\'option EDTSamedi)','Heures de présence','',0,NULL,40),
(126,'PlanningHebdo-Agents','boolean','1','Autoriser les agents à saisir leurs heures de présence (avec le module Planning Hebdo). Les heures saisies devront être validées par un administrateur','Heures de présence','',0,NULL,50),
(127,'PlanningHebdo-Pause2','boolean','0','2 pauses dans une journ&eacute;e','Heures de présence','',0,NULL,60),
(128,'PlanningHebdo-PauseLibre','boolean','0','Ajoute la possibilité de saisir un temps de pause libre dans les heures de présence (Module Planning Hebdo uniquement)','Heures de présence','',0,NULL,65),
(129,'PlanningHebdo-DebutPauseLibre','enum2','12:00:00','Début de période de pause libre','Heures de présence','[[\"11:00:00\",\"11h00\"],[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"]]',0,NULL,66),
(130,'PlanningHebdo-FinPauseLibre','enum2','14:00:00','Fin de période de pause libre','Heures de présence','[[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"],[\"15:00:00\",\"15h00\"]]',0,NULL,67),
(131,'PlanningHebdo-notifications1','checkboxes','[0,4]','Destinataires des notifications d\'enregistrement de nouvelles heures de présence','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,70),
(132,'PlanningHebdo-notifications2','checkboxes','[0,4]','Destinataires des notifications de modification des heures de présence','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,72),
(133,'PlanningHebdo-notifications3','checkboxes','[1]','Destinataires des notifications des validations niveau 1','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,74),
(134,'PlanningHebdo-notifications4','checkboxes','[4]','Destinataires des notifications des validations niveau 2','Heures de présence','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]',0,NULL,76),
(135,'PlanningHebdo-notifications-agent-par-agent','boolean','0','Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres PlanningHebdo-notifications1, 2, 3 et 4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','Heures de présence','',0,NULL,80),
(136,'PlanningHebdo-Validation-N2','enum2','0','La validation niveau 2 des heures de présence peut se faire directement ou doit attendre la validation niveau 1','Heures de présence','[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le planning doit &ecirc;tre valid&eacute; au niveau 1\"]]',0,NULL,85),
(137,'Planning-InitialNotification','enum2','-2','Envoyer une notification aux agents lors de la validation des plannings les concernant','Planning','[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]',0,NULL,40),
(138,'Planning-ChangeNotification','enum2','-2','Envoyer une notification aux agents lors d\'une modification de planning les concernant','Planning','[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]',0,NULL,41),
(139,'Planning-TableauxMasques','boolean','1','Autoriser le masquage de certains tableaux du planning','Planning','',0,NULL,50),
(140,'Planning-AppelDispo','boolean','0','Permettre l&apos;envoi d&apos;un mail aux agents disponibles pour leur demander s&apos;ils sont volontaires pour occuper le poste choisi.','Planning','',0,NULL,60),
(141,'Planning-AppelDispoSujet','text','Appel &agrave; disponibilit&eacute; [poste] [date] [debut]-[fin]','Sujet du mail pour les appels &agrave; disponibilit&eacute;','Planning','',0,NULL,70),
(142,'Planning-AppelDispoMessage','textarea','Chers tous,\n\nLe poste [poste] est vacant le [date] de [debut] &agrave; [fin].\n\nSi vous souhaitez occuper ce poste, vous pouvez r&eacute;pondre &agrave; cet e-mail.\n\nCordialement,\nLa cellule planning','Corps du mail pour les appels &agrave; disponibilit&eacute;','Planning','',0,NULL,80),
(143,'LDAP-Host','','','Nom d&apos;h&ocirc;te ou adresse IP du serveur LDAP','LDAP','',0,NULL,10),
(144,'LDAP-Port','','','Port du serveur LDAP','LDAP','',0,NULL,20),
(145,'LDAP-Protocol','enum','','Protocol utilis&eacute;','LDAP','ldap,ldaps',0,NULL,30),
(146,'LDAP-Suffix','','','Base LDAP','LDAP','',0,NULL,40),
(147,'LDAP-Filter','','','Filtre LDAP. OpenLDAP essayez \"(objectclass=inetorgperson)\" , Active Directory essayez \"(&(objectCategory=person)(objectClass=user))\". Vous pouvez bien-s&ucirc;r personnaliser votre filtre.','LDAP','',0,NULL,50),
(148,'LDAP-RDN','','','DN de connexion au serveur LDAP, laissez vide si connexion anonyme','LDAP','',0,NULL,60),
(149,'LDAP-Password','password','','Mot de passe de connexion','LDAP','',0,NULL,70),
(150,'LDAP-ID-Attribute','enum','uid','Attribut d&apos;authentification (OpenLDAP : uid, Active Directory : samaccountname)','LDAP','uid,samaccountname,supannaliaslogin',0,NULL,80),
(151,'LDAP-Matricule','text','','Attribut &agrave; importer dans le champ matricule (optionnel)','LDAP','',0,NULL,90),
(152,'LDIF-File','text','','Emplacement d\'un fichier LDIF pour l\'importation des agents','LDIF','',0,NULL,10),
(153,'LDIF-ID-Attribute','enum','uid','Attribut d\'authentification (OpenLDAP : uid, Active Directory : samaccountname)','LDIF','uid,samaccountname,supannaliaslogin,employeenumber',0,NULL,20),
(154,'LDIF-Matricule','text','','Attribut à importer dans le champ matricule (optionnel)','LDIF','',0,NULL,30),
(155,'LDIF-Encoding','enum','UTF-8','Encodage de caractères du fichier source','LDIF','UTF-8,ISO-8859-1',0,NULL,40),
(156,'CAS-Hostname','','','Nom d&apos;h&ocirc;te du serveur CAS','CAS','',0,NULL,30),
(157,'CAS-Port','','8080','Port serveur CAS','CAS','',0,NULL,30),
(158,'CAS-Version','enum','2.0','Version du serveur CAS','CAS','2.0,3.0,4.0',0,NULL,30),
(159,'CAS-CACert','','','Chemin absolut du certificat de l&apos;Autorit&eacute; de Certification. Si pas renseign&eacute;, l&apos;identit&eacute; du serveur ne sera pas v&eacute;rifi&eacute;e.','CAS','',0,NULL,30),
(160,'CAS-SSLVersion','enum2','1','Version SSL/TLS &agrave; utiliser pour les &eacute;changes avec le serveur CAS','CAS','[[1,\"TLSv1\"],[4,\"TLSv1_0\"],[5,\"TLSv1_1\"],[6,\"TLSv1_2\"]]',0,NULL,45),
(161,'CAS-LoginAttribute','text','','Attribut CAS à utiliser pour mapper l\'utilisateur si et seulement si l\'UID CAS ne convient pas. Laisser ce champ vide par défaut. Exemple : \"mail\", dans ce cas, l\'adresse mail de l\'utilisateur est fournie par le serveur CAS et elle est renseignée dans le champ \"login\" des fiches agents de Planno.','CAS','',0,NULL,48),
(162,'CAS-URI','','cas','Page de connexion CAS','CAS','',0,NULL,30),
(163,'CAS-URI-Logout','','cas/logout','Page de d&eacute;connexion CAS','CAS','',0,NULL,30),
(164,'Rappels-Actifs','boolean','0','Activer les rappels','Rappels','',0,NULL,10),
(165,'Rappels-Jours','enum2','3','Nombre de jours &agrave; contr&ocirc;ler pour les rappels','Rappels','[[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7]]',0,NULL,20),
(166,'Rappels-Renfort','boolean','0','Contr&ocirc;ler les postes de renfort lors des rappels','Rappels','',0,NULL,30),
(167,'IPBlocker-TimeChecked','text','10','Recherche les &eacute;checs d&apos;authentification lors des N derni&egrave;res minutes. ( 0 = IPBlocker d&eacute;sactiv&eacute; )','Authentification','',0,NULL,40),
(168,'IPBlocker-Attempts','text','5','Nombre d&apos;&eacute;checs d&apos;authentification autoris&eacute;s lors des N derni&egrave;res minutes','Authentification','',0,NULL,50),
(169,'IPBlocker-Wait','text','10','Temps de blocage de l&apos;IP en minutes','Authentification','',0,NULL,60),
(170,'ICS-Server1','text','','URL du 1<sup>er</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server.domain.com/calendars/[email].ics','ICS','',0,NULL,10),
(171,'ICS-Pattern1','text','','Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 1<sup>er</sup> serveur. Ex: Agenda Personnel','ICS','',0,NULL,20),
(172,'ICS-Status1','enum2','CONFIRMED','Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','ICS','[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]',0,NULL,22),
(173,'ICS-Description1','boolean','1','Inclure la description de l\'événement importé dans le commentaire de l\'absence','ICS','',0,NULL,23),
(174,'ICS-Server2','text','','URL du 2<sup>&egrave;me</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics','ICS','',0,NULL,30),
(175,'ICS-Pattern2','text','','Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 2<sup>&egrave;me</sup> serveur. Ex: Congés','ICS','',0,NULL,40),
(176,'ICS-Status2','enum2','CONFIRMED','Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','ICS','[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]',0,NULL,42),
(177,'ICS-Description2','boolean','1','Inclure la description de l\'événement importé dans le commentaire de l\'absence','ICS','',0,NULL,43),
(178,'ICS-Server3','boolean','0','Utiliser une URL d&eacute;finie pour chaque agent dans le menu Administration / Les agents','ICS','',0,NULL,44),
(179,'ICS-Description3','boolean','1','Inclure la description de l\'événement importé dans le commentaire de l\'absence','ICS','',0,NULL,48),
(180,'ICS-Pattern3','text','','Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s depuis l&apos;URL d&eacute;finie dans la fiche des agents. Ex: Agenda personnel','ICS','',0,NULL,45),
(181,'ICS-Status3','enum2','CONFIRMED','Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','ICS','[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]',0,NULL,47),
(182,'ICS-Export','boolean','0','Autoriser l&apos;exportation des plages de service public sous forme de calendriers ICS. Un calendrier par agent, accessible &agrave; l&apos;adresse [SERVER]/ics/calendar.php?login=[login_de_l_agent]','ICS','',0,NULL,60),
(183,'ICS-Code','boolean','1','Prot&eacute;ger les calendriers ICS par des codes de façon &agrave; ce qu&apos;on ne puisse pas deviner les URLs. Si l&apos;option est activ&eacute;e, les URL seront du type : [SERVER]/ics/calendar.php?login=[login_de_l_agent]&amp;code=[code_al&eacute;atoire]','ICS','',0,NULL,70),
(184,'PlanningHebdo-CSV','text','','Emplacement du fichier CSV &agrave; importer (importation automatis&eacute;e) Ex: /dossier/fichier.csv','Heures de présence','',0,NULL,90),
(185,'Agenda-Plannings-Non-Valides','boolean','1','Afficher ou non les plages de service public des plannings non valid&eacute;s dans les agendas.','Agenda','',0,NULL,10),
(186,'Planning-agents-volants','boolean','0','Utiliser le module \"Agents volants\" permettant de diff&eacute;rencier un groupe d&apos;agents dans le planning','Planning','',0,NULL,90),
(187,'Hamac-csv','text','','Chemin d&apos;acc&egrave;s au fichier CSV pour l&apos;importation des absences depuis Hamac','Hamac','',0,NULL,10),
(188,'Hamac-motif','text','','Motif pour les absences import&eacute;s depuis Hamac. Ex: Hamac','Hamac','',0,NULL,20),
(189,'Hamac-status','enum2','1,2,3,5,6','Importer les absences valid&eacute;es et en attente de validation ou seulement les absences valid&eacute;es.','Hamac','[[\"1,2,3,5,6\",\"Valid&eacute;es et en attente de validation\"],[\"2\",\"Valid&eacute;es\"]]',0,NULL,30),
(190,'Hamac-id','enum2','login','Champ Planno à utiliser pour mapper les agents.','Hamac','[[\"login\",\"Login\"],[\"matricule\",\"Matricule\"]]',0,NULL,40),
(191,'Hamac-debug','boolean','0','Active le mode débugage pour l\'importation des absences depuis Hamac. Les informations de débugage sont écrites dans la table \"log\". Attention, si cette option est activée, la taille de la base de données augmente considérablement.','Hamac','',0,NULL,50),
(192,'Journey-time-between-sites','text','0','Temps de trajet moyen entre sites (en minutes)','Planning','',0,NULL,95),
(193,'Journey-time-between-areas','text','0','Temps de trajet moyen entre zones (en minutes)','Planning','',0,NULL,96),
(194,'Journey-time-for-absences','text','0','Temps de trajet moyen entre une absence et un poste de service public (en minutes)','Planning','',0,NULL,97),
(195,'legalNotices','textarea','','Mentions légales (exemple : notice RGPD). La syntaxe markdown peut être utilisée pour la saisie.','Mentions légales','',0,NULL,10),
(196,'OIDC-Provider','text','','OpenID Connect Provider.','OpenID Connect','',1,NULL,10),
(197,'OIDC-CACert','text','','Path to the OpenID Connect CA Certificate.','OpenID Connect','',1,NULL,20),
(198,'OIDC-ClientID','text','','OpenID Connect Client ID (not to be confused with Secret ID).','OpenID Connect','',1,NULL,30),
(199,'OIDC-ClientSecret','text','','OpenID Connect Secret Value (not to be confused with Secret ID).','OpenID Connect','',1,NULL,40),
(200,'OIDC-LoginAttribute','text','','OpenID Connect Login Attribute.','OpenID Connect','',1,NULL,50),
(201,'OIDC-Debug','boolean','0','Debug mode. Logs information to the log table.','OpenID Connect','',1,NULL,60),
(202,'MSGraph-TenantID','text','','MS Graph Tenant ID.','Microsoft Graph API','',1,NULL,10),
(203,'MSGraph-ClientID','text','','MS Graph Client ID (not to be confused with Secret ID).','Microsoft Graph API','',1,NULL,20),
(204,'MSGraph-ClientSecret','text','','MS Graph Secret Value (not to be confused with Secret ID).','Microsoft Graph API','',1,NULL,30),
(205,'MSGraph-LoginSuffix','text','','Suffix that must be added to the Planno login to link with the MS login. Optional, empty by default.','Microsoft Graph API','',1,NULL,40),
(206,'MSGraph-IgnoredStatuses','text','free;tentative','List of statuses to ignore, separated by semicolons. Optional, \"free;tentative\" by default.','Microsoft Graph API','',1,NULL,50),
(207,'MSGraph-AbsenceReason','text','Office 365','Absence Reason to use for imported events. Optional, \"Outlook\" by default.','Microsoft Graph API','',1,NULL,60);
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
  `modification` timestamp NULL DEFAULT NULL,
  `valide_n1` int(11) NOT NULL DEFAULT 0,
  `validation_n1` timestamp NULL DEFAULT NULL,
  `valide` int(11) NOT NULL DEFAULT 0,
  `validation` timestamp NULL DEFAULT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conges`
--

LOCK TABLES `conges` WRITE;
/*!40000 ALTER TABLE `conges` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cron`
--

LOCK TABLES `cron` WRITE;
/*!40000 ALTER TABLE `cron` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `cron` VALUES
(1,'0','0','*','*','*','workingHourDaily','app:workinghour:daily','Daily Cron for Planning Hebdo module','0000-00-00 00:00:00',0),
(2,'0','0','1','1','*','holidayResetRemainder','app:holiday:reset:remainder --force','Reset holiday remainders','0000-00-00 00:00:00',0),
(3,'0','0','1','9','*','holidayResetCredit','app:holiday:reset:credits --force','Reset holiday credits','0000-00-00 00:00:00',0),
(4,'0','0','1','9','*','holidayResetCompTime','app:holiday:reset:comp-time --force','Reset holiday compensatory time','0000-00-00 00:00:00',0);
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
('App\\Migrations\\Version20250829094200','2025-12-09 14:48:33',29),
('App\\Migrations\\Version20250905075236','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20250919105120','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251001063442','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251001100644','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251013130108','2025-12-09 14:48:33',1),
('App\\Migrations\\Version20251017094116','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251031113317','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251103113513','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251110094218','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251114074411','2025-12-09 14:48:33',28),
('App\\Migrations\\Version20251114094436','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251128093056','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251204105518','2025-12-09 14:48:33',0),
('App\\Migrations\\Version20251205162839','2025-12-09 14:48:33',0);
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heures_absences`
--

LOCK TABLES `heures_absences` WRITE;
/*!40000 ALTER TABLE `heures_absences` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heures_sp`
--

LOCK TABLES `heures_sp` WRITE;
/*!40000 ALTER TABLE `heures_sp` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blocker`
--

LOCK TABLES `ip_blocker` WRITE;
/*!40000 ALTER TABLE `ip_blocker` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
set autocommit=0;
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
(4,10,25,'Bloquer les absences','/absence/block','config=Absences-blocage'),
(5,10,30,'Informations','/absences/info','config!=Plannok'),
(6,15,0,'Congés','/holiday/index','config=Conges-Enable'),
(7,15,10,'Liste des cong&eacute;s','/holiday/index','config=Conges-Enable'),
(8,15,15,'Liste des r&eacute;cup&eacute;rations','/holiday/index?recup=1','config=Conges-Enable;Conges-Recuperations'),
(9,15,20,'Poser des cong&eacute;s','/holiday/new','config=Conges-Enable'),
(10,15,24,'Poser des r&eacute;cup&eacute;rations','/comptime/add','config=Conges-Enable;Conges-Recuperations'),
(11,15,26,'Heures supplémentaires','/overtime','config=Conges-Enable'),
(12,15,30,'Informations','/holiday-info','config=Conges-Enable'),
(13,15,40,'Crédits','/holiday/accounts','config=Conges-Enable'),
(14,20,0,'Agenda','/calendar',NULL),
(15,30,0,'Planning','/',NULL),
(16,30,90,'Agents volants','/detached','config=Planning-agents-volants'),
(17,40,0,'Statistiques','/statistics','config!=Planook'),
(18,40,10,'Feuille de temps','/statistics/time','config!=Planook'),
(19,40,20,'Par agent','/statistics/agent','config!=Planook'),
(20,40,30,'Par poste','/statistics/position','config!=Planook'),
(21,40,40,'Par poste (Synth&egrave;se)','/statistics/positionsummary','config!=Planook'),
(22,40,50,'Postes de renfort','/statistics/supportposition','config!=Planook'),
(23,40,24,'Par service','/statistics/service','config!=Planook'),
(24,40,60,'Samedis','/statistics/saturday','config!=Planook'),
(25,40,70,'Absences','/statistics/absence','config!=Planook'),
(26,40,80,'Présents / absents','/statistics/attendeesmissing','config!=Planook'),
(27,40,26,'Par statut','/statistics/status','config!=Planook'),
(28,50,0,'Administration','/admin',NULL),
(29,50,10,'Informations','/admin/info','config!=Planook'),
(30,50,20,'Les activités','/skill','config!=Planook'),
(31,50,30,'Les agents','/agent',NULL),
(32,50,40,'Les postes','/position',NULL),
(33,50,50,'Les mod&egrave;les','/model',NULL),
(34,50,60,'Les tableaux','/framework',NULL),
(35,50,70,'Jours de fermeture','/closingday','config!=Planook&config=Conges-Enable'),
(36,50,75,'Heures de présence','/workinghour','config=PlanningHebdo'),
(37,50,77,'Notifications / Validations','/notification','config=Absences-notifications-agent-par-agent'),
(38,50,80,'Configuration fonctionnelle','/config',NULL),
(39,50,90,'Configuration technique','/config/technical',NULL),
(40,60,0,'Aide','/help',NULL);
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
  `nom` text NOT NULL DEFAULT '',
  `prenom` text NOT NULL DEFAULT '',
  `mail` text NOT NULL DEFAULT '',
  `statut` text NOT NULL DEFAULT '',
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
  `sites` text NOT NULL DEFAULT '',
  `temps` text NOT NULL,
  `informations` text NOT NULL,
  `recup` text NOT NULL,
  `supprime` tinyint(1) NOT NULL DEFAULT 0,
  `mails_responsables` text NOT NULL DEFAULT '',
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel`
--

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `personnel` VALUES
(1,'Administrateur','','','','','','0000-00-00','0000-00-00','','Inactif','[3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301]','admin','5f4dcc3b5aa765d61d8327deb882cf99','Compte créé lors de l\'installation du planning','0000-00-00 00:00:00','',0,'','','','',0,'',NULL,NULL,NULL,'[1,1,1]',1,0,NULL,NULL,NULL,NULL,NULL),
(2,'Tout le monde','','','','','','0000-00-00','0000-00-00','','Actif','[99,100]','','','Compte créé lors de l\'installation du planning','0000-00-00 00:00:00','',0,'','[[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"\",\"\",\"\",\"\"]]','','',0,'',NULL,NULL,NULL,'[1,1,1]',1,0,NULL,NULL,NULL,NULL,NULL);
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
  `update_time` timestamp NULL DEFAULT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_position_history`
--

LOCK TABLES `pl_position_history` WRITE;
/*!40000 ALTER TABLE `pl_position_history` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste`
--

LOCK TABLES `pl_poste` WRITE;
/*!40000 ALTER TABLE `pl_poste` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_cellules`
--

LOCK TABLES `pl_poste_cellules` WRITE;
/*!40000 ALTER TABLE `pl_poste_cellules` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_cellules` VALUES
(1,1,1,0,1),
(2,1,1,0,9),
(3,1,1,1,1),
(4,1,1,1,9),
(5,1,1,3,1),
(6,1,1,4,1),
(7,1,1,6,1),
(8,1,1,7,1),
(9,1,1,7,9),
(10,1,1,8,1),
(11,1,1,8,9),
(12,1,1,9,1),
(13,1,1,9,9),
(14,1,1,10,1),
(15,1,1,10,9),
(16,1,1,11,1),
(17,1,1,11,9),
(18,1,1,12,1),
(19,1,1,14,1),
(20,1,1,15,1),
(21,1,1,15,9),
(22,1,1,16,1),
(23,1,1,16,9),
(24,1,1,17,1),
(25,1,1,17,9),
(26,1,1,18,1),
(27,1,1,18,9),
(28,1,1,19,1),
(29,1,1,19,9),
(30,1,1,20,1),
(31,1,1,20,9),
(32,1,1,21,1),
(33,1,1,21,9),
(34,1,1,22,1),
(35,1,1,22,9),
(36,1,1,23,1),
(37,1,1,23,8),
(38,1,1,23,9),
(39,1,2,0,1),
(40,1,2,0,4),
(41,1,3,0,12),
(42,1,3,1,12),
(43,1,3,2,12),
(44,1,3,3,12),
(45,1,3,4,12),
(46,1,3,5,12),
(47,1,3,6,12),
(48,1,3,7,12),
(49,1,3,8,12),
(50,1,3,9,12),
(51,1,3,10,12);
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
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
(25,'20:00:00','22:00:00',3,1);
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
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_lignes`
--

LOCK TABLES `pl_poste_lignes` WRITE;
/*!40000 ALTER TABLE `pl_poste_lignes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_lignes` VALUES
(1,1,1,0,'24','poste'),
(2,1,1,1,'36','poste'),
(3,1,1,2,'3','ligne'),
(4,1,1,3,'4','poste'),
(5,1,1,4,'5','poste'),
(6,1,1,6,'6','poste'),
(7,1,1,7,'7','poste'),
(8,1,1,8,'8','poste'),
(9,1,1,9,'9','poste'),
(10,1,1,10,'10','poste'),
(11,1,1,11,'11','poste'),
(12,1,1,12,'12','poste'),
(13,1,1,13,'4','ligne'),
(14,1,1,15,'13','poste'),
(15,1,1,16,'14','poste'),
(16,1,1,17,'15','poste'),
(17,1,1,18,'16','poste'),
(18,1,1,19,'17','poste'),
(19,1,1,20,'19','poste'),
(20,1,1,21,'20','poste'),
(21,1,1,22,'21','poste'),
(22,1,1,23,'22','poste'),
(23,1,1,0,'Mezzanine','titre'),
(24,1,2,0,'23','poste'),
(25,1,2,0,'Réserve','titre'),
(26,1,3,0,'28','poste'),
(27,1,3,1,'25','poste'),
(28,1,3,2,'26','poste'),
(29,1,3,3,'27','poste'),
(30,1,3,4,'29','poste'),
(31,1,3,5,'30','poste'),
(32,1,3,6,'31','poste'),
(33,1,3,7,'32','poste'),
(34,1,3,8,'33','poste'),
(35,1,3,9,'34','poste'),
(36,1,3,10,'35','poste'),
(37,1,3,0,'Rangement','titre');
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_modeles`
--

LOCK TABLES `pl_poste_modeles` WRITE;
/*!40000 ALTER TABLE `pl_poste_modeles` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_modeles_tab`
--

LOCK TABLES `pl_poste_modeles_tab` WRITE;
/*!40000 ALTER TABLE `pl_poste_modeles_tab` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_tab`
--

LOCK TABLES `pl_poste_tab` WRITE;
/*!40000 ALTER TABLE `pl_poste_tab` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pl_poste_tab` VALUES
(1,1,'Tableau 1',1,NULL);
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_tab_affect`
--

LOCK TABLES `pl_poste_tab_affect` WRITE;
/*!40000 ALTER TABLE `pl_poste_tab_affect` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_tab_grp`
--

LOCK TABLES `pl_poste_tab_grp` WRITE;
/*!40000 ALTER TABLE `pl_poste_tab_grp` DISABLE KEYS */;
set autocommit=0;
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pl_poste_verrou`
--

LOCK TABLES `pl_poste_verrou` WRITE;
/*!40000 ALTER TABLE `pl_poste_verrou` DISABLE KEYS */;
set autocommit=0;
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
  `modification` timestamp NULL DEFAULT NULL,
  `valide_n1` int(11) NOT NULL DEFAULT 0,
  `validation_n1` timestamp NULL DEFAULT NULL,
  `valide` int(11) NOT NULL DEFAULT 0,
  `validation` timestamp NULL DEFAULT NULL,
  `actuel` int(1) NOT NULL DEFAULT 0,
  `remplace` int(11) NOT NULL DEFAULT 0,
  `cle` varchar(100) DEFAULT NULL,
  `exception` int(11) NOT NULL DEFAULT 0,
  `nb_semaine` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cle` (`cle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planning_hebdo`
--

LOCK TABLES `planning_hebdo` WRITE;
/*!40000 ALTER TABLE `planning_hebdo` DISABLE KEYS */;
set autocommit=0;
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
  `nom` text NOT NULL DEFAULT '',
  `groupe` text NOT NULL DEFAULT '',
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
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
(36,'Renseignement kiosque','',0,'Renfort','1','[9,10]',1,0,1,0,1,NULL,NULL,1);
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
  `modification` timestamp NULL DEFAULT NULL,
  `valide_n1` int(11) NOT NULL DEFAULT 0,
  `validation_n1` datetime DEFAULT NULL,
  `valide` int(11) NOT NULL DEFAULT 0,
  `validation` timestamp NULL DEFAULT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responsables`
--

LOCK TABLES `responsables` WRITE;
/*!40000 ALTER TABLE `responsables` DISABLE KEYS */;
set autocommit=0;
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
  `valeur` text NOT NULL DEFAULT '',
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
  `valeur` text NOT NULL DEFAULT '',
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
  `valeur` text NOT NULL DEFAULT '',
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
  `valeur` text NOT NULL DEFAULT '',
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
(1,'P&ocirc;le public',1,''),
(2,'P&ocirc;le conservation',2,''),
(3,'P&ocirc;le collection',3,''),
(4,'P&ocirc;le informatique',4,''),
(5,'P&ocirc;le administratif',5,''),
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
  `valeur` text NOT NULL DEFAULT '',
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
(2,'Biblioth&eacute;caire',2,'',1),
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `volants`
--

LOCK TABLES `volants` WRITE;
/*!40000 ALTER TABLE `volants` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `volants` ENABLE KEYS */;
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

-- Dump completed on 2025-12-09 14:50:39
