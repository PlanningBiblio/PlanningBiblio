<?php
use Doctrine\DBAL\DriverManager;

return DriverManager::getConnection([
    'dbname' => 'planningbiblio',
    'user' => 'planningbadmin',
    'password' => 'MxZ5sEsAE',
    'host' => 'localhost:3306',
    'driver' => 'pdo_mysql',
]);