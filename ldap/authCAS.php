<?php
/*
Planning Biblio, Version 1.8.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : ldap/authCAS.php
Création : 2 juillet 2014
Dernière modification : 2 juillet 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier permettant l'authentification CAS
*/

include "class.ldap.php";

if(substr($config['Auth-Mode'],0,3)=="CAS" and !isset($_GET['noCAS'])){
  $_SESSION['oups']['Auth-Mode']="CAS";
  $loginCAS=authCAS();
  if($loginCAS){
    echo "<script type='text/JavaScript'>document.form.login.value='$loginCAS';</script>\n";
    echo "<script type='text/JavaScript'>document.form.auth.value='CAS';</script>\n";
    echo "<script type='text/JavaScript'>document.form.submit();</script>\n";
  }
}
?>