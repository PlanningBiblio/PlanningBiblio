<?php
require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Dotenv\Dotenv;

$_SERVER['APP_ENV'] = 'test';
$_SERVER['KERNEL_CLASS'] = 'App\Kernel';

if (!file_exists(__DIR__ . "/../.env.test.local")) {
    die("Unable to find the .env.test.local file\n");
}

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . "/../.env.test.local");
$database_url = $_ENV['DATABASE_URL'];

$pattern = '/.[^\/]*\/\/(.[^:]*):(.[^@]*)@(.[^:]*):(\d*)\/(.*)/';

$config['dbuser'] = preg_replace($pattern, '\1', $database_url);
$config['dbpass'] = preg_replace($pattern, '\2', $database_url);
$config['dbhost'] = preg_replace($pattern, '\3', $database_url);
$config['dbport'] = preg_replace($pattern, '\4', $database_url);
$config['dbname'] = preg_replace($pattern, '\5', $database_url);
$config['dbprefix'] = $_ENV['DATABASE_PREFIX'];

//$config = parse_ini_file(__DIR__ . '/config.ini');

$dbname = $config['dbname'];
$dbprefix='';

$dblink= mysqli_init();
$dbconn = mysqli_real_connect($dblink, $config['dbhost'], $config['dbuser'], $config['dbpass'], 'mysql');

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

include_once(__DIR__ . '/../init/init.php');
include_once(__DIR__.'/../init/init_menu.php');
include_once(__DIR__.'/../init/init_templates.php');
include_once(__DIR__.'/../init/common.php');

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
