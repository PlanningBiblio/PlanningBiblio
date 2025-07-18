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

// Insertion des droits d'accés
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Personnel - Password', 100, '', 'personnel/password.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`, `ordre`) VALUES ('Absences - Infos', 201, 'Gestion des absences, validation niveau 1', '', 'Absences', 30);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES (\"Enregistrement d'absences pour plusieurs agents\",'9',\"Enregistrement d'absences pour plusieurs agents\", 'Absences', '25');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Index', 4, 'Voir les fiches des agents', '', 'Agents', 60);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Postes et activités', 5, 'Gestion des postes', '','Postes',160);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques', 17, 'Accès aux statistiques', '','Statistiques','170');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Liste des agents présents et absents', 1301, 'Accès aux statistiques Présents / Absents', '','Statistiques','171');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Configuration avancée', 20, 'Configuration avancée', '');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Suppression', 21, 'Gestion des agents', 'personnel/suppression.php', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Personnel - Valid', 21, 'Gestion des agents', '', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Gestion du personnel', 21, 'Gestion des agents', '', 'Agents', 70);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des horaires des tableaux', 22, 'Configuration des tableaux', '','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des horaires des tableaux', 22, 'Configuration des tableaux', '','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des lignes des tableaux', 22, 'Configuration des tableaux', 'planning/postes_cfg/lignes.php','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (id, nom, groupe_id, groupe, page, ordre, categorie) VALUES (NULL, 'Afficher les informations', 23, 'Informations', '', 0, 'Informations');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Configuration des tableaux - Modif',22,'Configuration des tableaux','','Planning',140);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Modification des plannings - menudiv','1001','Modification des plannings','planning/poste/menudiv.php','Planning','120');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Modification des plannings - majdb','1001','Modification des plannings','planning/poste/majdb.php','Planning','120');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Jours fériés','25','Gestion des jours fériés','');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Voir les agendas de tous','3','Voir les agendas de tous','', 'Agendas', 55);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Modifier ses propres absences','6','Modifier ses propres absences','','Absences',20);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Gestion des absences, validation niveau 2', 501, 'Gestion des absences, validation niveau 2', 'Absences', 40);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Gestion des absences, pièces justificatives', 701, 'Gestion des absences, pièces justificatives', 'Absences', 50);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Hebdo - Admin N1','1101','Gestion des heures de présence, validation niveau 1','','Heures de présence','90');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Hebdo - Admin N2','1201','Gestion des heures de présence, validation niveau 2','','Heures de présence','90');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Modification des commentaires des plannings','801','Modification des commentaires des plannings', 'Planning', 130);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Griser les cellules des plannings','901','Griser les cellules des plannings','Planning','125');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES ('Congés - Index','100','conges/index.php');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Poste', 301, 'Création / modification des plannings, utilisation et gestion des modèles', '', 'Planning', 110);";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe`,`groupe_id`,`categorie`,`ordre`) VALUES ('Gestion des congés, validation niveau 2','Gestion des congés, validation niveau 2',601,'Congés','76');";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES ('Gestion des congés, validation niveau 1','401','Gestion des congés, validation niveau 1','Congés','75');";

//	Insertion des activités
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('1', 'Assistance audiovisuel');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('2', 'Assistance autoformation');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('3', 'Communication');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('4', 'Communication réserve');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('5', 'Inscription');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('6', 'Prêt/retour de document');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('7', 'Prêt de matériel');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('8', 'Rangement');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('9', 'Renseignement');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('10', 'Renseignement bibliographique');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('11', 'Renseignement réserve');";
$sql[]="INSERT INTO `{$dbprefix}activites` (`id`, `nom`) VALUES ('12', 'Renseignement spécialisé');";

