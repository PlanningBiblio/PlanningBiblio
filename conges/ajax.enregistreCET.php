<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.enregistreCet.php
Création : 7 mars 2014
Dernière modification : 30 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre la demande de récupération
*/

include_once(__DIR__.'/../init_ajax.php');
include "class.conges.php";

$commentaires=filter_input(INPUT_GET, "commentaires", FILTER_SANITIZE_STRING);
$CSRFToken = filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$jours=filter_input(INPUT_GET, "jours", FILTER_SANITIZE_NUMBER_FLOAT);
$perso_id=filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);
$validation=filter_input(INPUT_GET, "validation", FILTER_SANITIZE_NUMBER_INT);

$isValidate=false;
$annee=date("Y")+1;

$data=array("perso_id"=>$perso_id,"jours"=>$jours,"commentaires"=>$commentaires);

// Si pas d'id, il s'git d'une demande, on ajoute l'annee pour laquelle le CET est demandé
if (!$id) {
    $data["annee"]=$annee;
}

switch ($validation) {
  case -2: $data['valide_n2']=-$_SESSION['login_id']; $data['validation_n2']=date("Y-m-d H:i:s"); break;
  case -1: $data['valide_n1']=-$_SESSION['login_id']; $data['validation_n1']=date("Y-m-d H:i:s"); break;
  case 1: $data['valide_n1']= $_SESSION['login_id']; $data['validation_n1']=date("Y-m-d H:i:s"); break;
  case 2: $data['valide_n2']= $_SESSION['login_id']; $data['validation_n2']=date("Y-m-d H:i:s"); $isValidate=true; break;
}

if (is_numeric($id)) {
    // Si la demande a déjà été validée, on interdit la modification
    $c=new conges();
    $c->id=$id;
    $c->getCET();
    if ($c->elements[0]['valide_n2']==0) {
        // Modifie la demande d'alimentation du CET
        $data["modif"]=$_SESSION['login_id'];
        $data["modification"]=date("Y-m-d H:i:s");

        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("conges_cet", $data, array("id"=>$id));
        if ($isValidate) {
            // Mise à jour du compteur personnel/reliquat
            $heures=$data['jours']*7;
            $db=new dbh();
            $db->CSRFToken = $CSRFToken;
            $db->prepare("UPDATE `{$dbprefix}personnel` SET `conges_reliquat`=(`conges_reliquat`-:heures) WHERE `id`=:id;");
            $db->execute(array(":heures"=>$heures,":id"=>$id));

            // Mise à jour du compteur conges_cet / solde_prec
            $db=new dbh();
            $db->CSRFToken = $CSRFToken;
            $db->prepare("SELECT `solde_actuel` FROM `{$dbprefix}conges_cet` WHERE `annee`=:annee AND `valide_n2`>0 
	  AND `validation_n2`=MAX(`validation_n2`) AND `perso_id`=:perso_id;");
            $db->execute(array(":annee"=>$annee,":perso_id"=>$id));
            $solde_prec=$db->result[0]['solde_actuel'];

            $c=new conges();
            $c->data=$data;
            $c->updateCETCredits();


            // Mise à jour des compteurs conges_cet / solde_actuel et solde_prec
            // A CONTINUER init :solde_actuel, solde_prec
            $db=new dbh();
            $db->CSRFToken = $CSRFToken;
            $db->prepare("UPDATE `{$dbprefix}conges_cet` SET `solde_actuel`=:solde_actuel, `solde_prec`=:solde_prec
	WHERE `annee`=:annee AND `valide_n2`>0 AND `validation_n2`=MAX(`validation_n2`) AND `perso_id`=:perso_id);");
            $db->execute(array(":annee"=>$annee,":perso_id"=>$id));
            // TODO : Mettre à jour les compteurs conges_cet/solde_prec et solde_actuel
        }
    }
} else {
    // Enregistrement de la demande d'alimentation du CET
    $data["saisie"]=date("Y-m-d H:i:s");
    $data["saisie_par"]=$_SESSION['login_id'];

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("conges_cet", $data);
    if ($isValidate) {
        // TODO : Mettre à jour les compteurs
        $c=new conges();
        $c->data=$data;
        $c->updateCETCredits();
    }
}

if ($db->error) {
    echo "###Demande-Erreur###";
} else {
    echo "###Demande-OK###";

    // Envoi d'un e-mail à l'agent et aux responsables
    $p=new personnel();
    $p->fetchById($perso_id);
    $nom=$p->elements[0]['nom'];
    $prenom=$p->elements[0]['prenom'];
    $mailsResponsables=$p->elements[0]['mails_responsables'];

    if ($config['Absences-notifications-agent-par-agent']) {
        $a = new absences();
        $a->getRecipients2(null, $perso_id, 1);
        $destinataires = $a->recipients;
    } else {
        $c = new conges();
        $c->getResponsables(null, null, $perso_id);
        $responsables = $c->responsables;

        // Choix des destinataires en fonction de la configuration
        $a = new absences();
        $a->getRecipients(1, $responsables, $perso_id, $mailsResponsables);
        $destinataires = $a->recipients;
    }

    if (!empty($destinataires)) {
        $sujet="Nouvelle demande de CET";
        $message="Une nouvelle demande de CET a été enregistrée pour $prenom $nom<br/><br/>";
        if ($commentaires) {
            $message.="Commentaires : ".str_replace("\n", "<br/>", $commentaires);
        }
        // Envoi du mail
        $m=new CJMail();
        $m->subject=$sujet;
        $m->message=$message;
        $m->to=$destinataires;
        $m->send();
    }
}
