<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : personnel/ajax.sendICSURL.php
Création : 4 avril 2018
Dernière modification : 4 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Envoi par mail à l'agent sélectionné l'URL de son agenda Planning Biblio
Lors de la validation du formulaire "Envoi de l'URL de l'agenda Planning Biblio" accessible depuis l'onglet Agenda des fiches "agent"
Script appelé par $( "#ics-url-form" ).dialog({ Envoyer ]), personnel/js/modif.js
*/

ini_set("display_errors",0);

session_start();

// Includes
require_once __DIR__.'/../include/config.php';
require_once __DIR__.'/../include/function.php';

// data: {recipient: recipient, subject: subject, message: message},

$CSRFToken = filter_input(INPUT_POST, 'CSRFToken', FILTER_SANITIZE_STRING);
$recipient = filter_input(INPUT_POST, 'recipient', FILTER_SANITIZE_EMAIL);
$subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

$message = trim($message);
$message = preg_replace("/(http:\/\/.*[^ \n])/", "<a href='$1' target='_blank'>$1</a>", $message);
$message = str_replace(array("\n","\r"), "<br/>", $message);

// Envoi du mail
$m = new CJMail();
$m->subject = $subject;
$m->message = $message;
$m->to = $recipient;
$isSent = $m->send();

// retour vers la fonction JS
if($m->error){
  echo json_encode(array("error"=>$m->error));
}elseif(!$isSent){
  echo json_encode(array("error"=>"Une erreur est survenue lors de l&apos;envoi du mail"));
}else{
  echo json_encode("ok");
}
?>