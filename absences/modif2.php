<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : absences/modif2.php
Création : mai 2011
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Page validant la modification d'une absence : enregistrement dans la BDD des modifications

Page appelée par la page index.php
Page d'entrée : absences/modif.php
*/


require_once "class.absences.php";
require_once "personnel/class.personnel.php";

// Initialisation des variables
$commentaires=trim(filter_input(INPUT_GET,"commentaires",FILTER_SANITIZE_STRING));
$CSRFToken=trim(filter_input(INPUT_GET,"CSRFToken",FILTER_SANITIZE_STRING));
$debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$hre_debut=filter_input(INPUT_GET,"hre_debut",FILTER_CALLBACK,array("options"=>"sanitize_time"));
$hre_fin=filter_input(INPUT_GET,"hre_fin",FILTER_CALLBACK,array("options"=>"sanitize_time_end"));
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$motif=filter_input(INPUT_GET,"motif",FILTER_SANITIZE_STRING);
$motif_autre=trim(filter_input(INPUT_GET,"motif_autre",FILTER_SANITIZE_STRING));
$nbjours=filter_input(INPUT_GET,"nbjours",FILTER_SANITIZE_NUMBER_INT);
$valide=filter_input(INPUT_GET,"valide",FILTER_SANITIZE_NUMBER_INT);
$groupe=filter_input(INPUT_GET,"groupe",FILTER_SANITIZE_STRING);

$motif = htmlentities($motif, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);

// perso_ids est un tableau de 1 ou plusieurs ID d'agent. Complété même si l'absence ne concerne qu'une personne
$perso_ids=$_GET['perso_ids'];
$perso_ids=filter_var_array($perso_ids,FILTER_SANITIZE_NUMBER_INT);

// Création du groupe si plusieurs agents et que le groupe n'est pas encore créé
if(count($perso_ids)>1 and !$groupe){
  // ID du groupe (permet de regrouper les informations pour affichage en une seule ligne et modification du groupe)
  $groupe=time()."-".rand(100,999);
}

// Pièces justificatives
$pj1=filter_input(INPUT_GET,"pj1",FILTER_CALLBACK,array("options"=>"sanitize_on01"));
$pj2=filter_input(INPUT_GET,"pj2",FILTER_CALLBACK,array("options"=>"sanitize_on01"));
$so=filter_input(INPUT_GET,"so",FILTER_CALLBACK,array("options"=>"sanitize_on01"));

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);
$debut_sql=$debutSQL." ".$hre_debut;
$fin_sql=$finSQL." ".$hre_fin;

$isValidate=true;
$valideN1=0;
$valideN2=0;
$validationN1=null;
$validationN2=null;

$nbjours=$nbjours?$nbjours:0;
$valide=$valide?$valide:0;

if($config['Absences-validation']){
  if($valide==1 or $valide==-1){
    $valideN2=$valide*$_SESSION['login_id'];
    $validationN2=date("Y-m-d H:i:s");
  }
  elseif($valide==2 or $valide==-2){
    $valideN1=($valide/2)*$_SESSION['login_id'];
    $validationN1=date("Y-m-d H:i:s");
  }
  $isValidate=$valideN2>0?true:false;
}


// Récupération des informations des agents concernés par l'absence avant sa modification
// ET autres informations concernant l'absence avant modification
$a=new absences();
$a->fetchById($id);
$agents=$a->elements['agents'];
$debut1=$a->elements['debut'];
$fin1=$a->elements['fin'];
$perso_ids1=$a->elements['perso_ids'];
$valide1N1=$a->elements['valide_n1'];
$valide1N2=$a->elements['valide_n2'];

// Si l'absence est importée depuis un agenda extérieur, on interdit la modification
$iCalKey=$a->elements['ical_key'];
if($iCalKey){
  include "include/accessDenied.php";
}

// Récuperation des informations des agents concernés par l'absence après sa modification (agents sélectionnés)
$p=new personnel();
$p->fetchById($perso_ids);
$agents_selectionnes=$p->elements;

// Tous les agents concernés (ajoutés, supprimés, restants)
$agents_tous=array();
foreach($agents as $elem){
  if(!array_key_exists($elem['perso_id'],$agents_tous)){
    $agents_tous[$elem['perso_id']]=$elem;
  }
}
foreach($agents_selectionnes as $elem){
  if(!array_key_exists($elem['id'],$agents_tous)){
    $elem['perso_id']=$elem['id'];
    $agents_tous[$elem['id']]=$elem;
  }
}

// Les agents supprimés de l'absence
$agents_supprimes=array();
foreach($agents as $elem){
  if(!array_key_exists($elem['perso_id'],$agents_selectionnes)){
    $agents_supprimes[$elem['perso_id']]=$elem;
  }
}

