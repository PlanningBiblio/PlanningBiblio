<?php
/**
Planning Biblio, Version 2.7.04
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : absences/ajouter.php
Création : mai 2011
Dernière modification : 1er novembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Farid Goara <farid.goara@u-pem.fr>

Description : 
Permet d'ajouter une absence. Formulaire, confirmation et validation.
la table absence est complétée.

Page appelée par la page index.php
*/

require_once "class.absences.php";
require_once "personnel/class.personnel.php";
require_once "motifs.php";

//	Initialisation des variables
$commentaires=trim(filter_input(INPUT_GET,"commentaires",FILTER_SANITIZE_STRING));
$confirm=filter_input(INPUT_GET,"confirm",FILTER_SANITIZE_NUMBER_INT);
$CSRFToken=trim(filter_input(INPUT_GET,"CSRFToken",FILTER_SANITIZE_STRING));
$debut=filter_input(INPUT_GET,"debut",FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_GET,"fin",FILTER_SANITIZE_STRING);
$hre_debut=filter_input(INPUT_GET,"hre_debut",FILTER_SANITIZE_STRING);
$hre_fin=filter_input(INPUT_GET,"hre_fin",FILTER_SANITIZE_STRING);
$motif=filter_input(INPUT_GET,"motif",FILTER_SANITIZE_STRING);
$motif_autre=trim(filter_input(INPUT_GET,"motif_autre",FILTER_SANITIZE_STRING));
$nbjours=filter_input(INPUT_GET,"nbjours",FILTER_SANITIZE_NUMBER_INT);
$perso_id=filter_input(INPUT_GET,"perso_id",FILTER_SANITIZE_NUMBER_INT);
$rrule=filter_input(INPUT_GET,"recurrence-hidden",FILTER_SANITIZE_STRING);
$rcheckbox=filter_input(INPUT_GET,"recurrence-checkbox",FILTER_SANITIZE_NUMBER_INT);
$valide=filter_input(INPUT_GET,"valide",FILTER_SANITIZE_NUMBER_INT);

// Contrôle sanitize_dateFr en 2 temps pour éviter les erreurs CheckMarx
$debut=filter_var($debut,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$hre_debut=filter_var($hre_debut,FILTER_CALLBACK,array("options"=>"sanitize_time"));
$hre_fin=filter_var($hre_fin,FILTER_CALLBACK,array("options"=>"sanitize_time_end"));

$motif = htmlentities($motif, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);

$perso_ids=array();
// Absence unique
if(!empty($perso_id)){
  $perso_ids[]=$perso_id;
}

// Absences multiples
if(isset($_GET["perso_ids"])){
  $perso_ids_get=filter_var_array($_GET["perso_ids"],FILTER_SANITIZE_NUMBER_INT);
  if(is_array($perso_ids_get)){
    $tmp=array();
    foreach($perso_ids_get as $elem){
      if($elem){
	$perso_ids[]=(int) $elem;
      }
    }
  }
}

// Pièces justificatives
$pj1=filter_input(INPUT_GET,"pj1",FILTER_SANITIZE_STRING);
$pj2=filter_input(INPUT_GET,"pj2",FILTER_SANITIZE_STRING);
$so=filter_input(INPUT_GET,"so",FILTER_SANITIZE_STRING);
$pj1=filter_var($pj1,FILTER_CALLBACK,array('options'=>'sanitize_on01'));
$pj2=filter_var($pj2,FILTER_CALLBACK,array('options'=>'sanitize_on01'));
$so=filter_var($so,FILTER_CALLBACK,array('options'=>'sanitize_on01'));

$nbjours=$nbjours?$nbjours:0;
$valide=$valide?$valide:0;

$admin = in_array(1, $droits);
$adminN2 = in_array(8, $droits);
$agents_multiples = ($admin or in_array(9, $droits));

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);

if($config['Absences-adminSeulement'] and !$admin){
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
  include "include/footer.php";
  exit;
}

// Récurrence
if(!$rcheckbox){
  $rrule = null;
}

echo <<<EOD
<h3>Ajouter une absence</h3>
<table>
<tr style='vertical-align:top'>
<td style='width:560px;'>
EOD;

