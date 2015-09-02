<?php
/*
Planning Biblio, Version 1.8.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : plugins/plugins.php
Création : 26 juin 2013
Dernière modification : 18 juin 2014
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Liste les plugins installés, vérifie s'ils sont à jour.
Inclus dans le fichier index.php
*/

include_once "class.plugins.php";

// $plugins = liste des plugins installés, array("plugin1","plugin2")
global $plugins;
$p=new plugins();
$p->fetch();
$plugins=$p->liste;

// Vérifie si la base de données est à jour, la met à jour si besoin
$p->checkUpdateDB();
?>