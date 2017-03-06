<?php
/**
Planning Biblio, Version 2.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : setup/db_data.php
Création : mai 2011
Dernière modification : 6 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Requêtes SQL insérant les données dans les tables lors de l'installation.
Ce fichier est appelé par le fichier setup/createdb.php. Les requêtes sont stockées dans le tableau $sql et executées par le
fichier setup/createdb.php
*/

//	Insertion des droits d'accés
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Authentification', 99, '', 'authentification.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning - Index', 99, '', 'planning/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning par poste - Index', 99, '', 'planning/poste/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning par poste - Semaine', 99, '', 'planning/poste/semaine.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Aide', 99, '', 'aide/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Index', 100, '', 'absences/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Voir', 100, '', 'absences/voir.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Ajouter', 100, '', 'absences/ajouter.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Password', 100, '', 'personnel/password.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Admin Index', 100, '', 'admin/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Agenda - index', 100, 'Agenda', 'agenda/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Modif', 100, '', 'absences/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Modif2', 100, '', 'absences/modif2.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Suppression', 100, '', 'absences/delete.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Infos', 1, 'Gestion des absences', 'absences/infos.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Index', 4, 'Voir le personnel', 'personnel/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Modif', 4, 'Voir le personnel', 'personnel/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Liste des postes - Index', 5, 'Gestion des postes', 'postes/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Liste des postes - Modif', 5, 'Gestion des postes', 'postes/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Liste des postes - Valid', 5, 'Gestion des postes', 'postes/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Poste - Suppression', 12, 'Modification du planning', 'planning/poste/supprimer.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Poste - horaires', 12, 'Modification du planning', 'planning/poste/horaires.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Poste - Importer un mod&egrave;le', 12, 'Modification du planning', 'planning/poste/importer.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Poste - Enregistrer un mod&egrave;le', 12, 'Modification du planning', 'planning/poste/enregistrer.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('D&eacute;bogage', 13, 'D&eacute;bogage', '');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques', 17, 'Statistiques', 'statistiques/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('stats agents par poste', 17, 'Statistiques', 'statistiques/agents.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('stats postes par agent', 17, 'Statistiques', 'statistiques/postes.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques Postes de renfort', 17, 'Statistiques', 'statistiques/postes_renfort.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques par poste (synth&egrave;se)', 17, 'Statistiques', 'statistiques/postes_synthese.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques', 17, 'Statistiques', 'statistiques/service.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques', 17, 'Statistiques', 'statistiques/statut.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Feuille de temps-  index', 17, 'Statistiques', 'statistiques/temps.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration avanc&eacute;e', 20, 'Configuration avanc&eacute;e', 'admin/config.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Suppression', 21, 'Gestion du personnel', 'personnel/suppression.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Valid', 21, 'Gestion du personnel', 'personnel/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Gestion du personnel', 21, 'Gestion du personnel', '');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des horaires des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/horaires.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des horaires des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/copie.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des lignes des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/lignes.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Activit&eacute;s - Index', 5, 'Gestion des postes','activites/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Activit&eacute;s - Modification', 5, 'Gestion des postes','activites/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Activit&eacute;s - Validation', 5, 'Gestion des postes','activites/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning - Mod&egrave;les', 12, 'Modification du planning','planning/modeles/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning - Mod&egrave;les', 12, 'Modification du planning','planning/modeles/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning - Mod&egrave;les', 12, 'Modification du planning','planning/modeles/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Suppression liste', 21, 'Gestion du personnel','personnel/suppression-liste.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Informations',23,'Informations','infos/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Informations',23,'Informations','infos/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Informations',23,'Informations','infos/supprime.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Informations',23,'Informations','infos/ajout.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/lignes_sep.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/groupes.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/groupes2.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Modification du planning - menudiv','12','Modification du planning','planning/poste/menudiv.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Modification du planning - majdb','12','Modification du planning','planning/poste/majdb.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Importation','21','Gestion du personnel','personnel/import.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Jours fériés','25','Gestion des jours fériés','joursFeries/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Jours fériés','25','Gestion des jours fériés','joursFeries/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Voir les agendas de tous','3','Voir les agendas de tous','');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Modifier ses propres absences','6','Modifier ses propres absences','');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques', 17, 'Statistiques', 'statistiques/samedis.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`) VALUES ('Gestion des absences, validation N2', 8, 'Gestion des absences, validation N2');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Statistiques', 17, 'Statistiques', 'statistiques/absences.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`) VALUES ('Gestion des absences, pi&egrave;ces justificatives', 701, 'Gestion des absences, pi&egrave;ces justificatives');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - Index','24','Gestion des plannings de présences','planningHebdo/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - Configuration','24','Gestion des plannings de présences','planningHebdo/configuration.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Modif','100','planningHebdo/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Mon Compte','100','monCompte.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Validation','100','planningHebdo/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - suppression','24','Gestion des plannings de présences','planningHebdo/supprime.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`) VALUES ('Modification des commentaires des plannings','801','Modification des commentaires des plannings');";

