<?php

$database_url = $_ENV['DATABASE_URL'];

$pattern = '/.[^\/]*\/\/(.[^:]*):(.[^@]*)@(.[^:]*):(\d*)\/(.*)/';

$config['dbuser'] = preg_replace($pattern, '\1', $database_url);
$config['dbpass'] = preg_replace($pattern, '\2', $database_url);
$config['dbhost'] = preg_replace($pattern, '\3', $database_url);
$config['dbport'] = preg_replace($pattern, '\4', $database_url);
$config['dbname'] = preg_replace($pattern, '\5', $database_url);
$config['dbprefix'] = $_ENV['DATABASE_PREFIX'];

$dbname = $config['dbname'];
$dbprefix='';

$dblink= mysqli_init();
$dbconn = mysqli_real_connect($dblink, $config['dbhost'], $config['dbuser'], $config['dbpass'], 'mysql');

$sql = array();
$sql[]="DROP DATABASE IF EXISTS `$dbname`;";
$sql[]="CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8 COLLATE utf8_bin;";
$sql[]="USE $dbname;";

require_once __DIR__ . '/../legacy/Common/function.php';
require_once __DIR__ . '/../legacy/migrations/schema.php';
require_once __DIR__ . '/../legacy/migrations/data.php';

ob_start();
require_once __DIR__.'/../legacy/migrations/update.php';
ob_end_clean();
