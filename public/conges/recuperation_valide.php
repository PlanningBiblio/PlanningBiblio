<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/recuperation_valide.php
Création : 30 août 2013
Dernière modification : 30 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de modifier et valider les demandes de récupérations des samedis (validation du formulaire)
*/

include "class.conges.php";

use App\Model\Agent;

// Initialisation des variables
$entityManager = $GLOBALS['entityManager'];
$CSRFToken = filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
$commentaires=trim(filter_input(INPUT_POST, "commentaires", FILTER_SANITIZE_STRING));
$heures=filter_input(INPUT_POST, "heures", FILTER_SANITIZE_STRING);
$refus=trim(filter_input(INPUT_POST, "refus", FILTER_SANITIZE_STRING));
$validation=filter_input(INPUT_POST, "validation", FILTER_SANITIZE_NUMBER_INT);

list($hours, $minutes) = explode(':', $heures);
$heures = intVal($hours) + intVal($minutes) / 60;

$msg=urlencode("Une erreur est survenue lors de la validation de vos modifications.");
$msgType="error";

// Récupération des éléments
$c=new conges();
$c->recupId=$id;
$c->getRecup();
$recup=$c->elements[0];
$perso_id=$recup['perso_id'];

// Droits d'administration niveau 1 et niveau 2
list($adminN1, $adminN2) = $entityManager
    ->getRepository(Agent::class)
    ->setModule('holiday')
    ->getValidationLevelFor($_SESSION['login_id'], $perso_id);


// Modification des heures
$update=array("heures"=>$heures,"commentaires"=>$commentaires,"modif"=>$_SESSION['login_id'],"modification"=>date("Y-m-d H:i:s"));

// Modification des heures  et validation par l'administrateur
if ($validation!==null and $adminN1) {

  // Validation niveau 1
    if ($validation == 2 or $validation == -2) {
        $update['valide_n1'] = $validation / 2 * $_SESSION['login_id'] ;
        $update['validation_n1'] = date("Y-m-d H:i:s");
    }

    // Validation niveau 2
    if ($validation == 1 or $validation == -1) {
        $update['valide'] = $validation * $_SESSION['login_id'] ;
        $update['validation'] = date("Y-m-d H:i:s");
    }

    $update['refus']=$refus;
}

if (isset($update)) {
    // Modification de la table recuperations
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("recuperations", $update, array("id"=>$id));
    if (!$db->error) {
        $msg=urlencode("Vos modifications ont été enregistrées");
        $msgType="success";
    }

    // Modification du crédit d'heures de récupérations s'il y a validation
    if (isset($update['valide']) and $update['valide']>0) {
        $db=new db();
        $db->select("personnel", "comp_time", "id='$perso_id'");
        $solde_prec=$db->result[0]['comp_time'];
        $recup_update=$solde_prec+$update['heures'];
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("personnel", array("comp_time"=>$recup_update), array("id"=>$perso_id));
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("recuperations", array("solde_prec"=>$solde_prec,"solde_actuel"=>$recup_update), array("id"=>$id));
    }

    // Envoi d'un e-mail à l'agent et aux responsables
    $agent = $entityManager->find(Agent::class, $perso_id);
    $nom = $agent->nom();
    $prenom = $agent->prenom();

    if (isset($update['valide']) and $update['valide'] > 0) {
        $sujet = $lang['comp_time_subject_accepted'];
        $notifications = 4;
    } elseif (isset($update['valide']) and $update['valide'] < 0) {
        $sujet = $lang['comp_time_subject_refused'];
        $notifications = 4;
    } elseif (isset($update['valide_n1']) and $update['valide_n1'] > 0) {
        $sujet = $lang['comp_time_subject_accepted_pending'];
        $notifications = 3;
    } elseif (isset($update['valide_n1']) and $update['valide_n1'] < 0) {
        $sujet = $lang['comp_time_subject_refused_pending'];
        $notifications = 3;
    } else {
        $sujet="Demande de récupération modifiée";
        $notifications = 2;
    }
  
    $message = $sujet;
    $message .= "<br/><br/>\n";
    $message .= "Pour l'agent : $prenom $nom";
    $message .= "<br/>\n";
    $message .= "Date : ".dateFr($recup['date']);
    $message .= "<br/>\n";
    $message .= "Nombre d'heures : ".heure4($update['heures']);
    if ($update['commentaires']) {
        $message.="<br/><br/><u>Commentaires</u> :<br/>".str_replace("\n", "<br/>", $update['commentaires']);
    }
    if ($update['refus']) {
        $message.="<br/><br/><u>Motif du refus</u> :<br/>".str_replace("\n", "<br/>", $update['refus']);
    }

    // Choix des destinataires en fonction de la configuration
    if ($config['Absences-notifications-agent-par-agent']) {
        $a = new absences();
        $a->getRecipients2(null, $perso_id, $notifications, 600, $recup['date'], $recup['date']);
        $destinataires = $a->recipients;
    } else {
        $c->getResponsables($recup['date'], $recup['date'], $perso_id);
        $responsables = $c->responsables;

        $a = new absences();
        $a->getRecipients($notifications, $responsables, $agent, 'Recup');
        $destinataires = $a->recipients;
    }

    // Envoi du mail
    $m=new CJMail();
    $m->subject=$sujet;
    $m->message=$message;
    $m->to=$destinataires;
    $m->send();

    // Si erreur d'envoi de mail, affichage de l'erreur
    $msg2=null;
    $msg2Type=null;
    if ($m->error) {
        $msg2=urlencode($m->error_CJInfo);
        $msg2Type="error";
    }
}

echo "<script type='text/JavaScript'>document.location.href='/comp-time?msg=$msg&msgType=$msgType&msg2=$msg2&msg2Type=$msg2Type';</script>\n";
