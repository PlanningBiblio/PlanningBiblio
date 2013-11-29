<?php
/*
Planning Biblio, Version 1.6.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : absences/ajouter.php
Création : mai 2011
Dernière modification : 28 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'ajouter une absence. Formulaire, confirmation et validation.
la table absence est complétée, la table pl_poste est mise à jour afin de barrer 
les agents absents déjà placés

Page appelée par la page index.php
*/

require_once "class.absences.php";

//	Initialisation des variables
$admin=in_array(1,$droits)?true:false;
$menu=isset($_GET['menu'])?$_GET['menu']:null;
$confirm=isset($_GET['confirm'])?$_GET['confirm']:null;
$perso_id=isset($_GET['perso_id'])?$_GET['perso_id']:null;
$debut=isset($_GET['debut'])?$_GET['debut']:null;
$fin=isset($_GET['fin'])?$_GET['fin']:null;
if($confirm){
  $fin=$fin?$fin:$debut;
  $nbjours=isset($_GET['nbjours'])?$_GET['nbjours']:0;
  $motif=$_GET['motif'];
  $hre_debut=$_GET['hre_debut']?$_GET['hre_debut']:"00:00:00";
  $hre_fin=$_GET['hre_fin']?$_GET['hre_fin']:"23:59:59";
  $commentaires=$_GET['commentaires'];
  if($config['Absences-validation']=='0'){
    $valide=1;
    $validation=date("Y-m-d H:i:s");
    $validationText=null;
  }
  elseif(!$admin){
    $valide=0;
    $validationText="En attente de validation";
    $validation="0000-00-00 00:00:00";
  }
  elseif($admin){
    $valide=$_GET['valide'];
    $validationText="En attente de validation";
    $validation="0000-00-00 00:00:00";
    if($valide>0){
      $validationText="Valid&eacute;e";
      $validation=date("Y-m-d H:i:s");
    }
    elseif($valide<0){
      $validationText="Refus&eacute;e";
      $validation=date("Y-m-d H:i:s");
    }
  }
}

if($config['Absences-adminSeulement'] and !$admin){
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
  include "include/footer.php";
  exit;
}

echo <<<EOD
<h3>Ajouter une absence</h3>
<table>
<tr style='vertical-align:top'>
<td style='width:550px;'>
EOD;

