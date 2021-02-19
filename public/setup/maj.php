<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : setup/maj.php
Création : mai 2011
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Ce fichier permet de mettre à jour la base de données lors de la mise à jour de l'application.
Cette page est appelée par la page index.php si la version du fichier index.php et différente de la version enregistrée
dans la base de données
*/


// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once(__DIR__.'/../include/accessDenied.php');
    exit;
}

$CSRFToken = CSRFToken();

$cli = isset($argv[0]);

echo "Mise à jour de la base de données version {$config['Version']} --> $version\n";
if (! $cli) {
    echo "<br/>\n";
}

if (version_compare($config['Version'], "2.0") === -1) {
    echo "<br/>Vous devez d'abord installer la version 2.0<br/>\n";
    exit;
}
$sql=array();


$v="2.0";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Planning' WHERE `nom`='CatAFinDeService';";
    $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Planning' WHERE `nom`='ctrlHresAgents';";
    $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Heures de pr&eacute;sence' WHERE `nom`='nb_semaine';";
    $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Heures de pr&eacute;sence' WHERE `nom`='dateDebutPlHebdo';";
    $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Heures de pr&eacute;sence' WHERE `nom`='EDTSamedi';";

    // Si le plugin n'était pas installé, on créé les tables et apporte les modifications nécessaires
    $db=new db();
    $db->select2("plugins", "*", array("nom"=>"planningHebdo"));
    if (!$db->result) {
        // Intégration du plugin Planning Hebdo
        $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
      ('PlanningHebdo', 'boolean', '0', 'Heures de pr&eacute;sence','40', 'Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)');";

        // Droits d'accès
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - Index','24','Gestion des plannings de présences','planningHebdo/index.php');";
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

        // Menu administration
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`) VALUES (50,75,'Plannings de présence','planningHebdo/index.php');";

        // Cron
        $sql[]="INSERT INTO `{$dbprefix}cron` (`h`,`m`,`dom`,`mon`,`dow`,`command`,`comments`) VALUES ('0','0','*','*','*','planningHebdo/cron.daily.php','Daily Cron for planningHebdo module');";

        $notifications="droit";
    }
    // Si le plugin était installé, on met à jour les liens
    else {
        // Intégration du plugin Planning Hebdo
        $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
      ('PlanningHebdo', 'boolean', '1', 'Heures de pr&eacute;sence','40', 'Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)');";

        // Modification des URL
        $sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='Gestion des plannings de présences' WHERE `groupe_id`='24';";
        $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/index.php' WHERE `page`='plugins/planningHebdo/index.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/modif.php' WHERE `page`='plugins/planningHebdo/modif.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/monCompte.php' WHERE `page`='plugins/planningHebdo/monCompte.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/valid.php' WHERE `page`='plugins/planningHebdo/valid.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET `page`='planningHebdo/supprime.php' WHERE `page`='plugins/planningHebdo/supprime.php';";
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='planningHebdo/index.php' WHERE `url`='plugins/planningHebdo/index.php';";
        $sql[]="UPDATE `{$dbprefix}cron` SET `command`='planningHebdo/cron.daily.php' WHERE `command`='plugins/planningHebdo/cron.daily.php';";

        // Suppression de la table plugins
        $sql[]="DELETE FROM `{$dbprefix}plugins` WHERE `nom`='planningHebdo';";

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
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {
    // Modification de la config pour les checkboxes Absences-notification
    $db=new db();
    $db->query("SELECT `valeurs` FROM `{$dbprefix}config` WHERE `nom`='Absences-notifications1';");
    if ($db->result[0]['valeurs'] != "[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]") {
        $db=new db();
        $db->query("SELECT `{$dbprefix}config` WHERE `nom` IN ('Absences-notifications1','Absences-notifications2','Absences-notifications3','Absences-notifications4');");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $tab=array();
                $valeurs=unserialize(stripslashes($elem['valeur']));
                if (in_array(1, $valeurs)) {
                    $tab[]=0;
                }
                if (in_array(2, $valeurs)) {
                    $tab[]=1;
                }
                if (in_array(3, $valeurs)) {
                    $tab[]=2;
                }
                if (in_array(5, $valeurs)) {
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
if (version_compare($config['Version'], $v) === -1) {
    // Modification de la config pour l'ajout de l'option "Absences-planningVide"
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Absences-planningVide','boolean','1','Absences', 
  'Autoriser le d&eacute;p&ocirc;t d&apos;absences sur des plannings en cours d&apos;&eacute;laboration','8');";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)' WHERE `nom`='PlanningHebdo';";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0.4' WHERE `nom`='Version';";
}

$v="2.0.5";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2.0.5' WHERE `nom`='Version';";
}

$v="2.1";
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.2.2";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.2.3";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3";
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {
    // Absences groupées
    $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `groupe` VARCHAR(14) NULL DEFAULT NULL ;";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3.2";
if (version_compare($config['Version'], $v) === -1) {
    // Suppression des postes
    $sql[]="ALTER TABLE `{$dbprefix}postes` ADD supprime DATETIME NULL DEFAULT NULL;";
    // Suppression des activités
    $sql[]="ALTER TABLE `{$dbprefix}activites` ADD supprime DATETIME NULL DEFAULT NULL;";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3.3";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.3.4";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4";
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {
    // Config
    // Supprime les éventuels doublons
    $db=new db();
    $db->select2("config");
    $tmp = array();
    foreach ($db->result as $elem) {
        if (in_array($elem['nom'], $tmp)) {
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
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.3";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires` = 'Corps du mail pour les appels &agrave; disponibilit&eacute;' WHERE `nom` = 'Planning-AppelDispoMessage';";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.4";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `nom`='Granularite', `commentaires`='Granularit&eacute; des champs horaires. ATTENTION : le choix \"5 minutes\" est exp&eacute;rimental. Certains calculs peuvent manquer de pr&eacute;cision si la valeur \"5 minutes\" est choisie.', `valeurs`='[[60,\"Heure\"],[30,\"Demi-heure\"],[15,\"Quart d&apos;heure\"],[5,\"5 minutes\"]]' WHERE `nom`='heuresPrecision'";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='60' WHERE `nom` = 'Granularite' and `valeur`='heure'";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='30' WHERE `nom` = 'Granularite' and `valeur`='demi-heure'";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='15' WHERE `nom` = 'Granularite' and `valeur`='quart-heure'";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='5' WHERE `nom` = 'Granularite' and `valeur`='5-min'";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.5";
if (version_compare($config['Version'], $v) === -1) {
    // Agenda
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Agenda-Plannings-Non-Valides', 'boolean', '1', 'Agenda', 'Afficher ou non les plages de service public des plannings non valid&eacute;s dans les agendas.', '10');";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.6";
if (version_compare($config['Version'], $v) === -1) {
    // PlanningHebdo
    $sql[]="ALTER TABLE `{$dbprefix}planningHebdo` CHANGE `key` `cle` VARCHAR( 100 ) NULL DEFAULT NULL;";
    $sql[]="ALTER TABLE `{$dbprefix}planningHebdo` ADD UNIQUE `cle` (`cle`);";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.7";
if (version_compare($config['Version'], $v) === -1) {
    // Affichage absences non validées
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Absences-non-validees','boolean','1','Absences', 'Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e','35');";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.8";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires`='Granularit&eacute; des champs horaires.' WHERE `nom`='Granularite';";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.4.9";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5";
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {

  // Transformation serialized -> JSON
    serializeToJson('personnel', 'droits', 'id', null, $CSRFToken);
    serializeToJson('personnel', 'postes', 'id', null, $CSRFToken);
    serializeToJson('personnel', 'sites', 'id', null, $CSRFToken);
    serializeToJson('postes', 'activites', 'id', null, $CSRFToken);
    serializeToJson('postes', 'categories', 'id', null, $CSRFToken);
    serializeToJson('config', 'valeur', 'id', array('type'=>'checkboxes'), $CSRFToken);
    serializeToJson('personnel', 'temps', 'id', null, $CSRFToken);
    serializeToJson('planningHebdo', 'temps', 'id', null, $CSRFToken);

    $sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `categories` `categories` TEXT NULL DEFAULT NULL;";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.2";
if (version_compare($config['Version'], $v) === -1) {
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
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page` = 'include/ajoutSelect.php';";
  
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.4";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.5";
if (version_compare($config['Version'], $v) === -1) {
    $SQL[]="ALTER TABLE `{$dbprefix}pl_poste` ADD INDEX `site` (`site`);";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.6";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.5.7";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-ID-Attribute', 'enum', 'uid', 'uid,samAccountName', 'Attribut d&apos;authentification (OpenLDAP : uid, ActiveDirectory : samAccountName)', 'LDAP', 80);";
  
    // Génération d'un CSRF Token
    // PHP 7
    if (phpversion() >= 7) {
        if (empty($_SESSION['oups']['CSRFToken'])) {
            $_SESSION['oups']['CSRFToken'] = bin2hex(random_bytes(32));
        }
    }

    // PHP 5.3+
    else {
        if (empty($_SESSION['oups']['CSRFToken'])) {
            $_SESSION['oups']['CSRFToken'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}


$v="2.5.8";
if (version_compare($config['Version'], $v) === -1) {
    // Encodage HTML de la table pl_poste_modeles
    $db = new db();
    $db->select2('pl_poste_modeles');
    if ($db->result) {
        foreach ($db->result as $elem) {
            $nom = htmlentities($elem['nom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
            $sql[] = "UPDATE `{$dbprefix}pl_poste_modeles` SET `nom` = '$nom' WHERE `id`='{$elem['id']}';";
        }
    }

    // Encodage HTML de la table personnel, champ service
    $db = new db();
    $db->select2('personnel', array('id','service'));
    if ($db->result) {
        foreach ($db->result as $elem) {
            $service = htmlentities($elem['service'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
            $sql[] = "UPDATE `{$dbprefix}personnel` SET `service` = '$service' WHERE `id`='{$elem['id']}';";
        }
    }
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6";
if (version_compare($config['Version'], $v) === -1) {
    // LDAP
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Attribut d&apos;authentification (OpenLDAP : uid, Active Directory : samaccountname)', `valeurs` = 'uid,samaccountname' WHERE `nom` = 'LDAP-ID-Attribute';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = 'samaccountname' WHERE `valeur` = 'samAccountName';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Filtre LDAP. OpenLDAP essayez \"(objectclass=inetorgperson)\" , Active Directory essayez \"(&(objectCategory=person)(objectClass=user))\". Vous pouvez bien-s&ucirc;r personnaliser votre filtre.' WHERE `nom` = 'LDAP-Filter';";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.1";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="ALTER TABLE `{$dbprefix}personnel` ENGINE=MyISAM;";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.3";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.4";
if (version_compare($config['Version'], $v) === -1) {
    // Renomme les tables en minuscules
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}appelDispo` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}appelDispo` TO `{$dbprefix}appel_dispo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}EDTSamedi` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}EDTSamedi` TO `{$dbprefix}edt_samedi`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}heures_Absences` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}heures_Absences` TO `{$dbprefix}heures_absences`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}heures_SP` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}heures_SP` TO `{$dbprefix}heures_sp`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}hiddenTables` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}hiddenTables` TO `{$dbprefix}hidden_tables`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
        $sql[] = "ALTER TABLE `{$dbprefix}hidden_tables` CHANGE `hiddenTables` `hidden_tables` TEXT NULL DEFAULT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}IPBlocker` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}IPBlocker` TO `{$dbprefix}ip_blocker`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}joursFeries` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}joursFeries` TO `{$dbprefix}jours_feries`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}planningHebdo` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}planningHebdo` TO `{$dbprefix}planning_hebdo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
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
if (version_compare($config['Version'], $v) === -1) {
    // Renomme les tables en minuscules si elles étaient déjà en minuscules (ajout d'undercores)
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}appeldispo` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}appeldispo` TO `{$dbprefix}appel_dispo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}edtsamedi` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}edtsamedi` TO `{$dbprefix}edt_samedi`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}hiddentables` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}hiddentables` TO `{$dbprefix}hidden_tables`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
        $sql[] = "ALTER TABLE `{$dbprefix}hidden_tables` CHANGE `hiddentables` `hidden_tables` TEXT NULL DEFAULT NULL; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}ipblocker` WHERE 1;");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}ipblocker` TO `{$dbprefix}ip_blocker`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}joursferies` WHERE 1; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}joursferies` TO `{$dbprefix}jours_feries`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }
    $db=new db();
    $db->query("SELECT count(*) FROM `{$dbprefix}planninghebdo` WHERE 1; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e");
    if ($db->result) {
        $sql[] = "RENAME TABLE `{$dbprefix}planninghebdo` TO `{$dbprefix}planning_hebdo`; # Si une erreur est affich&eacute;e, elle peut &ecirc;tre ignor&eacute;e";
    }

    // Contrôle des tables
    $check_tables = true;
  
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.9";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.6.91";
if (version_compare($config['Version'], $v) === -1) {
    // Mise à jour des menus déroulants (select_) : encodage html des valeurs
    $menus = array('abs', 'categories', 'etages', 'groupes', 'services', 'statuts');
    foreach ($menus as $m) {
        $db = new db();
        $db->query("SELECT `id`, `valeur` FROM `{$dbprefix}select_$m`;");
        if ($db->result) {
            foreach ($db->result as $r) {
                $val = htmlentities($r['valeur'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
                $sql[] = "UPDATE `{$dbprefix}select_$m` SET `valeur`='$val' WHERE `id`='{$r['id']}';";
            }
        }
    }
  
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7";
if (version_compare($config['Version'], $v) === -1) {
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

$v="2.7.01";
if (version_compare($config['Version'], $v) === -1) {
    // Statistiques
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Statistiques-Heures', 'textarea', 'Afficher des statistiques sur les cr&eacute;neaux horaires voulus. Les cr&eacute;neaux doivent &ecirc;tre au format 00h00-00h00 et s&eacute;par&eacute;s par des ; Exemple : 19h00-20h00; 20h00-21h00; 21h00-22h00','Statistiques','10');";
    $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom` IN ('Statistiques-19-20','Statistiques-20-22');";

    // Check ICS
    $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `check_ics` VARCHAR(10) NULL DEFAULT '[1,1,1]' AFTER `url_ics`;";

    // Encodage des caractères
    $sql[]="UPDATE `{$dbprefix}acces` SET `nom`='Jours f&eacute;ri&eacute;s', `groupe`='Gestion des jours f&eacute;ri&eacute;s' WHERE `groupe_id`='25';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='Gestion des plannings de pr&eacute;sences' WHERE `groupe_id`='24';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='Ce message a &eacute;t&eacute; envoy&eacute; par Planning Biblio.\nMerci de ne pas y r&eacute;pondre.' WHERE `nom`='Mail-Signature';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Contr&ocirc;le des heures des agents le samedi et le dimanche' WHERE `nom`='ctrlHresAgents';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='[[0,\"\"],[1,\"simple\"],[2,\"d&eacute;taill&eacute;\"],[3,\"absents et pr&eacute;sents\"]]', `type`='enum2' WHERE `nom`='Absences-planning';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='0' WHERE `valeur` = '' AND `nom`='Absences-planning';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='1' WHERE `valeur` = 'simple' AND `nom`='Absences-planning';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='2' WHERE `valeur` LIKE 'd%taill%' AND `nom`='Absences-planning';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='3' WHERE `valeur` LIKE 'absents et pr%sents' AND `nom`='Absences-planning';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;1' WHERE `nom`='Multisites-site1';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;2' WHERE `nom`='Multisites-site2';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;3' WHERE `nom`='Multisites-site3';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;4' WHERE `nom`='Multisites-site4';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;5' WHERE `nom`='Multisites-site5';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;6' WHERE `nom`='Multisites-site6';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;7' WHERE `nom`='Multisites-site7';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;8' WHERE `nom`='Multisites-site8';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;9' WHERE `nom`='Multisites-site9';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Nom du site N&deg;10' WHERE `nom`='Multisites-site10';";
    $sql[]="UPDATE `{$dbprefix}config` SET `categorie`='Cong&eacute;s' WHERE `categorie` LIKE 'Cong%s';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Heure de d&eacute;but pour la v&eacute;rification des sans repas' WHERE `nom`='Planning-SR-debut';";
    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires`='Heure de fin pour la v&eacute;rification des sans repas' WHERE `nom`='Planning-SR-fin';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='Appel &agrave; disponibilit&eacute; [poste] [date] [debut]-[fin]' WHERE `nom`='Planning-AppelDispoSujet';";
    $sql[]="UPDATE `{$dbprefix}menu` SET `titre`='Par poste (Synth&egrave;se)' WHERE `url`='statistiques/postes_synthese.php';";
    $sql[]="UPDATE `{$dbprefix}menu` SET `titre`='Les activit&eacute;s' WHERE `url`='activites/index.php';";
    $sql[]="UPDATE `{$dbprefix}menu` SET `titre`='Les mod&egrave;les' WHERE `url`='planning/modeles/index.php';";
    $sql[]="UPDATE `{$dbprefix}menu` SET `titre`='Plannings de pr&eacute;sence' WHERE `url`='planningHebdo/index.php';";

    // Séparation des droits Modification de planning, niveau 1 et niveau 2 et classement des droits d'accès
    $sql[]="UPDATE `{$dbprefix}personnel` SET `droits`= REPLACE(`droits`,'12','301');";
    $sql[]="ALTER TABLE `{$dbprefix}acces` ADD `ordre` INT(2) NOT NULL DEFAULT 0;";
    $sql[]="ALTER TABLE `{$dbprefix}acces` ADD `categorie` VARCHAR(30) NOT NULL DEFAULT '';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=301, `groupe`='Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', `categorie`='Planning', `ordre`=110 WHERE `page`='planning/poste/supprimer.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=301, `groupe`='Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', `categorie`='Planning', `ordre`=110 WHERE `page`='planning/poste/importer.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=301, `groupe`='Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', `categorie`='Planning', `ordre`=110 WHERE `page`='planning/poste/enregistrer.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=301, `groupe`='Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', `categorie`='Planning', `ordre`=110 WHERE `page`='planning/modeles/index.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=301, `groupe`='Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', `categorie`='Planning', `ordre`=110 WHERE `page`='planning/modeles/modif.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=301, `groupe`='Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', `categorie`='Planning', `ordre`=110 WHERE `page`='planning/modeles/valid.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=1001, `groupe`='Modification des plannings', `categorie`='Planning', `ordre`=120 WHERE `page`='planning/poste/menudiv.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id`=1001, `groupe`='Modification des plannings', `categorie`='Planning', `ordre`=120 WHERE `page`='planning/poste/majdb.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Planning', `ordre`=140 WHERE `groupe_id`='22';";
    $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/poste/horaires.php';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Planning', `ordre`=130 WHERE `groupe_id`='801';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Absences', `ordre`=20 WHERE `groupe_id`='6';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Absences', `ordre`=30 WHERE `groupe_id`='1';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Absences', `ordre`=40 WHERE `groupe_id`='8';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Absences', `ordre`=50 WHERE `groupe_id`='701';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Agendas', `ordre`=55 WHERE `groupe_id`='3';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='Voir les fiches des agents', `categorie`='Agents', `ordre`=60 WHERE `groupe_id`='4';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='Gestion des agents', `categorie`='Agents', `ordre`=70 WHERE `groupe_id`='21';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Postes', `ordre`=160 WHERE `groupe_id`='5';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='Acc&egrave;s au statistiques', `categorie`='Statistiques', `ordre`=170 WHERE `groupe_id`='17';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='Gestion des heures de pr&eacute;sence', `categorie`='Heures de pr&eacute;sence', `ordre`=80 WHERE `groupe_id`='24';";
    $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Planning', `ordre`=125 WHERE `groupe_id`='901';";
  
    // Dissociation des droits de valider les absences et d'enregistrer des absences pour plusieurs agents
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Enregistrement d&apos;absences pour plusieurs agents','9','Enregistrement d&apos;absences pour plusieurs agents', 'Absences', '25');";
  
    $sql[]="UPDATE `{$dbprefix}personnel` SET `droits` = REPLACE (`droits`, ',1,', ',1,9,');";
    $sql[]="UPDATE `{$dbprefix}personnel` SET `droits` = REPLACE (`droits`, '[1,', '[1,9,');";
    $sql[]="UPDATE `{$dbprefix}personnel` SET `droits` = REPLACE (`droits`, ',1]', ',1,9]');";
    $sql[]="UPDATE `{$dbprefix}personnel` SET `droits` = REPLACE (`droits`, '[1]', '[1,9]');";
    $sql[]="UPDATE `{$dbprefix}personnel` SET `droits` = REPLACE (`droits`, '\"1\"', '1,9');";

    // Notification lors de la validation des plannings
    $sql[]="ALTER TABLE `{$dbprefix}pl_notifications` ADD `site` INT(2) NOT NULL DEFAULT '1';";
    $sql[]="ALTER TABLE `{$dbprefix}pl_notifications` ADD KEY `date` (`date`), ADD KEY `site` (`site`);";

    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('Absences-tous', 'boolean', '0', 'Autoriser l&apos;enregistrement d&apos;absences pour tous les agents en une fois','Absences','37');";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.02";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.03";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Status1','enum2', 'CONFIRMED', '[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]', 'ICS', 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','22');";

    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Status2','enum2', 'CONFIRMED', '[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]', 'ICS', 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','42');";

    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('ICS-Status3','enum2', 'CONFIRMED', '[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]', 'ICS', 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','47');";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.04";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="UPDATE `{$dbprefix}menu` SET `url`='absences/voir.php' WHERE `url`='absences/index.php';";
    $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `uid` TEXT NULL DEFAULT NULL;";
    $sql[]="ALTER TABLE `{$dbprefix}absences` ADD `rrule` TEXT NULL DEFAULT NULL;";
    $sql[]="ALTER TABLE `{$dbprefix}absences` DROP `nbjours`;";
  
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.05";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="CREATE TABLE IF NOT EXISTS `{$dbprefix}absences_recurrentes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT, 
    `uid` VARCHAR(50), 
    `perso_id` INT,
    `event` TEXT,
    `end` ENUM ('0','1') NOT NULL DEFAULT '0',
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_update` VARCHAR(20) NOT NULL DEFAULT '',
    `last_check` VARCHAR(20) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `uid`(`uid`),
    KEY `perso_id`(`perso_id`), 
    KEY `end`(`end`),
    KEY `last_check`(`last_check`)) 
    ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    
    $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom` = 'Data-Folder';";
    
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.06";
if (version_compare($config['Version'], $v) === -1) {
    // Si la création de la table absences_recurrentes a échoué en 2.7.05 à cause des multiples champs TIMESTAMP, on la créé ici
    $sql[]="CREATE TABLE IF NOT EXISTS `{$dbprefix}absences_recurrentes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `uid` VARCHAR(50),
    `perso_id` INT,
    `event` TEXT,
    `end` ENUM ('0','1') NOT NULL DEFAULT '0',
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_update` VARCHAR(20) NOT NULL DEFAULT '',
    `last_check` VARCHAR(20) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `uid`(`uid`),
    KEY `perso_id`(`perso_id`),
    KEY `end`(`end`),
    KEY `last_check`(`last_check`))
    ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    // Si la table absences_recurrentes a bien été créée en 2.7.05 mais avec différents champs TIMESTAMP, on la  modifie ici.
    $sql[]="ALTER TABLE `{$dbprefix}absences_recurrentes` CHANGE `last_update` `last_update` VARCHAR(20) NOT NULL DEFAULT '', CHANGE `last_check` `last_check` VARCHAR(20) NOT NULL DEFAULT '';";

    $sql[]="ALTER TABLE `{$dbprefix}absences` ADD KEY `perso_id` (`perso_id`),  ADD KEY `debut` (`debut`), ADD KEY `fin` (`fin`), ADD KEY `groupe` (`groupe`);";

    $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom` = 'Data-Folder';";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.11";
if (version_compare($config['Version'], $v) === -1) {
    // Nettoyage de la table absences
    $sql[]="DELETE FROM `{$dbprefix}absences` WHERE `fin`='0000-00-00 00:00:00';";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.12";
if (version_compare($config['Version'], $v) === -1) {

  // CAS Service URL (pour bonne redirection si proxy)
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `ordre`, `commentaires`) VALUES 
    ('CAS-ServiceURL', 'text', 'CAS','47', 'URL de Planning Biblio. A renseigner seulement si la redirection ne fonctionne pas après authentification sur le serveur CAS, si vous utilisez un Reverse Proxy par exemple.');";
    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.13";
if (version_compare($config['Version'], $v) === -1) {

  // Importation CSV HAMAC
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Hamac-csv','text', '', '', 'Hamac', 'Chemin d&apos;acc&egrave;s au fichier CSV pour l&apos;importation des absences depuis Hamac','10');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Hamac-motif', 'text', '', '', 'Hamac', 'Motif pour les absences import&eacute;s depuis Hamac. Ex: Hamac','20');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Hamac-status','enum2', '1,2,3,5,6', '[[\"1,2,3,5,6\",\"Valid&eacute;es et en attente de validation\"],[\"2\",\"Valid&eacute;es\"]]', 'Hamac', 'Importer les absences valid&eacute;es et en attente de validation ou seulement les absences valid&eacute;es.','30');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Hamac-id','enum2', 'login', '[[\"login\",\"Login\"],[\"matricule\",\"Matricule\"]]', 'Hamac', 'Champ Planning Biblio &agrave; utiliser pour mapper les agents.','40');";
    $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `check_hamac` INT(1) NOT NULL DEFAULT '1' AFTER `check_ics`;";

    // Version
    $sql[]="UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.14";
if (version_compare($config['Version'], $v) === -1) {
    // Modification des IDs des droits Absences
    $db = new db();
    $db->select('personnel');
    if ($db->result) {
        foreach ($db->result as $elem) {
            $update = false;
            $droits = html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $droits = (array) json_decode($droits, true);
            foreach ($droits as $k => $val) {
                if ($val == 1) {
                    $droits[$k] = 201;
                    $update = true;
                }
                if ($val == 8) {
                    $droits[$k] = 501;
                    $update = true;
                }
            }

            if ($update) {
                $droits = json_encode($droits);
                $sql[] = "UPDATE `{$dbprefix}personnel` SET `droits` = '$droits' WHERE `id` = '{$elem['id']}';";
            }
        }
    }

    $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe_id` = '201', `groupe` = 'Gestion des absences, validation niveau 1' WHERE `groupe_id` = '1';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe_id` = '501', `groupe` = 'Gestion des absences, validation niveau 2' WHERE `groupe_id` = '8';";

    // Version
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.7.15";
if (version_compare($config['Version'], $v) === -1) {

  // Ajout du paramètre LDAP-Matricule
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-Matricule', 'text', '', '', 'Attribut &agrave; importer dans le champ matricule (optionnel)', 'LDAP', 90);";

    // Version
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}


$v="2.8";
if (version_compare($config['Version'], $v) === -1) {

  // Responsables et notifications
    $sql[]="ALTER TABLE `{$dbprefix}config` CHANGE `nom` `nom` VARCHAR(50);";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Absences-notifications-agent-par-agent','boolean', '0', 'Absences', 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres Absences-notifications1, 2, 3 et 4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','67');";
    $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES 
    ('50','77','Validations / Notifications','notifications/index.php','config=Absences-notifications-agent-par-agent');";
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Notifications / Validations', 21, 'Gestion des agents', 'notifications/index.php', 'Agents', 70);";
    $sql[]="CREATE TABLE IF NOT EXISTS `{$dbprefix}responsables` (
    `id` INT(11) NOT NULL AUTO_INCREMENT, 
    `perso_id` INT(11) NOT NULL DEFAULT '0', 
    `responsable` INT(11) NOT NULL DEFAULT '0', 
    `notification` INT(1) NOT NULL DEFAULT '0', 
    PRIMARY KEY (`id`))
    ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    // Modification des IDs des droits de gestion des heures de présence
    $db = new db();
    $db->select('personnel');
    if ($db->result) {
        foreach ($db->result as $elem) {
            $update = false;
            $droits = html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $droits = (array) json_decode($droits, true);
            foreach ($droits as $k => $val) {
                if ($val == 24) {
                    $droits[$k] = 1101;
                    $update = true;
                }
            }

            if ($update) {
                $droits = json_encode($droits);
                $sql[] = "UPDATE `{$dbprefix}personnel` SET `droits` = '$droits' WHERE `id` = '{$elem['id']}';";
            }
        }
    }

    $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe_id` = '1101', `groupe` = 'Gestion des heures de pr&eacute;sences, validation niveau 1'  WHERE `groupe_id` = '24';";
  
    // Double validation des heures de présence
    $sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Hebdo - Admin N2','1201','Gestion des heures de pr&eacute;sences, validation niveau 2','','Heures de pr&eacute;sence','90');";
  
    $sql[] = "ALTER TABLE `{$dbprefix}planning_hebdo` ADD `valide_n1` INT(11) NOT NULL DEFAULT '0' AFTER `modification`;";
    $sql[] = "ALTER TABLE `{$dbprefix}planning_hebdo` ADD `validation_n1` TIMESTAMP NULL DEFAULT NULL AFTER `valide_n1`;";
  
    $db = new db();
    $db->select2('config', array('valeur'), array('nom' => 'PlanningHebdo-Notifications'));
    $result = $db->result[0]['valeur'];
    $valeur = $result == 'droit' ? '[0,4]' : '[3,4]';

    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('PlanningHebdo-notifications1','checkboxes','$valeur','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications de nouveaux plannings de pr&eacute;sence','Heures de pr&eacute;sence','70');";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('PlanningHebdo-notifications2','checkboxes','$valeur','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification de planning de pr&eacute;sence','Heures de pr&eacute;sence','72');";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('PlanningHebdo-notifications3','checkboxes','$valeur','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1','Heures de pr&eacute;sence','74');";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
    VALUES ('PlanningHebdo-notifications4','checkboxes','$valeur','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2','Heures de pr&eacute;sence','76');";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('PlanningHebdo-notifications-agent-par-agent','boolean', '0', 'Heures de pr&eacute;sence', 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres PlanningHebdo-notifications1, 2, 3 et 4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','80');";
    $sql[] = "DELETE FROM `{$dbprefix}config` WHERE `nom` = 'PlanningHebdo-Notifications';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs`='[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]' WHERE `nom` IN ('Absences-notifications1','Absences-notifications2','Absences-notifications3','Absences-notifications4');";

    // Absences agent logué chargé automatiquement ou non
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-agent-preselection', 'boolean', '1', 'Présélectionner l&apos;agent connecté lors de l&apos;ajout d&apos;une nouvelle absence.','Absences','36');";

    // Affichage ou non des heures de SP et des couleurs dans le menu du planning
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Planning-Heures','boolean', '1', '', 'Planning', 'Afficher les heures &agrave; c&ocirc;t&eacute; du nom des agents dans le menu du planning','25');";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = 'uid,samaccountname,supannAliasLogin' WHERE `nom` =  'LDAP-ID-Attribute';";
  
    // Agents volants (SciencesPo)
    $sql[] = "INSERT INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES ('30','90','Agents volants','planning/volants/index.php','config=Planning-agents-volants');";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES 
    ('Planning-agents-volants','boolean', '0', '', 'Planning', 'Utiliser le module \"Agents volants\" permettant de diff&eacute;rencier un groupe d&apos;agents dans le planning','90');";
    $sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Agents volants', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', 'planning/volants/index.php', 'Planning', 110);";

    $sql[] = "CREATE TABLE IF NOT EXISTS `{$dbprefix}volants` (
    `id` INT(11) NOT NULL AUTO_INCREMENT, 
    `date` DATE NULL DEFAULT NULL, 
    `perso_id` INT(11) NOT NULL DEFAULT '0', 
    PRIMARY KEY (`id`))
    ENGINE=MyISAM  DEFAULT CHARSET=utf8;";


    // Version
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.8.01";
if (version_compare($config['Version'], $v) === -1) {

  // Suppression des options de débogage
    $sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `groupe_id` = '13';";
    $sql[] = "DELETE FROM `{$dbprefix}config` WHERE `nom` = 'display_errors';";

    // Suppression de la table planning_hebdo_periodes
    $sql[]="DROP TABLE IF EXISTS `{$dbprefix}planningHebdoPeriodes`;";
    $sql[]="DROP TABLE IF EXISTS `{$dbprefix}planning_hebdo_periodes`;";

    // PlanningHebdo : Attente de la validation niveau 1 avant d'autoriser la validation niveau 2
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('PlanningHebdo-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le planning doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Heures de pr&eacute;sence', 'La validation niveau 2 des plannings de pr&eacute;sence peut se faire directement ou doit attendre la validation niveau 1', '85');";
  
    $sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `groupe`, `page`, `ordre`, `categorie`) VALUES ('Planning Hebdo - Index', '1201', 'Gestion des heures de pr&eacute;sences, validation niveau 2', 'planningHebdo/index.php', '90', 'Heures de pr&eacute;sence');";

    // Emplois du temps différents les semaines avec samedi travaillé et en ouverture restreinte
    $sql[] = "UPDATE `{$dbprefix}config` SET `type` = 'enum2', `valeurs` = '[[0, \"D&eacute;sactiv&eacute;\"], [1, \"Emploi du temps diff&eacute;rent les semaines avec samedi travaill&eacute;\"], [2, \"Emploi du temps diff&eacute;rent les semaines avec samedi travaill&eacute; et les semaines &agrave; ouverture restreinte\"]]', `commentaires` = 'Emplois du temps diff&eacute;rents les semaines o&ugrave; le samedi est travaill&eacute; et les semaines &agrave; ouverture restreinte' WHERE `nom` = 'EDTSamedi';";

    $sql[] = "ALTER TABLE `{$dbprefix}edt_samedi` ADD `tableau` INT(1) NOT NULL DEFAULT 0;";
    $sql[] = "UPDATE `{$dbprefix}edt_samedi` SET `tableau` = 2;";

    // Version
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.8.03";
if (version_compare($config['Version'], $v) === -1) {
    // Version
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="2.8.04";
if (version_compare($config['Version'], $v) === -1) {
    $db = new db();
    $db->select2('config', array('nom', 'valeur'), array('type'=>'password'));
    if ($db->result) {
        foreach ($db->result as $elem) {
            $decrypted = decrypt_old($elem['valeur']);
            $encrypted = encrypt($decrypted);
            $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$encrypted' WHERE `nom`='{$elem['nom']}';";
        }
    }

    // Version
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.04.00";
if (version_compare($config['Version'], $v) === -1) {

    $db = new db();
    $db->select2('plugins', '*', array('nom' => 'conges'));
    $plugin_conges = $db->result ? true : false;

    // Plugin is installed yet.
    // So, updating access and menu elements.
    if ($plugin_conges) {

        // Update the plugin from 2.6 to 2.8 before integration (nothing to do between 2.0 and 2.6)
        $conges_version = $db->result[0]['version'];

        if ($conges_version < "2.6") {
            $sql[]="ALTER TABLE `{$dbprefix}conges` ADD `valideN1` INT(11) NOT NULL DEFAULT '0', ADD `validationN1` TIMESTAMP;";
        }

        if ($conges_version < "2.6.4") {
            $sql[]="ALTER TABLE `{$dbprefix}conges` CHANGE `valideN1` `valide_n1` INT(11) NOT NULL DEFAULT '0', CHANGE `validationN1` `validation_n1` TIMESTAMP;";
            $sql[]="ALTER TABLE `{$dbprefix}conges` CHANGE `supprDate` `suppr_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';";
            $sql[]="ALTER TABLE `{$dbprefix}conges_CET` CHANGE `valideN1` `valide_n1` INT(11) NOT NULL DEFAULT '0', CHANGE `validationN1` `validation_n1` TIMESTAMP;";
            $sql[]="ALTER TABLE `{$dbprefix}conges_CET` CHANGE `valideN2` `valide_n2` INT(11) NOT NULL DEFAULT '0', CHANGE `validationN2` `validation_n2` TIMESTAMP;";
        }

        if ($conges_version < "2.7") {
            // Configuration : gestion des rappels
            $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) 
        VALUES ('Conges-Rappels', 'boolean', '0', 'Congés', 'Activer / D&eacute;sactiver l&apos;envoi de rappels s&apos;il y a des cong&eacute;s non-valid&eacute;s', '6');";
            $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) 
        VALUES ('Conges-Rappels-Jours', 'text', '14', 'Congés', 'Nombre de jours &agrave; contr&ocirc;ler pour l&apos;envoi de rappels sur les cong&eacute;s non-valid&eacute;s', '7');";
            $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N1', 'checkboxes', '[\"Mail-Planning\"]', 
        '[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Congés', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 1', '8');";
            $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N2', 'checkboxes', '[\"mails_responsables\"]', 
      '[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Congés', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 2', '9');";
        }

        if ($conges_version < "2.7.01") {
            $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Cong&eacute;s', `ordre`='75' WHERE `groupe_id`='7';";
            $sql[]="UPDATE `{$dbprefix}acces` SET `categorie`='Cong&eacute;s', `ordre`='76' WHERE `groupe_id`='2';";
        }

        if ($conges_version < "2.7.06") {
            $sql[]="UPDATE `{$dbprefix}menu` SET `url`='plugins/conges/voir.php' WHERE `titre`='Cong&eacute;s';";
            $sql[]="UPDATE `{$dbprefix}acces` SET `groupe_id` = '100', `groupe` = '', `categorie` = '', ordre = '' WHERE `page`='plugins/conges/infos.php';";
        }
        
        if ($conges_version < "2.8") {
            $sql[] = "UPDATE `{$dbprefix}config` SET `categorie` = 'Cong&eacute;s' WHERE `categorie` LIKE 'Cong%s';";
            $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Recuperations', 'enum2', '0', '[[0,\"Assembler\"],[1,\"Dissocier\"]]', 'Cong&eacute;s', 'Traiter les r&eacute;cup&eacute;rations comme les cong&eacute;s (Assembler), ou les traiter s&eacute;par&eacute;ment (Dissocier)', '3');";
            $sql[] = "INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES (15, 24, 'Poser des r&eacute;cup&eacute;rations', 'plugins/conges/recup_pose.php', 'config=Conges-Recuperations');";
            $sql[] = "INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES (15, 15, 'Liste des r&eacute;cup&eacute;rations', 'plugins/conges/voir.php&amp;recup=1', 'config=Conges-Recuperations');";
            $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Poser des r&eacute;cup&eacute;rations','100','plugins/conges/recup_pose.php');";

            // Modification des ID des droits d'administration niveau 1 et 2
            $db = new db();
            $db->select('personnel');
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $update = false;
                    $conges_droits = html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
                    $conges_droits = (array) json_decode($conges_droits, true);
                    foreach ($conges_droits as $key => $value) {
                        if ($value == 7) {
                            $conges_droits[$key] = 401;
                            $update = true;
                        }
                        if ($value == 2) {
                            $conges_droits[$key] = 601;
                            $update = true;
                        }
                    }
                    if ($update) {
                        $conges_droits = json_encode($conges_droits);
                        $sql[] = "UPDATE `{$dbprefix}personnel` SET `droits` = '$conges_droits' WHERE `id` = '{$elem['id']}';";
                    }
                }
            }

            // Integration
            // updating access and menu elements.
            $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe_id` = '401', `groupe` = 'Gestion des cong&eacute;s, validation niveau 1' WHERE `groupe_id` = '7';";
            $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe_id` = '601', `groupe` = 'Gestion des cong&eacute;s, validation niveau 2' WHERE `groupe_id` = '2';";
      
            // Attente de la validation niveau 1 avant d'autoriser la validation niveau 2
            $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le cong&eacute; doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Cong&eacute;s', 'La validation niveau 2 des cong&eacute;s peut se faire directement ou doit attendre la validation niveau 1', '4');";

            // Suppression des majuscules dans les noms des tables et des champs
            $sql[] = "ALTER TABLE `{$dbprefix}conges` CHANGE `infoDate` `info_date` TIMESTAMP NULL DEFAULT NULL;";
            $sql[] = "UPDATE `{$dbprefix}conges` SET `info_date` = NULL WHERE `info_date` = '0000-00-00 00:00:00';";

            $sql[] = "RENAME TABLE `{$dbprefix}conges_CET` TO `{$dbprefix}conges_cet`;";

            $sql[]="ALTER TABLE `{$dbprefix}personnel` CHANGE `congesCredit` `conges_credit` FLOAT(10) DEFAULT 0, CHANGE `congesReliquat` `conges_reliquat` FLOAT(10) DEFAULT 0, CHANGE `congesAnticipation` `conges_anticipation` FLOAT(10) DEFAULT 0, CHANGE `recupSamedi` `recup_samedi` FLOAT(10) DEFAULT 0, CHANGE `congesAnnuel` `conges_annuel` FLOAT(10) DEFAULT 0;";

            // Suppression des doublons dans la table cron
            $db = new db();
            $db->select2('cron', 'id', array('command' => 'plugins/conges/cron.sept1.php'));
            if ($db->result) {
                for ($i = 1; $i < $db->nb; $i++) {
                    $sql[] = "DELETE FROM `{$dbprefix}cron` WHERE `id` = '".$db->result[$i]['id']."';";
                }
            }

            $db = new db();
            $db->select2('cron', 'id', array('command' => 'plugins/conges/cron.jan1.php'));
            if ($db->result) {
                for ($i = 1; $i < $db->nb; $i++) {
                    $sql[] = "DELETE FROM `{$dbprefix}cron` WHERE `id` = '".$db->result[$i]['id']."';";
                }
            }
      
            // Double validation des demandes de crédits de récupération
            $sql[] = "ALTER TABLE `{$dbprefix}recuperations` ADD `validation_n1` DATETIME NULL DEFAULT NULL AFTER `modification`;";
            $sql[] = "ALTER TABLE `{$dbprefix}recuperations` ADD `valide_n1` INT(11) NOT NULL DEFAULT 0 AFTER `modification`;";

            $sql[]="UPDATE `{$dbprefix}plugins` SET `version`='$version' WHERE `nom`='conges';";
        }

        // Insertion du module congés:  modification droits d'accès
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/index.php' WHERE page='plugins/conges/index.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/voir.php' WHERE page='plugins/conges/voir.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/enregistrer.php' WHERE page='plugins/conges/enregistrer.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/modif.php' WHERE page='plugins/conges/modif.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/infos.php' WHERE page='plugins/conges/infos.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/recuperations.php' WHERE page='plugins/conges/recuperations.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/recuperation_modif.php' WHERE page='plugins/conges/recuperation_modif.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/cet.php' WHERE page='plugins/conges/cet.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/credits.php' WHERE page='plugins/conges/credits.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/recuperation_valide.php' WHERE page='plugins/conges/recuperation_valide.php';";
        $sql[]="UPDATE `{$dbprefix}acces` SET page='conges/recup_pose.php' WHERE page='plugins/conges/recup_pose.php';";

        // Insertion du module congés: modification menu
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='conges/voir.php', `condition`='config=Conges-Enable' WHERE `url`='plugins/conges/voir.php';";
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='conges/enregistrer.php', `condition`='config=Conges-Enable' WHERE `url`='plugins/conges/enregistrer.php';";
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='conges/recuperations.php', `condition`='config=Conges-Enable' WHERE `url`='plugins/conges/recuperations.php';";
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='conges/infos.php', `condition`='config=Conges-Enable' WHERE `url`='plugins/conges/infos.php';";
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='conges/credits.php', `condition`='config=Conges-Enable' WHERE `url`='plugins/conges/credits.php';";
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='conges/voir.php&amp;recup=1', `condition`='config=Conges-Enable;Conges-Recuperations' WHERE `url`='plugins/conges/voir.php&amp;recup=1';";
        $sql[]="UPDATE `{$dbprefix}menu` SET `url`='conges/recup_pose.php', `condition`='config=Conges-Enable;Conges-Recuperations' WHERE `url`='plugins/conges/recup_pose.php';";

        // Insertion du module congés: ajout des taches planifiées
        $sql[]="UPDATE `{$dbprefix}cron` SET command='conges/cron.jan1.php' WHERE command='plugins/conges/cron.jan1.php';";
        $sql[]="UPDATE `{$dbprefix}cron` SET command='conges/cron.sept1.php' WHERE command='plugins/conges/cron.sept1.php';";

        // Activation du module Congés
        $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Enable', 'boolean', '1', 'Cong&eacute;s', 'Activer le module Congés', '1');";
    }
    // Plugin is not installed.
    // So installing it the normal way.
    else {
        // Insertion du module congés: configuration
        $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Enable', 'boolean', '0', 'Cong&eacute;s', 'Activer le module Congés', '1');";
        $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Recuperations', 'enum2', '0', '[[0,\"Assembler\"],[1,\"Dissocier\"]]', 'Cong&eacute;s', 'Traiter les r&eacute;cup&eacute;rations comme les cong&eacute;s (Assembler), ou les traiter s&eacute;par&eacute;ment (Dissocier)', '3');";
        $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le cong&eacute; doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Cong&eacute;s', 'La validation niveau 2 des cong&eacute;s peut se faire directement ou doit attendre la validation niveau 1', '4');";
        $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-SamediSeulement','boolean','0','Autoriser les demandes de récupération des samedis seulement','Cong&eacute;s','','20');";
        $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DeuxSamedis','boolean','0','Autoriser les demandes de récupération pour 2 samedis','Cong&eacute;s','','30');";
        $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiDefaut','text','7','Delai pour les demandes de récupération par d&eacute;faut (en jours)','Cong&eacute;s','','40');";
        $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiTitulaire1','enum','0','Delai pour les demandes de récupération des titulaires pour 1 samedi (en mois)','Cong&eacute;s','D&eacute;faut,0,1,2,3,4,5','50');";
        $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiTitulaire2','enum','0','Delai pour les demandes de récupération des titulaires pour 2 samedis (en mois)','Cong&eacute;s','D&eacute;faut,0,1,2,3,4,5','60');";
        $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiContractuel1','enum','0','Delai pour les demandes de récupération des contractuels pour 1 samedi (en semaines)','Cong&eacute;s','D&eacute;faut,0,1,2,3,4,5,6,7,8,9,10','70');";
        $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiContractuel2','enum','0','Delai pour les demandes de récupération des contractuels pour 2 samedis (en semaines)','Cong&eacute;s','D&eacute;faut,0,1,2,3,4,5,6,7,8,9,10','80');";

        # Set new variables in config because we need these keys later in the update.
        $config['Recup-DelaiTitulaire1'] = '0';
        $config['Recup-DelaiTitulaire2'] = '0';
        $config['Recup-DelaiContractuel1'] = '0';
        $config['Recup-DelaiContractuel2'] = '0';

        // Insertion du module congés: configuration - gestion des rappels
        $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` )
          VALUES ('Conges-Rappels', 'boolean', '0', 'Cong&eacute;s', 'Activer / D&eacute;sactiver l&apos;envoi de rappels s&apos;il y a des cong&eacute;s non-valid&eacute;s', '6');";
        $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` )
          VALUES ('Conges-Rappels-Jours', 'text', '14', 'Cong&eacute;s', 'Nombre de jours &agrave; contr&ocirc;ler pour l&apos;envoi de rappels sur les cong&eacute;s non-valid&eacute;s', '7');";
        $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N1', 'checkboxes', '[\"Mail-Planning\"]',
          '[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Cong&eacute;s', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 1', '8');";
        $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N2', 'checkboxes', '[\"mails_responsables\"]',
        '[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Cong&eacute;s', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 2', '9');";

        // Insertion du module congés:  droits d'accès
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Index','100','conges/index.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Liste','100','conges/voir.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Enregistrer','100','conges/enregistrer.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Modifier','100','conges/modif.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe`,`groupe_id`,`categorie`,`ordre`) VALUES ('Gestion des cong&eacute;s, validation niveau 2','Gestion des cong&eacute;s, validation niveau 2',601,'Cong&eacute;s','76');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Infos','100','conges/infos.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - r&eacute;cuperations','100','conges/recuperations.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - R&eacute;cup&eacute;ration','100','conges/recuperation_modif.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Gestion des cong&eacute;s, validation niveau 1','401','Gestion des cong&eacute;s, validation niveau 1','Cong&eacute;s','75');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Compte &Eacute;pargne Temps','100','conges/cet.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Cong&eacute;s - Cr&eacute;dits','100','','conges/credits.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - R&eacute;cup&eacute;rations','100','conges/recuperation_valide.php');";
        $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Cong&eacute;s - Poser des r&eacute;cup&eacute;rations','100','conges/recup_pose.php');";

        // Insertion du module congés: table conges
        $sql[]="CREATE TABLE `{$dbprefix}conges` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `perso_id` INT(11) NOT NULL,
        `debut` DATETIME NOT NULL,
        `fin` DATETIME NOT NULL,
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
        `info_date` TIMESTAMP NULL DEFAULT NULL);";

        // Insertion du module congés: table conges_infos
        $sql[]="CREATE TABLE `{$dbprefix}conges_infos` (
        `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `debut` DATE NULL,
        `fin` DATE NULL,
        `texte` TEXT NULL,
        `saisie` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";

        // Insertion du module congés: table récupérations
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
        `solde_actuel` FLOAT(10));";

        // Insertion du module congés: menu
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','0','Cong&eacute;s','conges/voir.php','config=Conges-Enable');";
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','10','Liste des cong&eacute;s','conges/voir.php','config=Conges-Enable');";
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','15','Liste des r&eacute;cup&eacute;rations','conges/voir.php&amp;recup=1','config=Conges-Enable;Conges-Recuperations');";
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','20','Poser des cong&eacute;s','conges/enregistrer.php','config=Conges-Enable');";
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','24','Poser des r&eacute;cup&eacute;rations','conges/recup_pose.php','config=Conges-Enable;Conges-Recuperations');";
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','26','R&eacute;cup&eacute;rations','conges/recuperations.php','config=Conges-Enable');";
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','30','Informations','conges/infos.php','config=Conges-Enable');";
        $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES ('15','40','Cr&eacute;dits','conges/credits.php','config=Conges-Enable');";

        // Insertion du module congés: modification de la table personnel
        $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `conges_credit` FLOAT(10), ADD `conges_reliquat` FLOAT(10), ADD `conges_anticipation` FLOAT(10);";
        $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `recup_samedi` FLOAT(10);";
        $sql[]="ALTER TABLE `{$dbprefix}personnel` ADD `conges_annuel` FLOAT(10);";

        // Insertion du module congés: ajout des taches planifiées
        $sql[]="INSERT INTO `{$dbprefix}cron` (m,h,dom,mon,dow,command,comments) VALUES (0,0,1,1,'*','conges/cron.jan1.php','Cron Congés 1er Janvier');";
        $sql[]="INSERT INTO `{$dbprefix}cron` (m,h,dom,mon,dow,command,comments) VALUES (0,0,1,9,'*','conges/cron.sept1.php','Cron Congés 1er Septembre');";

        // Insertion du module congés: création de la table conges_CET
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
        `annee` VARCHAR(10));";
    }

    // Supprime l'entrée "conges" de la table plugins
    $sql[]="DELETE FROM `{$dbprefix}plugins` WHERE `nom`='conges';";

    $sql[] = "UPDATE `{$dbprefix}acces` SET `categorie` = 'Cong&eacute;s' WHERE `groupe_id` IN ('401','601');";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `ordre` = '75' WHERE `groupe_id` = '401';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `ordre` = '76' WHERE `groupe_id` = '601';";

    # Define planning unit's emails site by site
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site1-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site2-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site3-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site4-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site5-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site6-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site7-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site8-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site9-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `valeurs`, `ordre` )
        VALUES ('Multisites-site10-mail', 'text', '', 'Multisites', '', '', 1);";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres PlanningHebdo-notifications1, 2, 3 et 4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations' WHERE `nom` = 'Absences-notifications-agent-par-agent';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Afficher le total d\'heures des 4 dernières semaine dans le menu' WHERE `nom` = 'hres4semaines';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Activer le débogage pour CAS. Créé un fichier \"cas_debug.txt\" dans le dossier \"[TEMP]\"' WHERE `nom` = 'CAS-Debug';";

    // Recup-Agent.
    $valeurs = addslashes('[[0,""],[1,"Texte"],[2,"Menu déroulant"]]');
    $valeur = $config['Recup-Agent'] ? (($config['Recup-Agent'] == 'Texte') ? 1 : 2) : 0;
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '$valeurs', `valeur` = '$valeur', `type` = 'enum2' WHERE `nom` = 'Recup-Agent';";

    //Recup-DelaiTitulaire1, Recup-DelaiTitulaire2.
    $valeurs = addslashes('[[-1,"Défaut"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]');

    $valeur = $config['Recup-DelaiTitulaire1'] == 'D&eacute;faut' ? -1 : $config['Recup-DelaiTitulaire1'];
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '$valeurs', `valeur` = '$valeur', `type` = 'enum2' WHERE `nom` = 'Recup-DelaiTitulaire1';";

    $valeur = $config['Recup-DelaiTitulaire2'] == 'D&eacute;faut' ? -1 : $config['Recup-DelaiTitulaire2'];
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '$valeurs', `valeur` = '$valeur', `type` = 'enum2' WHERE `nom` = 'Recup-DelaiTitulaire2';";

    //Recup-DelaiContractuel1, Recup-DelaiContractuel2.
    $valeurs = addslashes('[[-1,"Défaut"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10]]');

    $valeur = $config['Recup-DelaiContractuel1'] == 'D&eacute;faut' ? -1 : $config['Recup-DelaiContractuel1'];
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '$valeurs', `valeur` = '$valeur', `type` = 'enum2' WHERE `nom` = 'Recup-DelaiContractuel1';";

    $valeur = $config['Recup-DelaiContractuel2'] == 'D&eacute;faut' ? -1 : $config['Recup-DelaiContractuel2'];
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '$valeurs', `valeur` = '$valeur', `type` = 'enum2' WHERE `nom` = 'Recup-DelaiContractuel2';";

    # Allow more than one over time recovery per day
    $sql[] = "INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-Uneparjour','boolean','1','Autoriser une seule demande de r&eacute;cup&eacute;ration par jour','Cong&eacute;s','','15');";

    # Define planning unit's emails site by site
    $notification1 = $config['Absences-notifications1'];
    $notification2 = $config['Absences-notifications2'];
    $notification3 = $config['Absences-notifications3'];
    $notification4 = $config['Absences-notifications4'];

    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
          VALUES ('Recup-notifications1','checkboxes','$notification1','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications de nouvelles demandes de crédit de récupérations','Cong&eacute;s','100');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
          VALUES ('Recup-notifications2','checkboxes','$notification2','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications de modification de crédit de récupérations','Cong&eacute;s','100');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
          VALUES ('Recup-notifications3','checkboxes','$notification3','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications des validations de crédit de récupérations niveau 1','Cong&eacute;s','100');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
          VALUES ('Recup-notifications4','checkboxes','$notification4','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications des validations de crédit de récupérations niveau 2','Cong&eacute;s','100');";

    # New statistic page: presents/absents agents
    $sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`)
        VALUES ('40','80','Pr&eacute;sents / absents','statistiques/presents_absents.php',NULL);";

    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`)
            VALUES ('Liste des agents pr&eacute;sents et absents', 1301, 'Acc&egrave;s aux statistiques Pr&eacute;sents / Absents',
            'statistiques/presents_absents.php','Statistiques','171');";

    # Fix wrong link on notifications of recurring absences modification (github issue #36)
    $sql[] = "ALTER TABLE `{$dbprefix}absences` ADD `id_origin` INT NOT NULL DEFAULT 0;";

    // Version
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.04.01";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.10.00";
if (version_compare($config['Version'], $v) === -1) {
    // Update config multisites.
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '10' WHERE `nom` = 'Multisites-nombre';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '20', `commentaires` = 'Nom du site N°1' WHERE `nom` = 'Multisites-site1';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '30', `commentaires` = 'Nom du site N°2' WHERE `nom` = 'Multisites-site2';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '40', `commentaires` = 'Nom du site N°3' WHERE `nom` = 'Multisites-site3';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '50', `commentaires` = 'Nom du site N°4' WHERE `nom` = 'Multisites-site4';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '60', `commentaires` = 'Nom du site N°5' WHERE `nom` = 'Multisites-site5';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '70', `commentaires` = 'Nom du site N°6' WHERE `nom` = 'Multisites-site6';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '80', `commentaires` = 'Nom du site N°7' WHERE `nom` = 'Multisites-site7';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '90', `commentaires` = 'Nom du site N°8' WHERE `nom` = 'Multisites-site8';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '100', `commentaires` = 'Nom du site N°9' WHERE `nom` = 'Multisites-site9';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '110', `commentaires` = 'Nom du site N°10' WHERE `nom` = 'Multisites-site10';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '25', `commentaires` = 'Adresses e-mails de la cellule planning du site N°1, séparées par des ;' WHERE `nom` = 'Multisites-site1-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '35', `commentaires` = 'Adresses e-mails de la cellule planning du site N°2, séparées par des ;' WHERE `nom` = 'Multisites-site2-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '45', `commentaires` = 'Adresses e-mails de la cellule planning du site N°3, séparées par des ;' WHERE `nom` = 'Multisites-site3-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '55', `commentaires` = 'Adresses e-mails de la cellule planning du site N°4, séparées par des ;' WHERE `nom` = 'Multisites-site4-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '65', `commentaires` = 'Adresses e-mails de la cellule planning du site N°5, séparées par des ;' WHERE `nom` = 'Multisites-site5-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '75', `commentaires` = 'Adresses e-mails de la cellule planning du site N°6, séparées par des ;' WHERE `nom` = 'Multisites-site6-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '85', `commentaires` = 'Adresses e-mails de la cellule planning du site N°7, séparées par des ;' WHERE `nom` = 'Multisites-site7-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '95', `commentaires` = 'Adresses e-mails de la cellule planning du site N°8, séparées par des ;' WHERE `nom` = 'Multisites-site8-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '105', `commentaires` = 'Adresses e-mails de la cellule planning du site N°9, séparées par des ;' WHERE `nom` = 'Multisites-site9-mail';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '115', `commentaires` = 'Adresses e-mails de la cellule planning du site N°1O, séparées par des ;' WHERE `nom` = 'Multisites-site10-mail';";

    // Update config encoding
    $sql[] = "UPDATE `{$dbprefix}config` SET `categorie` = 'Congés' WHERE `categorie` = 'Cong&eacute;s';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `categorie` = 'Heures de présence' WHERE `categorie` = 'Heures de pr&eacute;sence';";

    // Bcrypt password.
    $sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `password` `password` VARCHAR(255) NOT NULL DEFAULT '';";

    // Update config CAS version.
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '2.0,3.0,4.0' WHERE `nom` = 'CAS-Version';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '2.0' WHERE `nom` = 'CAS-Version' AND `valeur`= '2';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '3.0' WHERE `nom` = 'CAS-Version' AND `valeur`= '3';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '4.0' WHERE `nom` = 'CAS-Version' AND `valeur`= '4';";

    // Add config Planning-CommentairesToujoursActifs.
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Planning-CommentairesToujoursActifs', 'boolean', '0', 'Planning','100', 'Afficher la zone de commentaire même si le planning n\'est pas encore commencé.');";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.001";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/help' WHERE `url` = 'aide/index.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/help' WHERE `page` = 'aide/index.php';";
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/config' WHERE `url` = 'admin/config.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/config' WHERE `page` = 'admin/config.php';";
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/absences/info' WHERE `url` = 'absences/infos.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/absences/info' WHERE `page` = 'absences/infos.php';";
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/statistics/attendeesmissing' WHERE `url` = 'statistiques/presents_absents.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/statistics/attendeesmissing' WHERE `page` = 'statistiques/presents_absents.php';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.002";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Mode', 'enum2', 'heures', '[[\"heures\",\"Heures\"],[\"jours\",\"Jours\"]]', 'Congés', 'Décompte des congés en heures ou en jours', '2');";

    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/agent' WHERE `page` = 'personnel/modif.php';";
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/holiday/index' WHERE `url` = 'conges/voir.php';";
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/holiday/index?recup=1' WHERE `url` = 'conges/voir.php&amp;recup=1';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/holiday/index' WHERE `page` = 'conges/voir.php';";
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/holiday/new' WHERE `url` = 'conges/enregistrer.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/holiday/new' WHERE `page` = 'conges/enregistrer.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/holiday/edit' WHERE `page` = 'conges/modif.php';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.003";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/absence' WHERE `page` = 'absences/ajouter.php';";
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/absence' WHERE `url` = 'absences/ajouter.php';";
    $sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'absences/modif.php';";

    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Non justifiée' WHERE `valeur` = 'Non justifi&eacute;e';";
    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Congés payés' WHERE `valeur` = 'Cong&eacute;s pay&eacute;s';";
    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Congé maternité' WHERE `valeur` = 'Cong&eacute; maternit&eacute;';";
    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Réunion syndicale' WHERE `valeur` = 'R&eacute;union syndicale';";
    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Grève' WHERE `valeur` = 'Gr&egrave;ve';";
    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Réunion' WHERE `valeur` = 'R&eacute;union';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications de nouvelles absences (Circuit A)' WHERE `nom` = 'Absences-notifications1';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A1' WHERE `nom` = 'Absences-notifications1';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications de modification d&apos;absences (Circuit A)' WHERE `nom` = 'Absences-notifications2';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A2' WHERE `nom` = 'Absences-notifications2';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications des validations niveau 1 (Circuit A)' WHERE `nom` = 'Absences-notifications3';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A3' WHERE `nom` = 'Absences-notifications3';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications des validations niveau 2 (Circuit A)' WHERE `nom` = 'Absences-notifications4';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A4' WHERE `nom` = 'Absences-notifications4';";


    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B1','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de nouvelles absences (Circuit B)','Absences','40');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B2','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification d&apos;absences (Circuit B)','Absences','50');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B3','checkboxes','[1]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1 (Circuit B)','Absences','60');";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B4','checkboxes','[3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2 (Circuit B)','Absences','65');";

    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres Absences-notifications-A1, A2, A3 et A4 ou B1, B2, B3 et B4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations' WHERE `nom` = 'Absences-notifications-agent-par-agent';";

    $sql[] = "ALTER table `{$dbprefix}select_abs` ADD COLUMN `notification_workflow` CHAR(1) AFTER `type`;";
    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `notification_workflow` = 'A' WHERE `type` != 1";


    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.004";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "CREATE TABLE `{$dbprefix}absences_documents` (id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,absence_id int(11) NOT NULL,filename text NOT NULL, date DATETIME NOT NULL);";
    $sql[] = "INSERT IGNORE INTO `{$dbprefix}acces` VALUES(NULL, 'Absences - Voir document', 100, '', '/absences/document', 0, 'Absences')";
    $sql[] = "INSERT IGNORE INTO `{$dbprefix}acces` VALUES(NULL, 'Absences - liste documents', 100, '', '/absences/documents', 0, 'Absences')";
    $sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Absences-DelaiSuppressionDocuments', 'text', '90', 'Absences','100', 'Les documents associ&eacute;s aux absences sont supprim&eacute;s au-del&agrave; du nombre de jours d&eacute;finis par ce param&egrave;tre.');";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.005";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/admin/info' WHERE `url` = 'infos/index.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/admin/info' WHERE `page` = 'infos/index.php';";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/admin/info/add' WHERE `page` = 'infos/ajout.php';";
    $sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'infos/modif.php';";
    $sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'infos/supprime.php';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.006";
if (version_compare($config['Version'], $v) === -1) {
    // Remove Old plugins system
    $sql[] = "DROP TABLE `{$dbprefix}plugins`;";
    $sql[] = "DELETE FROM `{$dbprefix}acces` WHERE page='plugins/%';";
    $sql[] = "DELETE FROM `{$dbprefix}acces` WHERE page='planningHebdo/configuration.php';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.007";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Conges-demi-journees','boolean','0','Autorise la saisie de congés en demi-journée. Fonctionne uniquement avec le mode de saisie en jour','Congés','','7');";
    $sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN halfday tinyint NULL DEFAULT 0 AFTER fin;";
    $sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN start_halfday varchar(20) NULL DEFAULT '' AFTER halfday;";
    $sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN end_halfday varchar(20) NULL DEFAULT '' AFTER start_halfday;";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.008";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('PlanningHebdo-PauseLibre', 'boolean', '0', 'Ajoute la possibilité de saisir un temps de pause libre dans le planning de présence (Module Planning Hebdo uniquement)', 'Heures de présence', 65);";

    $sql[] = "ALTER TABLE `{$dbprefix}planning_hebdo` ADD COLUMN `breaktime` TEXT NOT NULL AFTER `temps`;";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.009";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `recup_samedi` `comp_time` float NULL;";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.010";
if (version_compare($config['Version'], $v) === -1) {
    // Aff collate UTF8
    $sql[] = "ALTER TABLE `{$dbprefix}absences` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}absences_documents` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}absences_infos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}absences_recurrentes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}acces` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}activites` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}appel_dispo` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}config` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}conges` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}conges_cet` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}conges_infos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}cron` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}edt_samedi` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}heures_absences` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}heures_sp` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}hidden_tables` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}infos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}ip_blocker` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}jours_feries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}lignes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}log` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}menu` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}personnel` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_notes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_notifications` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_cellules` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_horaires` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_lignes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles_tab` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_affect` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}pl_poste_verrou` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}planning_hebdo` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}postes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}recuperations` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}responsables` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}select_abs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}select_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}select_etages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}select_groupes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}select_services` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}select_statuts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $sql[] = "ALTER TABLE `{$dbprefix}volants` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

    // drop table planning_hebdo_periodes
    $sql[]="DROP TABLE IF EXISTS `{$dbprefix}planning_hebdo_periodes`;";

    // fix a type in statistics_access
    $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe` = 'Acc&egrave;s aux statistiques' WHERE `groupe_id` = '17';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.011";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}acces` SET `categorie` = REPLACE(`categorie`, '&eacute;', 'é');";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe` = REPLACE(`groupe`, '&eacute;', 'é');";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe` = REPLACE(`groupe`, '&egrave;', 'è');";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `groupe` = REPLACE(`groupe`, '&apos;', \"'\");";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&Eacute;', 'É');";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&eacute;', 'é');";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&egrave;', 'è');";
    $sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = REPLACE(`nom`, '&apos;', \"'\");";

    $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `groupe_id` = '9';";
    $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES (\"Enregistrement d'absences pour plusieurs agents\",'9',\"Enregistrement d'absences pour plusieurs agents\", 'Absences', '25');";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.012";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = 'Congés - Nouveau' WHERE `page` = '/holiday/new';";
    $sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES (\"Congés - Enregistrer\",'100','/holiday');";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.013";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '70' WHERE `nom` = 'Absences-notifications-A4';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '80' WHERE `nom` = 'Absences-notifications-B1';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '90' WHERE `nom` = 'Absences-notifications-B2';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '100' WHERE `nom` = 'Absences-notifications-B3';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '110' WHERE `nom` = 'Absences-notifications-B4';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '120' WHERE `nom` = 'Absences-notifications-agent-par-agent';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '130' WHERE `nom` = 'Absences-notifications-titre';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '140' WHERE `nom` = 'Absences-notifications-message';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '150' WHERE `nom` = 'Absences-DelaiSuppressionDocuments';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '365' WHERE `nom` = 'Absences-DelaiSuppressionDocuments';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="19.11.00.014";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}menu` SET `titre` = 'Heures de présence' WHERE `url` = 'planningHebdo/index.php';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Date de début permettant la rotation des heures de présence (pour l\'utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)' WHERE `nom` = 'dateDebutPlHebdo';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Horaires différents les semaines avec samedi travaillé et semaines à ouverture restreinte' WHERE `nom` = 'EDTSamedi';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Utiliser le module \“Planning Hebdo\”. Ce module permet d\'enregistrer plusieurs horaires de présence par agent en définissant des périodes d\'utilisation. (Incompatible avec l\'option EDTSamedi)' WHERE `nom` = 'PlanningHebdo';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Autoriser les agents à saisir leurs heures de présence (avec le module Planning Hebdo). Les heures saisies devront être validées par un administrateur' WHERE `nom` = 'PlanningHebdo-Agents';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Ajoute la possibilité de saisir un temps de pause libre dans les heures de présence (Module Planning Hebdo uniquement)' WHERE `nom` = 'PlanningHebdo-PauseLibre';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications d\'enregistrement de nouvelles heures de présence' WHERE `nom` = 'PlanningHebdo-notifications1';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications de modification des heures de présence' WHERE `nom` = 'PlanningHebdo-notifications2';";
    $sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'La validation niveau 2 des heures de présence peut se faire directement ou doit attendre la validation niveau 1' WHERE `nom` = 'PlanningHebdo-Validation-N2';";

    $sql[]="UPDATE `{$dbprefix}config` SET `commentaires` = 'Nombre de semaines pour la rotation des heures de présence' WHERE `nom` = 'nb_semaine';";
    $sql[]="UPDATE `{$dbprefix}config` SET `valeurs` = '[[0, \"Désactivé\"], [1, \"Horaires différents les semaines avec samedi travaillé\"], [2, \"Horaires différents les semaines avec samedi travaillé et les semaines à ouverture restreinte\"]]' WHERE `nom` = 'EDTSamedi';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.04.00.000";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.001";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="ALTER TABLE `{$dbprefix}planning_hebdo` ADD `exception` INT(11) NOT NULL DEFAULT 0;";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.002";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "ALTER TABLE `{$dbprefix}absences` ADD COLUMN last_modified VARCHAR(255) NULL AFTER ical_key;";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.003";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`, `valeurs`) VALUES ('Absences-journeeEntiere', 'boolean', '1', 'Absences','38', 'Le paramètre \"Journée(s) entière(s)\" est coché par défaut lors de la saisie d\'une absence.', '');";

    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-between-sites', 'text', '0', 'Temps de trajet moyen entre sites (en minutes)', 'Planning', 95);";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-between-areas', 'text', '0', 'Temps de trajet moyen entre zones (en minutes)', 'Planning', 96);";
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-for-absences', 'text', '0', 'Temps de trajet moyen entre une absence et un poste de service public (en minutes)', 'Planning', 97);";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.004";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='/skill' WHERE `page`='activites/index.php';";

    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='/skill/add' WHERE `page`='activites/modif.php';";

    $sql[]="UPDATE `{$dbprefix}menu` SET `url`='/skill' where `url`='activites/index.php';";

    $db = new db();
    $db->select2('activites', array('id', 'nom'), "`nom` LIKE '%&%'");
    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['nom'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}activites` SET `nom` = '$new' WHERE `id` = '$id';";
            }
        }
    }

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.005";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'personnel/suppression-liste.php';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.006";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="UPDATE `{$dbprefix}acces` SET `page`='/calendar' WHERE `page`='agenda/index.php';";
    $sql[]="UPDATE `{$dbprefix}menu` SET `url`='/calendar' WHERE `url`='agenda/index.php';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.007";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/closingday' WHERE `page` ='joursFeries/index.php';";
    $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page` ='joursFeries/valid.php';";
    $sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/closingday' WHERE `url` ='joursFeries/index.php';";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.05.00.008";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/position'  WHERE  `page` = 'postes/index.php' ;";
    $sql[]="DELETE FROM `{$dbprefix}acces` WHERE  `page` = 'postes/valid.php' ;";
    $sql[]="DELETE FROM `{$dbprefix}acces` WHERE  `page` = 'postes/modif.php' ;";
    $sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/position'  WHERE  `url` = 'postes/index.php' ;";

    $db = new db();
    $db->select2('select_etages', array('id', 'valeur'), "`valeur` LIKE '%&%'");
    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['valeur'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}select_etages` SET `valeur` = '$new' WHERE `id` = '$id';";
            }
        }
    }

    $db = new db();
    $db->select2('select_groupes', array('id', 'valeur'), "`valeur` LIKE '%&%'");
    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['valeur'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}select_groupes` SET `valeur` = '$new' WHERE `id` = '$id';";
            }
        }
    }

    $db = new db();
    $db->select2('postes', array('id', 'nom'), "`nom` LIKE '%&%'");
    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['nom'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}postes` SET `nom` = '$new' WHERE `id` = '$id';";
            }
        }
    }

    $db = new db();
    $db->select2('postes', array('id', 'etage'), "`etage` LIKE '%&%'");
    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['etage'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}postes` SET `etage` = '$new' WHERE `id` = '$id';";
            }
        }
    }

    $db = new db();
    $db->select2('postes', array('id', 'groupe'), "`groupe` LIKE '%&%'");
    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['groupe'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}postes` SET `groupe` = '$new' WHERE `id` = '$id';";
            }
        }
    }

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.10.00.000";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.10.00.001";
if (version_compare($config['Version'], $v) === -1) {
    $db = new db();
    $db->select2('jours_feries', array('id', 'nom'), "`nom` LIKE '%&%'");

    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['nom'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}jours_feries` SET `nom` = '$new' WHERE `id` = '$id';";
            }
        }
    }

    $db = new db();
    $db->select2('jours_feries', array('id', 'commentaire'), "`commentaire` LIKE '%&%'");

    if($db->result){
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem['commentaire'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}jours_feries` SET `commentaire` = '$new' WHERE `id` = '$id';";
            }
        }
    }
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.10.01.000";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.10.02.000";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.10.03.000";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.10.04.000";
if (version_compare($config['Version'], $v) === -1) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

$v="20.10.04.001";
if (version_compare($config['Version'], $v) === -1) {
    $sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES ('Conges-fullday-switching-time', 'text', '4', '', 'Congés', '7', 'Temps définissant la bascule entre une demi-journée et une journée complète lorsque les crédits de congés sont comptés en jours. Format : entier ou décimal. Exemple : pour 3h30, tapez 3.5');";

    $sql[] = "ALTER TABLE `{$dbprefix}postes` ADD COLUMN `teleworking` ENUM('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' AFTER `statistiques`;";
    $sql[] = "ALTER TABLE `{$dbprefix}select_abs` ADD COLUMN `teleworking` INT(1) NOT NULL DEFAULT '0' AFTER `notification_workflow`;";

    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur`='$v' WHERE `nom`='Version';";
}

