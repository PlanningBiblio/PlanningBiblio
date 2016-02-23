<?php
/**
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/poste/semaine.php
Création : 26 mai 2014
Dernière modification : 3 décembre 2015
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Farid Goara <farid.goara@u-pem.fr>


Description :
Cette page affiche tous les plannings de la semaine choisie.

Cette page est appelée par la page index.php
*/

require_once "class.planning.php";
require_once "planning/postes_cfg/class.tableaux.php";
include_once "personnel/class.personnel.php";
include "fonctions.php";

// Initialisation des variables
$groupe=filter_input(INPUT_GET,"groupe",FILTER_SANITIZE_NUMBER_INT);
$site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);
$tableau=filter_input(INPUT_GET,"tableau",FILTER_SANITIZE_NUMBER_INT);
$verrou=false;

//		------------------		DATE		-----------------------//
$date=filter_input(INPUT_GET,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));
if(!$date and array_key_exists('PLdate',$_SESSION)){
  $date=$_SESSION['PLdate'];
}
elseif(!$date and !array_key_exists('PLdate',$_SESSION)){
  $date=date("Y-m-d");
}
$_SESSION['PLdate']=$date;
$d=new datePl($date);
$semaine=$d->semaine;
$semaine3=$d->semaine3;
$jour=$d->jour;
$dates=$d->dates;
$j1=$dates[0];
$j2=$dates[1];
$j3=$dates[2];
$j4=$dates[3];
$j5=$dates[4];
$j6=$dates[5];
$j7=$dates[6];
$dateAlpha=dateAlpha($date);

$_SESSION['oups']['week']=true;
//		------------------		FIN DATE		-----------------------//
//		------------------		TABLEAU		-----------------------//
// Multisites : la variable $site est égale à 1 par défaut.
// Elle prend la valeur GET['site'] si elle existe, sinon la valeur de la SESSION ['site']
// En dernier lieu, la valeur du site renseignée dans la fiche de l'agent
if(!$site and array_key_exists("site",$_SESSION['oups'])){
  $site=$_SESSION['oups']['site'];
}
if(!$site){
  $p=new personnel();
  $p->fetchById($_SESSION['login_id']);
  $site=$p->elements[0]['sites'][0];
}
$site=$site?$site:1;
$_SESSION['oups']['site']=$site;
//		------------------		FIN TABLEAU		-----------------------//
global $idCellule;
$idCellule=0;
//		------------------		Vérification des droits de modification (Autorisation)	------------------//
$autorisation=false;
if($config['Multisites-nombre']>1){
  if(in_array((300+$site),$droits)){
    $autorisation=true;
  }
}
else{
  $autorisation=in_array(12,$droits)?true:false;
}
//		-----------------		FIN Vérification des droits de modification (Autorisation)	----------//
// Catégories
$categories=array();
$db=new db();
$db->select2("select_categories");
if($db->result){
  foreach($db->result as $elem){
    $categories[$elem['id']]=$elem['valeur'];
  }
}

$fin=$config['Dimanche']?6:5;

//	Selection des messages d'informations
$db=new db();
$dateDebut=$db->escapeString($dates[0]);
$dateFin=$db->escapeString($dates[$fin]);
$db->query("SELECT * FROM `{$dbprefix}infos` WHERE `debut`<='$dateFin' AND `fin`>='$dateDebut' ORDER BY `debut`,`fin`;");
$messages_infos=null;
if($db->result){
  foreach($db->result as $elem){
    $messages_infos[]=$elem['texte'];
  }
  $messages_infos=join($messages_infos," - ");
}


//		---------------		Affichage du titre et du calendrier	--------------------------//
echo "<div id='planning-semaine'>\n";
echo "<div id='divcalendrier' class='text'>\n";

echo "<form name='form' method='get' action='#'>\n";
echo "<input type='hidden' id='date' name='date' data-set-calendar='$date' />\n";
echo "</form>\n";

echo "<table id='tab_titre'>\n";
echo "<tr><td><div class='noprint'>\n";
?>
<div id='pl-calendar' class='datepicker datepickerSemaine'></div>
<?php
echo "</div></td><td class='titreSemFixe'>\n";
echo "<div class='noprint'>\n";

