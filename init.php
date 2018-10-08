<?php
/**
  Planning Biblio, Version 2.7.11
  Licence GNU/GPL (version 2 et au dela)
  Voir les fichiers README.md et LICENSE
  @copyright 2011-2018 Jérôme Combes

  Fichier : init.php
  Création : mai 2018
  Dernière modification : 24 mai 201_
  @author Alex Arnaud <alex.arnaud@biblibre.com>

  Description :
    Initialisation de l'application et des variables,
    chargement des dépendances (vendor/*),
    inclusion des scripts de configuration,
    définition du décalage horaire par défaut,
    session,
*/

session_start();

// Version
$version="2.8.03";
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

// Redirection vers setup si le fichier config est absent
if(!file_exists("include/config.php")) {
  include "include/noConfig.php";
}

require_once "include/config.php";
require_once "include/sanitize.php";
require_once __DIR__."/lang/fr_FR.php";
if( file_exists( __DIR__."/lang/custom.php" )){
  require_once __DIR__."/lang/custom.php";
}

date_default_timezone_set("Europe/Paris");

// Initialisation des variables
$request = Request::createFromGlobals();

$date = $request->get('date');
$menu = $request->get('menu') == 'off' ? false : true;
$page = $request->get('page', 'planning/poste/index.php');
$login = $request->get('login');

// Login Anonyme
if($login and $login === "anonyme" and $config['Auth-Anonyme'] and !array_key_exists("login_id", $_SESSION)) {
    $_SESSION['login_id']=999999999;
    $_SESSION['login_nom']="Anonyme";
    $_SESSION['login_prenom']="";
    $_SESSION['oups']["Auth-Mode"]="Anonyme";
}

// Sécurité CSRFToken
$CSRFSession = isset($_SESSION['oups']['CSRFToken']) ? $_SESSION['oups']['CSRFToken'] : null;
$_SESSION['PLdate']=array_key_exists("PLdate",$_SESSION)?$_SESSION['PLdate']:date("Y-m-d");

if(!array_key_exists("oups",$_SESSION)){
    $_SESSION['oups']=array("week"=>false);
}

// Affichage de tous les plannings de la semaine
if($page=="planning/poste/index.php" and !$date and $_SESSION['oups']['week']){
    $page="planning/poste/semaine.php";
}


