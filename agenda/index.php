<?php
/*
Panning Biblio, Version 1.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : agenda/index.php
Création : mai 2011
Dernière modification : 22 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche l'agenda d'un agent entre 2 dates
Par défaut, la semaine courante de l'agent connecté est affiché

Page appelée par la page index.php
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

// Includes
include "joursFeries/class.joursFeries.php";

//	Initialisation des variables
if(!array_key_exists('agenda_debut',$_SESSION)){
  $_SESSION['agenda_debut']=null;
  $_SESSION['agenda_fin']=null;
  $_SESSION['agenda_order']=null;
  $_SESSION['agenda_perso_id']=$_SESSION['login_id'];
}

$nbColonnes=4;
$admin=in_array(3,$droits)?true:false;
if($admin){
  $perso_id=isset($_GET['perso_id'])?$_GET['perso_id']:$_SESSION['agenda_perso_id'];
}
else{
  $perso_id=$_SESSION['agenda_perso_id'];
}
$d=new datePl(date("Y-m-d"));
$order=isset($_GET['order'])?$_GET['order']:$_SESSION['agenda_order'];
$debut=isset($_GET['debut'])?$_GET['debut']:$_SESSION['agenda_debut'];
$fin=isset($_GET['fin'])?$_GET['fin']:$_SESSION['agenda_fin'];
$debut=$debut?$debut:$d->dates[0];		// lundi de la semaine courante
$fin=$fin?$fin:$d->dates[6];			// dimance de la semaine courante
$order=$order?$order:"`date` desc";
$_SESSION['agenda_debut']=$debut;
$_SESSION['agenda_fin']=$fin;
$_SESSION['agenda_perso_id']=$perso_id;
$_SESSION['agenda_order']=$order;
$class=null;

//	Sélection du personnel pour le menu déroulant
$toutlemonde=$config['toutlemonde']?null:" AND id<>2 ";
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}personnel` WHERE actif='Actif' $toutlemonde ORDER BY `nom`,`prenom`;");
$agents=$db->result;
if(is_array($agents)){
  foreach($agents as $elem){
    if($elem['id']==$perso_id){
      $agent=$elem['nom']." ".$elem['prenom'];
      break;
    }
  }
}

// Jours fériés
$j=new joursFeries();
$j->debut=$debut;
$j->fin=$fin;
$j->index="date";
$j->fetch();
$joursFeries=$j->elements;

// Affichage
if(isset($agent)){
  echo "<h3>Agenda de $agent du ".dateFr($debut)." au ".dateFr($fin).".</h3><br/>\n";
}
else{
  echo "<h3>Agenda</h3>\n";
}
if(is_array($agents)){
  echo "<form name='form' method='get' action='index.php'>\n";
  echo "<input type='hidden' name='page' value='agenda/index.php' />\n";
  echo "Début : <input type='text' name='debut' id='debut' value='$debut'/>&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"debut\");' alt='début'/>\n";
  echo "&nbsp;&nbsp;Fin : <input type='text' name='fin' value='$fin'/>&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"fin\");' alt='fin'/>\n";
  if($admin){
    echo "&nbsp;&nbsp;Agent : \n";
    echo "<select name='perso_id'>\n";
    foreach($agents as $elem)
      echo "<option value='{$elem['id']}'>{$elem['nom']} {$elem['prenom']}</option>\n";
    echo "</select>\n";
  }
  echo "&nbsp;&nbsp;<input type='submit' value='OK' />\n";
  echo "</form>\n";
}
else{
  echo "Aucun agent disponible";
}

if(!isset($agent)){
  echo "<br/><br/><br/><br/>";
  include "include/footer.php";
  exit;
}

//	Selection des horaires de travail
$db=new db();
$db->select("personnel","temps","id='$perso_id'");
$temps=unserialize($db->result[0]['temps']);		//	$temps = emploi du temps

//	Selection des absences
$filter=$config['Absences-validation']?"AND `valide`>0":null;
$db=new db();
$db->select("absences",null,"`perso_id`='$perso_id' $filter ");
$absences=$db->result;					//	$absences = tableau d'absences
	
//	Selection des postes occupés
$db=new db();
$requete="SELECT pl_poste.`date` AS `date`, pl_poste.debut AS debut, pl_poste.fin AS fin, pl_poste.absent AS absent, postes.nom as poste FROM pl_poste INNER JOIN postes on pl_poste.poste=postes.id WHERE pl_poste.perso_id='$perso_id' and `date`>='$debut' and `date`<='$fin' order by $order,`debut`,`fin`;";
$requete=str_replace("pl_poste","`{$dbprefix}pl_poste`",$requete);
$requete=str_replace("postes","`{$dbprefix}postes`",$requete);
$db->query($requete);
$postes=$db->result;

echo "<br/>\n";
echo "<table cellpadding='20' cellspacing='0' border='1'>\n";
$nb=0;

$current=$debut;
while($current<=$fin){
  $current_postes=array();
  $date_tab=explode("-",$current);
  $date_aff=dateAlpha($current);
  $semaine=date("W",strtotime($current));
  $jour=date("w",strtotime($current))-1;
  if($jour<0)
    $jour=6;
  if($config['nb_semaine']==2 and $semaine%2==0)
    $jour=$jour+7;

  $horaires=null;
  if(array_key_exists($jour,$temps)){
    $horaires=$temps[$jour];
  }

  $d=new datePl($current);
  $current_date=ucfirst($d->jour_complet);
  if(is_array($postes))
  foreach($postes as $elem){
    if($elem['date']==$current){
      $current_postes[]=$elem;
    }
  }
  $current_abs=array();
  if(is_array($absences))
  foreach($absences as $elem){
    $abs_deb=substr($elem['debut'],0,10);
    $abs_fin=substr($elem['fin'],0,10);
    if(($abs_deb<$current and $abs_fin>$current) or $abs_deb==$current or $abs_fin==$current){
      $current_abs[]=$elem;
    }
  }
  if(($nb++)%$nbColonnes==0){
    echo "<tr style='vertical-align:top;'>\n";
  }
  echo "<td>";
  echo "<div style='font-weight:bold;text-decoration:underline;margin-bottom:10px;'>$date_aff</div>\n";

  // Jours fériés : affiche Bibliothèque fermée et passe au jour suivant
  if(array_key_exists($current,$joursFeries) and $joursFeries[$current]['fermeture']){
    echo "<div class='ferie'>\n";
    echo "Biblioth&egrave;que ferm&eacute;e<br/><b>{$joursFeries[$current]['nom']}</b>";
    echo "</div></td>\n";
    $current=date("Y-m-d",mktime(0,0,0,$date_tab[1],$date_tab[2]+1,$date_tab[0]));
    if(($nb)%$nbColonnes==0){
      echo "</tr>\n";
    }
    continue;
  }

  echo "<div>\n";
  $site=null;
  if($config['Multisites-nombre']>1 and $config['Multisites-agentsMultisites'] and isset($horaires[4])){
    if($horaires[4]){
      $site="&agrave; ".$config['Multisites-site'.$horaires[4]];
    }
  }

  $horaire="";
  if($horaires[0])
    $horaire="Pr&eacute;sent(e) $site de ".heure2($horaires[0])." &agrave; ";
  if($horaires[1])
    $horaire.=heure2($horaires[1]);
  if($horaires[1] and $horaires[2])
    $horaire.=" et de ";
  if($horaires[2])
    $horaire.=heure2($horaires[2])." &agrave; ";
  if($horaires[3])
    $horaire.=heure2($horaires[3]);
  echo $horaire;
  echo "</div>\n";
  echo "<div style='color:#FF5E0E;'>";
  foreach($current_abs as $elem){
    if($elem['debut']<$current." 00:00:01" and $elem['fin']>$current." 23:59:58")
      echo "Absent(e) toute la journ&eacute;e : ".$elem['motif'];
    elseif(substr($elem['debut'],0,10)==$current and substr($elem['fin'],0,10)==$current){
      $deb=heure2(substr($elem['debut'],-8));
      $fi=heure2(substr($elem['fin'],-8));
      echo "Absent(e) de $deb &agrave; $fi : ".$elem['motif'];
    }
    elseif(substr($elem['debut'],0,10)==$current and $elem['fin']>$current." 23:59:58"){
      $deb=heure2(substr($elem['debut'],-8));
      echo "Absent(e) &agrave; partir de $deb : ".$elem['motif'];
    }
    elseif($elem['debut']<$current." 00:00:01" and substr($elem['fin'],0,10)==$current){
      $fi=heure2(substr($elem['fin'],-8));
      echo "Absent(e) jusqu'&agrave; $fi : ".$elem['motif'];
    }
    else
      echo $elem['debut']." --> ".$elem['fin']." : ".$elem['motif'];
  }
  echo "</div>\n";
  if(!empty($current_postes)){
    echo "<div style='margin-top:10px;'>Postes occup&eacute;s :<ul style='margin-top:0px;'>\n";
    foreach($current_postes as $elem){
      $heure=heure2($elem['debut'])." - ".heure2($elem['fin']);
      $barre=$elem['absent']?"color:#FF5E0E;text-decoration:line-through;":null;
      echo "<li style='$barre' >$heure {$elem['poste']}</li>\n";
    }
  echo "</ul></div>\n";
  }
	  
  $current=date("Y-m-d",mktime(0,0,0,$date_tab[1],$date_tab[2]+1,$date_tab[0]));
  if(($nb)%$nbColonnes==0)
    echo "</tr>\n";
}		
echo "</table>\n";
echo "<br/><br/><br/><br/>";
echo "<script type='text/JavaScript'>document.form.perso_id.value='$perso_id';</script>\n";
?>