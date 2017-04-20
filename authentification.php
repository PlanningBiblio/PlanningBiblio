<?php
/**
Planning Biblio, Version 2.6.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : authentification.php
Création : mai 2011
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche le formulaire d'authentification, vérifie le login et le mot de passe et créé la session 

Pages en entrée : inclus les fichiers config.php et header.php
Page en sortie :inclus le fichier footer.php
*/

// Cette page peut être chargée directment ou incluse dans la page index.php
// La page index.php démarre déjà une session. On contrôle donc qu'aucune session n'existe avant de la démarrer
// Si PHP version <5.4 et pas de session
if(PHP_VERSION_ID<50400 and session_id()==''){
  session_start();
// Si PHP version >=5.4 et pas de session
}elseif(PHP_VERSION_ID>=50400 and session_status() == PHP_SESSION_NONE){
  session_start();
}

// Initialisation des variables
$version="2.6.3";

// Redirection vers setup si le fichier config est absent
if(!file_exists("include/config.php")){
  include "include/noConfig.php";
}

require_once "include/config.php";
require_once "include/sanitize.php";

// IP Blocker : Affiche accès refusé, IP bloquée si 5 tentatives infructueuses lors les 10 dernières minutes
$IPBlocker=loginFailedWait();
if($IPBlocker>0){
	include "include/accessDenied.php";
	exit;
}

$newLogin=filter_input(INPUT_GET,"newlogin",FILTER_SANITIZE_STRING);
if(!isset($redirURL)){
  $redirURL=isset($_REQUEST['redirURL'])?stripslashes($_REQUEST['redirURL']):"index.php";
}
$redirURL=filter_var($redirURL,FILTER_SANITIZE_URL);

$page=null;
$auth=null;
$authArgs=null;

if(!array_key_exists("oups",$_SESSION)){
  $_SESSION['oups']=array("week"=>false);
}

// Error reporting
ini_set('display_errors',$config['display_errors']);

include "plugins/plugins.php";
include "include/header.php";

echo "<div id='content-auth'>\n";

//	Vérification du login et du mot de passe
if(isset($_POST['login'])){
  $login=filter_input(INPUT_POST,"login",FILTER_SANITIZE_STRING);
  $password=filter_input(INPUT_POST,"password",FILTER_UNSAFE_RAW);

  include "ldap/auth.php";

  if($config['Auth-Mode']=="SQL" or $login=="admin"){
    $auth=authSQL($login,$password);
  }

  if($authArgs and $redirURL){
    $authArgs.="&redirURL=".urlencode($redirURL);
  }elseif($redirURL){
    $authArgs="?redirURL=".urlencode($redirURL);
  }


  if($auth){
    // Log le login et l'IP du client en cas de succès, pour information
    loginSuccess($login);
    $db=new db();
    $db->select2("personnel","id,nom,prenom",array("login"=>$login));
    if($db->result){
      $_SESSION['login_id']=$db->result[0]['id'];
      $_SESSION['login_nom']=$db->result[0]['nom'];
      $_SESSION['login_prenom']=$db->result[0]['prenom'];
      
      // Génération d'un CSRF Token
      // PHP 7
      if(phpversion() >= 7){
        if (empty($_SESSION['oups']['CSRFToken'])) {
          $_SESSION['oups']['CSRFToken'] = bin2hex(random_bytes(32));
        }
      }

      // PHP 5.3+
      else{
        if (empty($_SESSION['oups']['CSRFToken'])) {
          if (function_exists('mcrypt_create_iv')) {
            $_SESSION['oups']['CSRFToken'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
          } else {
            $_SESSION['oups']['CSRFToken'] = bin2hex(openssl_random_pseudo_bytes(32));
          }
        }
      }
      
      $CSRFToken = $_SESSION['oups']['CSRFToken'];
      
      $db=new db();
      $db->CSRFToken = $CSRFToken;
      $db->update2("personnel",array("last_login"=>date("Y-m-d H:i:s")),array("id"=>$_SESSION['login_id']));
      echo "<script type='text/JavaScript'>document.location.href='$redirURL';</script>";
    }
    else{
      echo "<div style='text-align:center'>\n";
      echo "<br/><br/><h3 style='color:red'>L'utilisateur n'existe pas dans le planning</h3>\n";
      echo "<br/><a href='authentification.php{$authArgs}'>Re-essayer</a>\n";
      echo "</div>\n";
    }
  }
  else{
		// Log le login tenté et l'IP du client en cas d'echec, pour bloquer l'IP si trop de tentatives infructueuses
		loginFailed($login);

		// Si la limite est atteinte, on affiche directement la page "Accès refusé"
		if(loginFailedWait()>0){
			echo "<script type='text/JavaScript'>document.location.reload();</script>\n";
			exit;
		}

    echo <<<EOD
    <div id='auth'>
    <center><div id='auth-logo'></div></center>
    <h1 id='title'>{$config['Affichage-titre']}</h1>
    <h2 id='h2-planning-authentification'>Planning - Authentification</h2>
    <h2 id='h2-authentification'>Authentification</h2>
    <div style='text-align:center'>
    <h3 style='color:red'>Erreur lors de l'authentification</h3>
    <br/><a href='authentification.php{$authArgs}'>Re-essayer</a>
    </div>
    </div>
EOD;
  }
}
elseif(isset($_GET['acces'])){
  if(!isset($_GET['no_menu'])){
    include "include/menu.php";
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
  }
}
elseif(array_key_exists("login_id",$_SESSION)){		//		logout
  include "ldap/logoutCAS.php";

  session_destroy();
  echo "<script type='text/JavaScript'>location.href='authentification.php{$authArgs}';</script>";
}
else{		//		Formulaire d'authentification
  echo <<<EOD
    <div id='auth'>
    <center><div id='auth-logo'></div></center>
    <h1 id='title'>{$config['Affichage-titre']}</h1>
    <h2 id='h2-planning-authentification'>Planning - Authentification</h2>
    <h2 id='h2-authentification'>Authentification</h2>
    <form name='form' method='post' action='authentification.php'>
    <input type='hidden' name='auth' value='' />
    <input type='hidden' name='redirURL' value='$redirURL' />
    <table style='width:100%;'>
    <tr><td style='text-align:right;width:48%;'>Utilisateur : </td>
    <td><input type='text' name='login' value='$newLogin' /></td></tr>
    <tr><td align='right'>Mot de passe : </td>
    <td><input type='password' name='password' /></td></tr>
    <tr><td colspan='2' align='center'><br/><input type='submit' class='ui-button' value='Valider' /></td></tr>
EOD;
    if($config['Auth-Anonyme']){
      echo "<tr><td colspan='2' align='center'><br/><a href='index.php?login=anonyme'>Accès anonyme</a></td></tr>\n";
    }
    echo <<<EOD
    </table>
    <input type='hidden' name='width' />
    </form></div>
EOD;

  include "ldap/authCAS.php";
}

include "include/footer.php";
?>
