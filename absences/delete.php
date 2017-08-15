<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : absences/delete.php
Création : mai 2011
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de supprimer une absence : confirmation et suppression.

Page appelée par la page index.php après avoir cliqué sur l'icône supprimer de la page absences/modif.php
*/

require_once "class.absences.php";

// Initialisation des variables
$CSRFToken = filter_input(INPUT_GET,"CSRFToken",FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$errors=array();
// 
$a=new absences();
$a->fetchById($id);
$debut=$a->elements['debut'];
$fin=$a->elements['fin'];
$perso_id=$a->elements['perso_id'];
$motif=$a->elements['motif'];
$commentaires=$a->elements['commentaires'];
$valideN1=$a->elements['valide_n1'];
$valideN2=$a->elements['valide_n2'];
$groupe=$a->elements['groupe'];
$agents=$a->elements['agents'];

// Sécurité
// Droit 1 = modification de toutes les absences
// Droit 6 = modification de ses propres absences
$acces=in_array(1,$droits)?true:false;
if(!$acces){
  $acces=(in_array(6,$droits) and $perso_id==$_SESSION['login_id'] and !$groupe)?true:false;
}

if(!$acces){
  $msg=urlencode("Suppression refusée");
  echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&msg=$msg&msgType=error';</script>\n";
  exit;
}

// Envoi d'un mail à l'agent et aux responsables
$message="<b><u/>Suppression d'une absence</u></b> : \n";

if(count($agents)>1){
  $message.="<br/><br/>Agents :<ul>\n";
  foreach($agents as $agent){
    $message.="<li>{$agent['prenom']} {$agent['nom']}</li>\n";
  }
  $message.="</ul>\n";
}else{
  $message.="<br/><br/>Agent : {$agents[0]['prenom']} {$agents[0]['nom']}<br/><br/>\n";
}

$message.="Début : ".dateFr($debut);
$hre_debut=substr($debut,-8);
$hre_fin=substr($fin,-8);
if($hre_debut!="00:00:00")
  $message.=" ".heure3($hre_debut);
$message.="<br/>Fin : ".dateFr($fin);
if($hre_fin!="23:59:59")
  $message.=" ".heure3($hre_fin);
$message.="<br/><br/>Motif : $motif<br/>";

if($config['Absences-validation']){
  $validationText="Demand&eacute;e";
  if($valideN2>0){
    $validationText="Valid&eacute;e";
  }
  elseif($valideN2<0){
    $validationText="Refus&eacute;e";
  }
  elseif($valideN1>0){
    $validationText="Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
  }
  elseif($valideN1<0){
    $validationText="Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
  }

  $message.="<br/>Validation pr&eacute;c&eacute;dente : <br/>\n";
  $message.=$validationText;
  $message.="<br/>\n";
}

if($commentaires){
  $message.="<br/>Commentaire:<br/>$commentaires<br/>";
}

// Pour chaque agent, recherche des responsables absences 
$responsables=array();
foreach($agents as $agent){
  $a=new absences();
  $a->getResponsables($debut,$fin,$agent['perso_id']);
  $responsables=array_merge($responsables,$a->responsables);
}

// Pour chaque agent, recherche des destinataires de notification en fonction de la config. (responsables absences, responsables directs, agent).
$destinataires=array();
foreach($agents as $agent){
  $a=new absences();
  $a->getRecipients(2,$responsables,$agent['mail'],$agent['mails_responsables']);
  $destinataires=array_merge($destinataires,$a->recipients);
}

// Suppresion des doublons dans les destinataires
$tmp=array();
foreach($destinataires as $elem){
  if(!in_array($elem,$tmp)){
    $tmp[]=$elem;
  }
}
$destinataires=$tmp;

// Envoi du mail
$m=new CJMail();
$m->subject="Suppression d'une absence";
$m->message=$message;
$m->to=$destinataires;
$m->send();

// Si erreur d'envoi de mail, affichage de l'erreur
if($m->error){
  $errors[]=$m->error_CJInfo;
}

// Mise à jour du champs 'absent' dans 'pl_poste'
/**
 * @note : le champ pl_poste.absent n'est plus mis à 1 lors de la validation des absences depuis la version 2.4
 * mais nous devons garder la mise à 0 pour la suppresion des absences enregistrées avant cette version
 * NB : le champ pl_poste.absent est également utilisé pour barrer les agents depuis le planning, donc on ne supprime pas toutes ses valeurs
 */
foreach($agents as $agent){
  $db=new db();
  $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
    CONCAT(`date`,' ',`debut`) < '$fin' AND CONCAT(`date`,' ',`fin`) > '$debut'
    AND `perso_id`='{$agent['perso_id']}'";
  $db->query($req);
}

// suppression dans la table 'absences'
if($groupe){
  $db=new db();
  $db->CSRFToken = $CSRFToken;
  $db->delete("absences",array("groupe"=>$groupe));
}else{
  $db=new db();
  $db->CSRFToken = $CSRFToken;
  $db->delete("absences",array("id"=>$id));
}

$msg=urlencode("L'absence a été supprimée avec succès");
$msgType="success";

if(!empty($errors)){
  $msg2="<ul>";
  foreach($errors as $error){
    $msg2.="<li>$error</li>";
  }
  $msg2.="</ul>";
  $msg2=urlencode($msg2);
  $msg2Type="error";
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&msg=$msg&msgType=$msgType&msg2=$msg2&msg2Type=$msg2Type';</script>\n";
?>