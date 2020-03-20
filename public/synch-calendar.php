<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/../config/client_secret.json');
$client->addScope(Google_Service_Calendar::CALENDAR_READONLY);
$client->setRedirectUri('https://graph-planningb.test.biblibre.eu/synch-calendar.php');

echo "<h1>Hello</h1>";

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $token = $client->fetchAccessTokenWithAuthCode($code);
    echo "Code: $code";

    $_SESSION['token'] = $token;
    header('Location: ' . 'https://graph-planningb.test.biblibre.eu/synch-calendar.php', FILTER_SANITIZE_URL);
} 

if (!empty($_SESSION['token']) && $client->isAccessTokenExpired()) {
    unset($_SESSION['token']);
}
 
if (!empty($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
} else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
}
