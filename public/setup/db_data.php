<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

@file setup/db_data.php
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Alex arnaud <alex.arnaud@biblibre.com >

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
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Aide', 99, '', '/help');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Index', 100, '', 'absences/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Voir', 100, '', 'absences/voir.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Ajouter', 100, '', '/absence');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Password', 100, '', 'personnel/password.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Admin Index', 100, '', 'admin/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Agenda - index', 100, 'Agenda', 'agenda/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Modif', 100, '', 'absences/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Modif2', 100, '', 'absences/modif2.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Absences - Suppression', 100, '', 'absences/delete.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`, `ordre`) VALUES ('Absences - Infos', 201, 'Gestion des absences, validation niveau 1', '/absences/info', 'Absences', 30);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Index', 4, 'Voir les fiches des agents', 'personnel/index.php', 'Agents', 60);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Modif', 4, 'Voir les fiches des agents', '/agent', 'Agents', 60);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Liste des postes - Index', 5, 'Gestion des postes', 'postes/index.php','Postes',160);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Liste des postes - Modif', 5, 'Gestion des postes', 'postes/modif.php','Postes',160);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Liste des postes - Valid', 5, 'Gestion des postes', 'postes/valid.php','Postes',160);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Poste - Suppression', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', 'planning/poste/supprimer.php','Planning','110');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Poste - Importer un mod&egrave;le', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', 'planning/poste/importer.php','Planning','110');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Poste - Enregistrer un mod&egrave;le', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', 'planning/poste/enregistrer.php','Planning','110');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques', 17, 'Acc&egrave;s au statistiques', 'statistiques/index.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('stats agents par poste', 17, 'Acc&egrave;s au statistiques', 'statistiques/agents.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('stats postes par agent', 17, 'Acc&egrave;s au statistiques', 'statistiques/postes.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques Postes de renfort', 17, 'Acc&egrave;s au statistiques', 'statistiques/postes_renfort.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques par poste (synth&egrave;se)', 17, 'Acc&egrave;s au statistiques', 'statistiques/postes_synthese.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques', 17, 'Acc&egrave;s au statistiques', 'statistiques/service.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques', 17, 'Acc&egrave;s au statistiques', 'statistiques/statut.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Feuille de temps-  index', 17, 'Acc&egrave;s au statistiques', 'statistiques/temps.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Liste des agents pr&eacute;sents et absents', 1301, 'Acc&egrave;s aux statistiques Pr&eacute;sents / Absents', '/statistics/attendeesmissing','Statistiques','171');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration avanc&eacute;e', 20, 'Configuration avanc&eacute;e', '/config');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Suppression', 21, 'Gestion des agents', 'personnel/suppression.php', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Valid', 21, 'Gestion des agents', 'personnel/valid.php', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Gestion du personnel', 21, 'Gestion des agents', '', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/index.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des horaires des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/horaires.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des horaires des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/copie.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des lignes des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/lignes.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Activit&eacute;s - Index', 5, 'Gestion des postes','activites/index.php','Postes',160);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Activit&eacute;s - Modification', 5, 'Gestion des postes','activites/modif.php','Postes',160);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Activit&eacute;s - Validation', 5, 'Gestion des postes','activites/valid.php','Postes',160);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning - Mod&egrave;les', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les','planning/modeles/index.php','Planning','110');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning - Mod&egrave;les', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les','planning/modeles/modif.php','Planning','110');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning - Mod&egrave;les', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les','planning/modeles/valid.php','Planning','110');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Suppression liste', 21, 'Gestion des agents','personnel/suppression-liste.php', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/modif.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (id, nom, groupe_id, groupe, page, ordre, categorie) VALUES (NULL, 'Afficher les informations', 23, 'Informations', '/admin/info', 0, 'Informations');";
$sql[]="INSERT INTO `{$dbprefix}acces` (id, nom, groupe_id, groupe, page, ordre, categorie) VALUES (NULL, 'Ajouter une information', 23, 'Informations', '/admin/info/add', 0, 'Informations');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/lignes_sep.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/groupes.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','planning/postes_cfg/groupes2.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Modification des plannings - menudiv','1001','Modification des plannings','planning/poste/menudiv.php','Planning','120');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Modification des plannings - majdb','1001','Modification des plannings','planning/poste/majdb.php','Planning','120');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Importation','21','Gestion des agents','personnel/import.php', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Jours f&eacute;ri&eacute;s','25','Gestion des jours f&eacute;ri&eacute;s','joursFeries/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Jours f&eacute;ri&eacute;s','25','Gestion des jours f&eacute;ri&eacute;s','joursFeries/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Voir les agendas de tous','3','Voir les agendas de tous','', 'Agendas', 55);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Modifier ses propres absences','6','Modifier ses propres absences','','Absences',20);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques', 17, 'Acc&egrave;s au statistiques', 'statistiques/samedis.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Gestion des absences, validation niveau 2', 501, 'Gestion des absences, validation niveau 2', 'Absences', 40);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques', 17, 'Acc&egrave;s au statistiques', 'statistiques/absences.php','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Gestion des absences, pi&egrave;ces justificatives', 701, 'Gestion des absences, pi&egrave;ces justificatives', 'Absences', 50);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Hebdo - Index','1101','Gestion des heures de pr&eacute;sences, validation niveau 1','planningHebdo/index.php','Heures de présence','80');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Modif','100','planningHebdo/modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Mon Compte','100','monCompte.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Planning Hebdo - Validation','100','planningHebdo/valid.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Hebdo - suppression','1101','Gestion des heures de pr&eacute;sences, validation niveau 1','planningHebdo/supprime.php','Heures de présence','80');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Hebdo - Admin N2','1201','Gestion des heures de pr&eacute;sences, validation niveau 2','','Heures de présence','90');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Modification des commentaires des plannings','801','Modification des commentaires des plannings', 'Planning', 130);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Griser les cellules des plannings','901','Griser les cellules des plannings','Planning','125');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Notifications / Validations', 21, 'Gestion des agents', 'notifications/index.php', 'Agents', 70);";
$sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Agents volants', 301, 'Cr&eacute;ation / modification des plannings, utilisation et gestion des mod&egrave;les', 'planning/volants/index.php', 'Planning', 110);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Index','100','conges/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Liste','100','/holiday/index');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Enregistrer','100','/holiday/new');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Modifier','100','/holiday/edit');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe`,`groupe_id`,`categorie`,`ordre`) VALUES ('Gestion des cong&eacute;s, validation niveau 2','Gestion des cong&eacute;s, validation niveau 2',601,'Congés','76');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Infos','100','conges/infos.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - r&eacute;cuperations','100','conges/recuperations.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - R&eacute;cup&eacute;ration','100','conges/recuperation_modif.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Gestion des cong&eacute;s, validation niveau 1','401','Gestion des cong&eacute;s, validation niveau 1','Congés','75');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Compte &Eacute;pargne Temps','100','conges/cet.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Congés - Cr&eacute;dits','100','','conges/credits.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - R&eacute;cup&eacute;rations','100','conges/recuperation_valide.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Poser des r&eacute;cup&eacute;rations','100','conges/recup_pose.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Semaines fixes','100','/statedweek');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `page`, `ordre`) VALUES('Échanges de poste', 100, '/interchange', 0)";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `page`, `ordre`) VALUES('Demande d\'échange', 100, '/interchange/add', 0)";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`, `groupe_id`, `groupe`, `ordre`, `categorie`) VALUES('Échanges de poste', 1301, 'Validation des échanges de postes', 135, 'Semaines fixes')";

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
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Version', 'info', '19.11.00.009', 'Version de l&apos;application',' Divers','','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('URL', 'info', '', 'URL de l&apos;application',' Divers','','10');";
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
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Signature', 'textarea', 'Ce message a &eacute;t&eacute; envoy&eacute; par Planning Biblio.\nMerci de ne pas y r&eacute;pondre.', 'Signature des e-mails','Messagerie','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Dimanche', 'boolean', '0', 'Utiliser le planning le dimanche',' Divers','','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('nb_semaine','enum','1','Nombre de semaine pour l\'emploi du temps','Heures de présence','1,2,3','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `ordre`, `commentaires`, `categorie`) VALUES ('dateDebutPlHebdo','date','0','Date de d&eacute;but permettant la rotation des plannings hebdomadaires (pour l&apos;utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)','Heures de présence');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('ctrlHresAgents','boolean','1','Contr&ocirc;le des heures des agents le samedi et le dimanche','Planning','','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('agentsIndispo','boolean','1','Afficher les agents indisponibles','Planning','','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (nom,type,valeur,valeurs,commentaires,categorie,`ordre`) VALUES ('Granularite','enum2','60','[[60,\"Heure\"],[30,\"Demi-heure\"],[15,\"Quart d&apos;heure\"],[5,\"5 minutes\"]]','Granularit&eacute; des champs horaires.',' Divers',30);";
$sql[]="INSERT INTO `{$dbprefix}config` (nom,type,valeur,valeurs,commentaires,categorie,ordre) VALUES ('Absences-planning','enum2','','[[0,\"\"],[1,\"simple\"],[2,\"d&eacute;taill&eacute;\"],[3,\"absents et pr&eacute;sents\"]]','Afficher la liste des absences sur la page du planning','Absences','25');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Auth-Mode','enum','SQL','M&eacute;thode d&apos;authentification','Authentification','SQL,LDAP,LDAP-SQL,CAS,CAS-SQL','7');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Absences-apresValidation','boolean','1','Autoriser l&apos;enregistrement des absences apr&egrave;s validation des plannings','Absences','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Absences-planningVide','boolean','1','Absences', 
  'Autoriser le d&eacute;p&ocirc;t d&apos;absences sur des plannings en cours d&apos;&eacute;laboration','8');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-nombre','enum','1','Nombre de sites','Multisites','1,2,3,4,5,6,7,8,9,10','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site1','text','','Nom du site N°1','Multisites','','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site1-mail','text','','Adresses e-mails de la cellule planning du site N°1, séparées par des ;','Multisites','','25');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site2','text','','Nom du site N°2','Multisites','','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site2-mail','text','','Adresses e-mails de la cellule planning du site N°2, séparées par des ;','Multisites','','35');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site3','text','','Nom du site N°3','Multisites','','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site3-mail','text','','Adresses e-mails de la cellule planning du site N°3, séparées par des ;','Multisites','','45');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site4','text','','Nom du site N°4','Multisites','','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site4-mail','text','','Adresses e-mails de la cellule planning du site N°4, séparées par des ;','Multisites','','55');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site5','text','','Nom du site N°5','Multisites','','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site5-mail','text','','Adresses e-mails de la cellule planning du site N°5, séparées par des ;','Multisites','','65');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site6','text','','Nom du site N°6','Multisites','','70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site6-mail','text','','Adresses e-mails de la cellule planning du site N°6, séparées par des ;','Multisites','','75');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site7','text','','Nom du site N°7','Multisites','','80');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site7-mail','text','','Adresses e-mails de la cellule planning du site N°7, séparées par des ;','Multisites','','85');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site8','text','','Nom du site N°8','Multisites','','90');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site8-mail','text','','Adresses e-mails de la cellule planning du site N°8, séparées par des ;','Multisites','','95');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site9','text','','Nom du site N°9','Multisites','','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site9-mail','text','','Adresses e-mails de la cellule planning du site N°9, séparées par des ;','Multisites','','105');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site10','text','','Nom du site N°10','Multisites','','110');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site10-mail','text','','Adresses e-mails de la cellule planning du site N°10, séparées par des ;','Multisites','','115');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('hres4semaines','boolean','0','Afficher le total d\'heures des 4 dernières semaine dans le menu','Planning','','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Auth-Anonyme','boolean','0','Autoriser les logins anonymes','Authentification','7');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('EDTSamedi', 'enum2', '[[0, \"D&eacute;sactiv&eacute;\"], [1, \"Emploi du temps diff&eacute;rent les semaines avec samedi travaill&eacute;\"], [2, \"Emploi du temps diff&eacute;rent les semaines avec samedi travaill&eacute; et les semaines &agrave; ouverture restreinte\"]]', '0', 'Emplois du temps diff&eacute;rents les semaines o&ugrave; le samedi est travaill&eacute; et les semaines &agrave; ouverture restreinte', 'Heures de présence','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('ClasseParService', 'boolean', '1', 'Classer les agents par service dans le menu d&eacute;roulant du planning','Planning','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Alerte2SP', 'boolean', '0', 'Alerter si l&apos;agent fera 2 plages de service public de suite','Planning','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('CatAFinDeService', 'boolean', '0', 'Alerter si aucun agent de cat&eacute;gorie A n&apos;est plac&eacute; en fin de service','Planning','0');";

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Recuperations', 'enum2', '0', '[[0,\"Assembler\"],[1,\"Dissocier\"]]', 'Congés', 'Traiter les r&eacute;cup&eacute;rations comme les cong&eacute;s (Assembler), ou les traiter s&eacute;par&eacute;ment (Dissocier)', '3');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Recup-Agent','enum2','[[0,\"\"],[1,\"Texte\"],[2,\"Menu déroulant\"]]','Texte','Type de champ pour la r&eacute;cup&eacute;ration des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu d&eacute;roulant</b>','Congés','40');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-SamediSeulement','boolean','0','Autoriser les demandes de récupération des samedis seulement','Congés','','20');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-Uneparjour','boolean','1','Autoriser une seule demande de r&eacute;cup&eacute;ration par jour','Congés','','15');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DeuxSamedis','boolean','0','Autoriser les demandes de récupération pour 2 samedis','Congés','','30');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiDefaut','text','7','Delai pour les demandes de récupération par d&eacute;faut (en jours)','Congés','','40');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiTitulaire1','enum2','0','Delai pour les demandes de récupération des titulaires pour 1 samedi (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','50');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiTitulaire2','enum2','0','Delai pour les demandes de récupération des titulaires pour 2 samedis (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','60');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiContractuel1','enum2','0','Delai pour les demandes de récupération des contractuels pour 1 samedi (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','70');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Recup-DelaiContractuel2','enum2','0','Delai pour les demandes de récupération des contractuels pour 2 samedis (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','80');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications1','checkboxes','[2]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications de nouvelles demandes de crédit de récupérations','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications2','checkboxes','[2]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications de modification de crédit de récupérations','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications3','checkboxes','[1]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications des validations de crédit de récupérations niveau 1','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications4','checkboxes','[3]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications des validations de crédit de récupérations niveau 2','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels', 'boolean', '0', 'Congés', 'Activer / D&eacute;sactiver l&apos;envoi de rappels s&apos;il y a des cong&eacute;s non-valid&eacute;s', '6');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-Jours', 'text', '14', 'Congés', 'Nombre de jours &agrave; contr&ocirc;ler pour l&apos;envoi de rappels sur les cong&eacute;s non-valid&eacute;s', '7');";
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Conges-demi-journees','boolean','0','Autorise la saisie de congés en demi-journée. Fonctionne uniquement avec le mode de saisie en jour','Congés','','7');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Conges-Heures', 'boolean', '0', 'Congés','5', 'Permet la saisie de cong&eacute;s avec une heure de deacute;but et une heure de fin.');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N1', 'checkboxes', '[\"Mail-Planning\"]',
  '[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Congés', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 1', '8');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N2', 'checkboxes', '[\"mails_responsables\"]',
'[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Congés', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 2', '9');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le cong&eacute; doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Congés', 'La validation niveau 2 des cong&eacute;s peut se faire directement ou doit attendre la validation niveau 1', '4');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Enable', 'boolean', '0', '', 'Congés', 'Activer le module Congés', '1');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Mode', 'enum2', 'heures', '[[\"heures\",\"Heures\"],[\"jours\",\"Jours\"]]', 'Congés', 'Décompte des congés en heures ou en jours', '2');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-validation','boolean','0','Les absences doivent &ecirc;tre valid&eacute;es par un administrateur avant d&apos;&ecirc;tre prises en compte','Absences','30');";
// Affichage absences non validées
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Absences-non-validees','boolean','1','Absences', 'Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e','35');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-agent-preselection', 'boolean', '1', 'Présélectionner l&apos;agent connecté lors de l&apos;ajout d&apos;une nouvelle absence.','Absences','36');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-tous', 'boolean', '0', 'Autoriser l&apos;enregistrement d&apos;absences pour tous les agents en une fois','Absences','37');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-adminSeulement','boolean','0','Autoriser la saisie des absences aux administrateurs seulement.','Absences','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Mail-Planning','textarea','Adresses e-mails de la cellule planning, s&eacute;par&eacute;es par des ;','Messagerie','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Planning-sansRepas','boolean','1','Afficher une notification pour les Sans Repas dans le menu d&eacute;roulant et dans le planning','Planning','10'),
  ('Planning-dejaPlace','boolean','1','Afficher une notification pour les agents d&eacute;j&agrave; plac&eacute; sur un poste dans le menu d&eacute;roulant du planning','Planning','20');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Planning-Heures','boolean', '1', '', 'Planning', 'Afficher les heures &agrave; c&ocirc;t&eacute; du nom des agents dans le menu du planning','25');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES 
      ('Planning-CommentairesToujoursActifs', 'boolean', '0', 'Planning','100', 'Afficher la zone de commentaire m&ecirc;me si le planning n\'est pas encore commenc&eacute;.');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications-A1','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de nouvelles absences (Circuit A)','Absences','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications-A2','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification d&apos;absences (Circuit A)','Absences','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications-A3','checkboxes','[1]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1 (Circuit A)','Absences','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications-A4','checkboxes','[3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2 (Circuit A)','Absences','65');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Absences-notifications-agent-par-agent','boolean', '0', 'Absences', 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres Absences-notifications-A1, A2, A3 et A4 ou B1, B2, B3 et B4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','67');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B1','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de nouvelles absences (Circuit B)','Absences','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B2','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification d&apos;absences (Circuit B)','Absences','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B3','checkboxes','[1]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1 (Circuit B)','Absences','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B4','checkboxes','[3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2 (Circuit B)','Absences','65');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Absences-notifications-titre','text','Titre personnalis&eacute; pour les notifications de nouvelles absences','Absences','70'),
  ('Absences-notifications-message','textarea','Message personnalis&eacute; pour les notifications de nouvelles absences','Absences','80');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('Statistiques-Heures', 'textarea', 'Afficher des statistiques sur les cr&eacute;neaux horaires voulus. Les cr&eacute;neaux doivent &ecirc;tre au format 00h00-00h00 et s&eacute;par&eacute;s par des ; Exemple : 19h00-20h00; 20h00-21h00; 21h00-22h00','Statistiques','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Affichage-theme','text','default','Affichage',10,'Th&egrave;me de l&apos;application.');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) VALUES ('Affichage-titre','text','Titre affich&eacute; sur la page d&apos;accueil','Affichage','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Affichage-etages','boolean','0','Afficher les &eacute;tages des postes dans le planning','Affichage','','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`type`,`nom`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES
  ('enum','Planning-NbAgentsCellule','1,2,3,4,5,6,7,8,10,11,12,13,14,15,16,17,18,19,20','2','Nombre d&apos;agents maximum par cellule','Planning','2');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Planning-lignesVides','boolean','1','Planning',3,'Afficher ou non les lignes vides dans les plannings valid&eacute;s');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES
  ('Planning-SR-debut', 'enum2', '11:30:00', 
  '[[\"11:00:00\",\"11h00\"],[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"]]',
  'Planning','11', 'Heure de d&eacute;but pour la v&eacute;rification des sans repas');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES
  ('Planning-SR-fin', 'enum2', '14:30:00', 
  '[[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"],[\"15:00:00\",\"15h00\"]]',
  'Planning','12', 'Heure de fin pour la v&eacute;rification des sans repas');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('Planning-Absences-Heures-Hebdo', 'boolean', '0', 'Planning','30', 'Prendre en compte les absences pour calculer le nombre d&apos;heures de SP &agrave; effectuer. (Module PlanningHebdo requis)');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('CAS-Debug', 'boolean', '0', 'CAS','50', 'Activer le débogage pour CAS. Créé un fichier \"cas_debug.txt\" dans le dossier \"[TEMP]\"');";

//	Planning Hebdo
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('PlanningHebdo', 'boolean', '0', 'Heures de présence','40', 'Utiliser ou non le module &ldquo;Planning Hebdo&rdquo;. Ce module permet d&apos;enregistrer plusieurs plannings de pr&eacute;sence par agent en d&eacute;finissant des p&eacute;riodes d&apos;utilisation. (Incompatible avec l&apos;option EDTSamedi)');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('PlanningHebdo-Agents', 'boolean', '1', 'Heures de présence','50', 'Autoriser les agents &agrave; saisir leurs plannings de pr&eacute;sence (avec le module Planning Hebdo). Les plannings saisis devront &ecirc;tre valid&eacute;s par un administrateur.');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('PlanningHebdo-Pause2', 'boolean', '0', '2 pauses dans une journ&eacute;e', 'Heures de présence', 60);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('PlanningHebdo-PauseLibre', 'boolean', '0', 'Ajoute la possibilité de saisir un temps de pause libre dans le planning de présence (Module Planning Hebdo uniquement)', 'Heures de présence', 65);";

// Configuration : notifications
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('PlanningHebdo-notifications1','checkboxes','[0,4]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications de nouveaux plannings de pr&eacute;sence','Heures de présence','70');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('PlanningHebdo-notifications2','checkboxes','[0,4]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification de planning de pr&eacute;sence','Heures de présence','72');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('PlanningHebdo-notifications3','checkboxes','[1]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1','Heures de présence','74');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) 
  VALUES ('PlanningHebdo-notifications4','checkboxes','[4]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2','Heures de présence','76');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('PlanningHebdo-notifications-agent-par-agent','boolean', '0', 'Heures de présence', 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres PlanningHebdo-notifications1, 2, 3 et 4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','80');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('PlanningHebdo-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le planning doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Heures de présence', 'La validation niveau 2 des plannings de pr&eacute;sence peut se faire directement ou doit attendre la validation niveau 1', '85');";

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
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Filter','Filtre LDAP. OpenLDAP essayez \"(objectclass=inetorgperson)\" , Active Directory essayez \"(&(objectCategory=person)(objectClass=user))\". Vous pouvez bien-s&ucirc;r personnaliser votre filtre.','LDAP','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-RDN','DN de connexion au serveur LDAP, laissez vide si connexion anonyme','LDAP','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`commentaires`,`categorie`,`ordre`) VALUES ('LDAP-Password','password','Mot de passe de connexion','LDAP','70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-ID-Attribute', 'enum', 'uid', 'uid,samaccountname,supannAliasLogin', 'Attribut d&apos;authentification (OpenLDAP : uid, Active Directory : samaccountname)', 'LDAP', 80);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-Matricule', 'text', '', '', 'Attribut &agrave; importer dans le champ matricule (optionnel)', 'LDAP', 90);";

//	Ajout des infos CAS dans la table config
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Hostname','Nom d&apos;h&ocirc;te du serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Port','8080','Port serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Version','enum','2.0,3.0,4.0','2.0','Version du serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-CACert','Chemin absolut du certificat de l&apos;Autorit&eacute; de Certification. Si pas renseign&eacute;, l&apos;identit&eacute; du serveur ne sera pas v&eacute;rifi&eacute;e.','CAS','30');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES
  ('CAS-SSLVersion', 'enum2', '1', '[[1,\"TLSv1\"],[4,\"TLSv1_0\"],[5,\"TLSv1_1\"],[6,\"TLSv1_2\"]]', 'CAS','45', 'Version SSL/TLS &agrave; utiliser pour les &eacute;changes avec le serveur CAS');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `ordre`, `commentaires`) VALUES
  ('CAS-ServiceURL', 'text', 'CAS','47', 'URL de Planning Biblio. A renseigner seulement si la redirection ne fonctionne pas après authentification sur le serveur CAS, si vous utilisez un Reverse Proxy par exemple.');";

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
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Status1','enum2', 'CONFIRMED', '[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]', 'ICS', 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','22');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Server2','text','ICS', 'URL du 2<sup>&egrave;me</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Pattern2','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 2<sup>&egrave;me</sup> serveur. Ex: Congés','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Status2','enum2', 'CONFIRMED', '[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]', 'ICS', 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','42');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Server3','boolean','0','ICS', 'Utiliser une URL d&eacute;finie pour chaque agent dans le menu Administration / Les agents','44');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Pattern3','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s depuis l&apos;URL d&eacute;finie dans la fiche des agents. Ex: Agenda personnel','45');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Status3','enum2', 'CONFIRMED', '[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]', 'ICS', 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','47');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Export', 'boolean', '0', 'ICS', 'Autoriser l&apos;exportation des plages de service public sous forme de calendriers ICS. Un calendrier par agent, accessible &agrave; l&apos;adresse [SERVER]/ics/calendar.php?login=[login_de_l_agent]', '60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Code', 'boolean', '1', 'ICS', 'Prot&eacute;ger les calendriers ICS par des codes de façon &agrave; ce qu&apos;on ne puisse pas deviner les URLs. Si l&apos;option est activ&eacute;e, les URL seront du type : [SERVER]/ics/calendar.php?login=[login_de_l_agent]&amp;code=[code_al&eacute;atoire]', '70');";

// Importation CSV des heures de présences
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('PlanningHebdo-CSV','text','Heures de présence', 'Emplacement du fichier CSV &agrave; importer (importation automatis&eacute;e) Ex: /dossier/fichier.csv','90');";

// Agenda
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Agenda-Plannings-Non-Valides', 'boolean', '1', 'Agenda', 'Afficher ou non les plages de service public des plannings non valid&eacute;s dans les agendas.', '10');";

// Agents volants
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Planning-agents-volants','boolean', '0', '', 'Planning', 'Utiliser le module \"Agents volants\" permettant de diff&eacute;rencier un groupe d&apos;agents dans le planning','90');";

// Importation CSV HAMAC
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Hamac-csv','text', '', '', 'Hamac', 'Chemin d&apos;acc&egrave;s au fichier CSV pour l&apos;importation des absences depuis Hamac','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Hamac-motif', 'text', '', '', 'Hamac', 'Motif pour les absences import&eacute;s depuis Hamac. Ex: Hamac','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Hamac-status','enum2', '1,2,3,5,6', '[[\"1,2,3,5,6\",\"Valid&eacute;es et en attente de validation\"],[\"2\",\"Valid&eacute;es\"]]', 'Hamac', 'Importer les absences valid&eacute;es et en attente de validation ou seulement les absences valid&eacute;es.','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Hamac-id','enum2', 'login', '[[\"login\",\"Login\"],[\"matricule\",\"Matricule\"]]', 'Hamac', 'Champ Planning Biblio &agrave; utiliser pour mapper les agents.','40');";

// Cron
$sql[]="INSERT INTO `{$dbprefix}cron` (`h`,`m`,`dom`,`mon`,`dow`,`command`,`comments`) VALUES ('0','0','*','*','*','planningHebdo/cron.daily.php','Daily Cron for planningHebdo module');";
$sql[]="INSERT INTO `{$dbprefix}cron` (m,h,dom,mon,dow,command,comments) VALUES (0,0,1,1,'*','conges/cron.jan1.php','Cron Congés 1er Janvier');";
$sql[]="INSERT INTO `{$dbprefix}cron` (m,h,dom,mon,dow,command,comments) VALUES (0,0,1,9,'*','conges/cron.sept1.php','Cron Congés 1er Septembre');";

//	Lignes de séparations
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Magasins');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Mezzanine');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Rez de chauss&eacute;e');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Rez de jardin');";

// Menu
$sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES
  ('10','0','Absences','absences/voir.php',NULL),
  ('10','10','Voir les absences','absences/voir.php',NULL),
  ('10','20','Ajouter une absence','/absence',NULL),
  ('10','30','Informations','/absences/info',NULL),
  ('15','0','Congés','/holiday/index','config=Conges-Enable'),
  ('15','10','Liste des cong&eacute;s','/holiday/index','config=Conges-Enable'),
  ('15','15','Liste des r&eacute;cup&eacute;rations','/holiday/index?recup=1','config=Conges-Enable;Conges-Recuperations'),
  ('15','20','Poser des cong&eacute;s','/holiday/new','config=Conges-Enable'),
  ('15','24','Poser des r&eacute;cup&eacute;rations','conges/recup_pose.php','config=Conges-Enable;Conges-Recuperations'),
  ('15','26','R&eacute;cup&eacute;rations','conges/recuperations.php','config=Conges-Enable'),
  ('15','30','Informations','conges/infos.php','config=Conges-Enable'),
  ('15','40','Cr&eacute;dits','conges/credits.php','config=Conges-Enable'),
  ('20','0','Agenda','agenda/index.php',NULL),
  ('30','0','Planning','planning/poste/index.php',NULL),
  ('30','90','Agents volants','planning/volants/index.php','config=Planning-agents-volants'),
  ('30','95','Semaines fixes','/statedweek', 'config=statedweek_enabled'),
  ('40','0','Statistiques','statistiques/index.php',NULL),
  ('40','10','Feuille de temps','statistiques/temps.php',NULL),
  ('40','20','Par agent','statistiques/agents.php',NULL),
  ('40','30','Par poste','statistiques/postes.php',NULL),
  ('40','40','Par poste (Synth&egrave;se)','statistiques/postes_synthese.php',NULL),
  ('40','50','Postes de renfort','statistiques/postes_renfort.php',NULL),
  ('40','24','Par service','statistiques/service.php',NULL),
  ('40','60','Samedis','statistiques/samedis.php',NULL),
  ('40','70','Absences','statistiques/absences.php',NULL),
  ('40','80','Pr&eacute;sents / absents','/statistics/attendeesmissing',NULL),
  ('40','26','Par statut','statistiques/statut.php',NULL),
  ('50','0','Administration','admin/index.php',NULL),
  ('50','10','Informations','/admin/info',NULL),
  ('50','20','Les activit&eacute;s','activites/index.php',NULL),
  ('50','30','Les agents','personnel/index.php',NULL),
  ('50','40','Les postes','postes/index.php',NULL),
  ('50','50','Les mod&egrave;les','planning/modeles/index.php',NULL),
  ('50','60','Les tableaux','planning/postes_cfg/index.php',NULL),
  ('50','70','Jours de fermeture','joursFeries/index.php',NULL),
  ('50','75','Plannings de pr&eacute;sence','planningHebdo/index.php','config=PlanningHebdo'),
  ('50','77','Notifications / Validations','notifications/index.php','config=Absences-notifications-agent-par-agent'),
  ('50','80','Configuration','/config',NULL),
  ('60','0','Aide','/help',NULL),
  (35, 0, 'Échanges de poste', '/interchange', 'config=statedweek_enabled'),
  (35, 5, 'Voir les échanges', '/interchange', 'config=statedweek_enabled');";
  (35, 10, 'Demande d\'échange', '/interchange/add', 'config=statedweek_enabled');";

//	Personnel
$sql[]="INSERT INTO `{$dbprefix}personnel` (`id`,`nom`,`postes`,`actif`,`droits`,`login`,`password`,`commentaires`) VALUES (1, 'Administrateur', '', 'Inactif', '[3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301]','admin', 
'5f4dcc3b5aa765d61d8327deb882cf99', 'Compte cr&eacute;&eacute; lors de l&apos;installation du planning');";
$sql[]="INSERT INTO `{$dbprefix}personnel` (`id`,`nom`,`postes`,`actif`,`droits`,`commentaires`,`temps`) VALUES (2, 'Tout le monde', '', 'Actif', '[99,100]','Compte cr&eacute;&eacute; lors de l&apos;installation du planning', '[[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"\",\"\",\"\",\"\"]]');";

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
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Non justifiée', '1', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Congés payés', '2', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Maladie', '3', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Congé maternité', '4', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Réunion syndicale', '5', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Grève', '6', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Formation', '7', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Concours', '8', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Stage', '9', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Réunion', '10', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Entretien', '11', 'A');";
$sql[]="INSERT INTO `{$dbprefix}select_abs` (`valeur`,`rang`, `notification_workflow`) VALUES ('Autre', '12', 'A');";

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