// Enregitrement de l'absence
if($confirm and !empty($perso_ids)){

  // Sécurité : Si l'agent enregistrant l'absence n'est pas admin et n'est pas dans la liste des absents ou pas autorisé à enregistrer des absences pour plusieurs agents, l'accès est refusé.
  $access = false;
  if($admin){
    $access = true;
  } elseif( count($perso_ids) == 1 and in_array($_SESSION['login_id'], $perso_ids)){
    $access = true;
  } elseif($agents_multiples and in_array($_SESSION['login_id'], $perso_ids)){
    $access = true;
  }
    
  if(!$access){
    echo "</td></tr></table>\n";
    echo "<div id='acces_refuse'>Acc&egrave;s refus&eacute;</div>\n";
    echo "<a href='javascript:history.back();'>Retour</a>\n";
    include __DIR__."/../include/footer.php";
  }
  
  $fin=$fin?$fin:$debut;
  $finSQL=dateSQL($fin);
  $valideN1=0;
  $valideN2=0;
  if($config['Absences-validation']=='0'){
    $valideN2=1;
    $validation=date("Y-m-d H:i:s");
    $validationText=null;
  }
  elseif(!$admin){
    $valideN2=0;
    $validationText="Demand&eacute;e";
    $validation="0000-00-00 00:00:00";
  }
  elseif($admin){
    $validationText="Demand&eacute;e";
    $validation="0000-00-00 00:00:00";
    if($valide==1){
      $valideN2=$_SESSION['login_id'];
      $validationText="Valid&eacute;e";
      $validation=date("Y-m-d H:i:s");
    }
    elseif($valide==-1){
      $valideN2=$_SESSION['login_id']*-1;
      $validationText="Refus&eacute;e";
      $validation=date("Y-m-d H:i:s");
    }
    elseif($valide==2){
      $valideN2=0;
      $valideN1=$_SESSION['login_id'];
      $validationText="Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
      $validationN1=date("Y-m-d H:i:s");
    }
    elseif($valide==-2){
      $valideN2=0;
      $valideN1=$_SESSION['login_id']*-1;
      $validationText="Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
      $validationN1=date("Y-m-d H:i:s");
    }
  }

  // Choix des destinataires des notifications selon le degré de validation
  $notifications=1;
  if($config['Absences-validation'] and $valideN1!=0){
    $notifications=3;
  }
  elseif($config['Absences-validation'] and $valideN2!=0){
    $notifications=4;
  }

  // Formatage des dates/heures de début/fin pour les requêtes SQL
  $debut_sql=$debutSQL." ".$hre_debut;
  $fin_sql=$finSQL." ".$hre_fin;

  // Si erreur d'envoi de mail, affichage de l'erreur (Initialisation des variables)
  $msg2=null;
  $msg2Type=null;

  // ID du groupe (permet de regrouper les informations pour affichage en une seule ligne et modification du groupe)
  if(count($perso_ids)>1){
    $groupe=time()."-".rand(100,999);
  }else{
    $groupe=null;
  }

  // Pour chaque agents
  foreach($perso_ids as $perso_id){
    // Recherche du responsables pour l'envoi de notifications
    $a=new absences();
    $a->getResponsables($debutSQL,$finSQL,$perso_id);
    $responsables=$a->responsables;

    // Informations sur l'agent
    $p=new personnel();
    $p->fetchById($perso_id);
    $nom=$p->elements[0]['nom'];
    $prenom=$p->elements[0]['prenom'];
    $mail=$p->elements[0]['mail'];
    $mails_responsables=$p->elements[0]['mails_responsables'];

    // Choix des destinataires des notifications selon la configuration
    $a=new absences();
    $a->getRecipients($notifications,$responsables,$mail,$mails_responsables);
    $destinataires=$a->recipients;
    
    // Enregistrement des récurrences
    // Les événements récurrents sont enregistrés dans un fichier ICS puis importés dans la base de données
    // La méthode absences::update_ics se charge de créer le fichier et d'enregistrer les infos dans la base de données
    if($rrule){
      // Création du fichier ICS
      $a = new absences();
      $a->CSRFToken = $CSRFToken;
      $a->perso_id = $perso_id;
      $a->commentaires = $commentaires;
      $a->debut = $debut;
      $a->fin = $fin;
      $a->hre_debut = $hre_debut;
      $a->hre_fin = $hre_fin;
      $a->groupe = $groupe;
      $a->motif = $motif;
      $a->motif_autre = $motif_autre;
      $a->rrule = $rrule;
      $a->valideN1 = $valideN1;
      $a->valideN2 = $valideN2;
      $a->update_ics();

    // Les événements sans récurrence sont enregistrés directement dans la base de données
    } else {
      // Ajout de l'absence dans la table 'absence'
      $insert=array("perso_id"=>$perso_id, "debut"=>$debut_sql, "fin"=>$fin_sql, "nbjours"=>$nbjours, "motif"=>$motif, "motif_autre"=>$motif_autre, "commentaires"=>$commentaires, 
      "demande"=>date("Y-m-d H:i:s"), "pj1"=>$pj1, "pj2"=>$pj2, "so"=>$so, "groupe"=>$groupe);
    }

    if($valideN1!=0){
      $insert["valide_n1"]=$valideN1;
      $insert["validation_n1"]=$validationN1;
    }
    else{
      $insert["valide"]=$valideN2;
      $insert["validation"]=$validation;
    }

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("absences", $insert);
    

    // Récupération de l'ID de l'absence enregistrée pour la création du lien dans le mail
    $info=array(array("name"=>"MAX(id)","as"=>"id"));
    $where=array("debut"=>$debut_sql, "fin"=>$fin_sql, "perso_id"=>$perso_id);
    $db=new db();
    $db->select2("absences",$info,$where);
    if($db->result){
      $id=$db->result[0]['id'];
    }

    // Recherche des plages de SP concernées pour ajouter cette information dans le mail.
    $a=new absences();
    $a->debut=$debut_sql;
    $a->fin=$fin_sql;
    $a->perso_ids=$perso_ids;
    $a->infoPlannings();
    $infosPlanning=$a->message;

    // Titre différent si titre personnalisé (config) ou si validation ou non des absences (config)
    if($config['Absences-notifications-titre']){
      $titre=$config['Absences-notifications-titre'];
    }else{
      $titre=$config['Absences-validation']?"Nouvelle demande d absence":"Nouvelle absence";
    }

    // Si message personnalisé (config), celui-ci est inséré
    if($config['Absences-notifications-message']){
      $message="<b><u>{$config['Absences-notifications-message']}</u></b><br/>";
    }else{
      $message="<b><u>$titre</u></b> : ";
    }

    // On complète le message avec les informations de l'absence
    $message.="<ul><li>Agent : <strong>$prenom $nom</strong></li>";
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
      $message.="<li>Validation : <br/>\n";
      $message.=$validationText;
      $message.="</li>\n";
    }

    if($commentaires){
      $message.="<li>Commentaire: <br/>$commentaires</li>";
    }

    $message.="</ul>";

    // Ajout des informations sur les plannings
    $message.=$infosPlanning;
    
    // Ajout du lien permettant de rebondir sur l'absence
    $url=createURL("absences/modif.php&id=$id");
    $message.="<p>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a></p>";

    // Envoi du mail
    $m=new CJMail();
    $m->subject=$titre;
    $m->message=$message;
    $m->to=$destinataires;
    $m->send();

    // Si erreur d'envoi de mail
    if($m->error){
      $msg2.="<li>".$m->error_CJInfo."</li>";
      $msg2Type="error";
    }
  }

  // Confirmation de l'enregistrement
  if($config['Absences-validation'] and !$admin){
    $msg="La demande d&apos;absence a &eacute;t&eacute; enregistr&eacute;e";
  }else{
    $msg="L&apos;absence a &eacute;t&eacute; enregistr&eacute;e";
  }
  $msg=urlencode($msg);

  // Si erreur d'envoi de mail
  if($msg2Type){
    $msg2=urlencode("<ul>".$msg2."</ul>");
  }
  
  echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&msg=$msg&msgType=success&msg2=$msg2&msg2Type=$msg2Type';</script>\n";

}
// Formulaire
else{
  // Liste des agents
  if($agents_multiples){
    $db_perso=new db();
    $db_perso->select2("personnel","*",array("supprime"=>0,"id"=>"<>2"),"order by nom,prenom");
    $agents=$db_perso->result?$db_perso->result:array();
  }
  
  echo "<form name='form' action='index.php' method='get' onsubmit='return verif_absences(\"debut=date1;fin=date2;motif\");' >\n";
  echo "<input type='hidden' name='page' value='absences/ajouter.php' />\n";
  echo "<input type='hidden' name='confirm' value='1' />\n";
  echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
  echo "<input type='hidden' id='admin' value='".($admin?1:0)."' />\n";
  echo "<input type='hidden' id='login_id' value='{$_SESSION['login_id']}' />\n";

  echo "<table class='tableauFiches'>\n";
  echo "<tr><td>\n";
  if($agents_multiples){
    echo "<label class='intitule'>Agent(s)</label></td>\n";
  }else{
    echo "<label class='intitule'>Agent</label></td>\n";
  }
  echo "<td colspan='2'>\n";
  if($agents_multiples){
  
    // Par défaut, ajoute l'agent logué comme absent
    echo "<input type='hidden' name='perso_ids[]' value='{$_SESSION['login_id']}' id='hidden{$_SESSION['login_id']}' class='perso_ids_hidden'/>\n";
    echo "<ul id='perso_ul1' class='perso_ul'>\n";
    echo "<li id='li{$_SESSION['login_id']}' class='perso_ids_li'>{$_SESSION['login_nom']} {$_SESSION['login_prenom']}\n";
    if($admin){
      echo "<span class='perso-drop' onclick='supprimeAgent({$_SESSION['login_id']});' ><span class='pl-icon pl-icon-drop'></span></span>\n";
    }
    echo "</li>\n";
    echo "</ul>\n";
    echo "<ul id='perso_ul2' class='perso_ul'></ul>\n";
    echo "<ul id='perso_ul3' class='perso_ul'></ul>\n";
    echo "<ul id='perso_ul4' class='perso_ul'></ul>\n";
    echo "<ul id='perso_ul5' class='perso_ul'></ul>\n";
    
    echo "</td></tr>\n";
    echo "<tr><td>&nbsp;</td><td>\n";
    
    echo "<select name='perso_id' id='perso_ids' class='ui-widget-content ui-corner-all' style='margin-bottom:20px;'>\n";
    echo "<option value='0' selected='selected'>-- Ajoutez un agent --</option>\n";
    if($config['Absences-tous']){
      echo "<option value='tous'>Tous les agents</option>\n";
    }
    foreach($agents as $elem){
      $hide = $elem['id'] == $_SESSION['login_id'] ? "style='display:none;'" :null;
      echo "<option value='".$elem['id']."' id='option{$elem['id']}' $hide >".$elem['nom']." ".$elem['prenom']."</option>\n";
    }
    echo "</select>\n";    
  }
  else{
    echo "<input type='hidden' name='perso_id' value='{$_SESSION['login_id']}' class='perso_ids_hidden' />\n";
    echo $_SESSION['login_nom']." ".$_SESSION['login_prenom'];
  }
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "<label class='intitule'>Journée(s) entière(s) </label>\n";
  echo "</td><td>\n";
  echo "<input type='checkbox' name='allday' checked='checked' onclick='all_day();'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "<label class='intitule'>Date de début </label>\n";
  echo "</td><td style='white-space:nowrap;'>";
  echo "<input type='text' name='debut' value='$debut' style='width:100%;' class='recurrence-start datepicker' id='absence-start'/>\n";
  echo "</td></tr>\n";
  echo "<tr id='hre_debut' style='display:none;'><td>\n";
  echo "<label class='intitule'>Heure de début </label>\n";
  echo "</td><td>\n";
  echo "<select name='hre_debut' class='center ui-widget-content ui-corner-all'>\n";
  selectHeure(7,23,true);
  echo "</select>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "<label class='intitule'>Date de fin </label>\n";
  echo "</td><td style='white-space:nowrap;'>";
  echo "<input type='text' name='fin' value='$fin' style='width:100%;' class='datepicker'/>\n";
  echo "</td></tr>\n";
  echo "<tr id='hre_fin' style='display:none;'><td>\n";
  echo "<label class='intitule'>Heure de fin </label>\n";
  echo "</td><td>\n";
  echo "<select name='hre_fin' class='center ui-widget-content ui-corner-all' onfocus='setEndHour();'>\n";
  selectHeure(7,23,true);
  echo "</select>\n";
  echo "</td></tr>\n";
  
  echo "<tr><td style='padding-bottom:30px;'>\n";
  echo "<label class='intitule'>Récurrence</label>\n";
  echo "</td><td style='padding-bottom:30px;'>\n";
  echo "<input type='checkbox' name='recurrence-checkbox' id='recurrence-checkbox' value='1'/>\n";
  echo "<span id='recurrence-info' style='display:none;'><span id='recurrence-summary'>&nbsp;</span><a href='#' id='recurrence-link' style='margin-left:10px;'>Modifier</a></span>\n";
  echo "<input type='hidden' name='recurrence-hidden' id='recurrence-hidden' />\n";
  echo "</td></tr>\n";
  
  echo "<tr><td>\n";
  echo "<label class='intitule'>Motif </label>\n";
  echo "</td><td style='white-space:nowrap;'>\n";

  echo "<select name='motif' id='motif' style='width:100%;' class='ui-widget-content ui-corner-all'>\n";
  echo "<option value=''></option>\n";
  foreach($motifs as $elem){
    $class=$elem['type']==2?"padding20":"bold";
    $disabled=$elem['type']==1?"disabled='disabled'":null;
    $padding = $class == 'padding20' ? "&nbsp;&nbsp;&nbsp;" : null ;
    echo "<option value='".$elem['valeur']."' class='$class' $disabled >$padding".$elem['valeur']."</option>\n";
  }
  echo "</select>\n";
  if($admin){
    echo "<span class='pl-icon pl-icon-add' title='Ajouter' id='add-motif-button' style='cursor:pointer'></span>\n";
  }
  echo "</td></tr>\n";

  echo "<tr style='display:none;' id='tr_motif_autre'><td><label class='intitule'>Motif (autre)</label></td>\n";
  echo "<td><input type='text' name='motif_autre' style='width:100%;' class='ui-widget-content ui-corner-all'/></td></tr>\n";

  echo "<tr style='vertical-align:top;'><td>\n";
  echo "<label class='intitule'>Commentaires </label>\n";
  echo "</td><td>\n";
  echo "<textarea name='commentaires' cols='16' rows='5' class='ui-widget-content ui-corner-all'></textarea>\n";
  echo "</td></tr>\n";

  if(in_array(701,$droits)){
    echo "<tr style='vertical-align:top;'><td>\n";
    echo "<label class='intitule'>Pi&egrave;ces justificatives</label></td><td>";
    echo "<div class='absences-pj-fiche'>PJ1 <input type='checkbox' name='pj1' id='pj1' /></div>";
    echo "<div class='absences-pj-fiche'>PJ2 <input type='checkbox' name='pj2' id='pj2' /></div>";
    echo "<div class='absences-pj-fiche'>SO <input type='checkbox' name='so' id='so' /></div>";
    echo "</td>\n";
  }
  echo "</tr>";

  if($config['Absences-validation']){
    echo "<tr><td><label class='intitule'>&Eacute;tat </label></td><td>\n";
    if($admin){
      echo "<select name='valide' class='ui-widget-content ui-corner-all'>\n";
      echo "<option value='0'>Demand&eacute;e</option>\n";
      echo "<option value='2' >Accept&eacute;e (En attente de validation hi&eacute;rarchique)</option>\n";
      echo "<option value='-2' >Refus&eacute;e (En attente de validation hi&eacute;rarchique)</option>\n";
      if($adminN2){
	echo "<option value='1' >Accept&eacute;e</option>\n";
	echo "<option value='-1' >Refus&eacute;e</option>\n";
      }
      echo "</select>\n";
    }
    else{
      echo "Demand&eacute;e";
    }
    echo "</td></tr>\n";
  }

  echo "<tr><td>&nbsp;\n";
  echo "</td></tr><tr><td colspan='2' style='text-align:center;'>\n";
  echo "<input type='button' class='ui-button' value='Annuler' onclick='document.location.href=\"index.php?page=absences/voir.php\";' />";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' class='ui-button' value='Valider' />\n";

  echo "</td></tr></table>\n";
  echo "</form>\n";
}

