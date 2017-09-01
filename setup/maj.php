<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : setup/maj.php
Création : mai 2011
Dernière modification : 29 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Ce fichier permet de mettre à jour la base de données lors de la mise à jour de l'application.
Cette page est appelée par la page index.php si la version du fichier index.php et différente de la version enregistrée
dans la base de données
*/

$CSRFToken = CSRFToken();

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if(__FILE__ == $_SERVER['SCRIPT_FILENAME']){
  include_once "../include/accessDenied.php";
  exit;
}

echo "Mise &agrave; jour de la base de donn&eacute;es version {$config['Version']} --> $version<br/>\n";
if($config['Version']<"2.0"){
  echo "<br/>Vous devez d'abord installer la version 2.0<br/>\n";
  exit;
}
$sql=array();


$v="2.0";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Planning' WHERE `nom`='CatAFinDeService';";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Planning' WHERE `nom`='ctrlHresAgents';";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Heures de pr&eacute;sence' WHERE `nom`='nb_semaine';";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Heures de pr&eacute;sence' WHERE `nom`='dateDebutPlHebdo';";
  $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Heures de pr&eacute;sence' WHERE `nom`='EDTSamedi';";

  // Si le plugin n'était pas installé, on créé les tables et apporte les modifications nécessaires
  $db=new db();
  $db->select2("plugins","*",array("nom"=>"planningHebdo"));
  if(!$db->result){
    // Intégration du plugin Planning Hebdo
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
      ('PlanningHebdo', 'boolean', '0', 'Heures de pr&eacute;sence','40', 'Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)');";

    // Droits d'accès
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - Index','24','Gestion des plannings de présences','planningHebdo/index.php');";
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - Configuration','24','Gestion des plannings de présences','planningHebdo/configuration.php');";
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Modif','100','planningHebdo/modif.php');";
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Mon Compte','100','planningHebdo/monCompte.php');";
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Validation','100','planningHebdo/valid.php');";
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - suppression','24','Gestion des plannings de présences','planningHebdo/supprime.php');";

    // Création des tables
    $sql[]="CREATE TABLE `{$dbprefix}planningHebdo` (
      `id` INT(11) NOT NULL AUTO_INCREMENT, 
      `perso_id` INT(11) NOT NULL, 
      `debut` DATE NOT NULL, 
      `fin` DATE NOT NULL, 
      `temps` TEXT NOT NULL, 
      `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
      `modif` INT(11) NOT NULL DEFAULT '0',
      `modification` TIMESTAMP, 
      `valide` INT(11) NOT NULL DEFAULT '0',
      `validation` TIMESTAMP, 
      `actuel` INT(1) NOT NULL DEFAULT '0', 
      `remplace` INT(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`))
      ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    $sql[]="CREATE TABLE `{$dbprefix}planningHebdoPeriodes` (
      `id` INT(11) NOT NULL AUTO_INCREMENT, 
      `annee` VARCHAR(9), 
      `dates` TEXT,
      PRIMARY KEY (`id`))
      ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    // Menu administration
    $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES (50,75,'Plannings de présence','planningHebdo/index.php');";

    // Cron
    $sql[]="INSERT INTO `{$dbprefix}cron` (`h`,`m`,`dom`,`mon`,`dow`,`command`,`comments`) VALUES ('0','0','*','*','*','planningHebdo/cron.daily.php','Daily Cron for planningHebdo module');";

    // Périodes définies
    $periodesDefinies=0;
    $notifications="droit";
  }
  // Si le plugin était installé, on met à jour les liens
  else{
    // Intégration du plugin Planning Hebdo
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
      ('PlanningHebdo', 'boolean', '1', 'Heures de pr&eacute;sence','40', 'Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)');";

    // Modification des URL
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='Gestion des plannings de présences' WHERE `groupe_id`='24';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/index.php' WHERE `page`='plugins/planningHebdo/index.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/configuration.php' WHERE `page`='plugins/planningHebdo/configuration.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/modif.php' WHERE `page`='plugins/planningHebdo/modif.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/monCompte.php' WHERE `page`='plugins/planningHebdo/monCompte.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/valid.php' WHERE `page`='plugins/planningHebdo/valid.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/supprime.php' WHERE `page`='plugins/planningHebdo/supprime.php';";
    $sql[]="UPDATE `{$dbprefix}menu` SET `url`='planningHebdo/index.php' WHERE `url`='plugins/planningHebdo/index.php';";
    $sql[]="UPDATE `{$dbprefix}cron` SET `command`='planningHebdo/cron.daily.php' WHERE `command`='plugins/planningHebdo/cron.daily.php';";

    // Suppression de la table plugins
    $sql[]="DELETE FROM `{$dbprefix}plugins` WHERE `nom`='planningHebdo';";

    // Configuration : Périodes définies
    $db=new db();
    $db->query("SELECT `valeur` FROM `{$dbprefix}planningHebdoConfig` WHERE `nom`='periodesDefinies';");
    $periodesDefinies=$db->result[0]['valeur'];

    // Configuration : Notifications
    $db=new db();
    $db->query("SELECT `valeur` FROM `{$dbprefix}planningHebdoConfig` WHERE `nom`='notifications';");
    $notifications=$db->result[0]['valeur'];

    $sql[]="DROP TABLE `{$dbprefix}planningHebdoConfig`;";
  }

  $sql[]="ALTER TABLE `{$dbprefix}menu` ADD `condition` VARCHAR(100) NULL;";
  $sql[]="UPDATE `{$dbprefix}menu` SET `condition`='config=PlanningHebdo' WHERE `url`='planningHebdo/index.php';";

  // Planning Hebdo, saisie réservée aux admins
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
    ('PlanningHebdo-Agents', 'boolean', '1', 'Heures de pr&eacute;sence','50', 'Autoriser les agents &agrave; saisir leurs plannings de pr&eacute;sence (avec le module Planning Hebdo). Les plannings saisis devront &ecirc;tre valid&eacute;s par un administrateur.');";

  // Configuration : périodes définies
  // Période définies = 0 pour le moment. Option plus utilisée par la BUA. Développements complexes.
