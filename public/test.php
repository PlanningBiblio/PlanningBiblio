<?php
require __DIR__ . '/../vendor/autoload.php';

use PlanningBiblio\OAuth;
session_start();
$oauth = new OAuth();
$token = $oauth->getToken();
echo "token: $token";
?>