// Les agents ajoutés
$agents_ajoutes=array();
foreach($agents_selectionnes as $elem){
  if(!in_array($elem['id'],$perso_ids1)){
    $agents_ajoutes[]=$elem;
  }
}

// Sécurité
// Droit 1 = modification de toutes les absences (admin seulement)
// Droit 6 = modification de ses propres absences
// Droits 20x = modification de toutes les absences en multisites (admin seulement)

$acces=in_array(1,$droits)?true:false;
if(!$acces){
  if(is_array($perso_ids) and count($perso_ids) == 1){
    $acces=(in_array(6,$droits) and $perso_ids[0] == $_SESSION['login_id'] and !$groupe)?true:false;
  }
}
if(!$acces){
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
  include "include/footer.php";
  exit;
}

// Définition des droits d'accès pour les administrateurs en multisites
// Multisites, ne pas modifier les absences si aucun agent n'appartient à un site géré
if($config['Multisites-nombre']>1){
  // $sites_agents comprend l'ensemble des sites en lien avec les agents concernés par cette modification d'absence
  $sites_agents=array();
  foreach($agents_tous as $elem){
    if(is_array($elem['sites'])){
      foreach($elem['sites'] as $site){
        if(!in_array($site,$sites_agents)){
          $sites_agents[]=$site;
        }
      }
    }
  }

  $sites=array();
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    if(in_array((200+$i),$droits)){
      $sites[]=$i;
    }
  }

  $admin=false;
  foreach($sites_agents as $site){
    if(in_array($site,$sites)){
      $admin=true;
      break;
    }
  }
  if(!$admin and !$acces){
    echo "<h3>Modification de l'absence</h3>\n";
    echo "Vous n'êtes pas autorisé(e) à modifier cette absence.<br/><br/>\n";
    echo "<a href='index.php?page=absences/voir.php'>Retour à la liste des absences</a><br/><br/>\n";
    include "include/footer.php";
    exit;
  }
}else{
  $admin=in_array(1,$droits)?true:false;
}

// Mise à jour du champs 'absent' dans 'pl_poste'
// Suppression du marquage absent pour tous les agents qui étaient concernés par l'absence avant sa modification
// Comprend les agents supprimés et ceux qui restent
/**
 * @note : le champ pl_poste.absent n'est plus mis à 1 lors de la validation des absences depuis la version 2.4
 * mais nous devons garder la mise à 0 pour la suppresion ou modifications des absences enregistrées avant cette version.
 * NB : le champ pl_poste.absent est également utilisé pour barrer les agents depuis le planning, donc on ne supprime pas toutes ses valeurs
 */
$ids=implode(",",$perso_ids1);
$db=new db();
$debut1=$db->escapeString($debut1);
$fin1=$db->escapeString($fin1);
$ids=$db->escapeString($ids);
$req="UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
  CONCAT(`date`,' ',`debut`) < '$fin1' AND CONCAT(`date`,' ',`fin`) > '$debut1'
  AND `perso_id` IN ($ids)";
$db->query($req);


// Préparation des données pour mise à jour de la table absence et insertion pour les agents ajoutés
$data=array("motif"=>$motif, "motif_autre"=>$motif_autre, "nbjours"=>$nbjours, "commentaires"=>$commentaires, "debut"=>$debut_sql, "fin"=>$fin_sql, "groupe"=>$groupe);

if($admin){
  $data=array_merge($data,array("pj1"=>$pj1, "pj2"=>$pj2, "so"=>$so));
}

if($config['Absences-validation']){
  // Validation N1
  if($valideN1){
    $data["valide_n1"]=$valideN1;
    $data["validation_n1"]=$validationN1;
  }
  // Validation N2
  if($valideN2){
    $data["valide"]=$valideN2;
    $data["validation"]=$validationN2;
  }
  // Retour à l'état demandé
  if($valide==0){
    $data["valide"]=0;
    $data["valide_n1"]=0;
    $data["validation"]="0000-00-00 00:00:00";
    $data["validation_n1"]="0000-00-00 00:00:00";
  }
}

// Mise à jour de la table 'absences'
// Sélection des lignes à modifier dans la base à l'aide du champ id car fonctionne également si le groupe n'existait pas au départ contrairement au champ groupe
// (dans le cas d'une absence simple ou absence simple transformée en absence multiple).
// Récupération de tous les ids de l'absence avant modification
$ids=array();
foreach($agents as $agent){
  $ids[]=$agent['absence_id'];
}
$ids=implode(",",$ids);
$where=array("id"=>"IN $ids");

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->update2("absences",$data,$where);


// Ajout de nouvelles lignes dans la table absences si des agents ont été ajoutés
$insert=array();
foreach($agents_ajoutes as $agent){
  $insert[]=array_merge($data, array('perso_id'=>$agent['id']));
}
if(!empty($insert)){
  $db=new db();
  $db->CSRFToken = $CSRFToken;
  $db->insert2("absences",$insert);
}