/*
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
    ('PlanningHebdo-PeriodesDefinies', 'hidden', '$periodesDefinies', 'Heures de pr&eacute;sence','60', 'Utiliser des périodes définies pour les plannings hebdomadaires (Module Planning Hebdo)');";
*/
  // Configuration : notifications
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES 
    ('PlanningHebdo-Notifications', 'enum2', '$notifications', '[[\"droit\",\"Agents ayant le droit de g&eacute;rer les plannings de pr&eacute;sence\"],[\"Mail-Planning\",\"Cellule planning\"]]', 'Heures de pr&eacute;sence','70', 'A qui envoyer les notifications de nouveaux plannings de pr&eacute;sence (Module Planning Hebdo)');";

  // Mise à jour des paramètres pour CAS
  $db=new db();
  $db->query("SELECT `valeur` FROM `{$dbprefix}config` WHERE `nom`='CAS-Version';");
  $casVersion=$db->result[0]['valeur'];

  $sql[]="UPDATE `{$dbprefix}config` SET type='enum', valeur='$casVersion', valeurs='2,3,4' WHERE `nom`='CAS-Version';";
  $sql[]="UPDATE `{$dbprefix}config` SET commentaires='Chemin absolut du certificat de l&apos;Autorit&eacute; de Certification. Si pas renseign&eacute;, l&apos;identit&eacute; du serveur ne sera pas v&eacute;rifi&eacute;e.' WHERE `nom`='CAS-CACert';";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES 
    ('CAS-SSLVersion', 'enum2', '1', '[[1,\"TLSv1\"],[4,\"TLSv1_0\"],[5,\"TLSv1_1\"],[6,\"TLSv1_2\"]]', 'CAS','45', 'Version SSL/TLS &agrave; utiliser pour les &eacute;changes avec le serveur CAS');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
    ('CAS-Debug', 'boolean', '0', 'CAS','50', 'Activer le d&eacutebogage pour CAS. Cr&eacute;&eacute; un fichier &quot;cas_debug.txt&quot; dans le dossier &quot;[TEMP]&quot;');";

  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0' WHERE `nom`='Version';";
}

