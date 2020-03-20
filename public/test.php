<?php
require __DIR__ . '/../vendor/autoload.php';
require_once "include/config.php";

use App\PlanningBiblio\GraphClient;

session_start();

$tenantid = $config['graph_api_tenantid'];
$clientid = $config['graph_api_clientid'];
$clientsecret = $config['graph_api_clientsecret'];

$graph_client = new GraphClient($tenantid, $clientid, $clientsecret);
$response = $graph_client->getEvent();
echo "<pre>";
var_dump($response);
echo "</pre>";
?>

