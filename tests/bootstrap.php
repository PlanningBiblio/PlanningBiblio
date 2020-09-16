<?php
require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

require dirname(__DIR__).'/config/bootstrap.php';

$config = parse_ini_file(__DIR__ . '/config.ini');

$dbname = $config['dbname'];
$dbprefix='';

require dirname(__DIR__).'/config/bootstrap.php';

$session = new Session();
$session->start();
$_SESSION['login_id'] = 1;

include_once(__DIR__.'/../init/init.php');
include_once(__DIR__.'/../init/init_menu.php');
include_once(__DIR__.'/../init/init_templates.php');

$request = Request::createFromGlobals();
$path = $request->getPathInfo();

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$dblink= mysqli_init();
$dbconn = mysqli_real_connect($dblink, $config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);

$sql = array();
$sql[]="DROP DATABASE IF EXISTS `$dbname`;";
$sql[]="CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8 COLLATE utf8_bin;";
$sql[]="USE $dbname;";

include __DIR__ . '/../public/setup/db_structure.php';
include __DIR__ . '/../public/setup/db_data.php';

if ($dbconn) {
    foreach ($sql as $elem) {
        mysqli_multi_query($dblink, $elem);
    }
    mysqli_close($dblink);
}

$entitiesPath = array('src/Model');
$emConfig = Setup::createAnnotationMetadataConfiguration($entitiesPath, true);

$dbParams = array(
    'driver'   => 'pdo_mysql',
    'host'     => $config['dbhost'],
    'user'     => $config['dbuser'],
    'password' => $config['dbpass'],
    'dbname'   => $config['dbname'],
);

global $entityManager;
$entityManager = EntityManager::create($dbParams, $emConfig);
