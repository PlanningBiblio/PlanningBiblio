<?php
require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$config = parse_ini_file(__DIR__ . '/config.ini');

$dbname = $config['dbname'];
$dbprefix='';

$dblink= mysqli_init();
$dbconn = mysqli_real_connect($dblink, $config['dbhost'], $config['dbuser'], $config['dbpass'], 'mysql');

$sql = array();
$sql[]="DROP DATABASE IF EXISTS `$dbname`;";
$sql[]="CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8 COLLATE utf8_bin;";
$sql[]="USE $dbname;";

include __DIR__ . '/../setup/db_structure.php';
include __DIR__ . '/../setup/db_data.php';

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