$v="2.0.1";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[]="ALTER TABLE `{$dbprefix}personnel` CHANGE `heuresHebdo` `heuresHebdo` VARCHAR(6);";
  $sql[]="CREATE TABLE `{$dbprefix}heures_Absences` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `semaine` DATE,
  `update_time` INT(11),
  `heures` TEXT,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  $sql[]="CREATE TABLE `{$dbprefix}heures_SP` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `semaine` DATE,
  `update_time` INT(11),
  `heures` TEXT,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('Planning-Absences-Heures-Hebdo', 'boolean', '0', 'Planning','30', 'Prendre en compte les absences pour calculer le nombre d&apos;heures de SP &agrave; effectuer. (Module PlanningHebdo requis)');";

  $sql[]="ALTER TABLE `{$dbprefix}pl_notes` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;";
  $sql[]="ALTER TABLE `{$dbprefix}pl_notes` ADD `perso_id` INT NOT NULL AFTER `text`;";
  $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`) VALUES 
    ('Modification des commentaires des plannings','801','Modification des commentaires des plannings');";

  $sql[]="ALTER TABLE `{$dbprefix}planningHebdo` ENGINE=MyISAM;";

  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0.1' WHERE `nom`='Version';";
}

$v="2.0.2";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
    ('Planning-Notifications', 'boolean', '0', 'Planning','40', 'Envoyer une notification aux agents lors de la validation des plannings les concernant');";
  $sql[]="CREATE TABLE `{$dbprefix}pl_notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `date` VARCHAR(10),
  `update_time` TIMESTAMP,
  `data` TEXT,
  PRIMARY KEY (`id`))
  ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0.2' WHERE `nom`='Version';";
}


$v="2.0.3";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Modification de la config pour les checkboxes Absences-notification
  $db=new db();
  $db->query("SELECT `valeurs` FROM `{$dbprefix}config` WHERE `nom`='Absences-notifications1';");
  if($db->result[0]['valeurs'] != "[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]"){
    $db=new db();
    $db->query("SELECT `{$dbprefix}config` WHERE `nom` IN ('Absences-notifications1','Absences-notifications2','Absences-notifications3','Absences-notifications4');");
    if($db->result){
      foreach($db->result as $elem){
	$tab=array();
	$valeurs=unserialize(stripslashes($elem['valeur']));
	if(in_array(1,$valeurs)){
	  $tab[]=0;
	}
	if(in_array(2,$valeurs)){
	  $tab[]=1;
	}
	if(in_array(3,$valeurs)){
	  $tab[]=2;
	}
	if(in_array(5,$valeurs)){
	  $tab[]=3;
	}
	$valeurs=addslashes(serialize($tab));
	$sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$valeurs' WHERE `nom`='{$elem['nom']}';";
      }
    }
  }

  $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]'
    WHERE `nom` IN ('Absences-notifications1','Absences-notifications2','Absences-notifications3','Absences-notifications4');";
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0.3' WHERE `nom`='Version';";
}

$v="2.0.4";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Modification de la config pour l'ajout de l'option "Absences-planningVide"
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Absences-planningVide','boolean','1','Absences', 
  'Autoriser le d&eacute;p&ocirc;t d&apos;absences sur des plannings en cours d&apos;&eacute;laboration','8');";
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)' WHERE `nom`='PlanningHebdo';";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0.4' WHERE `nom`='Version';";
}

$v="2.0.5";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0.5' WHERE `nom`='Version';";
}