// Insertion de la config
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Version', 'info', '25.05.02', 'Version de l\'application',' Divers','','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('URL', 'info', '', 'URL de l\'application',' Divers','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('toutlemonde', 'boolean', '0', 'Affiche ou non l\'utilisateur \"tout le monde\" dans le menu.','Planning','','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-IsEnabled', 'boolean', '0', 'Active ou désactive l\'envoi des e-mails.','Messagerie', '', '10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Mail-IsMail-IsSMTP', 'enum', 'IsSMTP', 'Utiliser un relais SMTP (IsSMTP) ou le programme \"mail\" du serveur (IsMail).', 'Messagerie', 'IsSMTP,IsMail', 'onchange=\'mail_config();\'', '20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Hostname', '', '', 'Nom d\'hôte du serveur pour l\'envoi des e-mails.','Messagerie', '', '30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Host', '', '', 'Nom FQDN ou IP du serveur SMTP.', 'Messagerie', '', '40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Port', '', '25', 'Port du serveur SMTP', 'Messagerie', '', '50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-SMTPSecure', 'enum', '', 'Cryptage utilisé par le serveur STMP.', 'Messagerie', ',ssl,tls', '60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-SMTPAuth', 'boolean', '0', 'Le serveur SMTP requiert-il une authentification ?', 'Messagerie', '', '70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Username', '', '', 'Nom d\'utilisateur pour le serveur SMTP.', 'Messagerie', '', '80');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Password', 'password', '', 'Mot de passe pour le serveur SMTP.', 'Messagerie', '', '90');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-From', '', 'no-reply@planno.fr', 'Adresse e-mail de l\'expediteur.', 'Messagerie', '', '100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-FromName', '', 'Planno', 'Nom de l\'expediteur.', 'Messagerie', '', '110');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-Signature', 'textarea', 'Ce message a été envoyé par Planno.\nMerci de ne pas y répondre.', 'Signature des e-mails.', 'Messagerie', '', '130');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) VALUES ('Mail-Planning','textarea','Adresses e-mails de la cellule planning, séparées par des ;','Messagerie','140');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Dimanche', 'boolean', '0', 'Utiliser le planning le dimanche',' Divers','','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('nb_semaine','enum','1','Nombre de semaine pour la rotation des heures de présence. Les valeurs supérieures à 3 ne peuvent être utilisées que si le paramètre PlanningHebdo est coché','Heures de présence','1,2,3,4,5,6,7,8,9,10','0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `ordre`, `commentaires`, `categorie`) VALUES ('dateDebutPlHebdo','date','0','Date de début permettant la rotation des heures de présence (pour l\'utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)','Heures de présence');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Planning-IgnoreBreaks', 'boolean', '', '0', 'Planning','0', 'Si cette case est cochée, les périodes de pauses (ex: pause déjeuner) définies dans les heures de présence seront ignorées dans le menu permettant d\'ajouter les agents dans le planning et lors de l\'importation des modèles.');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('ctrlHresAgents','boolean','1','Contrôle des heures des agents le samedi et le dimanche','Planning','','1');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('agentsIndispo','boolean','1','Afficher les agents indisponibles','Planning','','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (nom,type,valeur,valeurs,commentaires,categorie,`ordre`) VALUES ('Granularite','enum2','1','[[1, \"Libre\"],[60,\"Heure\"],[30,\"Demi-heure\"],[15,\"Quart d\'heure\"],[5,\"5 minutes\"]]','Granularité des champs horaires.',' Divers',30);";
$sql[]="INSERT INTO `{$dbprefix}config` (nom,type,valeur,valeurs,commentaires,categorie,ordre) VALUES ('Absences-planning','enum2','','[[0,\"\"],[1,\"simple\"],[2,\"détaillé\"],[3,\"absents et présents\"],[4,\"absents et présents filtrés par site\"]]','Choix des listes de présence et d\'absences à afficher sous les plannings','Absences','25');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Auth-Mode','enum','SQL','Méthode d\'authentification','Authentification','SQL,LDAP,LDAP-SQL,CAS,CAS-SQL,OpenIDConnect','5');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Auth-LoginLayout', 'enum', 'firstname.lastname', 'Schéma à utiliser pour la construction des logins', 'Authentification', 'firstname.lastname,lastname.firstname,mail,mailPrefix', NULL, 10);";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Auth-PasswordLength', 'text', '8', 'Nombre minimum de caractères obligatoires pour le changement de mot de passe.','Authentification', '','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Absences-apresValidation','boolean','1','Autoriser l\'enregistrement d\'absences après validation des plannings','Absences','','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Absences-planningVide','boolean','1','Absences', 'Autoriser l\'enregistrement d\'absences sur des plannings en cours d\'élaboration','8');";
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
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('EDTSamedi', 'enum2', '[[0, \"Désactivé\"], [1, \"Horaires différents les semaines avec samedi travaillé\"], [2, \"Horaires différents les semaines avec samedi travaillé et les semaines à ouverture restreinte\"]]', '0', 'Horaires différents les semaines avec samedi travaillé et semaines à ouverture restreinte. Ce paramètre est ignoré si PlanningHebdo est activé.', 'Heures de présence', '0');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('ClasseParService', 'boolean', '1', 'Classer les agents par service dans le menu d&eacute;roulant du planning','Planning','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Alerte2SP', 'boolean', '0', 'Alerter si l&apos;agent fera 2 plages de service public de suite','Planning','5');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('CatAFinDeService', 'boolean', '0', 'Alerter si aucun agent de catégorie A n\'est placé en fin de service','Planning','2');";

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Recuperations', 'enum2', '0', '[[0,\"Assembler\"],[1,\"Dissocier\"]]', 'Congés', 'Traiter les r&eacute;cup&eacute;rations comme les cong&eacute;s (Assembler), ou les traiter s&eacute;par&eacute;ment (Dissocier)', '3');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Conges-tous', 'boolean', '0', 'Autoriser l\'enregistrement de congés pour tous les agents en une fois','Congés','6');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Conges-Heures','enum2','0','Permettre la saisie de congés sur quelques heures ou forcer la saisie de congés sur des journées complètes. Paramètre actif avec les options Conges-Mode=Heures et Conges-Recuperations=Dissocier', 'Congés', '[[0,\"Forcer la saisie de congés sur journées entières\"],[1,\"Permettre la saisie de congés sur quelques heures\"]]', '3');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Recup-Agent','enum2','[[0,\"\"],[1,\"Texte\"],[2,\"Menu déroulant\"]]','Texte','Type de champ pour la r&eacute;cup&eacute;ration des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu d&eacute;roulant</b>','Congés','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-SamediSeulement','boolean','0','Autoriser les demandes de récupération des samedis seulement','Congés','','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-Uneparjour','boolean','1','Autoriser une seule demande de r&eacute;cup&eacute;ration par jour','Congés','','19');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-DeuxSamedis','boolean','0','Autoriser les demandes de récupération pour 2 samedis','Congés','','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-DelaiDefaut','text','7','Delai pour les demandes de récupération par d&eacute;faut (en jours)','Congés','','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-DelaiTitulaire1','enum2','0','Delai pour les demandes de récupération des titulaires pour 1 samedi (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-DelaiTitulaire2','enum2','0','Delai pour les demandes de récupération des titulaires pour 2 samedis (en mois)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-DelaiContractuel1','enum2','0','Delai pour les demandes de récupération des contractuels pour 1 samedi (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Recup-DelaiContractuel2','enum2','0','Delai pour les demandes de récupération des contractuels pour 2 samedis (en semaines)','Congés','[[-1,\"Défaut\"],[0,0],[1,1],[2,2],[3,3],[4,4],[5,5]]','80');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications1','checkboxes','[2]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications de nouvelles demandes de crédit de récupérations','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications2','checkboxes','[2]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications de modification de crédit de récupérations','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications3','checkboxes','[1]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications des validations de crédit de récupérations niveau 1','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Recup-notifications4','checkboxes','[3]','[[0,\"Agents ayant le droit de gérer les récupérations\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concerné\"]]','Destinataires des notifications des validations de crédit de récupérations niveau 2','Congés','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-planningVide','boolean','1','Congés', 'Autoriser l\'enregistrement de congés sur des plannings en cours d\'élaboration','11');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-apresValidation','boolean','1','Congés', 'Autoriser l\'enregistrement de congés après validation des plannings','12');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels', 'boolean', '0', 'Congés', 'Activer / D&eacute;sactiver l&apos;envoi de rappels s&apos;il y a des cong&eacute;s non-valid&eacute;s', '6');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-Jours', 'text', '14', 'Congés', 'Nombre de jours &agrave; contr&ocirc;ler pour l&apos;envoi de rappels sur les cong&eacute;s non-valid&eacute;s', '7');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre` ) VALUES ('Conges-demi-journees','boolean','0','Autorise la saisie de congés en demi-journée. Fonctionne uniquement avec le mode de saisie en jour','Congés','','8');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES ('Conges-fullday-switching-time', 'text', '4', '', 'Congés', '9', 'Temps définissant la bascule entre une demi-journée et une journée complète lorsque les crédits de congés sont comptés en jours. Format : entier ou décimal. Exemple : pour 3h30, tapez 3.5');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Conges-fullday-reference-time','text','','Temps de référence (en heures) pour une journée complète. Si ce champ est renseigné et que les crédits de congés sont gérés en jours, la différence de temps de chaque journée sera créditée ou débitée du solde des récupérations. Format : entier ou décimal. Exemple : pour 7h30, tapez 7.5', 'Congés', '', '10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N1', 'checkboxes', '[\"Mail-Planning\"]',
  '[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Congés', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 1', '14');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Rappels-N2', 'checkboxes', '[\"mails_responsables\"]',
'[[\"Mail-Planning\",\"La cellule planning\"],[\"mails_responsables\",\"Les responsables hi&eacute;rarchiques\"]]','Congés', 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 2', '15');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre`) VALUES ('Conges-validation','boolean','1', 'Congés', 'Les congés doivent être validés par un administrateur avant d\'être pris en compte','4');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le cong&eacute; doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Congés', 'La validation niveau 2 des cong&eacute;s peut se faire directement ou doit attendre la validation niveau 1', '5');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Absences-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"L\'absence doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Absences', 'La validation niveau 2 des absences peut se faire directement ou doit attendre la validation niveau 1', '31');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Enable', 'boolean', '0', '', 'Congés', 'Activer le module Congés', '1');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Mode', 'enum2', 'heures', '[[\"heures\",\"Heures\"],[\"jours\",\"Jours\"]]', 'Congés', 'Décompte des congés en heures ou en jours', '2');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` ( `nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Conges-transfer-comp-time', 'boolean', '0', 'Transférer les récupérations restantes sur le reliquat', 'Congés', '', NULL, '16');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-validation','boolean','0','Les absences doivent &ecirc;tre valid&eacute;es par un administrateur avant d&apos;&ecirc;tre prises en compte','Absences','30');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Absences-blocage', 'boolean', '0', 'Permettre le blocage des absences et congés sur une période définie par les gestionnaires. Ce paramètre empêchera les agents qui n\'ont pas le droits de gérer les absences d\'enregistrer absences et congés sur les périodes définies. En configuration multi-sites, les agents de tous les sites seront bloqués sans distinction.', 'Absences', '', NULL, 5);";
// Affichage absences non validées
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Absences-non-validees','boolean','1','Absences', 'Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e','35');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Absences-agent-preselection', 'boolean', '1', 'Présélectionner l&apos;agent connecté lors de l&apos;ajout d&apos;une nouvelle absence.','Absences','36');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES ('Absences-journeeEntiere', 'boolean', '1', '', 'Absences','38', 'Le paramètre \"Journée(s) entière(s)\" est coché par défaut lors de la saisie d\'une absence.');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Absences-tous', 'boolean', '0', 'Autoriser l&apos;enregistrement d&apos;absences pour tous les agents en une fois','Absences','37');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Absences-adminSeulement','boolean','0','Autoriser la saisie des absences aux administrateurs seulement.','Absences','20');";
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
  VALUES ('Absences-notifications-A4','checkboxes','[3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2 (Circuit A)','Absences','70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('Absences-notifications-agent-par-agent','boolean', '0', 'Absences', 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres Absences-notifications-A1, A2, A3 et A4 ou B1, B2, B3 et B4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','120');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B1','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de nouvelles absences (Circuit B)','Absences','80');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B2','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification d&apos;absences (Circuit B)','Absences','90');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B3','checkboxes','[1]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1 (Circuit B)','Absences','100');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B4','checkboxes','[3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2 (Circuit B)','Absences','110');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Absences-notifications-titre','text','Titre personnalis&eacute; pour les notifications de nouvelles absences','Absences','130'),
  ('Absences-notifications-message','textarea','Message personnalis&eacute; pour les notifications de nouvelles absences','Absences','140');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Absences-DelaiSuppressionDocuments', 'text', '365', 'Absences','150', 'Les documents associ&eacute;s aux absences sont supprim&eacute;s au-del&agrave; du nombre de jours d&eacute;finis par ce param&egrave;tre.');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Absences-Exclusion', 'enum2', '[[0, \"Les agents ayant une absence validée sont exclus des plannings.\"],[1,\"Les agents ayant des absences importées validées peuvent être ajoutés au planning.\"],[2,\"Les agents ayant des absences validées, importées ou non, peuvent être ajoutés au planning.\"]]', '0', 'Absences','160', 'Autoriser l\'affectation au planning des agents absents.');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`)
  VALUES ('Statistiques-Heures', 'textarea', 'Afficher des statistiques sur les cr&eacute;neaux horaires voulus. Les cr&eacute;neaux doivent &ecirc;tre au format 00h00-00h00 et s&eacute;par&eacute;s par des ; Exemple : 19h00-20h00; 20h00-21h00; 21h00-22h00','Statistiques','10');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Affichage-theme','text','default','Affichage',10,'Th&egrave;me de l&apos;application.');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `commentaires`, `categorie`, `ordre`) VALUES ('Affichage-titre','text','Titre affich&eacute; sur la page d&apos;accueil','Affichage','20');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Affichage-etages','boolean','0','Afficher les &eacute;tages des postes dans le planning','Affichage','','30');";
$sql[]="INSERT IGNORE INTO `{$dbprefix}config` ( `nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Affichage-Agent', 'color', '#FFF3B3', 'Couleur des cellules de l\'agent connecté', 'Affichage', '', NULL, '40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`type`,`nom`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES
  ('enum','Planning-NbAgentsCellule','1,2,3,4,5,6,7,8,10,11,12,13,14,15,16,17,18,19,20','2','Nombre maximum d\'agents par cellule','Planning','3');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeur`,`categorie`,`ordre`,`commentaires`) VALUES ('Planning-lignesVides','boolean','1','Planning',4,'Afficher ou non les lignes vides dans les plannings validés');";
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

// Planook configuration is made to hide some information in order to propose a light version of Planno
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Planook', 'hidden', '0', 'Version Lite Planook',' Divers','','0');";

//	Planning Hebdo
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('PlanningHebdo', 'boolean', '0', 'Heures de présence','40', 'Utiliser le module \“Planning Hebdo\”. Ce module permet d\'enregistrer plusieurs horaires de présence par agent en définissant des périodes d\'utilisation. (Incompatible avec l\'option EDTSamedi)');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('PlanningHebdo-Agents', 'boolean', '1', 'Heures de présence','50', 'Autoriser les agents à saisir leurs heures de présence (avec le module Planning Hebdo). Les heures saisies devront être validées par un administrateur');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('PlanningHebdo-Pause2', 'boolean', '0', '2 pauses dans une journ&eacute;e', 'Heures de présence', 60);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('PlanningHebdo-PauseLibre', 'boolean', '0', 'Ajoute la possibilité de saisir un temps de pause libre dans les heures de présence (Module Planning Hebdo uniquement)', 'Heures de présence', 65);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES
  ('PlanningHebdo-DebutPauseLibre', 'enum2', '12:00:00',
  '[[\"11:00:00\",\"11h00\"],[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"]]',
  'Heures de présence','66', 'Début de période de pause libre');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES
  ('PlanningHebdo-FinPauseLibre', 'enum2', '14:00:00',
  '[[\"11:15:00\",\"11h15\"],[\"11:30:00\",\"11h30\"],[\"11:45:00\",\"11h45\"],[\"12:00:00\",\"12h00\"],[\"12:15:00\",\"12h15\"],[\"12:30:00\",\"12h30\"],[\"12:45:00\",\"12h45\"],[\"13:00:00\",\"13h00\"],[\"13:15:00\",\"13h15\"],[\"13:30:00\",\"13h30\"],[\"13:45:00\",\"13h45\"],[\"14:00:00\",\"14h00\"],[\"14:15:00\",\"14h15\"],[\"14:30:00\",\"14h30\"],[\"14:45:00\",\"14h45\"],[\"15:00:00\",\"15h00\"]]',
  'Heures de présence','67', 'Fin de période de pause libre');";


// Configuration : notifications
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('PlanningHebdo-notifications1','checkboxes','[0,4]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications d\'enregistrement de nouvelles heures de présence','Heures de présence','70');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('PlanningHebdo-notifications2','checkboxes','[0,4]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification des heures de présence','Heures de présence','72');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('PlanningHebdo-notifications3','checkboxes','[1]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1','Heures de présence','74');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`)
  VALUES ('PlanningHebdo-notifications4','checkboxes','[4]','[[0,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1\"],[1,\"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2\"],[2,\"Responsables directs\"],[3,\"Cellule planning\"],[4,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2','Heures de présence','76');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('PlanningHebdo-notifications-agent-par-agent','boolean', '0', 'Heures de présence', 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres PlanningHebdo-notifications1, 2, 3 et 4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations','80');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('PlanningHebdo-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"Le planning doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Heures de présence', 'La validation niveau 2 des heures de présence peut se faire directement ou doit attendre la validation niveau 1', '85');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('Planning-InitialNotification', 'enum2', '[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]', '-2', 'Planning','40', 'Envoyer une notification aux agents lors de la validation des plannings les concernant');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES
  ('Planning-ChangeNotification', 'enum2', '[[-2,\"Désactivé\"],[-1,\"Tous les plannings\"],[0,\"Planning du jour\"],[1,\"Jour à J+1\"],[2,\"Jour à J+2\"],[3,\"Jour à J+3\"],[4,\"Jour à J+4\"],[5,\"Jour à J+5\"],[6,\"Jour à J+6\"],[7,\"Jour à J+7\"],[8,\"Jour à J+8\"],[9,\"Jour à J+9\"],[10,\"Jour à J+10\"],[11,\"Jour à J+11\"],[12,\"Jour à J+12\"],[13,\"Jour à J+13\"],[14,\"Jour à J+14\"],[15,\"Jour à J+15\"]]', '-2', 'Planning','41', 'Envoyer une notification aux agents lors d\'une modification de planning les concernant');";
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
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-ID-Attribute', 'enum', 'uid', 'uid,samaccountname,supannaliaslogin', 'Attribut d&apos;authentification (OpenLDAP : uid, Active Directory : samaccountname)', 'LDAP', 80);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDAP-Matricule', 'text', '', '', 'Attribut &agrave; importer dans le champ matricule (optionnel)', 'LDAP', 90);";

