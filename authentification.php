<?php
/*
Planning Biblio, Version 1.7.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : authentification.php
Création : mai 2011
Dernière modification : 28 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le formulaire d'authentification, vérifie le login et le mot de passe et créé la session 

Pages en entrée : inclus les fichiers config.php et header.php
Page en sortie :inclus le fichier footer.php
*/

session_start();

// Initialisation des variables
$page=null;
$login=isset($_GET['newlogin'])?$_GET['newlogin']:null;
$auth=null;
$authArgs=null;

// Redirection vers setup si le fichier config est absent
if(!file_exists("include/config.php")){
  header("Location: setup/index.php");
  exit;
}

$version="1.7.5";

include "include/config.php";

ini_set('display_errors',$config['display_errors']);
switch($config['error_reporting']){
  case 0: error_reporting(0); break;
  case 1: error_reporting(E_ERROR | E_WARNING | E_PARSE); break;
  case 2: error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); break;
  case 3: error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); break;
  case 4: error_reporting(E_ALL ^ E_NOTICE); break;
  case 5: error_reporting(E_ALL); break;
  default: error_reporting(E_ALL ^ E_NOTICE); break;
}

include "plugins/plugins.php";
include "include/function.php";
include "include/header.php";

//	Login Anonyme
if(isset($_GET['login']) and $_GET['login']=="anonyme" and $config['Auth-Anonyme']){
  $_SESSION['login_id']=999999999;
  $_SESSION['login_nom']="Anonyme";
  $_SESSION['login_prenom']="";
  $_SESSION['oups']=array("Auth-Mode"=>"Anonyme");
  header("Location: index.php");
}

//	Vérification du login et du mot de passe
elseif(isset($_POST['login'])){
  $login=$_POST['login'];
  $password=$_POST['password'];

  if(in_array("ldap",$plugins)){
    include "plugins/ldap/auth.php";
  }
  if($config['Auth-Mode']=="SQL" or $login=="admin"){
    $auth=authSQL($login,$password);
  }

  if($auth){
    $db=new db();
    $db->query("select id,nom,prenom from {$dbprefix}personnel where login='$login';");
    if($db->result){		
      $_SESSION['login_id']=$db->result[0]['id'];
      $_SESSION['login_nom']=$db->result[0]['nom'];
      $_SESSION['login_prenom']=$db->result[0]['prenom'];
      if(!array_key_exists("oups",$_SESSION)){
	$_SESSION['oups']=array();
      }
      $db=new db();
      $db->query("update {$dbprefix}personnel set last_login=SYSDATE() where id='{$_SESSION['login_id']}';");
      echo "<script type='text/JavaScript'>document.location.href='index.php';</script>";
    }
    else{
      echo "<div style='text-align:center'>\n";
      echo "<br/><br/><h3 style='color:red'>L'utilisateur n'existe pas dans le planning</h3>\n";
      echo "<br/><a href='authentification.php'>Re-essayer</a>\n";
      echo "</div>\n";
    }
  }
  else{
    echo "<div style='text-align:center'>\n";
    echo "<br/><br/><h3 style='color:red'>Erreur lors de l'authentification</h3>\n";
    echo "<br/><a href='authentification.php{$authArgs}'>Re-essayer</a>\n";
    echo "</div>\n";
  }
}
elseif(isset($_GET['acces'])){
  if(!isset($_GET['no_menu'])){
    include "include/menu.php";
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
  }
}
elseif(array_key_exists("login_id",$_SESSION)){		//		logout
  if(in_array("ldap",$plugins)){
    include "plugins/ldap/logoutCAS.php";
  }
  session_destroy();
  echo "<script type='text/JavaScript'>location.href='authentification.php{$authArgs}';</script>";
}
else{		//		Formulaire d'authentification
  echo <<<EOD
    <div id='auth'>
    <center><img src='img/logo.png' alt='logo' id='auth-logo'/></center>
    <h1 id='title'>{$config['titre']}</h1>
    <h2 id='h2-planning-authentification'>Planning - Authentification</h2>
    <h2 id='h2-authentification'>Authentification</h2>
    <form name='form' method='post' action='authentification.php'>
    <input type='hidden' name='auth' value='' />
    <table style='width:100%;'>
    <tr><td style='text-align:right;width:48%;'>Utilisateur : </td>
    <td><input type='text' name='login' value='$login' /></td></tr>
    <tr><td align='right'>Mot de passe : </td>
    <td><input type='password' name='password' /></td></tr>
    <tr><td colspan='2' align='center'><br/><input type='submit' class='ui-button' value='Valider' /></td></tr>
EOD;
    if($config['Auth-Anonyme']){
      echo "<tr><td colspan='2' align='center'><br/><a href='authentification.php?login=anonyme'>Accès anonyme</a></td></tr>\n";
    }
    echo <<<EOD
    </table>
    <input type='hidden' name='width' />
    </form></div>
EOD;

  if(in_array("ldap",$plugins)){
    include "plugins/ldap/authCAS.php";
  }
}

include "include/footer.php";
?>
