<?php
/**
Planning Biblio, Version 2.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : ldap/ajax.ldaptest.php
Création : 6 mars 2017
Dernière modification : 6 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de tester les paramètres LDAP saisis sur la page administration / configuration
Script appelé par la fonction JS ldaptest() (admin/js/config?js) lors du click sur le bouton "test" de la page administration / configuration / LDAP
*/

session_start();

include_once "../include/config.php";
include_once "class.ldap.php";

$filter = filter_input(INPUT_POST,'filter',FILTER_SANITIZE_STRING);
$host = filter_input(INPUT_POST,'host',FILTER_SANITIZE_STRING);
$idAttribute = filter_input(INPUT_POST,'idAttribute',FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST,'password',FILTER_UNSAFE_RAW);
$port = filter_input(INPUT_POST,'port',FILTER_SANITIZE_NUMBER_INT);
$protocol = filter_input(INPUT_POST,'protocol',FILTER_SANITIZE_STRING);
$rdn = filter_input(INPUT_POST,'rdn',FILTER_SANITIZE_STRING);
$suffix = filter_input(INPUT_POST,'suffix',FILTER_SANITIZE_STRING);

// Connexion au serveur LDAP
$url = $protocol.'://'.$host;

if($fp=@fsockopen($host, $port, $errno, $errstr, 5)){
  if($ldapconn = ldap_connect($url)){

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    if(ldap_bind($ldapconn,$rdn,$password)){
      if(ldap_search($ldapconn,$suffix,$filter,array($idAttribute))){
        echo json_encode('ok');
        exit;
      }else{
        echo json_encode('search');
        exit;
      }
    }else{
      echo json_encode('bind');
      exit;
    }
  }
}

echo json_encode('error');



?>