// Ajout des infos LDIF dans la table config
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-File', 'text', '', '', 'Emplacement d\'un fichier LDIF pour l\'importation des agents', 'LDIF', 10);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-ID-Attribute', 'enum', 'uid', 'uid,samaccountname,supannaliaslogin,employeenumber', 'Attribut d\'authentification (OpenLDAP : uid, Active Directory : samaccountname)', 'LDIF', 20);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-Matricule', 'text', '', '', 'Attribut à importer dans le champ matricule (optionnel)', 'LDIF', 30);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('LDIF-Encoding', 'enum', 'UTF-8', 'UTF-8,ISO-8859-1', 'Encodage de caractères du fichier source', 'LDIF', 40);";

//	Ajout des infos CAS dans la table config
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Hostname','Nom d&apos;h&ocirc;te du serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Port','8080','Port serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`type`,`valeurs`,`valeur`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-Version','enum','2.0,3.0,4.0','2.0','Version du serveur CAS','CAS','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`,`commentaires`,`categorie`,`ordre`) VALUES ('CAS-CACert','Chemin absolut du certificat de l&apos;Autorit&eacute; de Certification. Si pas renseign&eacute;, l&apos;identit&eacute; du serveur ne sera pas v&eacute;rifi&eacute;e.','CAS','30');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES
  ('CAS-SSLVersion', 'enum2', '1', '[[1,\"TLSv1\"],[4,\"TLSv1_0\"],[5,\"TLSv1_1\"],[6,\"TLSv1_2\"]]', 'CAS','45', 'Version SSL/TLS &agrave; utiliser pour les &eacute;changes avec le serveur CAS');";

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `ordre`, `commentaires`) VALUES
  ('CAS-LoginAttribute', 'text', 'CAS','48', 'Attribut CAS à utiliser pour mapper l\'utilisateur si et seulement si l\'UID CAS ne convient pas. Laisser ce champ vide par défaut. Exemple : \"mail\", dans ce cas, l\'adresse mail de l\'utilisateur est fournie par le serveur CAS et elle est renseignée dans le champ \"login\" des fiches agents de Planno.');";

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
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('ICS-Description1','boolean','1','ICS', 'Inclure la description de l\'événement importé dans le commentaire de l\'absence','23');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Server2','text','ICS', 'URL du 2<sup>&egrave;me</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics','30');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Pattern2','text','ICS', 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 2<sup>&egrave;me</sup> serveur. Ex: Congés','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Status2','enum2', 'CONFIRMED', '[[\"CONFIRMED\",\"Confirm&eacute;s\"],[\"ALL\",\"Tous\"]]', 'ICS', 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si \"tous\" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation','42');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('ICS-Description2','boolean','1','ICS', 'Inclure la description de l\'événement importé dans le commentaire de l\'absence','43');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES
  ('ICS-Server3','boolean','0','ICS', 'Utiliser une URL d&eacute;finie pour chaque agent dans le menu Administration / Les agents','44');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('ICS-Description3','boolean','1','ICS', 'Inclure la description de l\'événement importé dans le commentaire de l\'absence','48');";
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
  ('Hamac-id','enum2', 'login', '[[\"login\",\"Login\"],[\"matricule\",\"Matricule\"]]', 'Hamac', 'Champ Planno à utiliser pour mapper les agents.','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Hamac-debug','boolean', '0', '', 'Hamac', 'Active le mode débugage pour l\'importation des absences depuis Hamac. Les informations de débugage sont écrites dans la table \"log\". Attention, si cette option est activée, la taille de la base de données augmente considérablement.','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-between-sites', 'text', '0', 'Temps de trajet moyen entre sites (en minutes)', 'Planning', 95);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-between-areas', 'text', '0', 'Temps de trajet moyen entre zones (en minutes)', 'Planning', 96);";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Journey-time-for-absences', 'text', '0', 'Temps de trajet moyen entre une absence et un poste de service public (en minutes)', 'Planning', 97);";

// Mentions légales
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('legalNotices', 'textarea', '', 'Mentions légales (exemple : notice RGPD). La syntaxe markdown peut être utilisée pour la saisie.', 'Mentions légales', 10);";

// Add OpenID Connect params
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-Provider', 'text', '', 'OpenID Connect Provider.', 'OpenID Connect', '', 1, 10);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-CACert', 'text', '', 'Path to the OpenID Connect CA Certificate.', 'OpenID Connect', '', 1, 20);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-ClientID', 'text', '', 'OpenID Connect Client ID (not to be confused with Secret ID).', 'OpenID Connect', '', 1, 30);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-ClientSecret', 'text', '', 'OpenID Connect Secret Value (not to be confused with Secret ID).', 'OpenID Connect', '', 1, 40);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('OIDC-LoginAttribute', 'text', '', 'OpenID Connect Login Attribute.', 'OpenID Connect', '', 1, 50);";

// Add MS Graph params
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-TenantID', 'text', '', 'MS Graph Tenant ID.', 'Microsoft Graph API', '', 1, 10);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-ClientID', 'text', '', 'MS Graph Client ID (not to be confused with Secret ID).', 'Microsoft Graph API', '', 1, 20);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-ClientSecret', 'text', '', 'MS Graph Secret Value (not to be confused with Secret ID).', 'Microsoft Graph API', '', 1, 30);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-LoginSuffix', 'text', '', 'Suffix that must be added to the Planno login to link with the MS login. Optional, empty by default.', 'Microsoft Graph API', '', 1, 40);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-IgnoredStatuses', 'text', 'free;tentative', 'List of statuses to ignore, separated by semicolons. Optional, \"free;tentative\" by default.', 'Microsoft Graph API', '', 1, 50);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `technical`, `ordre`) VALUES ('MSGraph-AbsenceReason', 'text', 'Office 365', 'Absence Reason to use for imported events. Optional, \"Outlook\" by default.', 'Microsoft Graph API', '', 1, 60);";

// Cron
$sql[]="INSERT INTO `{$dbprefix}cron` (`h`,`m`,`dom`,`mon`,`dow`,`command`,`comments`) VALUES ('0','0','*','*','*','cron.planning_hebdo_daily.php','Daily Cron for Planning Hebdo module');";
$sql[]="INSERT INTO `{$dbprefix}cron` (m,h,dom,mon,dow,command,comments) VALUES (0,0,1,1,'*','cron.holiday_reset_remainder.php','Reset holiday remainders');";
$sql[]="INSERT INTO `{$dbprefix}cron` (m,h,dom,mon,dow,command,comments) VALUES (0,0,1,9,'*','cron.holiday_reset_credits.php','Reset holiday credits');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}cron` (`m`, `h`, `dom`, `mon`, `dow`, `command`, `comments`) VALUES ( '0', '0', '1', '9', '*', 'cron.holiday_reset_comp_time.php', 'Reset holiday compensatory time');";

