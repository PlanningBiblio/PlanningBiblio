<?php
/*
Planning Biblio, Version 1.5.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : planning/poste/validation.php
Création : mai 2011
Dernière modification : 19 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Contrôle en arrière plan la date et l'heure de validation du planning actuellement affiché afin de rafraichir la page si une 
modification a eue lieu à l'aide de la fonction JavaScript refresh_poste

Cette page est appelée par la fonction JavaScript refresh_poste
*/

require_once "class.planning.php";

// Les 3 # permettent d'isoler le texte pour la fonction JavaScript (Refresh_Poste)
$date=$_SESSION['PLdate'];
$site=$_SESSION['oups']['site'];
$db=new db();
$db->query("SELECT `validation2` FROM `{$dbprefix}pl_poste_verrou` WHERE `date`='$date' AND `site`='$site';");
echo "###{$db->result[0]['validation2']}###";
?>