echo "</td><td style='color:#FF5E0E;'>\n";

$date=date("Y-m-d");
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}absences_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
if($db->result){
  echo "<b>Informations congés / absences :</b><br/><br/>\n";
  foreach($db->result as $elem)
    echo "Du ".dateFr($elem['debut'])." au ".dateFr($elem['fin'])." : {$elem['texte']}<br/>\n";
}
?>
</td></tr></table>

<div id="recurrence-form" title="Récurrence" class='noprint' style='display:none;'>
  <p class="validateTips">&nbsp;</p>
  <form>
  <table class='tableauFiches'>
  <tr><td>
    <label for='recurrence-freq' >R&eacute;current : 
  </td><td>
    <select name='recurrence-freq' id='recurrence-freq' class='recurrence'>
      <option value='DAILY'>Tous les jours</option>
      <option value='WEEKLY' selected='selected'>Toutes les semaines</option>
      <option value='MONTHLY'>Tous les mois</option>
    </select>
  </td></tr>
  <tr><td>
    <label for='recurrence-interval' >R&eacute;p&eacute;ter tous les : </label>
  </td><td>
    <select name='recurrence-interval' id='recurrence-interval' style='width:50%;' class='recurrence'>
<?php
  for($i=1; $i<31; $i++){
    echo "<option value='$i'>$i</option>\n";
  }
