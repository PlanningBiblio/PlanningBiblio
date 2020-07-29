<?php
use Symfony\Component\HttpFoundation\Session\Session;

$session = new Session();
$session->start();

$_SESSION['login_id'] = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : '';
$_SESSION['login_nom'] = isset($_SESSION['login_nom']) ? $_SESSION['login_nom'] : '';
$_SESSION['login_prenom'] = isset($_SESSION['login_prenom']) ? $_SESSION['login_prenom'] : '';
$_SESSION['oups']['Auth-Mode'] = isset($_SESSION['oups']['Auth-Mode']) ? $_SESSION['oups']['Auth-Mode'] : '';
$_SESSION['oups']['week'] = isset($_SESSION['oups']['week']) ? $_SESSION['oups']['week'] : '';

// Version
$version="20.05.00.002"; // xx.xx.xx.xxx
$displayed_version="20.05.00"; // xx.xx.xx

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

use App\Model\Agent;
use App\Model\Access;

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

date_default_timezone_set("Europe/Paris");

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
$page = preg_replace('/([a-z\/]*).*/', "$1", $page);
$page = rtrim($page, '/add');
$page = rtrim($page, '/');

$ajax = false;
if (strpos($page, '/ajax') === 0) {
    $ajax = true;
}

$login = $request->get('login');

// Login Anonyme
if ($login and $login === "anonyme" and $config['Auth-Anonyme'] and empty($_SESSION["login_id"])) {
    $_SESSION['login_id']=999999999;
    $_SESSION['login_nom']="Anonyme";
    $_SESSION['login_prenom']="";
    $_SESSION['oups']["Auth-Mode"]="Anonyme";
}

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
$logged_in = $entityManager->find(Agent::class, $_SESSION['login_id']);
$droits = $logged_in ? $logged_in->droits() : array();

$_SESSION['droits'] = array_merge($droits, array(99));

// Droits necessaires pour consulter la page en cours
$accesses = $entityManager->getRepository(Access::class)->findBy(array('page' => $page));
$authorized = $logged_in ? $logged_in->can_access($accesses) : false;

if ($_SESSION['oups']["Auth-Mode"] == 'Anonyme' ) {
    foreach ($accesses as $access) {
        if ($access->groupe_id() == '99') {
            $authorized = true;
        }
    }
};

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