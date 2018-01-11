<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ldap/authCAS.php
Création : 2 juillet 2014
Dernière modification : 14 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant l'authentification CAS
*/

include "class.ldap.php";

if(substr($config['Auth-Mode'],0,3)=="CAS" and !isset($_GET['noCAS'])){
  $_SESSION['oups']['Auth-Mode']="CAS";
  $loginCAS=authCAS();
  if($loginCAS){
    $_SESSION['login_id']=$loginCAS;
    echo "<script type='text/JavaScript'>document.form.login.value='$loginCAS';</script>\n";
    echo "<script type='text/JavaScript'>document.form.auth.value='CAS';</script>\n";
    echo "<script type='text/JavaScript'>document.form.submit();</script>\n";
  }
}
?>