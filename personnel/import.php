<?php
/*
Planning Biblio, Version 1.5.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : personnel/index.php
Création : 27 juin 2013
Dernière modification : 4 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier permettant l'importation des agents dans le planning
*/

if(in_array('ldap',$plugins)){
  include "plugins/ldap/import.php";
}
?>
