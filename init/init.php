<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\Model\Access;
use App\Model\Agent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

$session = new Session();
$session->start();

$loginId = $session->get('loginId') ?? '';

$_SESSION['login_nom'] = isset($_SESSION['login_nom']) ? $_SESSION['login_nom'] : '';
$_SESSION['login_prenom'] = isset($_SESSION['login_prenom']) ? $_SESSION['login_prenom'] : '';
$_SESSION['oups']['Auth-Mode'] = isset($_SESSION['oups']['Auth-Mode']) ? $_SESSION['oups']['Auth-Mode'] : '';

// Version
$version="25.05.00"; // xx.xx.xx
$GLOBALS['version'] = $version;

// Redirection vers setup si le fichier config est absent
if (!file_exists(__DIR__.'/../.env.local')) {
    include(__DIR__.'/../public/include/noConfig.php');
}

require_once(__DIR__.'/../public/include/config.php');
require_once(__DIR__.'/../public/include/sanitize.php');
require_once(__DIR__.'/../public/lang/fr_FR.php');
if (file_exists(__DIR__.'/../public/lang/custom.php')) {
    require_once(__DIR__.'/../public/lang/custom.php');
}

require_once(__DIR__.'/init_entitymanager.php');
require_once(__DIR__.'/init_plugins.php');

// Vérification de la version de la base de données
// Si la version est différente, mise à jour de la base de données
if ($version!=$config['Version'] && $version != 'ajax') {
    require_once(__DIR__.'/../public/setup/maj.php');
}

// Initialisation des variables
$request = Request::createFromGlobals();

$date = $request->get('date');
$show_menu = $request->get('menu') == 'off' ? false : true;

// To control access rights, we keep only the part of the URI before the numbers
// e.g. : we keep /absences/info when the URI is /absences/info/11/edit
$page = $request->getPathInfo();

$login = $request->get('login');

// Sécurité CSRFToken
$CSRFSession = isset($_SESSION['oups']['CSRFToken']) ? $_SESSION['oups']['CSRFToken'] : null;
$_SESSION['PLdate']=array_key_exists("PLdate", $_SESSION)?$_SESSION['PLdate']:date("Y-m-d");

// Recupération des droits d'accès de l'agent
$logged_in = $entityManager->find(Agent::class, $loginId);
$droits = $logged_in ? $logged_in->droits() : array();
$_SESSION['droits'] = array_merge($droits, array(99));

$theme=$config['Affichage-theme']?$config['Affichage-theme']:"default";
if (!file_exists("themes/$theme/$theme.css")) {
    $theme="default";
}

$themeJQuery = $config['Affichage-theme'] ?$config['Affichage-theme'] : "default";
if (!file_exists("themes/$themeJQuery/jquery-ui.min.css")) {
    $themeJQuery="default";
}
