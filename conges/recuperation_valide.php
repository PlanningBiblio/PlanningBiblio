<?php
/**
Planning Biblio, Plugin Congés Version 2.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/recuperation_valide.php
Création : 30 août 2013
Dernière modification : 21 avril 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de modifier et valider les demandes de récupérations des samedis (validation du formulaire)
*/

include "class.conges.php";

// Initialisation des variables
$CSRFToken = filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
$commentaires=trim(filter_input(INPUT_POST, "commentaires", FILTER_SANITIZE_STRING));
$heures=filter_input(INPUT_POST, "heures", FILTER_SANITIZE_STRING);
$refus=trim(filter_input(INPUT_POST, "refus", FILTER_SANITIZE_STRING));
$validation=filter_input(INPUT_POST, "validation", FILTER_SANITIZE_NUMBER_INT);

$admin=in_array(2, $_SESSION['droits'])?true:false;
$msg=urlencode("Une erreur est survenue lors de la validation de vos modifications.");
$msgType="error";

// Sécurité
if (!$admin and $perso_id!=$_SESSION['login_id']) { // Undefined $perso_id
  include_once "../include/accessDenied.php";
}

// Récupération des éléments
$c=new conges();
$c->recupId=$id;
$c->getRecup();
$recup=$c->elements[0];
$perso_id=$recup['perso_id'];

// Modification des heures
$update=array("heures"=>$heures,"commentaires"=>$commentaires,"modif"=>$_SESSION['login_id'],"modification"=>date("Y-m-d H:i:s"));

// Modification des heures  et validation par l'administrateur
if ($validation!==null and $admin) {
    $update['valide']=$validation;
    $update['validation']=date("Y-m-d H:i:s");
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
        $db->select("personnel", "recupSamedi", "id='$perso_id'");
        $solde_prec=$db->result[0]['recupSamedi'];
        $recupSamedi=$solde_prec+$update['heures'];
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("personnel", array("recupSamedi"=>$recupSamedi), array("id"=>$perso_id));
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("recuperations", array("solde_prec"=>$solde_prec,"solde_actuel"=>$recupSamedi), array("id"=>$id));
    }

    // Envoi d'un e-mail à l'agent et aux responsables
    $p=new personnel();
    $p->fetchById($perso_id);
    $nom=$p->elements[0]['nom'];
    $prenom=$p->elements[0]['prenom'];
    $mail=$p->elements[0]['mail'];
    $mailsResponsables=$p->elements[0]['mails_responsables'];

    $c->getResponsables($recup['date'], $recup['date'], $perso_id);
    $responsables=$c->responsables;

    if (isset($update['valide']) and $update['valide']>0) {
        $sujet="Demande de récupération validée";
        $message="Demande de récupération du ".dateFr($recup['date'])." validée pour $prenom $nom";
        $notifications=4;
    } elseif (isset($update['valide']) and $update['valide']<0) {
        $sujet="Demande de récupération refusée";
        $message="Demande de récupération du ".dateFr($recup['date'])." refusée pour $prenom $nom";
        $message.="<br/><br/>".str_replace("\n", "<br/>", $update['refus']);
        $notifications=4;
    } else {
        $sujet="Demande de récupération modifiée";
        $message="Demande de récupération du ".dateFr($recup['date'])." modifiée pour $prenom $nom";
        $notifications=2;
    }

    // Choix des destinataires en fonction de la configuration
    $a=new absences();
    $a->getRecipients($notifications, $responsables, $mail, $mailsResponsables);
    $destinataires=$a->recipients;

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

echo "<script type='text/JavaScript'>document.location.href='index.php?page=conges/recuperations.php&msg=$msg&msgType=$msgType&msg2=$msg2&msg2Type=$msg2Type';</script>\n";