?>
    </select>
    <span id='recurrence-repet-freq'>semaines</span>
  </td></tr>
  <tr id='recurrence-tr-semaine'><td>
    <label>R&eacute;p&eacute;ter le : </label>
  </td><td>
    <input type='checkbox' name='recurrence-by-day[]' id='recurrence-by-day1' value='MO' class='recurrence recurrence-by-day recurrence-by-day1' /><label for='recurrence-by-day1'>L</label> 
    <input type='checkbox' name='recurrence-by-day[]' id='recurrence-by-day2' value='TU' class='recurrence recurrence-by-day recurrence-by-day2' /><label for='recurrence-by-day2'>Ma</label>  
    <input type='checkbox' name='recurrence-by-day[]' id='recurrence-by-day3' value='WE' class='recurrence recurrence-by-day recurrence-by-day3' /><label for='recurrence-by-day3'>Me</label>  
    <input type='checkbox' name='recurrence-by-day[]' id='recurrence-by-day4' value='TH' class='recurrence recurrence-by-day recurrence-by-day4' /><label for='recurrence-by-day4'>J</label>  
    <input type='checkbox' name='recurrence-by-day[]' id='recurrence-by-day5' value='FR' class='recurrence recurrence-by-day recurrence-by-day5' /><label for='recurrence-by-day5'>V</label>  
    <input type='checkbox' name='recurrence-by-day[]' id='recurrence-by-day6' value='SA' class='recurrence recurrence-by-day recurrence-by-day6' /><label for='recurrence-by-day6'>S</label>  
    <input type='checkbox' name='recurrence-by-day[]' id='recurrence-by-day0' value='SU' class='recurrence recurrence-by-day recurrence-by-day0' /><label for='recurrence-by-day0'>D</label>  
  </td></tr>
  <tr id='recurrence-tr-mois' style='display:none;'><td>
    <label>R&eacute;p&eacute;ter chaque : </label>
  </td><td>
    <input type='radio' name='recurrence-repet-mois' id='recurrence-repet-mois1' class='recurrence' value='BYMONTHDAY' checked='cheched' /><label for='recurrence-repet-mois1'>jour du mois</label> 
    <input type='radio' name='recurrence-repet-mois' id='recurrence-repet-mois2' class='recurrence' value='BYDAY' /><label for='recurrence-repet-mois2'>jour de la semaine</label>  
  </td></tr>
  <tr><td>
    <label for='recurrence-debut'>Date de d&eacute;but : </label>
  </td><td>
