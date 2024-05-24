<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.enregistreRecup.php
Création : 11 octobre 2013
Dernière modification : 30 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre la demande de récupération
*/

require_once(__DIR__ . '/../../init/init_ajax.php');
include('class.conges.php');

use App\Model\Agent;
use App\Model\OverTime;

// Initialisation des variables
$commentaires = $request->get('commentaires');
$CSRFToken = $request->get('CSRFToken');
$heures = $request->get('heures');
$date = $request->get('date');
$date2 = $request->get('date2');
$perso_id = $request->get('perso_id');

$date = filter_var($date, FILTER_CALLBACK, array('options' => 'sanitize_dateFr'));
$date2 = filter_var($date2, FILTER_CALLBACK, array('options' => 'sanitize_dateFr'));
$perso_id = filter_var($perso_id, FILTER_SANITIZE_NUMBER_INT);

list($hours, $minutes) = explode(':', $heures);
$heures = intVal($hours) + intVal($minutes) / 60;

// Les dates sont au format DD/MM/YYYY et converti en YYYY-MM-DD
$date=dateSQL($date);
$date2=dateSQL($date2);

if ($perso_id===null) {
    $perso_id=$_SESSION['login_id'];
}

$insert=array("perso_id"=>$perso_id,"date"=>$date,"date2"=>$date2,"heures"=>$heures,"commentaires"=>$commentaires,
  "saisie_par"=>$_SESSION['login_id']);
$db=new db();
$db->CSRFToken = $CSRFToken;
$db->insert("recuperations", $insert);
if ($db->error) {
    $return=array("Demande-Erreur");
    echo json_encode($return);
    exit;
} else {
    $return=array("Demande-OK");

    // Envoi d'un e-mail à l'agent et aux responsables
    $agent = $entityManager->find(Agent::class, $perso_id);
    $nom = $agent->nom();
    $prenom = $agent->prenom();

    if ($config['Absences-notifications-agent-par-agent']) {
        $a = new absences();
        $a->getRecipients2(null, $perso_id, 1);
        $destinataires = $a->recipients;
    } else {
        $c = new conges();
        $c->getResponsables($date, $date, $perso_id);
        $responsables = $c->responsables;

        // Choix des destinataires en fonction de la configuration
        $a = new absences();
        $a->getRecipients(1, $responsables, $agent, 'Recup');
        $destinataires = $a->recipients;
    }

    if (!empty($destinataires)) {
        $sujet="Nouvelle demande d'heures supplémentaires";
        $message="Demande d'heures supplémentaires du ".dateFr($date)." enregistrée pour $prenom $nom<br/><br/>";
        if ($commentaires) {
            $message.="Commentaires : ".str_replace("\n", "<br/>", $commentaires);
        }

        // ajout d'un lien permettant de rebondir sur la demande
        $overtime = $entityManager->getRepository(OverTime::class)->findOneBy(
            array(
                'perso_id' => $perso_id,
                'date' => DateTime::createFromFormat('Y-m-d', $date),
                'saisie_par' => $_SESSION['login_id'],
            ),
            array(
                'id' => 'desc'
            ),
        );

        $url = $config['URL'] . '/overtime/' . $overtime->id();
        $message.="<p>Lien vers la demande d'heures supplémentaires :<br/><a href='$url'>$url</a></p>";

        // Envoi du mail
        $m=new CJMail();
        $m->subject=$sujet;
        $m->message=$message;
        $m->to=$destinataires;
        $m->send();
    
        $return[]=$m->error_CJInfo;
    }

    echo json_encode($return);
}