$v="2.1";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Masquer les tableaux du planning
  $sql[]="CREATE TABLE `{$dbprefix}hiddenTables` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `perso_id` int(11) NOT NULL DEFAULT '0',
    `tableau` int(11) NOT NULL DEFAULT '0',
    `hiddenTables` TEXT,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Planning-TableauxMasques','boolean','1','Planning', 'Autoriser le masquage de certains tableaux du planning','50');";

  // Appel à disponibilité
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Planning-AppelDispo','boolean','0','Planning', 'Permettre l&apos;envoi d&apos;un mail aux agents disponibles pour leur demander s&apos;ils sont volontaires pour occuper le poste choisi.','60');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Planning-AppelDispoSujet','text','Appel &agrave; disponibilit&eacute; [poste] [date] [debut]-[fin]','Planning', 'Sujet du mail pour les appels &agrave; disponibilit&eacute;','70');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Planning-AppelDispoMessage','textarea','Chers tous,\n\nLe poste [poste] est vacant le [date] de [debut] &agrave; [fin].\n\nSi vous souhaitez occuper ce poste, vous pouvez r&eacute;pondre &agrave; cet e-mail.\n\nCordialement,\nLa cellule planning','Planning', 'Corp du mail pour les appels &agrave; disponibilit&eacute;','80');";

  $sql[]="CREATE TABLE `{$dbprefix}appelDispo` (
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
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

  // Table log
  $sql[]="CREATE TABLE `{$dbprefix}log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `msg` TEXT NULL,
    `program` VARCHAR(30) NULL,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

  //	Rappels
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Rappels-Actifs','boolean','0','Rappels', 'Activer les rappels','10');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Rappels-Jours','enum2','[[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7]]','3','Rappels', 'Nombre de jours &agrave; contr&ocirc;ler pour les rappels','20');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Rappels-Renfort','boolean','0','Rappels', 'Contr&ocirc;ler les postes de renfort lors des rappels','30');";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.1' WHERE `nom`='Version';";
}

