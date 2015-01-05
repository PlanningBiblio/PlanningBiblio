<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.refresh.php
Création : mai 2011
Dernière modification : 5 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Contrôle en arrière plan la date et l'heure de validation du planning actuellement affiché afin de rafraichir la page si une 
modification a eue lieu à l'aide de la fonction JavaScript refresh_poste

Cette page est appelée par la fonction JavaScript refresh_poste
*/

session_start();
require_once "../../include/config.php";
require_once "class.planning.php";

$date=$_SESSION['PLdate'];
$site=$_SESSION['oups']['site'];
$db=new db();
$db->query("SELECT `validation2` FROM `{$dbprefix}pl_poste_verrou` WHERE `date`='$date' AND `site`='$site';");
echo $db->result[0]['validation2'];
?>