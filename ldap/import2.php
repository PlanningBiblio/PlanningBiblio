<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : ldap/import2.php
Création : 2 juillet 2014
Dernière modification : 3 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet l'import des agents à partir d'un annuaire LDAP.
Recherche les informations sur les agents sélectionnés à partir de l'annuaire et les copie dans la base de données MySQL

Fichier appelé par la page personnel/import.php	
*/

//	Récupération des uid des agents sélectionnés
$keys=array_keys($_POST);
$uids=array();
foreach($keys as $key){
  if(substr($key,0,3)=="chk"){
    $uids[]=$_POST[$key];
  }
}
if(empty($uids)){
  echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/import.php&import-type=ldap&message=empty&recherche-ldap={$_POST['recherche']}';</script>";
  exit;
}

//	Connexion au serveur LDAP
if(!$config['LDAP-Port']){
  $config['LDAP-Port']="389";
}
$ldapconn = ldap_connect($config['LDAP-Host'],$config['LDAP-Port'])
  or die ("Impossible de se connecter au serveur LDAP");
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
if($ldapconn){
  $ldapbind=ldap_bind($ldapconn,$config['LDAP-RDN'],decrypt($config['LDAP-Password']));
}

$req="INSERT INTO `{$dbprefix}personnel` (`login`,`nom`,`prenom`,`mail`,`password`,`droits`,`arrivee`,`postes`,`actif`,`commentaires`) ";
$req.="VALUES (:login, :nom, :prenom, :mail, :password, :droits, :arrivee, :postes, :actif, :commentaires);";

$date=date("Y-m-d H:i:s");

$erreurs=false;
//	Recuperation des infos LDAP
if($ldapbind){
  $db=new dbh();
  $db->prepare($req);
  foreach($uids as $uid){
    $filter="(uid=$uid)";
    $justthese=array("dn","uid","sn","givenname","userpassword","mail");
    $sr=ldap_search($ldapconn,$config['LDAP-Suffix'],$filter,$justthese);
    $infos=ldap_get_entries($ldapconn,$sr);
    if($infos[0]['uid']){
      $infos[0]['sn'][0]=htmlentities($infos[0]['sn'][0],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
      $infos[0]['givenname'][0]=htmlentities($infos[0]['givenname'][0],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
      $values=array(":login"=>$infos[0]['uid'][0], ":nom"=>$infos[0]['sn'][0], ":prenom"=>$infos[0]['givenname'][0], 
	":mail"=>$infos[0]['mail'][0], ":password"=>"password_bidon_pas_importé_depuis_ldap", ":droits"=>"a:2:{i:0;i:99;i:1;i:100;}",
	":arrivee"=>$date, ":postes"=>'a:1:{i:0;s:0:"";}', ":actif"=>"Actif", ":commentaires"=> "Importation LDAP $date");
      $db->execute($values);
      if($db->error){
	$erreurs=true;
      }
      
    }
  }
}

if($erreurs){
  $msg=urlencode("Il y a eu des erreus pendant l'importation.<br/>Veuillez vérifier la liste des agents");
  $msgType="error";
}else{
  $msg=urlencode("Les agents ont été importés avec succès");
  $msgType="success";
}
echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/import.php&import-type=ldap&msg=$msg&msgType=$msgType&recherche-ldap={$_POST['recherche']}';</script>";
?>