switch($config['nb_semaine']){
  case 2 :	$type_sem=$semaine%2?"Impaire":"Paire";	$affSem="$type_sem ($semaine)";	break;
  case 3 : 	$type_sem=$semaine3;			$affSem="$type_sem ($semaine)";	break;
  default :	$affSem=$semaine;	break;	
}
echo "<b>Semaine $affSem</b>\n";
echo "</div>";
echo "<div id='semaine_planning'<b>Du ".dateFr($j1)." au ".dateFr($j7)."</b>\n";
echo "</div>\n";
echo <<<EOD
  <table class='noprint' id='tab_jours'><tr valign='top'>
    <td><a href='index.php?date=$j1' class='menu' >Lundi</a> / </td>
    <td><a href='index.php?date=$j2' class='menu' >Mardi</a> / </td>
    <td><a href='index.php?date=$j3' class='menu' >Mercredi</a> / </td>
    <td><a href='index.php?date=$j4' class='menu' >Jeudi</a> / </td>
    <td><a href='index.php?date=$j5' class='menu' >Vendredi</a> / </td>
    <td><a href='index.php?date=$j6' class='menu' >Samedi</a></td>
EOD;
if($config['Dimanche']){
  echo "<td align='center'> / <a href='index.php?date=$j7' class='menu' >Dimanche</a> </td>";
}

echo "<td> / <a href='index.php?page=planning/poste/semaine.php' class='menuRed' >Semaine</a></td>\n";

echo "</tr></table>";
  
if($config['Multisites-nombre']>1){
  echo "<h3 id='h3-Multisites'>{$config['Multisites-site'.$site]}</h3>";
}
//	---------------------		Affichage des messages d'informations		-----------------//
echo "<div id='messages_infos'>\n";
echo "<marquee>\n";
echo $messages_infos;
echo "</marquee>\n";
echo "</div>";

echo "</td><td id='td_boutons'>\n";

//	----------------------------	Récupération des postes		-----------------------------//
$postes=Array();
$db=new db();
$db->select2("postes","*","1","ORDER BY `id`");
if($db->result){
  foreach($db->result as $elem){
    $postes[$elem['id']]=Array("nom"=>$elem['nom'],"etage"=>$elem['etage'],"obligatoire"=>$elem['obligatoire'],"categories"=>is_serialized($elem['categories'])?unserialize($elem['categories']):array());
  }
}
//	-----------------------		FIN Récupération des postes	-----------------------------//

echo "<a href='javascript:print();' title='Imprimer le planning'><span class='pl-icon pl-icon-printer'></span></a>\n";
echo "<a href='index.php' title='Actualiser'><span class='pl-icon pl-icon-refresh'></a>\n";
echo "<div id='planningTips'>&nbsp;</div>";
echo "</td></tr>\n";

//----------------------	FIN Verrouillage du planning		-----------------------//
echo "</table></div>\n";

//		---------------		FIN Affichage du titre et du calendrier		--------------------------//

// Lignes de separation
$db=new db();
$db->select2("lignes");
if($db->result){
  foreach($db->result as $elem){
    $lignes_sep[$elem['id']]=$elem['nom'];
  }
}

