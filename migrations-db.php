<?php

require 'vendor/autoload.php';
require 'public/include/config.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Symfony\Component\Console\Helper\HelperSet;

$dbParams = array(
    'dbname' => $config['dbname'],
    'user' => $config['dbuser'],
    'password' => $config['dbpass'],
    'host' => $config['dbhost'],
    'port' => $config['dbport'],
    'driver' => 'pdo_mysql'
);

$connection = DriverManager::getConnection($dbParams);

return new HelperSet([
    'db' => new ConnectionHelper($connection),
]);