$v="2.2";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
	// IPBlocker
  $sql[]="CREATE TABLE `{$dbprefix}IPBlocker` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
		`ip` VARCHAR(20) NOT NULL,
		`login` VARCHAR(100) NULL,
		`status` VARCHAR(10) NOT NULL,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ip` (`ip`),
    KEY `status` (`status`),
    KEY `timestamp` (`timestamp`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('IPBlocker-TimeChecked','text','10','Authentification', 'Recherche les &eacute;checs d&apos;authentification lors des N derni&egrave;res minutes. ( 0 = IPBlocker d&eacute;sactiv&eacute; )','40');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('IPBlocker-Attempts','text','5','Authentification', 'Nombre d&apos;&eacute;checs d&apos;authentification autoris&eacute;s lors des N derni&egrave;res minutes','50');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('IPBlocker-Wait','text','10','Authentification', 'Temps de blocage de l&apos;IP en minutes','60');";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.2' WHERE `nom`='Version';";
}

$v="2.2.1";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.2.2";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.2.3";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Suppression des tableaux
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_tab` ADD `supprime` TIMESTAMP NULL DEFAULT NULL ;";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_tab_grp` ADD `supprime` TIMESTAMP NULL DEFAULT NULL ;";
  
  // Activités : classes
  $sql[]="ALTER TABLE `{$dbprefix}activites` ADD `classeAgent` VARCHAR(100) NULL DEFAULT NULL, ADD `classePoste` VARCHAR(100) NULL DEFAULT NULL;";
  
  // Tableaux : classes
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste_lignes` CHANGE `type` `type` ENUM('poste','ligne','titre','classe') NOT NULL;"; 

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3.1";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Absences groupées
  $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `groupe` VARCHAR(14) NULL DEFAULT NULL ;";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3.2";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Suppression des postes
  $sql[]="ALTER TABLE `{$dbprefix}postes` ADD supprime DATETIME NULL DEFAULT NULL;";
  // Suppression des activités
  $sql[]="ALTER TABLE `{$dbprefix}activites` ADD supprime DATETIME NULL DEFAULT NULL;";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3.3";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3.4";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Absences
  $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `CALNAME` VARCHAR(300) NOT NULL;";
  $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `iCalKey` TEXT NOT NULL;";
  $sql[]="ALTER TABLE `{$dbprefix}absences` ADD INDEX `CALNAME` (`CALNAME`);";

  // PlanningHebdo
  $sql[]="ALTER TABLE `{$dbprefix}planningHebdo` ADD `key` VARCHAR( 100 ) NULL DEFAULT NULL;";

  // ICS
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Server1','text','ICS', 'URL du 1<sup>er</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server.domain.com/calendars/[email].ics','10');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Pattern1','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 1<sup>er</sup> serveur. Ex: Agenda Personnel','20');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Server2','text','ICS', 'URL du 2<sup>&egrave;me</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics','30');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Pattern2','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 2<sup>&egrave;me</sup> serveur. Ex: Cong&eacute;s','40');";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.1";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Config
  // Supprime les éventuels doublons
  $db=new db();
  $db->select2("config");
  $tmp = array();
  foreach($db->result as $elem){
    if(in_array($elem['nom'], $tmp)){
      $sql[] = "DELETE FROM `{$dbprefix}config` WHERE `id` = '{$elem['id']}';";
    } else {
      $tmp[] = $elem['nom'];
    }
  }
  // Rend le champ `nom` UNIQUE
  $sql[]="ALTER TABLE `{$dbprefix}config` ADD UNIQUE `nom` (`nom`);";

  // Importation CSV des heures de présences
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('PlanningHebdo-CSV','text','Heures de pr&eacute;sence', 'Emplacement du fichier CSV &agrave; importer (importation automatis&eacute;e) Ex: /dossier/fichier.csv','90');";
  
  // Exportation ICS
  $sql[] = "ALTER TABLE `{$dbprefix}personnel` ADD `codeICS` VARCHAR(100) NULL DEFAULT NULL;";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Export', 'boolean', '0', 'ICS', 'Autoriser l&apos;exportation des plages de service public sous forme de calendriers ICS. Un calendrier par agent, accessible &agrave; l&apos;adresse [SERVER]/ics/calendar.php?login=[login_de_l_agent]', '60');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Code', 'boolean', '1', 'ICS', 'Prot&eacute;ger les calendriers ICS par des codes de façon &agrave; ce qu&apos;on ne puisse pas deviner les URLs. Si l&apos;option est activ&eacute;e, les URL seront du type : [SERVER]/ics/calendar.php?login=[login_de_l_agent]&amp;code=[code_al&eacute;atoire]', '70');";
  $sql[]="UPDATE `{$dbprefix}acces` SET `page`='monCompte.php' WHERE `page`='PlanningHebdo/monCompte.php';";
  
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.2";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.3";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[]="UPDATE `{$dbprefix}config` SET `commentaires` = 'Corps du mail pour les appels &agrave; disponibilit&eacute;' WHERE `nom` = 'Planning-AppelDispoMessage';";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.4";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[] = "UPDATE `{$dbprefix}config` SET `nom`='Granularite', `commentaires`='Granularit&eacute; des champs horaires. ATTENTION : le choix \"5 minutes\" est exp&eacute;rimental. Certains calculs peuvent manquer de pr&eacute;cision si la valeur \"5 minutes\" est choisie.', `valeurs`='[[60,\"Heure\"],[30,\"Demi-heure\"],[15,\"Quart d&apos;heure\"],[5,\"5 minutes\"]]' WHERE `nom`='heuresPrecision'";
  $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='60' WHERE `nom` = 'Granularite' and `valeur`='heure'";
  $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='30' WHERE `nom` = 'Granularite' and `valeur`='demi-heure'";
  $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='15' WHERE `nom` = 'Granularite' and `valeur`='quart-heure'";
  $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='5' WHERE `nom` = 'Granularite' and `valeur`='5-min'";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}


$v="2.4.5";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Agenda
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Agenda-Plannings-Non-Valides', 'boolean', '1', 'Agenda', 'Afficher ou non les plages de service public des plannings non valid&eacute;s dans les agendas.', '10');";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.6";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // PlanningHebdo
  $sql[]="ALTER TABLE `{$dbprefix}planningHebdo` CHANGE `key` `cle` VARCHAR( 100 ) NULL DEFAULT NULL;";
  $sql[]="ALTER TABLE `{$dbprefix}planningHebdo` ADD UNIQUE `cle` (`cle`);";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}


$v="2.4.7";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Affichage absences non validées
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Absences-non-validees','boolean','1','Absences', 'Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e','35');";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.8";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires`='Granularit&eacute; des champs horaires.' WHERE `nom`='Granularite';";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.9";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Select groupe (pour les postes)
  $sql[]="CREATE TABLE `{$dbprefix}select_groupes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `valeur` text NOT NULL DEFAULT '',
    `rang` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
  
  $sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `groupe` `groupe` TEXT NOT NULL DEFAULT '';";
  
  // Activités
  $sql[] = "ALTER TABLE `{$dbprefix}activites` DROP `classeAgent`, DROP `classePoste`;";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}


