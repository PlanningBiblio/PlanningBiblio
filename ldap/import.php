<?php
/*
Planning Biblio, Version 1.8.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : ldap/import.php
Création : 2 juillet 2014
Dernière modification : 2 juillet 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet l'import des agents à partir d'un annuaire LDAP.
Affiche un formulaire de recherche et la liste des agents correspondants à la recherche.

Fichier appelé par la page personnel/import.php
*/

include_once "class.ldap.php";

echo "<h3>Importation des agents à partir de l'annuaire LDAP</h3>\n";
echo "<div id='import-div' style='position:relative; margin:30px 0 0 0;'>\n";
echo "<div id='ldap' style='margin-left:80px;'>\n";
echo "<form method='get' action='index.php'>\n";

if(isset($_GET['message'])){	//		Affichage du résultat de l'importation
  if($_GET['message']=="empty"){
    echo "<b style='color:red'>Aucun agent n'est s&eacute;lectionn&eacute;.</b><br/><br/>\n";
  }
  elseif($_GET['message']=="erreurs"){
    echo "<b style='color:red'>Il y a eu des erreurs pendant l'importation.<br/>\n";
    echo "Veuillez v&eacute;rifier la liste des agents et recommencer si besoin.</b><br/><br/>\n";
  }
  else{
    echo "<b style='color:green'>Importation r&eacute;ussie.</b><br/><br/>\n";
  }
}

//		Formulaire de recherche
echo "Importation de nouveaux agents &agrave; partir de l'annuaire LDAP<br/><br/>\n";
$req=isset($_GET['recherche-ldap'])?$_GET['recherche-ldap']:null;
echo "<input type='text' name='recherche-ldap' value='$req' />\n";
?>
<input type='hidden' name='import-type' value='ldap' />
<input type='hidden' name='page' value='personnel/import.php' />
<input type='submit' value='Rechercher' />
</form>
<br/>

<?php
//		Recherche dans l'annuaire si le formulaire est validé
if(isset($_GET['recherche-ldap'])){
  $infos=array();
  if(!$config['LDAP-Port']){
    $config['LDAP-Port']="389";	//	port par defaut
  }
  if(!$config['LDAP-Filter']){
    $filter="(objectclass=inetorgperson)";	//	filtre par defaut
  }
  elseif($config['LDAP-Filter'][0]!="("){
    $filter="({$config['LDAP-Filter']})";
  }
  else{
    $filter=$config['LDAP-Filter'];
  }

  //	Ajout des infos de recherche dans le filtre
  if($req){
    $filter="(&{$filter}(|(uid=*$req*)(givenname=*$req*)(sn=*$req*)(mail=*$req*)))";
  }

  //	Connexion au serveur LDAP
  $ldapconn = ldap_connect($config['LDAP-Host'],$config['LDAP-Port'])
    or die ("Impossible de joindre le serveur LDAP");
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
  if($ldapconn){
    $ldapbind=ldap_bind($ldapconn,$config['LDAP-RDN'],decrypt($config['LDAP-Password']))
      or die ("Impossible de se connecter au serveur LDAP");
  }
  if($ldapbind){
    $justthese=array("dn","uid","sn","givenname","userpassword","mail");
    $sr=ldap_search($ldapconn,$config['LDAP-Suffix'],$filter,$justthese);
    $infos=ldap_get_entries($ldapconn,$sr);
  }
  
  //	Recherche des agents existants
  $agents_existants=array();
  $db=new db();
  $db->query("SELECT `login` FROM `{$dbprefix}personnel` WHERE `supprime`<>'2' ORDER BY `login`;");
  if($db->result){
    foreach($db->result as $elem){
      $agents_existants[]=$elem['login'];
    }
  }

  //	Suppression des agents existant du tableau LDAP
  $tab=array();
  if(!empty($infos)){
    foreach($infos as $info){
      if(!in_array($info['uid'][0],$agents_existants) and !empty($info)){
	$tab[]=$info;
      }
    }
    $infos=$tab;
  }

  //	Affichage du tableau
  if(!empty($infos)){
    usort($infos,"cmp_ldap");
    $i=0;
    $class="tr1";
    echo "<form name='form' method='post' action='index.php'>\n";
    echo "<input type='hidden' name='page' value='personnel/import.php' />\n";
    echo "<input type='hidden' name='import-type' value='ldap' />\n";
    echo "<input type='hidden' name='recherche' value='$req' />\n";
    echo "<table cellspacing='0'>\n";
    echo "<tr class='th'>\n";
    echo "<td><input type='checkbox' onclick='checkall(\"form\",this);' /></td>\n";
    echo "<td>Nom</td><td>Pr&eacute;nom</td><td>e-mail</td><td>Login</td></tr>\n";

    foreach($infos as $info){
      $class=$class=="tr1"?"tr2":"tr1";
      $sn=array_key_exists('sn',$info)?$info['sn'][0]:null;
      $givenname=array_key_exists('givenname',$info)?$info['givenname'][0]:null;
      $mail=array_key_exists('mail',$info)?$info['mail'][0]:null;
      echo "<tr class='$class'>\n";
      echo "<td><input type='checkbox' name='chk$i' value='".utf8_decode($info['uid'][0])."' /></td>\n";
      echo "<td>$sn</td>\n";
      echo "<td>$givenname</td>\n";
      echo "<td>$mail</td>\n";
      echo "<td>{$info['uid'][0]}</td>\n";
      echo "</tr>\n";
      $i++;
    }
    echo "</table><br/>\n";
    echo "<input type='submit' value='Importer' />\n";
    echo "</form>\n";
  }
}
echo "<br/><a href='index.php?page=personnel/index.php'>Retour &agrave; la liste des agents</a><br/>\n";
echo "</div> <!-- ldap -->\n";
echo "</div>\n";
?>