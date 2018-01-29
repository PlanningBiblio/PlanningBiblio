<?php
/**
Planning Biblio, Version 2.7.12
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ldap/logoutCAS.php
Création : 2 juillet 2014
Dernière modification : 24 janvier 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
La femeture de session CAS
Fichier inclus dans la page authentification.php
*/
require_once "class.ldap.php";

if(substr($config['Auth-Mode'],0,3)=="CAS"){
  $authArgs=$_SESSION['oups']['Auth-Mode']=="CAS"?null:"?noCAS";
}

if(substr($config['Auth-Mode'],0,3)=="CAS" and $_SESSION['oups']['Auth-Mode']=="CAS"){
  session_destroy();
  echo "<script type='text/JavaScript'>location.href='https://{$config['CAS-Hostname']}:{$config['CAS-Port']}/{$config['CAS-URI-Logout']}';</script>";
  include "include/footer.php";
  exit;
}
?>