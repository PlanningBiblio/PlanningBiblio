<?php
/*
Planning Biblio, Version 1.8.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : absences/ajouter.php
Création : mai 2011
Dernière modification : 9 décembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'ajouter une absence. Formulaire, confirmation et validation.
la table absence est complétée, la table pl_poste est mise à jour afin de barrer 
les agents absents déjà placés

Page appelée par la page index.php
*/

require_once "class.absences.php";
require_once "motifs.php";

//	Initialisation des variables
$admin=in_array(1,$droits)?true:false;
$adminN2=in_array(8,$droits)?true:false;
$quartDHeure=$config['heuresPrecision']=="quart d&apos;heure"?true:false;
$menu=isset($_GET['menu'])?$_GET['menu']:null;
$confirm=isset($_GET['confirm'])?$_GET['confirm']:null;
$perso_id=isset($_GET['perso_id'])?$_GET['perso_id']:null;
$debut=isset($_GET['debut'])?$_GET['debut']:null;
$fin=isset($_GET['fin'])?$_GET['fin']:null;

// Pièces justificatives
$pj1=(isset($_GET['pj1']) and $_GET['pj1'])?1:0;
$pj2=(isset($_GET['pj2']) and $_GET['pj2'])?1:0;
$so=(isset($_GET['so']) and $_GET['so'])?1:0;

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);

if($config['Absences-adminSeulement'] and !$admin){
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
  include "include/footer.php";
  exit;
}

echo <<<EOD
<h3>Ajouter une absence</h3>
<table>
<tr style='vertical-align:top'>
<td style='width:560px;'>
EOD;