//	Lignes de séparations
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Magasins');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Mezzanine');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Rez de chaussée');";
$sql[]="INSERT INTO `{$dbprefix}lignes` (`nom`) VALUES ('Rez de jardin');";

// Menu
$sql[]="INSERT INTO `{$dbprefix}menu` (`niveau1`,`niveau2`,`titre`,`url`,`condition`) VALUES
  ('10','0','Absences','/absence',NULL),
  ('10','10','Voir les absences','/absence',NULL),
  ('10','20','Ajouter une absence','/absence/add',NULL),
  ('10','25','Bloquer les absences','/absence/block','config=Absences-blocage'),
  ('10','30','Informations','/absences/info', 'config!=Plannok'),
  ('15','0','Congés','/holiday/index','config=Conges-Enable'),
  ('15','10','Liste des cong&eacute;s','/holiday/index','config=Conges-Enable'),
  ('15','15','Liste des r&eacute;cup&eacute;rations','/holiday/index?recup=1','config=Conges-Enable;Conges-Recuperations'),
  ('15','20','Poser des cong&eacute;s','/holiday/new','config=Conges-Enable'),
  ('15','24','Poser des r&eacute;cup&eacute;rations','/comptime/add','config=Conges-Enable;Conges-Recuperations'),
  ('15','26','Heures supplémentaires','/overtime','config=Conges-Enable'),
  ('15','30','Informations','/holiday-info','config=Conges-Enable'),
  ('15','40','Crédits','/holiday/accounts','config=Conges-Enable'),
  ('20','0','Agenda','/calendar',NULL),
  ('30','0','Planning','/',NULL),
  ('30','90','Agents volants','/detached','config=Planning-agents-volants'),
  ('40','0','Statistiques','/statistics', 'config!=Planook'),
  ('40','10','Feuille de temps','/statistics/time', 'config!=Planook'),
  ('40','20','Par agent','/statistics/agent', 'config!=Planook'),
  ('40','30','Par poste','/statistics/position', 'config!=Planook'),
  ('40','40','Par poste (Synth&egrave;se)','/statistics/positionsummary', 'config!=Planook'),
  ('40','50','Postes de renfort','/statistics/supportposition', 'config!=Planook'),
  ('40','24','Par service','/statistics/service', 'config!=Planook'),
  ('40','60','Samedis','/statistics/saturday', 'config!=Planook'),
  ('40','70','Absences','/statistics/absence', 'config!=Planook'),
  ('40','80','Présents / absents','/statistics/attendeesmissing', 'config!=Planook'),
  ('40','26','Par statut','/statistics/status', 'config!=Planook'),
  ('50','0','Administration','/admin',NULL),
  ('50','10','Informations','/admin/info', 'config!=Planook'),
  ('50','20','Les activités','/skill', 'config!=Planook'),
  ('50','30','Les agents','/agent',NULL),
  ('50','40','Les postes','/position',NULL),
  ('50','50','Les mod&egrave;les','/model',NULL),
  ('50','60','Les tableaux','/framework',NULL),
  ('50','70','Jours de fermeture','/closingday', 'config!=Planook&config=Conges-Enable'),
  ('50','75','Heures de présence','/workinghour','config=PlanningHebdo'),
  ('50','77','Notifications / Validations','/notification','config=Absences-notifications-agent-par-agent'),
  ('50','80','Configuration fonctionnelle','/config',NULL),
  ('50','90','Configuration technique','/config/technical',NULL),
  ('60','0','Aide','/help',NULL);";