//	Insertion des activités
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('1', 'Assistance audiovisuel');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('2', 'Assistance autoformation');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('3', 'Communication');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('4', 'Communication r&eacute;serve');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('5', 'Inscription');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('6', 'Pr&ecirc;t/retour de document');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('7', 'Pr&ecirc;t de mat&eacute;riel');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('8', 'Rangement');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('9', 'Renseignement');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('10', 'Renseignement bibliographique');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('11', 'Renseignement r&eacute;serve');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('12', 'Renseignement sp&eacute;cialis&eacute;');";

// Insertion de la config
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Version', 'info', '2.5.7', 'Version de l&apos;application',' Divers','','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-IsEnabled', 'boolean', '0', 'Active ou d&eacute;sactive l&apos;envoi des mails','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('toutlemonde', 'boolean', '0', 'Affiche ou non l&apos;utilisateur \"tout le monde\" dans le menu.','Planning','','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-IsMail-IsSMTP', 'enum', 'IsSMTP', 'Classe &agrave; utiliser : SMTP, fonction PHP IsMail','Messagerie','IsSMTP,IsMail','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-WordWrap', '', '50', '','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Hostname', '', '', 'Nom d''h&ocirc;te du serveur pour l&apos;envoi des mails.','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Host', '', '', 'Nom FQDN ou IP du serveur SMTP.','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Port', '', '25', 'Port du serveur SMTP','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-SMTPSecure', 'enum', '', 'Cryptage utilis&eacute; par le serveur STMP.','Messagerie',',ssl,tls','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-SMTPAuth', 'boolean', '0', 'Le serveur SMTP requiert-il une authentification?','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Username', '', '', 'Nom d&apos;utilisateur pour le serveur SMTP.','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Password', 'password', '', 'Mot de passe pour le serveur SMTP','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-From', '', 'no-reply@planningbiblio.fr', 'Adresse email de l&apos;expediteur.','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-FromName', '', 'Planning', 'Nom de l&apos;expediteur.','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Signature', 'textarea', 'Ce message a été envoyé par Planning Biblio.\nMerci de ne pas y répondre.', 'Signature des e-mails','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Dimanche', 'boolean', '0', 'Utiliser le planning le dimanche',' Divers','','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('nb_semaine','enum','1','Nombre de semaine pour l\'emploi du temps','Heures de pr&eacute;sence','1,2,3','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `ordre`, `commentaires`, `categorie`) VALUES ('dateDebutPlHebdo','date','0','Date de d&eacute;but permettant la rotation des plannings hebdomadaires (pour l&apos;utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)','Heures de pr&eacute;sence');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('ctrlHresAgents','boolean','1','Contrôle des heures des agents le samedi et le dimanche','Planning','','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('agentsIndispo','boolean','1','Afficher les agents indisponibles','Planning','','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (nom,type,valeur,valeurs,ordre,commentaires,categorie) VALUES ('Granularite','enum2','60','[[60,\"Heure\"],[30,\"Demi-heure\"],[15,\"Quart d&apos;heure\"],[5,\"5 minutes\"]]','0','Granularit&eacute; des champs horaires.',' Divers');";
$sql[]="INSERT INTO `{$dbprefix}config` (nom,type,valeur,valeurs,commentaires,categorie,ordre) VALUES ('Absences-planning','enum','',',simple,détaillé,absents et présents','Afficher la liste des absences sur la page du planning','Absences','25');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Auth-Mode','enum','SQL','M&eacute;thode d&apos;authentification','Authentification','SQL,LDAP,LDAP-SQL,CAS,CAS-SQL','7');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Absences-apresValidation','boolean','1','Autoriser l&apos;enregistrement des absences apr&egrave;s validation des plannings','Absences','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Absences-planningVide','boolean','1','Absences', 
  'Autoriser le d&eacute;p&ocirc;t d&apos;absences sur des plannings en cours d&apos;&eacute;laboration','8');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-nombre','enum','1','Nombre de sites','Multisites','1,2,3,4,5,6,7,8,9,10','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site1','text','','Nom du site N°1','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site2','text','','Nom du site N°2','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site3','text','','Nom du site N°3','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site4','text','','Nom du site N°4','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site5','text','','Nom du site N°5','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site6','text','','Nom du site N°6','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site7','text','','Nom du site N°7','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site8','text','','Nom du site N°8','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site9','text','','Nom du site N°9','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site10','text','','Nom du site N°10','Multisites','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('hres4semaines','boolean','0','Afficher le total d&apos;heures des 4 derni&egrave,res semaine dans le menu','Planning','','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Auth-Anonyme','boolean','0','Autoriser les logins anonymes','Authentification','7');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('EDTSamedi', 'boolean', '0', 'Emplois du temps diff&eacute;rents les semaines o&ugrave; les samedis sont travaill&eacute;s', 'Heures de pr&eacute;sence','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('ClasseParService', 'boolean', '1', 'Classer les agents par service dans le menu d&eacute;roulant du planning','Planning','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Alerte2SP', 'boolean', '0', 'Alerter si l&apos;agent fera 2 plages de service public de suite','Planning','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('CatAFinDeService', 'boolean', '0', 'Alerter si aucun agent de cat&eacute;gorie A n&apos;est plac&eacute; en fin de service','Planning','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Recup-Agent','enum',',Texte,Menu d&eacute;roulant','Texte','Type de champ pour la r&eacute;cup&eacute;ration des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu d&eacute;roulant</b>','Congés','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-validation','boolean','0','Les absences doivent &ecirc;tre valid&eacute;es par un administrateur avant d&apos;&ecirc;tre prises en compte','Absences','30');";
// Affichage absences non validées
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Absences-non-validees','boolean','1','Absences', 'Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e','35');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-adminSeulement','boolean','0','Autoriser la saisie des absences aux administrateurs seulement.','Absences','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('display_errors','boolean','0','Afficher les erreurs PHP','D&eacute;bogage','4');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Mail-Planning','textarea','Adresses e-mails de la cellule planning, s&eacute;par&eacute;es par des ;','Messagerie','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Planning-sansRepas','boolean','1','Afficher une notification pour les Sans Repas dans le menu d&eacute;roulant et dans le planning','Planning','10'),
  ('Planning-dejaPlace','boolean','1','Afficher une notification pour les agents d&eacute;j&agrave; plac&eacute; sur un poste dans le menu d&eacute;roulant du planning','Planning','20');";

  
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications1','checkboxes','[0,1,2,3]','[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]','Destinataires des notifications de nouvelles absences','Absences','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications2','checkboxes','[0,1,2,3]','[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]','Destinataires des notifications de modification d&apos;absences','Absences','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications3','checkboxes','[1]','[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1','Absences','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications4','checkboxes','[3]','[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2','Absences','65');";

/*$sql[]="UPDATE `{$dbprefix}config` SET `valeurs`='[[0,\"Aux agents ayant le droit de g&eacute;rer les absences\"],[1,\"Au responsable direct\"],[2,\"A la cellule planning\"],[3,\"A l&apos;agent concern&eacute;\"]]'
    WHERE `nom` IN ('Absences-notifications1','Absences-notifications2','Absences-notifications3','Absences-notifications4');";
*/
  
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications-titre','text','Titre personnalis&eacute; pour les notifications de nouvelles absences','Absences','70'),
  ('Absences-notifications-message','textarea','Message personnalis&eacute; pour les notifications de nouvelles absences','Absences','80');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES
  ('Statistiques-19-20','boolean','1','Affiche ou non la colonne 19h-20h dans les statistiques','Statistiques','10'),
  ('Statistiques-20-22','boolean','1','Affiche ou non la colonne 20h-22h dans les statistiques','Statistiques','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Affichage-theme','text','default','Affichage',10,'Th&egrave;me de l&apos;application.');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) VALUES ('Affichage-titre','text','Titre affich&eacute; sur la page d&apos;accueil','Affichage','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Affichage-etages','boolean','0','Afficher les &eacute;tages des postes dans le planning','Affichage','','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`type`,`nom`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES 
  ('enum','Planning-NbAgentsCellule','1,2,3,4,5,6,7,8,10,11,12,13,14,15,16,17,18,19,20','2','Nombre d&apos;agents maximum par cellule','Planning','2');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Planning-lignesVides','boolean','1','Planning',3,'Afficher ou non les lignes vides dans les plannings valid&eacute;s');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('Planning-SR-debut', 'enum2', '11:30:00', 
  '[[\"11:00:00\",\"11h00\"],[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"]]',
  'Planning','11', 'Heure de début pour la vérification des sans repas');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('Planning-SR-fin', 'enum2', '14:30:00', 
  '[[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"],[\"15:00:00\",\"15h00\"]]',
  'Planning','12', 'Heure de fin pour la vérification des sans repas');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('Planning-Absences-Heures-Hebdo', 'boolean', '0', 'Planning','30', 'Prendre en compte les absences pour calculer le nombre d&apos;heures de SP &agrave; effectuer. (Module PlanningHebdo requis)');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('CAS-Debug', 'boolean', '0', 'CAS','50', 'Activer le d&eacutebogage pour CAS. Cr&eacute;&eacute; un fichier &quot;cas_debug.txt&quot; dans le dossier &quot;[TEMP]&quot;');";

//	Planning Hebdo
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('PlanningHebdo', 'boolean', '0', 'Heures de pr&eacute;sence','40', 'Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('PlanningHebdo-Agents', 'boolean', '1', 'Heures de pr&eacute;sence','50', 'Autoriser les agents &agrave; saisir leurs plannings de pr&eacute;sence (avec le module Planning Hebdo). Les plannings saisis devront &ecirc;tre valid&eacute;s par un administrateur.');";
// Configuration : Périodes définies
// Période définies = 0 pour le moment. Option plus utilisée par la BUA. Développements complexes.
/*
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('PlanningHebdo-PeriodesDefinies', 'boolean', '0', 'Heures de pr&eacute;sence','60', 'Utiliser des périodes définies pour les plannings hebdomadaires (Module Planning Hebdo)');";
*/
// Configuration : notifications
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('PlanningHebdo-Notifications', 'enum2', 'droit', '[[\"droit\",\"Agents ayant le droit de g&eacute;rer les plannings de pr&eacute;sence\"],[\"Mail-Planning\",\"Cellule planning\"]]', 'Heures de pr&eacute;sence','70', 'A qui envoyer les notifications de nouveaux plannings de pr&eacute;sence (Module Planning Hebdo)');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('Planning-Notifications', 'boolean', '0', 'Planning','40', 'Envoyer une notification aux agents lors de la validation des plannings les concernant');";
// Masquer les tableaux du planning
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Planning-TableauxMasques','boolean','1','Planning', 'Autoriser le masquage de certains tableaux du planning','50');";
// Appel à disponibilité
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Planning-AppelDispo','boolean','0','Planning', 'Permettre l&apos;envoi d&apos;un mail aux agents disponibles pour leur demander s&apos;ils sont volontaires pour occuper le poste choisi.','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Planning-AppelDispoSujet','text','Appel &agrave; disponibilit&eacute; [poste] [date] [debut]-[fin]','Planning', 'Sujet du mail pour les appels &agrave; disponibilit&eacute;','70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Planning-AppelDispoMessage','textarea','Chers tous,\n\nLe poste [poste] est vacant le [date] de [debut] &agrave; [fin].\n\nSi vous souhaitez occuper ce poste, vous pouvez r&eacute;pondre &agrave; cet e-mail.\n\nCordialement,\nLa cellule planning','Planning', 'Corps du mail pour les appels &agrave; disponibilit&eacute;','80');";


//	Ajout des infos LDAP dans la table config
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Host','Nom d&apos;h&ocirc;te ou adresse IP du serveur LDAP','LDAP','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Port','Port du serveur LDAP','LDAP','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`commentaires`,`categorie`,`valeurs`,`ordre`) VALUES ('LDAP-Protocol','enum','Protocol utilis&eacute;','LDAP','ldap,ldaps','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Suffix','Base LDAP','LDAP','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Filter','Filtre','LDAP','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-RDN','DN de connexion au serveur LDAP, laissez vide si connexion anonyme','LDAP','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Password','password','Mot de passe de connexion','LDAP','70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-ID-Attribute', 'enum', 'uid', 'uid,samAccountName', 'Attribut d&apos;authentification (OpenLDAP : uid, ActiveDirectory : samAccountName)', 'LDAP', 80);";

//	Ajout des infos CAS dans la table config
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Hostname','Nom d&apos;h&ocirc;te du serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Port','8080','Port serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Version','enum','2,3,4','2','Version du serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-CACert','Chemin absolut du certificat de l&apos;Autorit&eacute; de Certification. Si pas renseign&eacute;, l&apos;identit&eacute; du serveur ne sera pas v&eacute;rifi&eacute;e.','CAS','30');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES 
  ('CAS-SSLVersion', 'enum2', '1', '[[1,\"TLSv1\"],[4,\"TLSv1_0\"],[5,\"TLSv1_1\"],[6,\"TLSv1_2\"]]', 'CAS','45', 'Version SSL/TLS &agrave; utiliser pour les &eacute;changes avec le serveur CAS');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-URI','cas','Page de connexion CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-URI-Logout','cas/logout','Page de d&eacute;connexion CAS','CAS','30');";

//	Rappels
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Rappels-Actifs','boolean','0','Rappels', 'Activer les rappels','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Rappels-Jours','enum2','[[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7]]','3','Rappels', 'Nombre de jours &agrave; contr&ocirc;ler pour les rappels','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Rappels-Renfort','boolean','0','Rappels', 'Contr&ocirc;ler les postes de renfort lors des rappels','30');";

// IP Blocker
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('IPBlocker-TimeChecked','text','10','Authentification', 'Recherche les &eacute;checs d&apos;authentification lors des N derni&egrave;res minutes. ( 0 = IPBlocker d&eacute;sactiv&eacute; )','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('IPBlocker-Attempts','text','5','Authentification', 'Nombre d&apos;&eacute;checs d&apos;authentification autoris&eacute;s lors des N derni&egrave;res minutes','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('IPBlocker-Wait','text','10','Authentification', 'Temps de blocage de l&apos;IP en minutes','60');";

// ICS  
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Server1','text','ICS', 'URL du 1<sup>er</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server.domain.com/calendars/[email].ics','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Pattern1','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 1<sup>er</sup> serveur. Ex: Agenda Personnel','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Server2','text','ICS', 'URL du 2<sup>&egrave;me</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Pattern2','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 2<sup>&egrave;me</sup> serveur. Ex: Cong&eacute;s','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Server3','boolean','0','ICS', 'Utiliser une URL d&eacute;finie pour chaque agent dans le menu Administration / Les agents','44');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Pattern3','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s depuis l&apos;URL d&eacute;finie dans la fiche des agents. Ex: Agenda personnel','45');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Export', 'boolean', '0', 'ICS', 'Autoriser l&apos;exportation des plages de service public sous forme de calendriers ICS. Un calendrier par agent, accessible &agrave; l&apos;adresse [SERVER]/ics/calendar.php?login=[login_de_l_agent]', '60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('ICS-Code', 'boolean', '1', 'ICS', 'Prot&eacute;ger les calendriers ICS par des codes de façon &agrave; ce qu&apos;on ne puisse pas deviner les URLs. Si l&apos;option est activ&eacute;e, les URL seront du type : [SERVER]/ics/calendar.php?login=[login_de_l_agent]&amp;code=[code_al&eacute;atoire]', '70');";

// Importation CSV des heures de présences
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('PlanningHebdo-CSV','text','Heures de pr&eacute;sence', 'Emplacement du fichier CSV &agrave; importer (importation automatis&eacute;e) Ex: /dossier/fichier.csv','90');";

// Agenda
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES 
  ('Agenda-Plannings-Non-Valides', 'boolean', '1', 'Agenda', 'Afficher ou non les plages de service public des plannings non valid&eacute;s dans les agendas.', '10');";

// Cron
$sql[]="INSERT INTO `{$dbprefix}cron` (`h`,`m`,`dom`,`mon`,`dow`,`command`,`comments`) VALUES ('0','0','*','*','*','planningHebdo/cron.daily.php','Daily Cron for planningHebdo module');";

//	Lignes de séparations
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Magasins');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Mezzanine');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Rez de chauss&eacute;e');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Rez de jardin');";

// Menu
$sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES 
  ('10','0','Absences','absences/index.php',NULL),
  ('10','10','Voir les absences','absences/voir.php',NULL),
  ('10','20','Ajouter une absence','absences/ajouter.php',NULL),
  ('10','30','Informations','absences/infos.php',NULL),
  ('20','0','Agenda','agenda/index.php',NULL),
  ('30','0','Planning','planning/poste/index.php',NULL),
  ('40','0','Statistiques','statistiques/index.php',NULL),
  ('40','10','Feuille de temps','statistiques/temps.php',NULL),
  ('40','20','Par agent','statistiques/agents.php',NULL),
  ('40','30','Par poste','statistiques/postes.php',NULL),
  ('40','40','Par poste (Synthèse)','statistiques/postes_synthese.php',NULL),
  ('40','50','Postes de renfort','statistiques/postes_renfort.php',NULL),
  ('40','24','Par service','statistiques/service.php',NULL),
  ('40','60','Samedis','statistiques/samedis.php',NULL),
  ('40','70','Absences','statistiques/absences.php',NULL),
  ('40','26','Par statut','statistiques/statut.php',NULL),
  ('50','0','Administration','admin/index.php',NULL),
  ('50','10','Informations','infos/index.php',NULL),
  ('50','20','Les activités','activites/index.php',NULL),
  ('50','30','Les agents','personnel/index.php',NULL),
  ('50','40','Les postes','postes/index.php',NULL),
  ('50','50','Les modèles','planning/modeles/index.php',NULL),
  ('50','60','Les tableaux','planning/postes_cfg/index.php',NULL),
  ('50','70','Jours de fermeture','joursFeries/index.php',NULL),
  ('50','75','Plannings de présence','planningHebdo/index.php','config=PlanningHebdo'),
  ('50','80','Configuration','admin/config.php',NULL),
  ('60','0','Aide','aide/index.php',NULL);";

//	Personnel
$sql[]="INSERT INTO `{$dbprefix}personnel` (`id`,`nom`,`postes`,`actif`,`droits`,`login`,`password`,`commentaires`) VALUES (1, 'Administrateur', '', 'Inactif', '[1,2,3,4,5,6,7,8,12,13,17,20,21,22,23,24,25,99,100,201,202,301,302,501,502,701,801,802]','admin', 
'5f4dcc3b5aa765d61d8327deb882cf99', 'Compte cr&eacute;&eacute; lors de l&apos;installation du planning');";
$sql[]="INSERT INTO `{$dbprefix}personnel` (`id`,`nom`,`postes`,`actif`,`droits`,`commentaires`,`temps`) VALUES (2, 'Tout le monde', '', 'Actif', '[99,100]','Compte cr&eacute;&eacute; lors de l&apos;installation du planning', 'a:6:{i:0;a:4:{i:0;s:8:\"09:00:00\";i:1;s:8:\"12:00:00\";i:2;s:8:\"13:00:00\";i:3;s:8:\"17:00:00\";}i:1;a:4:{i:0;s:8:\"09:00:00\";i:1;s:8:\"12:00:00\";i:2;s:8:\"13:00:00\";i:3;s:8:\"17:00:00\";}i:2;a:4:{i:0;s:8:\"09:00:00\";i:1;s:8:\"12:00:00\";i:2;s:8:\"13:00:00\";i:3;s:8:\"17:00:00\";}i:3;a:4:{i:0;s:8:\"09:00:00\";i:1;s:8:\"12:00:00\";i:2;s:8:\"13:00:00\";i:3;s:8:\"17:00:00\";}i:4;a:4:{i:0;s:8:\"09:00:00\";i:1;s:8:\"12:00:00\";i:2;s:8:\"13:00:00\";i:3;s:8:\"17:00:00\";}i:5;a:4:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";}}');";

