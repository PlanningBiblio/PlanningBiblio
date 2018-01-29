<?php
/**
Planning Biblio, Version 2.7.12
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ldap/authCAS.php
Création : 2 juillet 2014
Dernière modification : 24 janvier 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant l'authentification CAS
*/

if( substr($config['Auth-Mode'],0,3)=="CAS"
    and !isset($_GET['noCAS'])
    and empty($_SESSION['login_id'])
    and !isset($_POST['login'])
    and !isset($_POST['acces']) )
{

  require_once "class.ldap.php";

  $_SESSION['oups']['Auth-Mode']="CAS";

  // La fonction authCAS() redirige l'utilisateur vers le serveur CAS s'il n'y a pas de ticket valide.
  // Une fois authentifié sur le serveur CAS, la fonction récupère le login utilisé et vérifie si le compte existe dans la base de données Planning Biblio
  // Si l'utilisateur existe, la fonction authCAS créé la session et log les infos d'ouverture de session dans la base de données.
  $login = authCAS();

  // Si le login est un succès, on redirige l'utilisateur vers la page demandée
  if($login)
  {

    // Redirection vers le planning
    $url = createURL();

    if($redirURL){
      $url .= '/'.$redirURL;
    }

    if(strstr($url, 'authentification.php')){
      $url = createURL();
    }

    header("Location: $url");

    exit;
  }

  // Si la fonction authCAS return false ($login == false), elle affiche à l'écran un message disant que l'utilisateur n'est pas autorisé à utiliser l'application.
  // Donc on a juste à interrompre le processus pour ne pas afficher la page d'authentification.
  exit;
}
?>