// Enregitrement de l'absence
if($confirm){
  $fin=$fin?$fin:$debut;
  $finSQL=dateSQL($fin);
  $nbjours=isset($_GET['nbjours'])?$_GET['nbjours']:0;
  $motif=$_GET['motif'];
  $motif_autre=htmlentities($_GET['motif_autre'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
  $hre_debut=$_GET['hre_debut']?$_GET['hre_debut']:"00:00:00";
  $hre_fin=$_GET['hre_fin']?$_GET['hre_fin']:"23:59:59";
  $commentaires=htmlentities($_GET['commentaires'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
  $valide=isset($_GET['valide'])?$_GET['valide']:0;
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

  $a=new absences();
  $a->getResponsables($debutSQL,$finSQL,$perso_id);
  $responsables=$a->responsables;

  $db_perso=new db();
  $db_perso->select("personnel","*","id=$perso_id");
  $nom=$db_perso->result[0]['nom'];
  $prenom=$db_perso->result[0]['prenom'];

  // Choix des destinataires des notifications selon le degré de validation
  $notifications=$config['Absences-notifications'];
  if($config['Absences-validation'] and $valideN1!=0){
    $notifications=$config['Absences-notifications3'];
  }
  elseif($config['Absences-validation'] and $valideN2!=0){
    $notifications=$config['Absences-notifications4'];
  }

  // Choix des destinataires des notifications selon la configuration
  $mailsResponsables=explode(";",$db_perso->result[0]['mailsResponsables']);
  $a=new absences();
  $a->getRecipients($notifications,$responsables,$db_perso->result[0]['mail'],$mailsResponsables);
  $destinataires=$a->recipients;

  $debut_sql=$debutSQL." ".$hre_debut;
  $fin_sql=$finSQL." ".$hre_fin;

  // Ajout de l'absence dans la table 'absence'
  $db=new db();
  $insert=array("perso_id"=>$perso_id, "debut"=>$debut_sql, "fin"=>$fin_sql, "nbjours"=>$nbjours, "motif"=>$motif, 
    "motif_autre"=>$motif_autre, "commentaires"=>$commentaires, "demande"=>date("Y-m-d H:i:s"), "pj1"=>$pj1, "pj2"=>$pj2, "so"=>$so );

  if($valideN1!=0){
    $insert["valideN1"]=$valideN1;
    $insert["validationN1"]=$validationN1;
  }
  else{
    $insert["valide"]=$valideN2;
    $insert["validation"]=$validation;
  }

  $db->insert2("absences", $insert);

  // Récupération de l'ID de l'absence enregistrée pour la création du lien dans le mail
  $db=new db();
  $db->select("absences","MAX(id) AS id","debut='$debut_sql' AND fin='$fin_sql' AND perso_id='$perso_id'");
  if($db->result){
    $id=$db->result[0]['id'];
  }

  // Mise à jour du champs 'absents' dans 'pl_poste'
  if($valideN2>0){
    $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='1' WHERE
      ((CONCAT(`date`,' ',`debut`) < '$fin_sql' AND CONCAT(`date`,' ',`debut`) >= '$debut_sql')
      OR (CONCAT(`date`,' ',`fin`) > '$debut_sql' AND CONCAT(`date`,' ',`fin`) <= '$fin_sql'))
      AND `perso_id`='$perso_id'";
    $db=new db();
    $db->query($req);
  }
  
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
  $message.="<br/><br/><b>$prenom $nom</b><br/><br/>Début : $debut";
  if($hre_debut!="00:00:00")
    $message.=" ".heure3($hre_debut);
  $message.="<br/>Fin : $fin";
  if($hre_fin!="23:59:59")
    $message.=" ".heure3($hre_fin);
  $message.="<br/><br/>Motif : $motif";
  if($motif_autre){
    $message.=" / $motif_autre";
  }
  $message.="<br/>";

  if($config['Absences-validation']){
    $message.="<br/>Validation : <br/>\n";
    $message.=$validationText;
    $message.="<br/>\n";
  }

  if($commentaires){
    $message.="<br/>Commentaire:<br/>$commentaires<br/>";
  }

  // Ajout du lien permettant de rebondir sur l'absence
  $url=createURL("absences/modif.php&id=$id");
  $message.="<br/><br/>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a><br/><br/>";

  // Envoi du mail
  if(!empty($destinataires)){
    sendmail($titre,$message,$destinataires);
  }

  if($menu=="off"){
    echo "<script type='text/JavaScript'>parent.document.location.reload(false);</script>\n";
    echo "<script type='text/JavaScript'>popup_closed();</script>\n";
  }
  else{
    $msg=($config['Absences-validation'] and !$admin)?"La demande d&apos;absence a &eacute;t&eacute; enregistr&eacute;e":"L&apos;absence a &eacute;t&eacute; enregistr&eacute;e";
    $msg=urlencode($msg);
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&messageOK=$msg';</script>\n";
  }
}
else{					//	Formulaire
  echo "<form name='form' action='index.php' method='get' onsubmit='return verif_absences(\"debut=date1;fin=date2;motif\");' >\n";
  echo "<input type='hidden' name='page' value='absences/ajouter.php' />\n";
  echo "<input type='hidden' name='menu' value='$menu' />\n";
  echo "<input type='hidden' name='confirm' value='confirm' />\n";
  echo "<table class='tableauFiches'>\n";
  echo "<tr><td>\n";
  echo "<label class='intitule'>Nom, prénom </label></td>\n";
  echo "<td>\n";
  if($admin){
    $db_perso=new db();
    $db_perso->query("select * from {$dbprefix}personnel where actif='Actif' order by nom,prenom;");
    echo "<select name='perso_id' class='ui-widget-content ui-corner-all'>\n";
    foreach($db_perso->result as $elem){
      if($perso_id==$elem['id'])
	echo "<option value='".$elem['id']."' selected='selected'>".$elem['nom']." ".$elem['prenom']."</option>\n";
      else
	echo "<option value='".$elem['id']."'>".$elem['nom']." ".$elem['prenom']."</option>\n";
    }
    echo "</select>\n";
  }
  else{
    echo "<input type='hidden' name='perso_id' value='{$_SESSION['login_id']}' />\n";
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
  echo "<input type='text' name='debut' value='$debut' style='width:100%;' class='datepicker'/>\n";
  echo "</td></tr>\n";
  echo "<tr id='hre_debut' style='display:none;'><td>\n";
  echo "<label class='intitule'>Heure de début </label>\n";
  echo "</td><td>\n";
  echo "<select name='hre_debut' class='center ui-widget-content ui-corner-all'>\n";
  selectHeure(7,23,true,$quartDHeure);
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
  echo "<select name='hre_fin' class='center ui-widget-content ui-corner-all'>\n";
  selectHeure(7,23,true,$quartDHeure);
  echo "</select>\n";
  echo "</td></tr>\n";
  
  echo "<tr><td>\n";
  echo "<label class='intitule'>Motif </label>\n";
  echo "</td><td style='white-space:nowrap;'>\n";

  echo "<select name='motif' style='width:100%;' class='ui-widget-content ui-corner-all'>\n";
  echo "<option value=''></option>\n";
  foreach($motifs as $elem){
  $class=$elem['type']==2?"padding20":"bold";
  $disabled=$elem['type']==1?"disabled='disabled'":null;
  echo "<option value='".$elem['valeur']."' class='$class' $disabled >".$elem['valeur']."</option>\n";
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
  if($menu=="off")
    echo "<input type='button' class='ui-button' value='Annuler' onclick='popup_closed();' />";
  else
    echo "<input type='button' class='ui-button' value='Annuler' onclick='document.location.href=\"index.php?page=absences/index.php\";' />";
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