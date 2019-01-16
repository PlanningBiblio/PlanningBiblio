<?php
/**
 * Planning Biblio, Version 2.8.03
 * Licence GNU/GPL (version 2 et au dela)
 * Voir les fichiers README.md et LICENSE
 * @copyright 2011-2018 Jérôme Combes

 * Fichier : init.php
 * Création : mai 2018
 * Dernière modification : 9 octobre 2018
 * @author Alex Arnaud <alex.arnaud@biblibre.com>

 * Description :
 *   Initialisation de l'application et des variables,
 *   chargement des dépendances (vendor/*),
 *   inclusion des scripts de configuration,
 *   définition du décalage horaire par défaut,
 *   session,
 */

session_start();
$_SESSION['login_id'] = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : '';
$_SESSION['login_nom'] = isset($_SESSION['login_nom']) ? $_SESSION['login_nom'] : '';
$_SESSION['login_prenom'] = isset($_SESSION['login_prenom']) ? $_SESSION['login_prenom'] : '';
$_SESSION['oups']['Auth-Mode'] = isset($_SESSION['oups']['Auth-Mode']) ? $_SESSION['oups']['Auth-Mode'] : '';
$_SESSION['oups']['week'] = isset($_SESSION['oups']['week']) ? $_SESSION['oups']['week'] : '';

// Version
$version="2.8.04";
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use PlanningBiblio\LegacyCodeChecker;
use Model\Extensions\TablePrefix;
use Model\Personnel;
use Model\Access;

// Redirection vers setup si le fichier config est absent
if (!file_exists(__DIR__.'/include/config.php')) {
    include(__DIR__.'/include/noConfig.php');
}

require_once(__DIR__.'/include/config.php');
require_once(__DIR__.'/include/sanitize.php');
require_once(__DIR__.'/lang/fr_FR.php');
if (file_exists(__DIR__.'/lang/custom.php')) {
    require_once(__DIR__.'/lang/custom.php');
}

date_default_timezone_set("Europe/Paris");

// Vérification de la version de la base de données
// Si la version est différente, mise à jour de la base de données
if ($version!=$config['Version']) {
    require_once(__DIR__.'/setup/maj.php');
}

// Initialisation des variables
$request = Request::createFromGlobals();

$date = $request->get('date');
$show_menu = $request->get('menu') == 'off' ? false : true;
$page = $request->get('page', 'planning/poste/index.php');
$login = $request->get('login');

// Login Anonyme
if ($login and $login === "anonyme" and $config['Auth-Anonyme'] and !array_key_exists("login_id", $_SESSION)) {
    $_SESSION['login_id']=999999999;
    $_SESSION['login_nom']="Anonyme";
    $_SESSION['login_prenom']="";
    $_SESSION['oups']["Auth-Mode"]="Anonyme";
}

// Instanciating entity manager.
$entitiesPath = array('src/Model');
$emConfig = Setup::createAnnotationMetadataConfiguration($entitiesPath, true);

// Handle table prefix.
$evm = new \Doctrine\Common\EventManager;
$tablePrefix = new Model\Extensions\TablePrefix($config['dbprefix']);
$evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);

$dbParams = array(
    'driver'   => 'pdo_mysql',
    'charset'  => 'utf8',
    'host'     => $config['dbhost'],
    'user'     => $config['dbuser'],
    'password' => $config['dbpass'],
    'dbname'   => $config['dbname'],
);

$entityManager = EntityManager::create($dbParams, $emConfig, $evm);

// Sécurité CSRFToken
$CSRFSession = isset($_SESSION['oups']['CSRFToken']) ? $_SESSION['oups']['CSRFToken'] : null;
$_SESSION['PLdate']=array_key_exists("PLdate", $_SESSION)?$_SESSION['PLdate']:date("Y-m-d");

if (!array_key_exists("oups", $_SESSION)) {
    $_SESSION['oups']=array("week" => false);
}

// Affichage de tous les plannings de la semaine
if ($page=="planning/poste/index.php" and !$date and $_SESSION['oups']['week']) {
    $page="planning/poste/semaine.php";
}

$content_planning = 0;
if ($page == 'planning/poste/index.php' or $page == 'planning/poste/semaine.php' or !$show_menu) {
    $content_planning = 1;
}

// Recupération des droits d'accès de l'agent

$logged_in = $entityManager->find(Personnel::class, $_SESSION['login_id']);
$droits = $logged_in ? $logged_in->droits() : array();
$_SESSION['droits'] = array_merge($droits, array(99));

// Droits necessaires pour consulter la page en cours
$accesses = $entityManager->getRepository(Access::class)->findBy(array('page' => $page));
$authorized = $logged_in ? $logged_in->can_access($accesses, $page) : false;

$theme=$config['Affichage-theme']?$config['Affichage-theme']:"default";
if (!file_exists("themes/$theme/$theme.css")) {
    $theme="default";
}

$themeJQuery = $config['Affichage-theme'] ?$config['Affichage-theme'] : "default";
if (!file_exists("themes/$theme/jquery-ui.min.css")) {
    $themeJQuery="default";
}

$favicon = null;
if (!file_exists("themes/$theme/favicon.png")) {
    $favicon = "themes/$theme/images/favicon.png";
}

function CSRFTokenOK($token, $session) {
    $error = "CSRF Token Exception {$_SERVER['SCRIPT_NAME']}";

    if (!$token) {
        error_log($error);
        return false;
    }

    if (!$session['oups']['CSRFToken']) {
        error_log($error);
        return false;
    }

    if ($token !== $session['oups']['CSRFToken']) {
        error_log($error);
        return false;
    }

    return true;
}