//	Execution des requetes et affichage
foreach ($sql as $elem) {
    $db=new db();
    $db->query($elem);
    if (!$db->error) {
        if ($cli) {
            echo "$elem : \033[32m[OK]\e[0m\n";
        } else {
            echo "$elem : <font style='color:green;'>OK</font><br/>\n";
        }
    } else {
        if ($cli) {
            echo "\e[1m$elem : \033[31m[KO]\e[0m\n";
        } else {
            echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
        }
    }
}

if (isset($check_tables) and $check_tables === true) {
    echo "<p><h3>V&eacute;rification des tables</h3>\n";
    $tables = array('appel_dispo', 'edt_samedi', 'heures_absences', 'heures_sp', 'hidden_tables', 'ip_blocker', 'jours_feries', 'planning_hebdo');
    foreach ($tables as $elem) {
        $db=new db();
        $db->query("SELECT count(*) FROM `{$dbprefix}{$elem}` WHERE 1;");
        if ($db->result) {
            echo "$elem : <font style='color:green;'>OK</font><br/>\n";
        } else {
            echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
        }
    }
    echo "</p>\n";


    echo "<p><h3>V&eacute;rification des champs</h3>\n";
    $champs = array(
    array('absences', array('cal_name','ical_key','valide_n1','validation_n1')),
    array('pl_poste_tab_grp', array('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche')),
    array('personnel', array('heures_hebdo','heures_travail','mails_responsables','code_ics')));
    foreach ($champs as $elem) {
        foreach ($elem[1] as $field) {
            $db=new db();
            $db->query("SELECT `$field` FROM `{$dbprefix}{$elem[0]}` WHERE 1");
            if ($db->error) {
                echo "Table {$elem[0]}, champs $field : <font style='color:red;'>Erreur</font><br/>\n";
            } else {
                echo "Table {$elem[0]}, champs $field : <font style='color:green;'>OK</font><br/>\n";
            }
        }
    }
    echo "</p>\n";
}