//	Personnel
$sql[]="INSERT INTO `{$dbprefix}personnel` (`id`,`nom`,`postes`,`actif`,`droits`,`login`,`password`,`commentaires`) VALUES (1, 'Administrateur', '', 'Inactif', '[3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301]','admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'Compte créé lors de l\'installation du planning');";
$sql[]="INSERT INTO `{$dbprefix}personnel` (`id`,`nom`,`postes`,`actif`,`droits`,`commentaires`,`temps`) VALUES (2, 'Tout le monde', '', 'Actif', '[99,100]','Compte créé lors de l\'installation du planning', '[[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"09:00:00\",\"12:00:00\",\"13:00:00\",\"17:00:00\"],[\"\",\"\",\"\",\"\"]]');";

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
$sql[]="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`, `tableau`, `ligne`, `poste`, `type`) VALUES ('1', '2', '0', 'Réserve', 'titre');";
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
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('4', 'Inscription 1', '', '0', 'Obligatoire', '2', '[5,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('5', 'Retour', '', '0', 'Obligatoire', '2', '[6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('6', 'Prêt / retour 1', '', '0', 'Obligatoire', '2', '[7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('7', 'Prêt / retour 2', '', '0', 'Renfort', '2', '[7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('8', 'Prêt / retour 3', '', '0', 'Renfort', '2', '[5,7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('9', 'Prêt / retour 4', '', '0', 'Renfort', '2', '[7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('10', 'Inscription 2', '', '0', 'Renfort', '2', '[5]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('11', 'Communication RDC', '', '0', 'Renfort', '2', '[3,7,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('12', 'Renseignement RDC', '', '0', 'Obligatoire', '2', '[9,10]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('13', 'Renseignement spécialisé 1', '', '0', 'Obligatoire', '3', '[9,10,12]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('14', 'Renseignement spécialisé 2', '', '0', 'Renfort', '3', '[9,10,12]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('15', 'Renseignement spécialisé 3', '', '0', 'Renfort', '3', '[9,10,12]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('16', 'Communication (banque 1)', '', '0', 'Obligatoire', '3', '[3,7,6,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('17', 'Communication (banque 2)', '', '0', 'Renfort', '3', '[3,9,10]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('19', 'Communication (coordination)', '', '0', 'Obligatoire', '3', '[3]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('20', 'Communication (magasin 1)', '', '0', 'Obligatoire', '3', '[3]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('21', 'Communication (magasin 2)', '', '0', 'Obligatoire', '3', '[11]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('22', 'Communication (magasin 3)', '', '0', 'Renfort', '3', '[3]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('23', 'Consultation de la réserve', '', '0', 'Obligatoire', '3', '[4,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('24', 'Audiovisuel et autoformation', '', '0', 'Obligatoire', '1', '[1,2,7,9]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('25', 'Rangement 2', '', '0', 'Obligatoire', '2', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('26', 'Rangement 3', '', '0', 'Obligatoire', '2', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('27', 'Rangement 4', '', '0', 'Renfort', '2', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('28', 'Rangement 1', '', '0', 'Obligatoire', '1', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('29', 'Rangement 5', '', '0', 'Obligatoire', '3', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('30', 'Rangement 6', '', '0', 'Obligatoire', '3', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('31', 'Rangement 7', '', '0', 'Renfort', '3', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('32', 'Rangement 8', '', '0', 'Renfort', '3', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('33', 'Rangement 9', '', '0', 'Renfort', '3', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('34', 'Rangement 10', '', '0', 'Obligatoire', '4', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('35', 'Rangement 11', '', '0', 'Obligatoire', '4', '[8]','1','1');";
$sql[]=" INSERT INTO `{$dbprefix}postes` (`id`, `nom`, `groupe`, `groupe_id`, `obligatoire`, `etage`, `activites`, `statistiques`, `bloquant`) VALUES ('36', 'Renseignement kiosque', '', '0', 'Renfort', '1', '[9,10]','1','1');";

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