// Suppresion des lignes de la table absences concernant les agents supprimés
$agents_supprimes_ids=array();
foreach($agents_supprimes as $agent){
  $agents_supprimes_ids[]=$agent['perso_id'];
}
$agents_supprimes_ids=implode(",",$agents_supprimes_ids);

$db=new db();
$db->delete2("absences",array("id"=>"IN $ids", "perso_id"=>"IN $agents_supprimes_ids"));


// Envoi d'un mail de notification
$sujet="Modification d'une absence";

// Choix des destinataires des notifications selon le degré de validation
// Si pas de validation, la notification est envoyée au 1er groupe
if($config['Absences-validation']=='0'){
  $notifications=2;
}
else{
  if($valide1N2<=0 and $valideN2>0){
    $sujet="Validation d'une absence";
    $notifications=4;
  }
  elseif($valide1N2>=0 and $valideN2<0){
    $sujet="Refus d'une absence";
    $notifications=4;
  }
  elseif($valide1N1<=0 and $valideN1>0){
    $sujet="Acceptation d'une absence (en attente de validation hiérarchique)";
    $notifications=3;
  }
  elseif($valide1N1>=0 and $valideN1<0){
    $sujet="Refus d'une absence (en attente de validation hiérarchique)";
    $notifications=3;
  }
  else{
    $sujet="Modification d'une absence";
    $notifications=2;
  }
}

// Liste des responsables
// Pour chaque agent, recherche des responsables absences 
$responsables=array();
foreach($agents_tous as $agent){
  $a=new absences();
  $a->getResponsables($debutSQL,$finSQL,$agent['perso_id']);
  $responsables=array_merge($responsables,$a->responsables);
}

// Pour chaque agent, recherche des destinataires de notification en fonction de la config. (responsables absences, responsables directs, agent).
$destinataires=array();
foreach($agents_tous as $agent){
  $a=new absences();
  $a->getRecipients($notifications,$responsables,$agent['mail'],$agent['mails_responsables']);
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

// Recherche des plages de SP concernées pour ajouter cette information dans le mail.
$a=new absences();
$a->debut=$debut_sql;
$a->fin=$fin_sql;
$a->perso_ids=$perso_ids;
$a->infoPlannings();
$infosPlanning=$a->message;

// Message
usort($agents_selectionnes,"cmp_prenom_nom");
usort($agents_supprimes,"cmp_prenom_nom");

$message="<b><u>$sujet</u></b> :";
$message.="<ul><li>";
if((count($agents_selectionnes) + count($agents_supprimes)) >1){
  $message.="Agents :<ul>\n";
  foreach($agents_selectionnes as $agent){
    $message.="<li><strong>{$agent['prenom']} {$agent['nom']}</strong></li>\n";
  }
  foreach($agents_supprimes as $agent){
    $message.="<li><span class='striped'>{$agent['prenom']} {$agent['nom']}</span></li>\n";
  }
  $message.="</ul>\n";
}else{
  $message.="Agent : <strong>{$agents_selectionnes[0]['prenom']} {$agents_selectionnes[0]['nom']}</strong>\n";
}
$message.="</li>\n";

$message.="<li>Début : <strong>$debut";
if($hre_debut!="00:00:00")
  $message.=" ".heure3($hre_debut);
$message.="</strong></li><li>Fin : <strong>$fin";
if($hre_fin!="23:59:59")
  $message.=" ".heure3($hre_fin);
$message.="</strong></li><li>Motif : $motif";
if($motif_autre){
  $message.=" / $motif_autre";
}
$message.="</li>";

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

  $message.="<li>Validation : <br/>\n";
  $message.=$validationText;
  $message.="</li>\n";
}

if($commentaires){
  $message.="<li>Commentaire:<br/>$commentaires</li>";
}
$message.="</ul>";

// Ajout des informations sur les plannings
$message.=$infosPlanning;

// Ajout du lien permettant de rebondir sur l'absence
$url=createURL("absences/modif.php&id=$id");
$message.="<br/><br/>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a><br/><br/>";

// Envoi du mail
$m=new CJMail();
$m->subject=$sujet;
$m->message=$message;
$m->to=$destinataires;
$m->send();

// Si erreur d'envoi de mail, affichage de l'erreur
$msg2=null;
$msg2Type=null;
if($m->error){
  $msg2=urlencode($m->error_CJInfo);
  $msg2Type="error";
}

  
$msg=urlencode("L'absence a été modifiée avec succés");
echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&msg=$msg&msgType=success&msg2=$msg2&msg2Type=$msg2Type';</script>\n";
?>