// Pour tous les jours de la semaine
for($j=0;$j<=$fin;$j++){
  $date=$dates[$j];

  //-----------------------------			Verrouillage du planning			-----------------------//
  $perso2=null;
  $date_validation2=null;
  $heure_validation2=null;

  $db=new db();
  $db->select2("pl_poste_verrou","*",array("date"=>$date, "site"=>$site));
  if($db->result){
    $verrou=$db->result[0]['verrou2'];
    $perso=nom($db->result[0]['perso']);
    $perso2=nom($db->result[0]['perso2']);
    $date_validation=dateFr(substr($db->result[0]['validation'],0,10));
    $heure_validation=substr($db->result[0]['validation'],11,5);
    $date_validation2=dateFr(substr($db->result[0]['validation2'],0,10));
    $heure_validation2=substr($db->result[0]['validation2'],11,5);
    $validation2=$db->result[0]['validation2'];
  }

  //		---------------		Choix du tableau	-----------------------------//	
  $db=new db();
  $db->select2("pl_poste_tab_affect","tableau",array("date"=>$date, "site"=>$site));
  $tab=$db->result[0]['tableau'];

  if(!$tab){
    continue 1;
  }

  $validationMessage=null;
  if($verrou and $tab){
    $validationMessage="<u>Validation</u> : $perso2 $date_validation2 $heure_validation2";
  }
  if(!$verrou or !$tab){
    $attention=$autorisation?"Attention ! ":null;
    $validationMessage="<font class='important bold'>$attention Le planning du ".dateFr($date)." n'est pas validé !</font>";
  }

  echo "<div class='tableau'>\n";
  echo "<p class='pl-semaine-header'>\n";
  echo "<font class='pl-semaine-date'>".dateAlpha($date)."</font>\n";
  echo "<font class='pl-semaine-validation'>$validationMessage</font>\n";
  echo "</p>\n";

//-------------------------------	FIN Choix du tableau	-----------------------------//	
//-------------------------------	Vérification si le planning est validé	------------------//
  if($verrou or $autorisation){
    //--------------	Recherche des infos cellules	------------//
    // Toutes les infos seront stockées danx un tableau et utilisées par les fonctions cellules_postes
    $db=new db();
    $db->selectInnerJoin(array("pl_poste","perso_id"),array("personnel","id"),
      array("perso_id","debut","fin","poste","absent","supprime"),
      array("nom","prenom","statut","service"),
      array("date"=>$date, "site"=>$site),
      array(),
      "ORDER BY `{$dbprefix}pl_poste`.`absent` desc,`{$dbprefix}personnel`.`nom`, `{$dbprefix}personnel`.`prenom`"); 

    global $cellules;
    $cellules=$db->result;
    
    // Informations sur les congés
    if(in_array("conges",$plugins)){
      include "plugins/conges/planning_cellules.php";
    }
    //--------------	FIN Recherche des infos cellules	------------//
  
    //--------------	Affichage du tableau			------------//

    // Récupération de la structure du tableau
    $t=new tableau();
    $t->id=$tab;
    $t->get();
    $tabs=$t->elements;

    // affichage du tableau :
    // affichage de la lignes des horaires

    echo "<table class='tabsemaine1' cellspacing='0' cellpadding='0' class='text'>\n";
    $k=0;
    foreach($tabs as $tab){
      //		Lignes horaires
      echo "<tr class='tr_horaires'>\n";
      echo "<td class='td_postes'>{$tab['titre']}</td>\n";
      $colspan=0;
      foreach($tab['horaires'] as $horaires){
	echo "<td colspan='".nb30($horaires['debut'],$horaires['fin'])."'>".heure3($horaires['debut'])."-".heure3($horaires['fin'])."</td>";
	$colspan+=nb30($horaires['debut'],$horaires['fin']);
      }
      echo "</tr>\n";
      
      //	Lignes postes et grandes lignes
      foreach($tab['lignes'] as $ligne){
	if($ligne['type']=="poste" and $ligne['poste']){
	  $classTD=$postes[$ligne['poste']]['obligatoire']=="Obligatoire"?"td_obligatoire":"td_renfort";
	  $classTR=array();
	  if(!empty($postes[$ligne['poste']]['categories'])){
	    foreach($postes[$ligne['poste']]['categories'] as $cat){
	      if(array_key_exists($cat,$categories)){
		$classTR[]="tr_".str_replace(" ","",removeAccents(html_entity_decode($categories[$cat],ENT_QUOTES|ENT_IGNORE,"UTF-8")));
	      }
	    }
	  }
	  $classTR=join(" ",$classTR);

	  echo "<tr class='$classTR'><td class='td_postes $classTD'>{$postes[$ligne['poste']]['nom']}";
	  if($config['Affichage-etages'] and $postes[$ligne['poste']]['etage']){
	    echo " ({$postes[$ligne['poste']]['etage']})";
	  }
	  echo "</td>\n";
	  $i=1;
	  foreach($tab['horaires'] as $horaires){
	    // recherche des infos à afficher dans chaque cellule 
	    // fonction cellule_poste(date,debut,fin,colspan,affichage,poste,site)
	    if(in_array("{$ligne['ligne']}_{$i}",$tab['cellules_grises'])){
	      echo "<td colspan='".nb30($horaires['debut'],$horaires['fin'])."' class='cellule_grise' oncontextmenu='cellule=\"\";' >&nbsp;</td>";
	    }
	    else{
	      echo cellule_poste($date,$horaires["debut"],$horaires["fin"],nb30($horaires['debut'],$horaires['fin']),"noms",$ligne['poste'],$site);
	    }
	  $i++;
	  }
	  echo "</tr>\n";
	}
	if($ligne['type']=="ligne"){
	  echo "<tr class='tr_separation'>\n";
	  echo "<td>{$lignes_sep[$ligne['poste']]}</td><td colspan='$colspan'>&nbsp;</td></tr>\n";
	}
      }
      $k++;
    }
    echo "</table>\n";
  }

  // Notes : Affichage
  $p=new planning();
  $p->date=$date;
  $p->site=$site;
  $p->getNotes();
  $notes=$p->notes;
  $notesDisplay=trim($notes)?null:"style='display:none;'";

  echo <<<EOD
  <div class='pl-notes-div1' $notesDisplay >
  $notes
  </div>
EOD;
}
?>
</div>
</div>