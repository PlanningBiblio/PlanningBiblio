<?php
/**
Planning Biblio, Version 2.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ldap/ajax.mailtest.php
Création : 7 mars 2017
Dernière modification : 7 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de tester les paramètres de messageire saisis sur la page administration / configuration
Script appelé par la fonction JS ldapmail() (admin/js/config?js) lors du click sur le bouton "test" de la page administration / configuration / Messagerie
*/

session_start();

include_once "../include/config.php";

$mailSmtp = filter_input(INPUT_POST, 'mailSmtp', FILTER_SANITIZE_STRING);
$wordwrap = filter_input(INPUT_POST, 'wordwrap', FILTER_SANITIZE_STRING);
$hostname = filter_input(INPUT_POST, 'hostname', FILTER_SANITIZE_STRING);
$host = filter_input(INPUT_POST, 'host', FILTER_SANITIZE_STRING);
$port = filter_input(INPUT_POST, 'port', FILTER_SANITIZE_STRING);
$secure = filter_input(INPUT_POST, 'secure', FILTER_SANITIZE_STRING);
$auth = filter_input(INPUT_POST, 'auth', FILTER_SANITIZE_STRING);
$auth = filter_input(INPUT_POST, 'auth', FILTER_SANITIZE_STRING);
$user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
$fromMail = filter_input(INPUT_POST, 'fromMail', FILTER_SANITIZE_STRING);
$fromName = filter_input(INPUT_POST, 'fromName', FILTER_SANITIZE_STRING);
$signature = filter_input(INPUT_POST, 'signature', FILTER_SANITIZE_STRING);
$planning = filter_input(INPUT_POST, 'planning', FILTER_SANITIZE_STRING);

  
  
// Connexion au serveur de messagerie
if ($fp=@fsockopen($host, $port, $errno, $errstr, 5)) {
    $config['Mail-IsEnabled'] = 1;
    $config['Mail-IsMail-IsSMTP'] = $mailSmtp;
    $config['Mail-WordWrap'] = $wordwrap;
    $config['Mail-Hostname'] = $hostname;
    $config['Mail-Host'] = $host;
    $config['Mail-Port'] = $port;
    $config['Mail-SMTPSecure'] = $secure;
    $config['Mail-SMTPAuth'] = $auth;
    $config['Mail-Username'] = $user;
    $config['Mail-Password'] = encrypt($password);
    $config['Mail-From'] = $fromMail;
    $config['Mail-FromName'] = $fromName;
    $config['Mail-Signature'] = $signature;
    $config['Mail-Planning'] = $planning;
  
    include_once "../include/function.php";

    $m=new CJMail();
    $m->subject="Message de test, Planning Biblio";
    $m->message="Message de test, Planning Biblio<br/><br/>La messagerie de votre application Planning Biblio est correctement param&eacute;tr&eacute;e.";
    $m->to=$planning;
    $m->send();
  
    if ($m->error) {
        echo json_encode($m->error_CJInfo);
        exit;
    } else {
        echo json_encode('ok');
        exit;
    }
} else {
    echo json_encode('socket');
    exit;
}

echo json_encode('error');
