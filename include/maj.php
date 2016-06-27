<?php
/**
Planning Biblio, Version 2.3.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : include/maj.php
Création : mai 2011
Dernière modification : 27 juin 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Ce fichier permet de mettre à jour la base de données lors de la mise à jour de l'application.
Cette page est appelée par la page index.php si la version du fichier index.php et différente de la version enregistrée
dans la base de données
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if(__FILE__ == $_SERVER['SCRIPT_FILENAME']){
  include_once "accessDenied.php";
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

exit;
?>