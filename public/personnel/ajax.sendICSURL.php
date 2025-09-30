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
Envoi par mail à l'agent sélectionné les URL de ses agendas Planno
Lors de la validation du formulaire "Envoi de l'URL de l'agenda Planning Biblio" accessible depuis l'onglet Agenda des fiches "agent"
Script appelé par $( "#ics-url-form" ).dialog({ Envoyer ]), public/js/agent.js
*/

ini_set("display_errors", 0);

// Includes
require_once(__DIR__ . '/../../init/init_ajax.php');
require_once(__DIR__ . '/../include/function.php');

// data: {recipient: recipient, subject: subject, message: message},

$CSRFToken = $request->get('CSRFToken');
$message = $request->get('message');
$recipient = $request->get('recipient');
$subject = $request->get('subject');

$message = trim($message);
$message = preg_replace("/(http:\/\/.*[^ \n])/", "<a href='$1' target='_blank'>$1</a>", $message);
$message = str_replace(array("\n","\r"), "<br/>", $message);

$recipient = filter_var($recipient, FILTER_SANITIZE_EMAIL);

// Envoi du mail
$m = new CJMail();
$m->subject = $subject;
$m->message = $message;
$m->to = $recipient;
$isSent = $m->send();

// retour vers la fonction JS
if ($m->error) {
    echo json_encode(array("error"=>$m->error));
} elseif (!$isSent) {
    echo json_encode(array("error"=>"Une erreur est survenue lors de l&apos;envoi du mail"));
} else {
    echo json_encode("ok");
}