<!--    <input type='text' value='<?php echo $debut; ?>' id='recurrence-start' class='recurrence recurrence-start datepicker' /> -->
    <span id='recurrence-start'><?php echo $debut; ?></span>
  </td></tr>
  <tr><td>
    <label for='recurrence-end'>Fin : </label>
  </td><td>
    <ul style='margin:0; padding:0; list-style:none;'>
      <li style='margin:5px 0;' id='recurrence-end1-li'>
        <input type='radio' name='recurrence-end' class='recurrence recurrence-end' id='recurrence-end1' value='never' checked='checked' />
        <label for='recurrence-end1'> Jamais</label>
      </li>
      <li style='margin:5px 0;' id='recurrence-end2-li'>
        <input type='radio' name='recurrence-end' class='recurrence recurrence-end' id='recurrence-end2' value='count'/>
        <label for='recurrence-end2'>  Apr&egrave;s 
          <input type='text' name='recurrence-count' id='recurrence-count' style='width:30px;' class='recurrence ui-widget ui-corner-all ui-widget-content'/> r&eacute;p&eacute;titions
        </label>
      </li>
      <li style='margin:5px 0;' id='recurrence-end3-li'>
        <input type='radio' name='recurrence-end' class='recurrence recurrence-end' id='recurrence-end3' value='until'/>
        <label for='recurrence-end3'> Le 
          <input type='text' name='recurrence-until' id='recurrence-until' style='width:130px;' class='recurrence datepicker'/>
        </label>
      </li>
    </ul>
  </td></tr>
  <tr><td>
    <label>R&eacute;sum&eacute; : </label>
  </td><td>
    <span id='recurrence-summary-form' style='max-width:200px; word-wrap:break-word;'>&nbsp;</span>
  </td></tr>
    
  </table>
    
    
  </form>
</div>