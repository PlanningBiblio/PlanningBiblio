<?php
/*
Planning Biblio, Version 1.8.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : personnel/index.php
Création : 27 juin 2013
Dernière modification : 2 juillet 2014
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant l'importation des agents dans le planning
*/

if(isset($_POST['import-type'])){
  if($_POST['import-type']=="ldap"){
    include "ldap/import2.php";
  }
}
else{
  include "ldap/import.php";
}
?>