$v="2.5.1";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){

  // Transformation serialized -> JSON
  serializeToJson('personnel','droits', 'id', null, $CSRFToken);
  serializeToJson('personnel','postes', 'id', null, $CSRFToken);
  serializeToJson('personnel','sites', 'id', null, $CSRFToken);
  serializeToJson('postes','activites', 'id', null, $CSRFToken);
  serializeToJson('postes','categories', 'id', null, $CSRFToken);
  serializeToJson('config','valeur','id', array('type'=>'checkboxes'), $CSRFToken);
  serializeToJson('planningHebdoPeriodes','dates', 'id', null, $CSRFToken);
  serializeToJson('personnel','temps', 'id', null, $CSRFToken);
  serializeToJson('planningHebdo','temps', 'id', null, $CSRFToken);

  $sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `categories` `categories` TEXT NULL DEFAULT NULL;";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.2";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // ICS si pas openURL
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Server3','boolean','0','ICS', 'Utiliser une URL d&eacute;finie pour chaque agent dans le menu Administration / Les agents','44');";
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Pattern3','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s depuis l&apos;URL d&eacute;finie dans la fiche des agents. Ex: Agenda personnel','45');";
  $sql[] = "ALTER TABLE `{$dbprefix}personnel` ADD `url_ics` TEXT NULL DEFAULT NULL;";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.3";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page` = 'include/ajoutSelect.php';";
  
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.4";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.5";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $SQL[]="ALTER TABLE `{$dbprefix}pl_poste` ADD INDEX `site` (`site`);";
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.6";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.7";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-ID-Attribute', 'enum', 'uid', 'uid,samAccountName', 'Attribut d&apos;authentification (OpenLDAP : uid, ActiveDirectory : samAccountName)', 'LDAP', 80);";
  
  // Génération d'un CSRF Token
  // PHP 7
  if(phpversion() >= 7){
    if (empty($_SESSION['oups']['CSRFToken'])) {
      $_SESSION['oups']['CSRFToken'] = bin2hex(random_bytes(32));
    }
  }

  // PHP 5.3+
  else{
    if (empty($_SESSION['oups']['CSRFToken'])) {
      if (function_exists('mcrypt_create_iv')) {
        $_SESSION['oups']['CSRFToken'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
      } else {
        $_SESSION['oups']['CSRFToken'] = bin2hex(openssl_random_pseudo_bytes(32));
      }
    }
  }
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}


$v="2.5.8";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Encodage HTML de la table pl_poste_modeles
  $db = new db();
  $db->select2('pl_poste_modeles');
  if($db->result){
    foreach($db->result as $elem){
      $nom = htmlentities($elem['nom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
      $sql[] = "UPDATE `{$dbprefix}pl_poste_modeles` SET `nom` = '$nom' WHERE `id`='{$elem['id']}';";
    }
  }

  // Encodage HTML de la table personnel, champ service
  $db = new db();
  $db->select2('personnel',array('id','service'));
  if($db->result){
    foreach($db->result as $elem){
      $service = htmlentities($elem['service'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
      $sql[] = "UPDATE `{$dbprefix}personnel` SET `service` = '$service' WHERE `id`='{$elem['id']}';";
    }
  }
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // LDAP
  $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Attribut d&apos;authentification (OpenLDAP : uid, Active Directory : samaccountname)', `valeurs` = 'uid,samaccountname' WHERE `nom` = 'LDAP-ID-Attribute';";
  $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = 'samaccountname' WHERE `valeur` = 'samAccountName';";
  $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Filtre LDAP. OpenLDAP essayez \"(objectclass=inetorgperson)\" , Active Directory essayez \"(&(objectCategory=person)(objectClass=user))\". Vous pouvez bien-s&ucirc;r personnaliser votre filtre.' WHERE `nom` = 'LDAP-Filter';";

   // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.1";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  $sql[]="ALTER TABLE `{$dbprefix}personnel` ENGINE=MyISAM;";
   // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.3";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
   // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.4";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Renomme les tables en minuscules
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}appelDispo` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}appelDispo` TO `{$dbprefix}appel_dispo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}EDTSamedi` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}EDTSamedi` TO `{$dbprefix}edt_samedi`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}heures_Absences` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}heures_Absences` TO `{$dbprefix}heures_absences`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}heures_SP` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}heures_SP` TO `{$dbprefix}heures_sp`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}hiddenTables` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}hiddenTables` TO `{$dbprefix}hidden_tables`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    $sql[] = "ALTER TABLE `{$dbprefix}hidden_tables` CHANGE `hiddenTables` `hidden_tables` TEXT NULL DEFAULT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}IPBlocker` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}IPBlocker` TO `{$dbprefix}ip_blocker`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}joursFeries` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}joursFeries` TO `{$dbprefix}jours_feries`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}planningHebdo` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}planningHebdo` TO `{$dbprefix}planning_hebdo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}planningHebdoPeriodes` WHERE 1; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}planningHebdoPeriodes` TO `{$dbprefix}planning_hebdo_periodes`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }

  // Renomme les champs en minuscules
  $sql[] = "ALTER TABLE `{$dbprefix}absences` CHANGE `CALNAME` `cal_name` VARCHAR(300) NOT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}absences` DROP INDEX `CALNAME`, ADD INDEX `cal_name` (`cal_name`) USING BTREE; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}absences` CHANGE `iCalKey` `ical_key` TEXT NOT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}absences` CHANGE `valideN1` `valide_n1` INT(11) NOT NULL DEFAULT 0; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}absences` CHANGE `validationN1` `validation_n1` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `Lundi` `lundi` INT; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `Mardi` `mardi` INT; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `Mercredi` `mercredi` INT; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `Jeudi` `jeudi` INT; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `Vendredi` `vendredi` INT; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `Samedi` `samedi` INT; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `Dimanche` `dimanche` INT; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `heuresHebdo` `heures_hebdo` VARCHAR(6) NOT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `heuresTravail` `heures_travail` FLOAT(5) NOT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `mailsResponsables` `mails_responsables` TEXT NOT NULL DEFAULT ''; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  $sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `codeICS` `code_ics` VARCHAR(100) NULL DEFAULT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.8";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Renomme les tables en minuscules si elles étaient déjà en minuscules (ajout d'undercores)
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}appeldispo` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}appeldispo` TO `{$dbprefix}appel_dispo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}edtsamedi` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}edtsamedi` TO `{$dbprefix}edt_samedi`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}hiddentables` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}hiddentables` TO `{$dbprefix}hidden_tables`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    $sql[] = "ALTER TABLE `{$dbprefix}hidden_tables` CHANGE `hiddentables` `hidden_tables` TEXT NULL DEFAULT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}ipblocker` WHERE 1;");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}ipblocker` TO `{$dbprefix}ip_blocker`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}joursferies` WHERE 1; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}joursferies` TO `{$dbprefix}jours_feries`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}planninghebdo` WHERE 1; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}planninghebdo` TO `{$dbprefix}planning_hebdo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }
  $db=new db();
  $db->query("SELECT count(*) FROM `{$dbprefix}planninghebdoperiodes` WHERE 1; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e");
  if($db->result){
    $sql[] = "RENAME TABLE `{$dbprefix}planninghebdoperiodes` TO `{$dbprefix}planning_hebdo_periodes`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
  }

  // Contrôle des tables
  $check_tables = true;
  
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.9";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.91";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // Mise à jour des menus déroulants (select_) : encodage html des valeurs
  $menus = array('abs', 'categories', 'etages', 'groupes', 'services', 'statuts');
  foreach($menus as $m){
    $db = new db();
    $db->query("SELECT `id`, `valeur` FROM `{$dbprefix}select_$m`;");
    if($db->result){
      foreach($db->result as $r){
        $val = htmlentities($r['valeur'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
        $sql[] = "UPDATE `{$dbprefix}select_$m` SET `valeur`='$val' WHERE `id`='{$r['id']}';";
      }
    }
  }
  
  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7";
if(strcmp($v,$config['Version'])>0 and strcmp($v,$version)<=0){
  // 2 pauses par jour
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('PlanningHebdo-Pause2', 'boolean', '0', '2 pauses dans une journ&eacute;e', 'Heures de pr&eacute;sence', 60);";

  $sql[] = "UPDATE `{$dbprefix}personnel` SET `actif`='Supprim&eacute;' WHERE `actif` LIKE 'Supprim%';";
  
  $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('URL', 'info', '', 'URL de l&apos;application',' Divers','','10');";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='20' WHERE `nom`='Dimanche';";
  $sql[]="UPDATE `{$dbprefix}config` SET `ordre`='30' WHERE `nom`='Granularite';";

  // Griser les cellules des plannings
  $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`) VALUES ('Griser les cellules des plannings','901','Griser les cellules des plannings');";
  $sql[]="ALTER TABLE `{$dbprefix}pl_poste` ADD `grise` ENUM ('0','1') DEFAULT '0';";

  // Version
  $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
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

if(isset($check_tables) and $check_tables === true){
  echo "<p><h3>V&eacute;rification des tables</h3>\n";
  $tables = array('appel_dispo', 'edt_samedi', 'heures_absences', 'heures_sp', 'hidden_tables', 'ip_blocker', 'jours_feries', 'planning_hebdo', 'planning_hebdo_periodes');
  foreach($tables as $elem){
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}{$elem}` WHERE 1;");
    if($db->result){
      echo "$elem : <font style='color:green;'>OK</font><br/>\n";
    }else{
      echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
    }
  }
  echo "</p>\n";


  echo "<p><h3>V&eacute;rification des champs</h3>\n";
  $champs = array(
    array('absences', array('cal_name','ical_key','valide_n1','validation_n1')),
    array('pl_poste_tab_grp', array('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche')),
    array('personnel', array('heures_hebdo','heures_travail','mails_responsables','code_ics')));
  foreach($champs as $elem){
    foreach($elem[1] as $field){
      $db=new db();
      $db->query("SELECT `$field` FROM `{$dbprefix}{$elem[0]}` WHERE 1");
      if($db->error){
        echo "Table {$elem[0]}, champs $field : <font style='color:red;'>Erreur</font><br/>\n";
      }else{
        echo "Table {$elem[0]}, champs $field : <font style='color:green;'>OK</font><br/>\n";
      }
    }
  }
  echo "</p>\n";
}

echo "<br/><br/><a href='index.php'>Continuer</a>\n";
include "include/footer.php";

/**
 * serializeToJson
 * Convertit les données seriali en json dans la base de données
 * @param string $table : nom de la table
 * @param string $field : nom du champ à modifier
 * @param string $id : nom du champ ID (clé)
 * @param array $where : condition sql where : ex: array('type'=>'checkboxes')
 */
function serializeToJson($table,$field,$id='id',$where=null, $CSRFToken){
  // Transformation serialized  -> json
  $dbh = new dbh();
  $dbh->CSRFToken = $CSRFToken;
  $dbh->prepare("UPDATE `{$GLOBALS['config']['dbprefix']}$table` SET `$field`=:value WHERE `$id`=:key;");
  echo "UPDATE `{$GLOBALS['config']['dbprefix']}$table` SET `$field`=:value WHERE `$id`=:key;<br/>";

  $db = new db();
  $db->select2($table,array($id,$field),$where);

  if($db->result){
    foreach($db->result as $elem){
      $value = $elem[$field];
      if($value){
        $value = unserialize(html_entity_decode($value,ENT_QUOTES|ENT_IGNORE,'UTF-8'));
        if(is_array($value)){
          $value = json_encode($value);
          $dbh->execute(array(':key'=>$elem[$id], ':value'=>$value));
          echo ":key => {$elem[$id]}, :value' => {$value}";
          if(!$dbh->error)
            echo " : <font style='color:green;'>OK</font><br/>\n";
          else
            echo " : <font style='color:red;'>Erreur</font><br/>\n";
        }
      }
    }
  }
}


exit;
?>