if($confirm=="confirm2"){		//	2eme confirmation
  $a=new absences();
  $a->getResponsables($debut,$fin,$perso_id);
  $responsables=$a->responsables;

  $db_perso=new db();
  $db_perso->select("personnel","*","id=$perso_id");
  $nom=$db_perso->result[0]['nom'];
  $prenom=$db_perso->result[0]['prenom'];
  $destinataires=array();
  $destinataires[]=$db_perso->result[0]['mail'];

  if($config['Absences-notifications']=="A tous" or $config['Absences-notifications']=="Au responsable direct"){
    $destinataires[]=$db_perso->result[0]['mailResponsable'];
  }
  if($config['Absences-notifications']=="A tous" or substr($config['Absences-notifications'],0,25)=="Aux agents ayant le droit"){
    foreach($responsables as $elem){
      $destinataires[]=$elem['mail'];
    }
  }

  $debut_sql=$debut." ".$hre_debut;
  $fin_sql=$fin." ".$hre_fin;

  $db=new db();				//	ajout de l'absence dans la table 'absence'
  $insert=array("perso_id"=>$perso_id, "debut"=>$debut_sql, "fin"=>$fin_sql, "nbjours"=>$nbjours, "motif"=>$motif, 
    "commentaires"=>$commentaires, "demande"=>date("Y-m-d H:i:s"), "valide"=>$valide, "validation"=>$validation);
  $db->insert2("absences", $insert);

				      //	Mise à jour du champs 'absents' dans 'pl_poste'
  if($config['Absences-validation']=='0'){
    $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='1' WHERE
      ((CONCAT(`date`,' ',`debut`) < '$fin_sql' AND CONCAT(`date`,' ',`debut`) >= '$debut_sql')
      OR (CONCAT(`date`,' ',`fin`) > '$debut_sql' AND CONCAT(`date`,' ',`fin`) <= '$fin_sql'))
      AND `perso_id`='$perso_id'";
    $db=new db();
    $db->query($req);
  }
  
  $titre=$config['Absences-validation']?"Nouvelle demande d'absence":"Nouvelle absence";
  $message="$titre : <br/>$prenom $nom<br/>Début : ".dateFr($debut);

  if($hre_debut!="00:00:00")
    $message.=" ".heure3($hre_debut);
  $message.="<br/>Fin : ".dateFr($fin);
  if($hre_fin!="23:59:59")
    $message.=" ".heure3($hre_fin);
  $message.="<br/>Motif : $motif<br/>";
  if($commentaires)
    $message.="Commentaire:<br/>$commentaires<br/>";
  sendmail($titre,$message,$destinataires);
  if($menu=="off"){
    echo "<script type=text/JavaScript>parent.document.location.reload(false);</script>\n";
    echo "<script type=text/JavaScript>popup_closed();</script>\n";
  }
  else{
    echo $config['Absences-validation']?"La demande d'absence a &eacute;t&eacute; enregistr&eacute;e":"L'absence a été enregistrée";
    echo "<br/><br/>";
    echo "<a href='index.php?page=absences/index.php'>Retour</a>";
  }
}
elseif($confirm=="confirm1"){		//	1ere Confirmation
  $commentaires=str_replace("\'","&#146;",$_GET['commentaires']);
  $commentaires=str_replace('\"',"&#34;",$commentaires);
  $commentaires=str_replace("'","&#146;",$commentaires);
  $commentaires=str_replace('"',"&#34;",$commentaires);

  $db=new db();
  $db->query("select nom, prenom from {$dbprefix}personnel where id=$perso_id;");
  $nom=$db->result[0]['nom'];
  $prenom=$db->result[0]['prenom'];


  // Interdiction d'ajouter des absences si l'agent apparaît dans un planning validé pour les dates sélectionnées
  // Si CONFIG absencesApresValidation = 0
  $disableSubmit=null;
  $datesValidees=null;
  if($config['absencesApresValidation']==0){
    $datesValidees=array();
    $db=new db();
    $db->select("pl_poste","date,site","perso_id='$perso_id' AND date>='$debut' AND date<='$fin'","group by date");
    if($db->result){
      foreach($db->result as $elem){
	$db2=new db();
	$db2->select("pl_poste_verrou","*","date='{$elem['date']}' AND site='{$elem['site']}'");
	if($db2->result){
	  $datesValidees[]=dateFr($elem['date']);
	}
      }
    }
    if(!empty($datesValidees)){
      $datesValidees=join(" ; ",$datesValidees);
      if(!$admin){
	$disableSubmit="disabled='disabled'";
      }
    }
  }

  echo "<b>Confirmation</b>\n";

  echo "<table class='tableauFiches'><tr><td>\n";
  echo "Nom, Prénom :</td><td>\n";
  echo $nom." ".$prenom;
  echo "</td></tr><tr><td>\n";
  echo "Début de l'absence : </td><td>\n";
  echo dateFr($debut);
  if($_GET['hre_debut'])
    echo "&nbsp;-&nbsp;".heure2($_GET['hre_debut']);
  echo "</td></tr>\n";
  echo "<tr><td>";
  echo "Fin de l'absence : </td><td>\n";
  echo dateFr($fin);
  if($_GET['hre_fin'])
    echo "&nbsp;-&nbsp;".heure2($_GET['hre_fin']);
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "Motif : </td><td>\n";
  echo $motif;
  echo "</td></tr><tr><td>\n";
  echo "Commentaires : </td><td>\n";
  echo $commentaires;
  echo "</td></tr>\n";

  if($config['Absences-validation']){
    echo "<tr><td>Validation : </td><td>\n";
    echo $validationText;
    echo "</td></tr>\n";
  }

  echo "<tr><td>\n";
  echo "&nbsp;";
  echo "</td></tr></table>\n";
  echo "<form method='get' action='index.php' name='form'>\n";
  echo "<input type='hidden' name='page' value='absences/ajouter.php' />\n";
  echo "<input type='hidden' name='perso_id' value='$perso_id' />\n";
  echo "<input type='hidden' name='debut' value='$debut' />\n";
  echo "<input type='hidden' name='fin' value='$fin' />\n";
  echo "<input type='hidden' name='hre_debut' value='{$_GET['hre_debut']}' />\n";
  echo "<input type='hidden' name='hre_fin' value='{$_GET['hre_fin']}' />\n";
  echo "<input type='hidden' name='nbjours' value='$nbjours' />\n";
  echo "<input type='hidden' name='motif' value='$motif' />\n";
  echo "<input type='hidden' name='commentaires' value='$commentaires' />\n";
  echo "<input type='hidden' name='valide' value='$valide' />\n";
  echo "<input type='hidden' name='confirm' value='confirm2' />\n";
  echo "<input type='hidden' name='menu' value='$menu' />\n";

  if($datesValidees){
    if($admin){
      echo "<div id='AbsencesTips' class='ui-widget' style='margin: 0px 120px 20px 0;'>";
      echo "Attention, l'agent sélectionné apparaît dans des plannings validés : $datesValidees</div>\n";
    }
    else{
      echo "<div id='AbsencesTips' class='ui-widget' style='margin: 0px 120px 20px 0;'>";
      echo "Vous ne pouvez pas ajouter d'absences pour les dates suivantes car les plannings sont validés : $datesValidees <br/>\n";
      echo "Veuillez modifier vos dates ou contacter le responsable du planning.</div>";
    }
  }

  if($menu=="off")
    echo "<input type='button' value='Annuler' onclick='popup_closed();' />";
  else
    echo "<input type='button' value='Annuler' onclick='document.location.href=\"index.php?page=absences/index.php\";' />";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' value='Valider' $disableSubmit />\n";
  echo "</form>\n";
}
else{					//	Formulaire
  echo "<form name='form' action='index.php' method='get' onsubmit='return verif_absences(\"debut=date1;fin=date2;motif\");' >\n";
  echo "<input type='hidden' name='page' value='absences/ajouter.php' />\n";
  echo "<input type='hidden' name='menu' value='$menu' />\n";
  echo "<input type='hidden' name='confirm' value='confirm1' />\n";
  echo "<table class='tableauFiches'>\n";
  echo "<tr><td>\n";
  echo "Nom, prénom : </td>\n";
  echo "<td>\n";
  if($admin){
    $db_perso=new db();
    $db_perso->query("select * from {$dbprefix}personnel where actif='Actif' order by nom,prenom;");
    echo "<select name='perso_id'>\n";
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
  echo "Journée(s) entière(s) : \n";
  echo "</td><td>\n";
  echo "<input type='checkbox' name='allday' checked='checked' onclick='all_day();'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "Date de début : \n";
  echo "</td><td>";
  echo "<input type='text' name='debut' value='$debut' style='width:85%;'/>&nbsp;\n";
  echo "<img src='img/calendrier.gif' onclick='calendrier(\"debut\");' alt='début' />\n";
  echo "</td></tr>\n";
  echo "<tr id='hre_debut' style='display:none;'><td>\n";
  echo "Heure de début : \n";
  echo "</td><td>\n";
  echo "<select name='hre_debut'>\n";
  selectHeure(8,23,true);
  echo "</select>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "Date de fin : \n";
  echo "</td><td>";
  echo "<input type='text' name='fin' value='$fin' style='width:85%;'/>&nbsp;\n";
  echo "<img src='img/calendrier.gif' onclick='calendrier(\"fin\");' alt='fin' />\n";
  echo "</td></tr>\n";
  echo "<tr id='hre_fin' style='display:none;'><td>\n";
  echo "Heure de fin : \n";
  echo "</td><td>\n";
  echo "<select name='hre_fin'>\n";
  selectHeure(8,23,true);
  echo "</select>\n";
  echo "</td></tr>\n";
  
  echo "<tr><td>\n";
  echo "Motif : \n";
  echo "</td><td>\n";

  echo "<select name='motif' style='width:87%;'>\n";
  echo "<option value=''>-----------------------</option>\n";
  $db_select=new db();
  $db_select->query("select valeur from {$dbprefix}select_abs order by rang;");
  foreach($db_select->result as $elem){
    echo "<option value='".$elem['valeur']."'>".$elem['valeur']."</option>\n";
  }
  echo "</select>\n";
  if($admin){
    echo "<a href='javascript:popup(\"include/ajoutSelect.php&amp;table=select_abs&amp;terme=motif\",400,400);'>\n";
    echo "<img src='img/add.gif' alt='*' style=width:15px;'/></a>\n";
  }
  echo "</td></tr><tr valign='top'><td>\n";
  echo "Commentaires : \n";
  echo "</td><td>\n";
  echo "<textarea name='commentaires' cols='16' rows='5' ></textarea>\n";
  echo "</td></tr>\n";

  if($config['Absences-validation']){
    echo "<tr><td>Validation : </td><td>\n";
    if($admin){
      echo "<select name='valide'>\n";
      echo "<option value='0'>En attente de validation</option>\n";
      echo "<option value='1'>Accept&eacute;e</option>\n";
      echo "<option value='-1'>Refus&eacute;e</option>\n";
      echo "</select>\n";
    }
    else{
      echo "En attente de validation";
    }
    echo "</td></tr>\n";
  }

  echo "<tr><td>&nbsp;\n";
  echo "</td></tr><tr><td colspan='2'>\n";
  if($menu=="off")
    echo "<input type='button' value='Annuler' onclick='popup_closed();' />";
  else
    echo "<input type='button' value='Annuler' onclick='document.location.href=\"index.php?page=absences/index.php\";' />";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' value='Valider' />\n";

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
<script type='text/JavaScript'>
errorHighlight($("#AbsencesTips"),"error");
</script>