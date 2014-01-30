<?php
/*
Planning Biblio, Version 1.6.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : index.php
Création : mai 2011
Dernière modification : 26 décembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Page principale,
Vérifie si la base de données doit être mise à jour, inclus les pages de configuration et
de fonctions communes, vérifie les droits à la page demandée en argument et l'inclus si l'utilisateur
est autorisé à la consulter

Pages en entrée : inclus les fichiers config.php, doctype.php et header.php
Page en sortie :inclus le fichier footer.php
*/

session_start();

// Initialisation des variables
$version="1.6.8";
$get_menu=isset($_GET['menu'])?$_GET['menu']:"";
$post_menu=isset($_POST['menu'])?$_POST['menu']:"";
$page=isset($_GET['page'])?$_GET['page']:"planning/poste/index.php";
$page=isset($_POST['page'])?$_POST['page']:$page;

// Redirection vers setup si le fichier config est absent
if(!file_exists("include/config.php")){
  header("Location: setup/index.php");
  exit;
}

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

date_default_timezone_set("Europe/Paris");

include "include/function.php";

// Vérification de la version de la base de données
// Si la version est différente, mise à jour de la base de données 
if($version!=$config['Version']){
  include "include/maj.php";
}
// Sinon, on continue
else{
  include "include/feries.php";
  include "plugins/plugins.php";
  include "include/cron.php";
}

//		Si pas de session, redirection vers la page d'authentification
if(!$_SESSION['login_id']){
  if($get_menu=="off" or $post_menu=="off")	// dans le cas d'une action executée dans un popup alors que la session a été perdue, on affiche la page d'auth sur le parent
    echo "<script type='text/JavaScript'>parent.location.href='authentification.php';</script>\n";
  else
    header("Location: authentification.php");		// session perdue, on affiche la page d'authentification
}

//		La page d'impression n'est pas compatible avec le DocType
if(substr($page,0,29)!="planning/poste/impression.php")
  include "include/doctype.php";
include "include/header.php";
if(!$get_menu=="off")
  include "include/menu.php";

//		Recupération des droits d'accès de l'agent
$db=new db();
$db->query("SELECT droits FROM `{$dbprefix}personnel` WHERE `id`='{$_SESSION['login_id']}';");
$droits=unserialize($db->result[0]['droits']);
$droits[]=99;	// Ajout du droit de consultation pour les connexions anonymes
$_SESSION['droits']=$droits;

//		Droits necessaires pour consulter la page en cours
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}acces` WHERE `page`='$page';");

$divId=substr($page,0,24)=="planning/poste/index.php"?"planningPoste":"content";
echo "<div id='$divId'>\n";
if(in_array($db->result[0]['groupe_id'],$droits)){
  include $page;
}
else{
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
}
if($get_menu!="off" and $post_menu!="off"){
  include "include/footer.php";
}
?>
