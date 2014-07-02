<?php
/*
Planning Biblio, Version 1.8.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : ldap/class.ldap.php
Création : 2 juillet 2014
Dernière modification : 2 juillet 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fonctions permettant les authentifications LDAP et CAS
Fichier inclus par ldap/auth.php
*/

function authCAS(){
  include "include/CAS-1.3.2/CAS.php";
  phpCAS::setDebug("data/cas_debug.txt");
  phpCAS::client($GLOBALS['config']['CAS-Version'], $GLOBALS['config']['CAS-Hostname'], intval($GLOBALS['config']['CAS-Port']), $GLOBALS['config']['CAS-URI'],false);
  phpCAS::setExtraCurlOption(CURLOPT_SSLVERSION,3);
  if($GLOBALS['config']['CAS-CACert']){
    phpCAS::setCasServerCACert($GLOBALS['config']['CAS-CACert']);
  }
  else{
    phpCAS::setNoCasServerValidation();
  }
  phpCAS::forceAuthentication();

  $login=phpCAS::getUser();

  // Vérifions si l'utilisateur existe dans le planning
  $db=new db();
  $db->select("personnel","id,nom,prenom","login='$login' AND `supprime`='0';");
  if(!$db->result){
    echo <<<EOD
    <div id='JSInformation'>Vous avez &eacute;t&eacute; correctement identifi&eacute;(e) mais vous n&apos;est pas autoris&eacute;(e) &agrave; 
      utiliser cette application.<br/><b>Veuillez fermer votre navigateur et recommencer avec un autre identifiant</b>.</div>
    <script type='text/JavaScript'>
      errorHighlight($("#JSInformation"),"error");
      position($("#JSInformation"),160,"center");
    </script>
EOD;
    return false;
  }

  // Si authentification CAS et utilisateur existe : retourne son login
  return $login;
}

function authLDAP($login,$password){
  // Variables
  $auth=false;
  if(!$GLOBALS['config']['LDAP-Port']){
    $GLOBALS['config']['LDAP-Port']="389";
  }

  // Vérifions si l'utilisateur existe dans le planning
  $db=new db();
  $db->select("personnel","id,nom,prenom","login='$login' AND `supprime`='0';");
  if(!$db->result){
    return false;
  }

  //	Connexion au serveur LDAP
  $ldapconn = ldap_connect($GLOBALS['config']['LDAP-Host'],$GLOBALS['config']['LDAP-Port'])
    or die ("Impossible de se connecter au serveur LDAP");
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
  if($ldapconn){
    if(@ldap_bind($ldapconn,"uid=".$login.",".$GLOBALS['config']['LDAP-Suffix'],$password)){
      $auth=true;
      $_SESSION['oups']['Auth-Mode']="LDAP";
    }
  }
  return $auth;
}

function cmp_ldap($a,$b){	//tri par nom puis prenom (sn et givenname)
  if ($a['sn'][0] == $b['sn'][0]){
    if($a['givenname'][0] == $b['givenname'][0])
      return 0;
    return ($a['givenname'][0] < $b['givenname'][0]) ? -1 : 1;
    }
  return ($a['sn'][0] < $b['sn'][0]) ? -1 : 1;
}

?>