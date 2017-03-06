<?php
/**
Planning Biblio, Version 2.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : ldap/import2.php
Création : 2 juillet 2014
Dernière modification : 6 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet l'import des agents à partir d'un annuaire LDAP.
Recherche les informations sur les agents sélectionnés à partir de l'annuaire et les copie dans la base de données MySQL

Fichier appelé par la page personnel/import.php	
*/

require_once "class.ldap.php";

// Initialisation des variables
$actif="Actif";
$date=date("Y-m-d H:i:s");
$commentaires= "Importation LDAP $date";
$droits="a:2:{i:0;i:99;i:1;i:100;}";
$password="password_bidon_pas_importé_depuis_ldap";
$postes='a:1:{i:0;s:0:"";}';
$erreurs=false;

$post=filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
$recherche=$post["recherche"];

//	Récupération des uid des agents sélectionnés
$uids=array();
if(array_key_exists("chk",$post)){
  foreach($post["chk"] as $elem){
    $uids[]=ldap_escape($elem, '', LDAP_ESCAPE_FILTER);
  }
}else{
  $msg=urlencode("Aucun agent n&apos;est s&eacute;lectionn&eacute;.");
  echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/import.php&import-type=ldap&msg=$msg&msgType=error&recherche-ldap=$recherche';</script>";
  exit;
}

//	Connexion au serveur LDAP
if(!$config['LDAP-Port']){
  $config['LDAP-Port']="389";
}

$url = $GLOBALS['config']['LDAP-Protocol'].'://'.$GLOBALS['config']['LDAP-Host'].':'.$GLOBALS['config']['LDAP-Port'];
$ldapconn = ldap_connect($url)
  or die ("Impossible de se connecter au serveur LDAP");
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
if($ldapconn){
  $ldapbind=ldap_bind($ldapconn,$config['LDAP-RDN'],decrypt($config['LDAP-Password']));
}

// Préparation de la requête pour insérer les données dans la base de données
$req="INSERT INTO `{$dbprefix}personnel` (`login`,`nom`,`prenom`,`mail`,`password`,`droits`,`arrivee`,`postes`,`actif`,`commentaires`) ";
$req.="VALUES (:login, :nom, :prenom, :mail, :password, :droits, :arrivee, :postes, :actif, :commentaires);";
$db=new dbh();
$db->prepare($req);

// Recuperation des infos LDAP et insertion dans la base de données
if($ldapbind){
  foreach($uids as $uid){
    $filter="({$config['LDAP-ID-Attribute']}=$uid)";
    $justthese=array("dn",$config['LDAP-ID-Attribute'],"sn","givenname","userpassword","mail");
    $sr=ldap_search($ldapconn,$config['LDAP-Suffix'],$filter,$justthese);
    $infos=ldap_get_entries($ldapconn,$sr);
    if($infos[0][$config['LDAP-ID-Attribute']]){
      $login=$infos[0][$config['LDAP-ID-Attribute']][0];
      $nom=array_key_exists("sn",$infos[0])?htmlentities($infos[0]['sn'][0],ENT_QUOTES|ENT_IGNORE,"UTF-8",false):"";
      $prenom=array_key_exists("givenname",$infos[0])?htmlentities($infos[0]['givenname'][0],ENT_QUOTES|ENT_IGNORE,"UTF-8",false):"";
      $mail=array_key_exists("mail",$infos[0])?$infos[0]['mail'][0]:"";

      $values=array(":login"=>$login, ":nom"=>$nom, ":prenom"=>$prenom, ":mail"=>$mail, ":password"=> $password, ":droits"=> $droits,
	":arrivee"=>$date, ":postes"=> $postes, ":actif"=> $actif, ":commentaires"=> $commentaires);

      // Execution de la requête (insertion dans la base de données)
      $db->execute($values);
      if($db->error){
	$erreurs=true;
      }
    }
  }
}

if($erreurs){
  $msg=urlencode("Il y a eu des erreus pendant l'importation.#BR#Veuillez vérifier la liste des agents");
  $msgType="error";
}else{
  $msg=urlencode("Les agents ont été importés avec succès");
  $msgType="success";
}
echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/import.php&import-type=ldap&msg=$msg&msgType=$msgType&recherche-ldap=$recherche';</script>";
?>