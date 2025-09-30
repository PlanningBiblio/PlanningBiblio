<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/ajax.appelDispoMail.php
Création : 22 décembre 2015
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Envoi un mail aux agents disponibles pour l'occupation d'un poste vacant.
Lors de la validation du formulaire "Appel à disponibilité"
Script appelé par $( "#pl-appelDispo-form" ).dialog({ Envoyer ]), public/js/planning.js
*/

ini_set("display_errors", 0);

// Includes
require_once(__DIR__ . '/../../../init/init_ajax.php');
require_once(__DIR__ . '/../../include/function.php');

$CSRFToken = $request->get('CSRFToken');
$site = $request->get('site');
$poste = $request->get('poste');
$date = $request->get('date');
$debut = $request->get('debut');
$fin = $request->get('fin');
$agents = $request->get('agents');
$sujet = $request->get('sujet');
$message = $request->get('message');

$agents=html_entity_decode($agents, ENT_QUOTES|ENT_IGNORE, "UTF-8");
$agents=json_decode($agents, true);

$message=str_replace(array("\n","\r"), "<br/>", $message);

if (!is_array($agents)) {
    return;
}

// Récupération des destinataires
$destinataires=array();
foreach ($agents as $elem) {
    $destinataires[]=$elem['mail'];
}

// Envoi du mail
$m=new CJMail();
$m->subject=$sujet;
$m->message=$message;
$m->to=$destinataires;
$isSent=$m->send();

// Enregistrement dans la base de données pour signaler que l'envoi a eu lieu
if ($isSent) {
    $successAddresses=implode(";", $m->successAddresses);
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("appel_dispo", array( "site"=>$site, "poste"=>$poste, "date"=>$date, "debut"=>$debut, "fin"=>$fin,
    "destinataires"=>$successAddresses, "sujet"=>$sujet, "message"=>$message));
}

// retour vers la fonction JS
if ($m->error) {
    echo json_encode(array("error"=>$m->error));
} elseif (!$isSent) {
    echo json_encode(array("error"=>"Une erreur est survenue lors de l&apos;envoi du mail"));
} else {
    echo json_encode("ok");
}