//	Insertion des horaires
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('09:00:00', '10:00:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('10:00:00', '11:30:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('11:30:00', '13:00:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('13:00:00', '14:30:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('14:30:00', '16:00:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('16:00:00', '17:30:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('17:30:00', '19:00:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('19:00:00', '20:00:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('20:00:00', '22:00:00', '1', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('09:00:00', '14:00:00', '2', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('14:00:00', '16:00:00', '2', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('16:00:00', '18:00:00', '2', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('18:00:00', '22:00:00', '2', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('09:00:00', '10:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('10:00:00', '11:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('11:00:00', '12:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('12:00:00', '13:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('13:00:00', '14:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('14:00:00', '15:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('15:00:00', '16:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('16:00:00', '17:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('17:00:00', '18:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('18:00:00', '19:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('19:00:00', '20:00:00', '3', 1);";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`, `fin`, `tableau`, `numero`) VALUES ('20:00:00', '22:00:00', '3', 1);";

//	Insertion des lignes
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '0', '24', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '1', '36', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '2', '3', 'ligne');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '3', '4', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '4', '5', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '6', '6', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '7', '7', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '8', '8', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '9', '9', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '10', '10', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '11', '11', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '12', '12', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '13', '4', 'ligne');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '15', '13', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '16', '14', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '17', '15', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '18', '16', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '19', '17', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '20', '19', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '21', '20', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '22', '21', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '23', '22', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '1', '0', 'Mezzanine', 'titre');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '2', '0', '23', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '2', '0', 'R&eacute;serve', 'titre');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '0', '28', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '1', '25', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '2', '26', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '3', '27', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '4', '29', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '5', '30', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '6', '31', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '7', '32', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '8', '33', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '9', '34', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '10', '35', 'poste');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '3', '0', 'Rangement', 'titre');";

// Insertion des cellules grise
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '0', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '0', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '1', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '1', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '3', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '4', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '6', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '7', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '7', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '8', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '8', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '9', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '9', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '10', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '10', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '11', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '11', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '12', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '14', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '15', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '15', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '16', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '16', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '17', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '17', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '18', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '18', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '19', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '19', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '20', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '20', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '21', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '21', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '22', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '22', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '23', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '23', '8');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '1', '23', '9');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '2', '0', '1');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '2', '0', '4');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '0', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '1', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '2', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '3', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '4', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '5', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '6', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '7', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '8', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '9', '12');";
$sql[]="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`, `tableau`, `ligne`, `colonne`) VALUES ('1', '3', '10', '12');";

$sql[]="INSERT INTO `{$dbprefix}pl_poste_tab` (`tableau`,`nom`) VALUES(1, 'Tableau 1');";

//	Insertion des postes
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('4', 'Inscription 1', '', '0', 'Obligatoire', 'RDC', '[5,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('5', 'Retour', '', '0', 'Obligatoire', 'RDC', '[6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('6', 'Pr&ecirc;t / retour 1', '', '0', 'Obligatoire', 'RDC', '[7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('7', 'Pr&ecirc;t / retour 2', '', '0', 'Renfort', 'RDC', '[7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('8', 'Pr&ecirc;t / retour 3', '', '0', 'Renfort', 'RDC', '[5,7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('9', 'Pr&ecirc;t / retour 4', '', '0', 'Renfort', 'RDC', '[7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('10', 'Inscription 2', '', '0', 'Renfort', 'RDC', '[5]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('11', 'Communication RDC', '', '0', 'Renfort', 'RDC', '[3,7,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('12', 'Renseignement RDC', '', '0', 'Obligatoire', 'RDC', '[9,10]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('13', 'Renseignement sp&eacute;cialis&eacute; 1', '', '0', 'Obligatoire', 'RDJ', '[9,10,12]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('14', 'Renseignement sp&eacute;cialis&eacute; 2', '', '0', 'Renfort', 'RDJ', '[9,10,12]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('15', 'Renseignement sp&eacute;cialis&eacute; 3', '', '0', 'Renfort', 'RDJ', '[9,10,12]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('16', 'Communication (banque 1)', '', '0', 'Obligatoire', 'RDJ', '[3,7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('17', 'Communication (banque 2)', '', '0', 'Renfort', 'RDJ', '[3,9,10]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('19', 'Communication (coordination)', '', '0', 'Obligatoire', 'RDJ', '[3]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('20', 'Communication (magasin 1)', '', '0', 'Obligatoire', 'RDJ', '[3]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('21', 'Communication (magasin 2)', '', '0', 'Obligatoire', 'RDJ', '[11]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('22', 'Communication (magasin 3)', '', '0', 'Renfort', 'RDJ', '[3]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('23', 'Consultation de la r&eacute;serve', '', '0', 'Obligatoire', 'RDJ', '[4,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('24', 'Audiovisuel et autoformation', '', '0', 'Obligatoire', 'Mezzanine', '[1,2,7,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('25', 'Rangement 2', '', '0', 'Obligatoire', 'RDC', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('26', 'Rangement 3', '', '0', 'Obligatoire', 'RDC', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('27', 'Rangement 4', '', '0', 'Renfort', 'RDC', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('28', 'Rangement 1', '', '0', 'Obligatoire', 'Mezzanine', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('29', 'Rangement 5', '', '0', 'Obligatoire', 'RDJ', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('30', 'Rangement 6', '', '0', 'Obligatoire', 'RDJ', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('31', 'Rangement 7', '', '0', 'Renfort', 'RDJ', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('32', 'Rangement 8', '', '0', 'Renfort', 'RDJ', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('33', 'Rangement 9', '', '0', 'Renfort', 'RDJ', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('34', 'Rangement 10', '', '0', 'Obligatoire', 'Magasins', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('35', 'Rangement 11', '', '0', 'Obligatoire', 'Magasins', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('36', 'Renseignement kiosque', '', '0', 'Renfort', 'Mezzanine', '[9,10]','1','1');";

//	Insertion des motif d'absences
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Non justifi&eacute;e', '1');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Cong&eacute;s pay&eacute;s', '2');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Maladie', '3');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Cong&eacute; maternit&eacute;', '4');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('R&eacute;union syndicale', '5');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Gr&egrave;ve', '6');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Formation', '7');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Concours', '8');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Stage', '9');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('R&eacute;union', '10');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Entretien', '11');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`) VALUES ('Autre', '12');";

//	Insertion des catégories
$sql[]="INSERT INTO `{$dbprefix}select_categories` (`valeur`,`rang`) VALUES ('Cat&eacute;gorie A','10'),('Cat&eacute;gorie B','20'),('Cat&eacute;gorie C','30');";

//	Insertion des étages
$sql[]="INSERT INTO `{$dbprefix}select_etages` (`valeur`,`rang`) VALUES ('Mezzanine','1'),('RDC','2'),('RDJ','3'),('Magasins','4');";

//	Insertion des noms des services
$sql[]="INSERT INTO `{$dbprefix}select_services` (`valeur`,`rang`) VALUES ('P&ocirc;le public', '1');";
$sql[]="INSERT INTO `{$dbprefix}select_services` (`valeur`,`rang`) VALUES ('P&ocirc;le conservation', '2');";
$sql[]="INSERT INTO `{$dbprefix}select_services` (`valeur`,`rang`) VALUES ('P&ocirc;le collection', '3');";
$sql[]="INSERT INTO `{$dbprefix}select_services` (`valeur`,`rang`) VALUES ('P&ocirc;le informatique', '4');";
$sql[]="INSERT INTO `{$dbprefix}select_services` (`valeur`,`rang`) VALUES ('P&ocirc;le administratif', '5');";
$sql[]="INSERT INTO `{$dbprefix}select_services` (`valeur`,`rang`) VALUES ('Direction', '6');";

//	Insertion des statuts
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('Conservateur', '1', '1');";
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('Biblioth&eacute;caire', '2', '1');";
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('AB', '3', '0');";
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('BAS', '4', '2');";
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('Magasinier', '5', '3');";
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('Etudiant', '6', '3');";
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('Garde de nuit', '7', '0');";
$sql[]="INSERT INTO `{$dbprefix}select_statuts` (`valeur`,`rang`,`categorie`) VALUES ('Autre', '8', '0');";
?>