if (!$cli) {
    echo "<br/><br/><a href='index.php'>Continuer</a>\n";
    include(__DIR__.'/../include/footer.php');
}

/**
 * Functions used for migrations
 */

/**
 * Old decrypt function (release < 2.8.04)
 */
function decrypt_old($str)
{
    if (!function_exists('mcrypt_create_iv')) {
        return null;
    }

    $key="AB0972FA445DDE66178ADF76";
    if (!empty($GLOBALS['config']['secret'])) {
        $key = $GLOBALS['config']['secret'];
    }

    // Vérifie si la chaîne est encodée en base64
    if (base64_encode(base64_decode($str, true)) === $str) {
        // si oui, base64_decode
        $str = base64_decode($str);
    }

    $str = mcrypt_decrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);

    $block = mcrypt_get_block_size('tripledes', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    return substr($str, 0, strlen($str) - $pad);
}

/**
 * serializeToJson
 * Convertit les données seriali en json dans la base de données
 * @param string $table : nom de la table
 * @param string $field : nom du champ à modifier
 * @param string $id : nom du champ ID (clé)
 * @param array $where : condition sql where : ex: array('type'=>'checkboxes')
 */
function serializeToJson($table, $field, $id='id', $where=null, $CSRFToken)
{
    // Transformation serialized  -> json
    $dbh = new dbh();
    $dbh->CSRFToken = $CSRFToken;
    $dbh->prepare("UPDATE `{$GLOBALS['config']['dbprefix']}$table` SET `$field`=:value WHERE `$id`=:key;");
    echo "UPDATE `{$GLOBALS['config']['dbprefix']}$table` SET `$field`=:value WHERE `$id`=:key;<br/>";

    $db = new db();
    $db->select2($table, array($id,$field), $where);

    if ($db->result) {
        foreach ($db->result as $elem) {
            $value = $elem[$field];
            if ($value) {
                $value = unserialize(html_entity_decode($value, ENT_QUOTES|ENT_IGNORE, 'UTF-8'));
                if (is_array($value)) {
                    $value = json_encode($value);
                    $dbh->execute(array(':key'=>$elem[$id], ':value'=>$value));
                    echo ":key => {$elem[$id]}, :value' => {$value}";
                    if (!$dbh->error) {
                        echo " : <font style='color:green;'>OK</font><br/>\n";
                    } else {
                        echo " : <font style='color:red;'>Erreur</font><br/>\n";
                    }
                }
            }
        }
    }
}


exit;
