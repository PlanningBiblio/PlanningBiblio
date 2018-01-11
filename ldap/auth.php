<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ldap/auth.php
Création : 2 juillet 2014
Dernière modification : 14 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant l'authentification LDAP
*/

include_once "class.ldap.php";
if(substr($config['Auth-Mode'],0,3)=="CAS"){
  $authArgs=null;
  if(array_key_exists("oups",$_SESSION) and array_key_exists("Auth-Mode",$_SESSION['oups']) and $_SESSION['oups']['Auth-Mode']=="CAS"){
    $authArgs="?noCAS";
  }
}

if($login!="admin"){
  switch($config['Auth-Mode']){		//	Methode d'authentification
    case "LDAP" :				//	LDAP
      $auth=authLDAP($login,$password);
      break;

    case "LDAP-SQL" :			//	LDAP puis SQL en cas d'echec
      $auth=authLDAP($login,$password);
      if(!$auth){
	$auth=authSQL($login,$password);
      }
      break;

    case "CAS" :			//	CAS
      if($login and $_POST['auth']=="CAS" and array_key_exists("login_id",$_SESSION) and $login==$_SESSION['login_id']){
	$auth=true;
      }
      break;

    case "CAS-SQL" :		//	CAS puis SQL en cas d'echec
      if($login and $_POST['auth']=="CAS" and array_key_exists("login_id",$_SESSION) and $login==$_SESSION['login_id']){
	$auth=true;
      }
      if(!$auth){
	$auth=authSQL($login,$password);
      }
      break;
  }
}
?>