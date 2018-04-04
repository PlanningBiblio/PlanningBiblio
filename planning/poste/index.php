<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/index.php
Création : mai 2011
Dernière modification : 4 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Farid Goara <farid.goara@u-pem.fr>


Description :
Cette page affiche le planning. Par défaut, le planning du jour courant est affiché. On peut choisir la date voulue avec le
calendrier ou les jours de la semaine.

Cette page est appelée par la page index.php
*/

require_once "class.planning.php";
require_once "planning/postes_cfg/class.tableaux.php";
include_once "absences/class.absences.php";
include_once "activites/class.activites.php";
include_once "personnel/class.personnel.php";
echo "<div id='planning'>\n";

include "fonctions.php";

// Initialisation des variables
$CSRFToken=filter_input(INPUT_GET,"CSRFToken",FILTER_SANITIZE_STRING);
$groupe=filter_input(INPUT_GET,"groupe",FILTER_SANITIZE_NUMBER_INT);
$site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);
$tableau=filter_input(INPUT_GET,"tableau",FILTER_SANITIZE_NUMBER_INT);
$date=filter_input(INPUT_GET,"date",FILTER_SANITIZE_STRING);

// Contrôle sanitize en 2 temps pour éviter les erreurs CheckMarx
$date=filter_var($date,FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));

$verrou=false;
//		------------------		DATE		-----------------------//
if(!$date and array_key_exists('PLdate',$_SESSION)){
  $date=$_SESSION['PLdate'];
}
elseif(!$date and !array_key_exists('PLdate',$_SESSION)){
  $date=date("Y-m-d");
}
$_SESSION['PLdate']=$date;
$dateFr=dateFr($date);
$d=new datePl($date);
$semaine=$d->semaine;
$semaine3=$d->semaine3;
$jour=$d->jour;
$dates=$d->dates;
$datesSemaine=join(",",$dates);
$j1=$dates[0];
$j2=$dates[1];
$j3=$dates[2];
$j4=$dates[3];
$j5=$dates[4];
$j6=$dates[5];
$j7=$dates[6];
$dateAlpha=dateAlpha($date);

$_SESSION['oups']['week']=false;
//		------------------		FIN DATE		-----------------------//
//		------------------		TABLEAU		-----------------------//
$t=new tableau();
$t->fetchAllGroups();
$groupes=$t->elements;

// Multisites : la variable $site est égale à 1 par défaut.
// Elle prend la valeur GET['site'] si elle existe, sinon la valeur de la SESSION ['site']
// En dernier lieu, la valeur du site renseignée dans la fiche de l'agent
if(!$site and array_key_exists("site",$_SESSION['oups'])){
  $site=$_SESSION['oups']['site'];
}
if(!$site){
  $p=new personnel();
  $p->fetchById($_SESSION['login_id']);
  $site = isset($p->elements[0]['sites'][0]) ? $p->elements[0]['sites'][0] : null;
}
$site=$site?$site:1;
$_SESSION['oups']['site']=$site;

$db=new db();
$db->select2("pl_poste","*",array("date"=>"IN$datesSemaine", "site"=>$site));
$pasDeDonneesSemaine=$db->result?false:true;
//		------------------		FIN TABLEAU		-----------------------//
global $idCellule;
$idCellule=0;
//		------------------		Vérification des droits de modification (Autorisation)	------------------//
$autorisationN1 = (in_array((300+$site),$droits) or in_array((1000+$site),$droits));
$autorisationN2 = in_array((300+$site),$droits);
$autorisationNotes = (in_array((300+$site),$droits) or in_array((800+$site),$droits));

//		-----------------		FIN Vérification des droits de modification (Autorisation)	----------//

//		---------------		changement de couleur du menu et de la periode en fonction du jour sélectionné	---------//
$class=array('menu','menu','menu','menu','menu','menu','menu','menu');

switch($jour){
  case "lun":	$jour3="Lundi";		$periode2='semaine';	$class[0]='menuRed';	break;
  case "mar":	$jour3="Mardi";		$periode2='semaine';	$class[1]='menuRed';	break;
  case "mer":	$jour3="Mercredi";	$periode2='semaine';	$class[2]='menuRed';	break;
  case "jeu":	$jour3="Jeudi";		$periode2='semaine';	$class[3]='menuRed';	break;
  case "ven":	$jour3="Vendredi";	$periode2='semaine';	$class[4]='menuRed';	break;
  case "sam":	$jour3="Samedi";	$periode2='samedi';	$class[5]='menuRed';	break;
  case "dim":	$jour3="Dimanche";	$periode2='samedi';	$class[6]='menuRed';	break;
}
	
//-----------------------------			Verrouillage du planning			-----------------------//
$db=new db();
$db->select2("pl_poste_verrou","*", array("date"=>$date, "site"=>$site));
if($db->result){
  $verrou=$db->result[0]['verrou2'];
  $perso=nom($db->result[0]['perso']);
  $perso2=nom($db->result[0]['perso2']);
  $date_validation=dateFr(substr($db->result[0]['validation'],0,10));
  $heure_validation=substr($db->result[0]['validation'],11,5);
  $date_validation2=dateFr(substr($db->result[0]['validation2'],0,10));
  $heure_validation2=substr($db->result[0]['validation2'],11,5);
  $validation2=$db->result[0]['validation2'];
}else{
  $perso2=null;
  $date_validation2=null;
  $heure_validation2=null;
  $validation2=null;
}
//	---------------		FIN changement de couleur du menu et de la periode en fonction du jour sélectionné	--------------------------//

//	Selection des messages d'informations
$db=new db();
$db->select2("infos","*",array("debut"=>"<={$date}", "fin"=>">={$date}"),"ORDER BY `debut`,`fin`");
$messages_infos=null;
if($db->result){
  foreach($db->result as $elem){
    $messages_infos[]=$elem['texte'];
  }
  $messages_infos=join($messages_infos," - ");
}

//		---------------		Affichage du titre et du calendrier	--------------------------//
echo "<div id='divcalendrier' class='text'>\n";

echo "<form name='form' method='get' action='#'>\n";
echo "<input type='hidden' id='date' name='date' value='$date' data-set-calendar='$date' />\n";
echo "<input type='hidden' id='site' name='date' value='$site' />\n";
echo "</form>\n";

echo "<table id='tab_titre'>\n";
echo "<tr><td><div class='noprint'>\n";
?>
<div id='pl-calendar' class='datepicker'></div>
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
echo "<div id='semaine_planning'><b>Du ".dateFr($j1)." au ".dateFr($j7)."</b>\n";
echo "</div>\n";
echo "<div id='date_planning'>Planning du $dateAlpha";
if(jour_ferie($date)){
  echo " - <font id='ferie'>".jour_ferie($date)."</font>";
}
echo <<<EOD
  </div>
  <table class='noprint' id='tab_jours'><tr valign='top'>
    <td><a href='index.php?date=$j1'  class='{$class[0]}' >Lundi</a> / </td>
    <td><a href='index.php?date=$j2'  class='{$class[1]}' >Mardi</a> / </td>
    <td><a href='index.php?date=$j3'  class='{$class[2]}' >Mercredi</a> / </td>
    <td><a href='index.php?date=$j4'  class='{$class[3]}' >Jeudi</a> / </td>
    <td><a href='index.php?date=$j5'  class='{$class[4]}' >Vendredi</a> / </td>
    <td><a href='index.php?date=$j6'  class='{$class[5]}' >Samedi</a></td>
EOD;
if($config['Dimanche']){
    echo "<td align='center'> / <a href='index.php?date=$j7'  class='".$class[6]."' >Dimanche</a> </td>";
}

echo "<td> / <a href='index.php?page=planning/poste/semaine.php' class='{$class[7]}' >Semaine</a></td>\n";

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

// Récupération des activités pour appliquer les classes aux lignes postes en fonction de celles-ci
$a=new activites();
$a->deleted=true;
$a->fetch();
$activites=$a->elements;

// Récupération des catégories pour appliquer les classes aux lignes postes en fonction de celles-ci
$categories=array();
$db=new db();
$db->select2("select_categories");
if($db->result){
  foreach($db->result as $elem){
    $categories[$elem['id']]=$elem['valeur'];
  }
}

// Récupération des postes
$db=new db();
$db->select2("postes","*","1","ORDER BY `id`");

if($db->result){
  foreach($db->result as $elem){
    
    // Classes CSS du poste
    $classesPoste=array();

    // Ajout des classes en fonction des activités
    $activitesPoste = $elem['activites'] ? json_decode(html_entity_decode($elem['activites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true) : array();
    foreach($activitesPoste as $a){
      if(isset($activites[$a]['nom'])){
        $classesPoste[] = 'tr_activite_'.strtolower(removeAccents(str_replace(array(' ','/'), '_', $activites[$a]['nom'])));
      }
    }
    
    // Ajout des classes de la ligne en fonction des catégories requises par le poste (A,B ou C)
    $categoriesPoste = $elem['categories'] ? json_decode(html_entity_decode($elem['categories'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true) : array();
    foreach($categoriesPoste as $cat){
      if(array_key_exists($cat,$categories)){
	$classesPoste[]="tr_".str_replace(" ","",removeAccents(html_entity_decode($categories[$cat],ENT_QUOTES|ENT_IGNORE,"UTF-8")));
      }
    }
    

    // Tableau $postes
    $postes[$elem['id']]=Array("nom"=>$elem['nom'], "etage"=>$elem['etage'], "obligatoire"=>$elem['obligatoire'], "classes"=>join(" ",$classesPoste));
    
  }
}

//	-----------------------		FIN Récupération des postes	-----------------------------//

// Vérifie si Catégorie A en fin de service si admin et config CatAFinDeService
if($autorisationN1 and $config['CatAFinDeService']){
  echo "<div id='pl-verif-categorie-A'></div>\n";
}

echo "<div id='validation'>\n";
if($autorisationN1){
  $display1=$verrou?null:"display:none";
  $display2=$verrou?"display:none":null;

  echo "<div class='pl-validation' style='$display1'><u>Validation</u><br/>$perso2 $date_validation2 $heure_validation2</div>\n";
  echo "<span id='icon-lock' class='pl-icon pl-icon-lock pointer noprint' title='Déverrouiller le planning' style='$display1'></span></a>\n";
  echo "<span id='icon-unlock' class='pl-icon pl-icon-unlock pointer noprint' title='Verrouiller le planning' style='$display2'></span></a>\n";
}

if($autorisationN2){
  echo "<a href='javascript:popup(\"planning/poste/enregistrer.php&date=$date&site=$site&CSRFToken=$CSRFSession\",500,240);' title='Enregistrer comme modèle'><span class='pl-icon pl-icon-save'></span></a>";
  if(!$verrou){
    echo "<a href='javascript:popup(\"planning/poste/importer.php&date=$date&site=$site\",500,270);' title='Importer un modèle'><span class='pl-icon pl-icon-open'></span></a>";
    echo "<a href='javascript:popup(\"planning/poste/supprimer.php&date=$date&site=$site\",500,200);' title='Supprimer le planning'><span class='pl-icon pl-icon-drop'></span></a>";
  }
}
if($verrou){
  if(!$autorisationN1){
    echo "<div class='pl-validation'><u>Validation</u><br/>$perso2 $date_validation2 $heure_validation2</div>\n";
  }
  echo "<a href='javascript:print();' title='Imprimer le planning'><span class='pl-icon pl-icon-printer'></span></a>\n";
}

echo "<a href='index.php?date=$date&amp;site=$site' title='Actualiser'><span class='pl-icon pl-icon-refresh'></a>\n";
echo "</div>\n";

echo "<div id='planningTips'>&nbsp;</div>";
echo "</td></tr>\n";

//----------------------	FIN Verrouillage du planning		-----------------------//
echo "</table></div>\n";

//		---------------		FIN Affichage du titre et du calendrier		--------------------------//
//		---------------		Choix du tableau	-----------------------------//	
$db=new db();
$db->select2("pl_poste_tab_affect","tableau",array("date"=>$date, "site"=>$site));

if(!$db->result[0]['tableau'] and !$tableau and !$groupe and $autorisationN2){
  $db=new db();
  $db->select2("pl_poste_tab","*",array("supprime"=>null),"order by `nom` DESC");
  if($db->result){
    echo <<<EOD
    <div id='choix_tableaux'>
    <b>Choisissez un tableau pour le $dateAlpha</b><br/>
    <form name='form' action='index.php' method='get'>
    <input type='hidden' name='CSRFToken' value='$CSRFSession' />
    <input type='hidden' name='page' value='planning/poste/index.php' />
    <input type='hidden' name='site' value='$site' />
    <table>
    <tr><td>Choix d'un tableau : </td>
      <td>
      <select name='tableau' class='ui-widget-content ui-corner-all'>
      <option value=''>&nbsp;</option>
EOD;
      foreach($db->result as $elem){
	if($elem['site']==$site or $config['Multisites-nombre']<2){
	  echo "<option value='{$elem['tableau']}'>{$elem['nom']}</option>\n";
	}
      }
      echo <<<EOD
      </select></td>
      <td><input type='submit' value='Valider' class='ui-button' /></td></tr>
    </table>
    </form>
EOD;
    if($pasDeDonneesSemaine and $groupes){
      echo <<<EOD
      <br/><br/><b>OU un groupe de tableaux pour la semaine $semaine</b><br/>
      <form name='form' action='index.php' method='get'>
      <input type='hidden' name='CSRFToken' value='$CSRFSession' />
      <input type='hidden' name='page' value='planning/poste/index.php' />
      <input type='hidden' name='site' value='$site' />
      <table>
      <tr><td>Choix d'un groupe : </td>
	<td><select name='groupe' class='ui-widget-content ui-corner-all'>
	<option value=''>&nbsp;</option>
EOD;
	foreach($groupes as $elem){
	  if($elem['site']==$site or $config['Multisites-nombre']<2){
	    echo "<option value='{$elem['id']}'>{$elem['nom']}</option>\n";
	  }
	}
	echo <<<EOD
	</select></td>
	<td><input type='submit' value='Valider' class='ui-button' /></td></tr>
      </table>
      </form>
EOD;
    }
  }
  echo "</div>\n";
  include "include/footer.php";
  exit;
}
elseif($groupe and $autorisationN2){	//	Si Groupe en argument
  $t=new tableau();
  $t->fetchGroup($groupe);
  $groupeTab=$t->elements;
  $tmp=array();
  $tmp[$dates[0]]=array($dates[0],$groupeTab['lundi']);
  $tmp[$dates[1]]=array($dates[1],$groupeTab['mardi']);
  $tmp[$dates[2]]=array($dates[2],$groupeTab['mercredi']);
  $tmp[$dates[3]]=array($dates[3],$groupeTab['jeudi']);
  $tmp[$dates[4]]=array($dates[4],$groupeTab['vendredi']);
  $tmp[$dates[5]]=array($dates[5],$groupeTab['samedi']);
  if(array_key_exists("dimanche",$groupeTab)){
    $tmp[$dates[6]]=array($dates[6],$groupeTab['dimanche']);
  }
  foreach($tmp as $elem){
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("pl_poste_tab_affect",array("date"=>$elem[0], "site"=>$site));
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("pl_poste_tab_affect",array("date"=>$elem[0], "tableau"=>$elem[1], "site"=>$site));
  }
  $tab=$tmp[$date][1];

}
elseif($tableau and $autorisationN2){	//	Si tableau en argument
  $tab=$tableau;
  $db=new db();
  $db->CSRFToken = $CSRFToken;
  $db->delete("pl_poste_tab_affect", array("date"=>$date, "site"=>$site));
  $db=new db();
  $db->CSRFToken = $CSRFToken;
  $db->insert("pl_poste_tab_affect",array("date"=>$date, "tableau"=>$tab, "site"=>$site));
}
else{
  $tab=$db->result[0]['tableau'];
}
if(!$tab){
  echo "Le planning n'est pas pr&ecirc;t.\n";
  include "include/footer.php";
  exit;
}

//-------------------------------	FIN Choix du tableau	-----------------------------//	
//-------------------------------	Vérification si le planning semaine fixe est validé	------------------//

// Div planning-data : permet de transmettre les valeurs $verrou et $autorisationN1 à la fonction affichant le menudiv
// data-validation pour les fonctions refresh_poste et verrouillage du planning
// Lignes vides pour l'affichage ou non des lignes vides au chargement de la page et après validation (selon la config)

$lignesVides=$config['Planning-lignesVides'];

echo "<div id='planning-data' data-verrou='$verrou' data-autorisation='$autorisationN1' data-validation='$validation2' 
  data-lignesVides='$lignesVides' data-sr-debut='{$config['Planning-SR-debut']}' data-sr-fin='{$config['Planning-SR-fin']}'
  data-CSRFToken='$CSRFSession' style='display:none;'>&nbsp;</div>\n";

// Actualisation du planning si validé et mis à jour depuis un autre poste
if($verrou){
  echo "<script type='text/JavaScript'>refresh_poste();</script>";
}

if(!$verrou and !$autorisationN1){
  echo "<br/><br/><font color='red'>Le planning du $dateFr n'est pas validé !</font><br/>\n";
  include "include/footer.php";
  exit;
}
else{
  //--------------	Recherche des infos cellules	------------//
  // Toutes les infos seront stockées danx un tableau et utilisées par les fonctions cellules_postes
  $db=new db();
  $db->selectLeftJoin(array("pl_poste","perso_id"),array("personnel","id"),
    array("perso_id","debut","fin","poste","absent","supprime","grise"),
    array("nom","prenom","statut","service","postes"),
    array("date"=>$date, "site"=>$site),
    array(),
    "ORDER BY `{$dbprefix}personnel`.`nom`, `{$dbprefix}personnel`.`prenom`");

  global $cellules;
  $cellules=$db->result?$db->result:array();
  usort($cellules,"cmp_nom_prenom");
  
  // Recherche des absences
  // Le tableau $absences sera utilisé par la fonction cellule_poste pour barrer les absents dans le plannings et pour afficher les absents en bas du planning
  $a=new absences();
  $a->valide=false;
  $a->fetch("`nom`,`prenom`,`debut`,`fin`",null,$date,$date);
  $absences=$a->elements;
  global $absences;
  
  // Ajoute les qualifications de chaque agent (activités) dans le tableaux $cellules pour personnaliser l'affichage des cellules en fonction des qualifications
  foreach($cellules as $k => $v){
    if($v['postes']){
      $p = json_decode(html_entity_decode($v['postes'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
      $cellules[$k]['activites'] = array();
      foreach($activites as $elem){
        if(in_array($elem['id'], $p)){
          $cellules[$k]['activites'][] = $elem['nom'];
        }
      }
    }
  }

  // Tri des absences par nom
  usort($absences,"cmp_nom_prenom_debut_fin");
  
  // Affichage des absences en bas du planning : absences concernant le site choisi
  $a=new absences();
  $a->valide=false;
  $a->fetch("`nom`,`prenom`,`debut`,`fin`",null,$date,$date,array($site));
  $absences_planning = $a->elements;

  // Informations sur les congés
  if(in_array("conges",$plugins)){
    include "plugins/conges/planning_cellules.php";
  }
  //--------------	FIN Recherche des infos cellules	------------//
  
  
  //	------------		Affichage du tableau			--------------------//
  //	Lignes de separation
  $db=new db();
  $db->select2("lignes");
  if($db->result){
    foreach($db->result as $elem){
      $lignes_sep[$elem['id']]=$elem['nom'];
    }
  }

  // Récupération de la structure du tableau
  $t=new tableau();
  $t->id=$tab;
  $t->get();
  $tabs=$t->elements;

  // Repère les heures de début et de fin de chaque tableau pour ajouter des colonnes si ces heures sont différentes
  $debut="23:59";
  $fin=null;
  foreach($tabs as $elem){
    $debut=$elem["horaires"][0]["debut"]<$debut?$elem["horaires"][0]["debut"]:$debut;
    $nb=count($elem["horaires"])-1;
    $fin=$elem["horaires"][$nb]["fin"]>$fin?$elem["horaires"][$nb]["fin"]:$fin;
  }

  // affichage du tableau :
  echo "<div id='tableau' data-tableId='$tab' >\n";
  // affichage de la lignes des horaires
  echo "<table id='tabsemaine1' cellspacing='0' cellpadding='0' class='text tabsemaine1'>\n";


  // Tableaux masqués
  $hiddenTables = array();
  $db=new db();
  $db->select2("hidden_tables","*",array("perso_id"=>$_SESSION['login_id'],"tableau"=>$tab));
  if($db->result){
    $hiddenTables = json_decode(html_entity_decode($db->result[0]['hidden_tables'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
  }

  // $sn : index des tableaux Sans nom pour l'affichage des tableaux masqués
  $sn=1;
  
  $j=0;
  foreach($tabs as $tab){
  
    $hiddenTable = in_array($j, $hiddenTables) ? 'hidden-table' :null;
    
    // Comble les horaires laissés vides : créé la colonne manquante, les cellules de cette colonne seront grisées
    $cellules_grises=array();
    $tmp=array();
    
    // Première colonne : si le début de ce tableau est supérieur au début d'un autre tableau
    $k=0;
    if($tab['horaires'][0]['debut']>$debut){
      $tmp[]=array("debut"=>$debut, "fin"=>$tab['horaires'][0]['debut']);
      $cellules_grises[]=$k++;
    }
    
    // Colonnes manquantes entre le début et la fin
    foreach($tab['horaires'] as $key => $value){
      if($key==0 or $value["debut"]==$tab['horaires'][$key-1]["fin"]){
	$tmp[]=$value;
      }elseif($value["debut"]>$tab['horaires'][$key-1]["fin"]){
	$tmp[]=array("debut"=>$tab['horaires'][$key-1]["fin"], "fin"=>$value["debut"]);
	$tmp[]=$value;
	$cellules_grises[]=$k++;
      }
      $k++;
    }

    // Dernière colonne : si la fin de ce tableau est inférieure à la fin d'un autre tableau
    $nb=count($tab['horaires'])-1;
    if($tab['horaires'][$nb]['fin']<$fin){
      $tmp[]=array("debut"=>$tab['horaires'][$nb]['fin'], "fin"=>$fin);
      $cellules_grises[]=$k;
    }

    
    $tab['horaires']=$tmp;
    
    // Titre des tableaux, ajoute les intutulés "Sans nom x" pour les tableaux qui ne sont pas nommés afin de pouvoir les afficher s'ils sont cachés
    $tab['titre2'] = $tab['titre'];
    if(!$tab['titre']){
      $tab['titre2'] = "Sans nom $sn";
      $sn++;
    }

    // Masquer les tableaux
    $masqueTableaux=null;
    if($config['Planning-TableauxMasques']){
      $masqueTableaux="<span title='Masquer' class='pl-icon pl-icon-hide masqueTableau pointer noprint' data-id='$j' ></span>";
    }

    //		Lignes horaires
    echo "<tr class='tr_horaires tableau$j {$tab['classe']} $hiddenTable'>\n";
    echo "<td class='td_postes' data-id='$j' data-title='{$tab['titre2']}'>{$tab['titre']} $masqueTableaux </td>\n";
    $colspan=0;
    foreach($tab['horaires'] as $horaires){
      echo "<td colspan='".nb30($horaires['debut'],$horaires['fin'])."'>".heure3($horaires['debut'])."-".heure3($horaires['fin'])."</td>";
      $colspan+=nb30($horaires['debut'],$horaires['fin']);
    }
    echo "</tr>\n";
    
    //	Lignes postes et grandes lignes
    foreach($tab['lignes'] as $ligne){
      // Regardons si la ligne est vide afin de ne pas l'afficher si $config['Planning-lignes-vides']=0
      $emptyLine=null;
      if(!$config['Planning-lignesVides'] and $verrou and isAnEmptyLine($ligne['poste'])){
	$emptyLine="empty-line";
      }

      // Lignes postes
      if($ligne['type']=="poste" and $ligne['poste']){
	// Classe de la première cellule en fonction du type de poste (obligatoire ou de renfort)
        $classTD = $postes[$ligne['poste']]['obligatoire'] == "Obligatoire" ? "td_obligatoire" : "td_renfort";
        // Classe de la ligne en fonction du type de poste (obligatoire ou de renfort)
        $classTR = $postes[$ligne['poste']]['obligatoire'] == "Obligatoire" ? "tr_obligatoire" : "tr_renfort";

	// Classe de la ligne en fonction des activités et des catégories
        $classTR .= ' ' . $postes[$ligne['poste']]['classes'];

	// Affichage de la ligne
	echo "<tr class='pl-line tableau$j $classTR {$tab['classe']} $hiddenTable $emptyLine'>\n";
	echo "<td class='td_postes $classTD'>{$postes[$ligne['poste']]['nom']}";
	// Affichage ou non des étages
	if($config['Affichage-etages'] and $postes[$ligne['poste']]['etage']){
	  echo " ({$postes[$ligne['poste']]['etage']})";
	}
	echo "</td>\n";
	$i=1;
	$k=1;
	foreach($tab['horaires'] as $horaires){
	  // Recherche des infos à afficher dans chaque cellule 
	  // Cellules grisées si définies dans la configuration du tableau et si la colonne a été ajoutée automatiquement
	  if(in_array("{$ligne['ligne']}_{$k}",$tab['cellules_grises']) or in_array($i-1,$cellules_grises)){
	    echo "<td colspan='".nb30($horaires['debut'],$horaires['fin'])."' class='cellule_grise'>&nbsp;</td>";
	    // Si colonne ajoutée, ça décale les cellules grises initialement prévues. On se décale d'un cran en arrière pour rétablir l'ordre 
	    if(in_array($i-1,$cellules_grises)){
	      $k--;
	    }
	  }
	  // fonction cellule_poste(date,debut,fin,colspan,affichage,poste,site)
	  else{
	    echo cellule_poste($date,$horaires["debut"],$horaires["fin"],nb30($horaires['debut'],$horaires['fin']),"noms",$ligne['poste'],$site);
	  }
	$i++;
	$k++;
	}
	echo "</tr>\n";
      }
      // Lignes de séparation
      if($ligne['type']=="ligne"){
	echo "<tr class='tr_separation tableau$j {$tab['classe']} $hiddenTable'>\n";
	echo "<td>{$lignes_sep[$ligne['poste']]}</td><td colspan='$colspan'>&nbsp;</td></tr>\n";
      }
    }
  echo "<tr class='tr_espace tableau$j {$tab['classe']} $hiddenTable'><td>&nbsp;</td></tr>\n";
  $j++;
  }
  echo "</table>\n";
  echo "</div>\n";
  
  // Notes : Affichage
  $p=new planning();
  $p->date=$date;
  $p->site=$site;
  $p->getNotes();
  $notes=$p->notes;
  $notesTextarea=$p->notesTextarea;
  $notesValidation=$p->validation;
  $notesDisplay=trim($notes)?null:"style='display:none;'";
  $notesSuppression=($notesValidation and !trim($notes)) ? "Suppression du commentaire : ":null;


  echo <<<EOD
  <div id='pl-notes-div1' $notesDisplay >
  $notes
  </div>
EOD;

  // Notes : Modifications
  if($autorisationNotes){
    echo <<<EOD
    <div id='pl-notes-div2' class='noprint'>
    <input type='button' class='ui-button noprint' id='pl-notes-button' value='Ajouter un commentaire' />
    </div>

    <div id="pl-notes-form" title="Commentaire" class='noprint' style='display:none;'>
      <p class="validateTips" id='pl-notes-tips'>Vous pouvez écrire ici un commentaire qui sera affich&eacute; en bas du planning.</p>
      <form>
      <textarea id='pl-notes-text'>$notesTextarea</textarea>
      </form>
    </div>
EOD;
  }

echo <<<EOD
  <div id='pl-notes-div1-validation'>
  $notesSuppression$notesValidation
  </div>  
EOD;

// Appel à disponibilités : envoi d'un mail aux agents disponibles pour occuper le poste choisi depuis le menu des agents
if($config['Planning-AppelDispo']){
  echo <<<EOD
    <div id="pl-appelDispo-form" title="Appel &agrave; disponibilit&eacute;" class='noprint' style='display:none;'>
      <p class="validateTips" id='pl-appelDispo-tips' >Envoyez un e-mail aux agents disponibles pour leur demander s&apos;ils sont volontaires pour occuper le poste choisi.</p>
      <form>
      <label for='pl-appelDispo-sujet'>Sujet</label><br/>
      <input type='text' id='pl-appelDispo-sujet' name='pl-appelDispo-sujet' /><br/><br/>
      <label for='pl-appelDispo-text'>Message</label><br/>
      <textarea id='pl-appelDispo-text' name='pl-appelDispo-text'>&nbsp;</textarea>
      </form>
    </div>
EOD;
}

  // Affichage des absences
  if($config['Absences-planning']){

    // Ajout des congés
    if(in_array("conges",$plugins)){
      include "plugins/conges/planning.php";
    }

    switch($config['Absences-planning']){
      case "1" :
	if(!empty($absences_planning)){
	  echo "<h3 style='text-align:left;margin:40px 0 0 0;'>Liste des absents</h3>\n";
	  echo "<table class='tableauStandard'>\n";
	  $class="tr1";
	  foreach($absences_planning as $elem){
            if($elem['valide'] <= 0 and $config['Absences-non-validees'] == 0){
              continue;
            }

	    $heures=null;
	    $debut=null;
	    $fin=null;
	    if($elem['debut']>"$date 00:00:00"){
	      $debut=substr($elem['debut'],-8);
	    }
	    if($elem['fin']<"$date 23:59:59"){
	      $fin=substr($elem['fin'],-8);
	    }
	    if($debut and $fin){
	      $heures=" de ".heure2($debut)." à ".heure2($fin);
	    }
	    elseif($debut){
	      $heures=" à partir de ".heure2($debut);
	    }
	    elseif($fin){
	      $heures=" jusqu'à ".heure2($fin);
	    }
	    
	    $bold = null;
	    $nonValidee = null;
            if($config['Absences-non-validees'] == 1){
              if($elem['valide'] > 0){
                $bold = 'bold';
              }else{
                $nonValidee = " (non valid&eacute;e)";
              }
            }

	    $class=$class=="tr1"?"tr2":"tr1";
	    echo "<tr class='$class $bold'><td style='text-align:left;'>{$elem['nom']} {$elem['prenom']}{$heures}{$nonValidee}</td></tr>\n";
	  }
	  echo "</table>\n";
	}
	break;

      case "2" :
	if(!empty($absences_planning)){
	  echo "<h3 style='text-align:left;margin:40px 0 0 0;'>Liste des absents</h3>\n";
	  echo "<table id='tablePlanningAbsences' class='CJDataTable' data-sort='[[0],[1]]'><thead>\n";
	  echo "<tr><th>Nom</th><th>Pr&eacute;nom</th>\n";
	  echo "<th class='dataTableDateFR'>D&eacute;but</th>\n";
	  echo "<th class='dataTableDateFR'>Fin</th>\n";
	  echo "<th>Motif</th></tr></thead>\n";
	  echo "<tbody>\n";
	  foreach($absences_planning as $elem){
            if($elem['valide'] <= 0 and $config['Absences-non-validees'] == 0){
              continue;
            }

	    $bold = null;
	    $nonValidee = null;
            if($config['Absences-non-validees'] == 1){
              if($elem['valide'] > 0){
                $bold = 'bold';
              }else{
                $nonValidee = " (non valid&eacute;e)";
              }
            }
            
	    echo "<tr class='$bold'><td>{$elem['nom']}</td><td>{$elem['prenom']}</td>";
	    echo "<td>{$elem['debutAff']}</td><td>{$elem['finAff']}</td>";
	    echo "<td>{$elem['motif']}{$nonValidee}</td></tr>\n";
	  }
	  echo "</tbody></table>\n";
	}
	break;

      case "3" :
	// Sélection des agents présents
	$heures=null;
	$presents=array();
	$absents=array(2);	// 2 = Utilisateur "Tout le monde", on le supprime

	// On exclus ceux qui sont absents toute la journée
	if(!empty($absences_planning)){
	  foreach($absences_planning as $elem){
	    if($elem['debut']<=$date." 00:00:00" and $elem['fin']>=$date." 23:59:59" and $elem['valide']>0 ){
	      $absents[]=$elem['perso_id'];
	    }
	  }
	}

	// recherche des personnes à exclure (ne travaillant ce jour)
	$db=new db();
	$dateSQL=$db->escapeString($date);
	$db->select("personnel","*","`actif` LIKE 'Actif' AND (`depart` > $dateSQL OR `depart` = '0000-00-00')","ORDER BY `nom`,`prenom`");

	$verif=true;	// verification des heures des agents
	if(!$config['ctrlHresAgents'] and ($d->position==6 or $d->position==0)){
	  $verif=false; // on ne verifie pas les heures des agents le samedi et le dimanche (Si ctrlHresAgents est desactivé)
	}

	// Si il y a des agents et verification des heures de présences
	if($db->result and $verif){

	  // Si module PlanningHebdo : recherche des plannings correspondant à la date actuelle
	  if($config['PlanningHebdo']){
	    include "planningHebdo/planning.php";
	  }

	  // Pour chaque agent
	  foreach($db->result as $elem){
	    $heures=null;

	    // Récupération du planning de présence
	    $temps=array();

	    // Si module PlanningHebdo : emploi du temps récupéré à partir de planningHebdo
	    if($config['PlanningHebdo']){
	      if(array_key_exists($elem['id'],$tempsPlanningHebdo)){
		$temps=$tempsPlanningHebdo[$elem['id']];
	      }
	    }else{
	      // Emploi du temps récupéré à partir de la table personnel
	      $temps = json_decode(html_entity_decode($elem['temps'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	    }

	    $jour=$d->position-1;		// jour de la semaine lundi = 0 ,dimanche = 6
	    if($jour==-1){
	      $jour=6;
	    }

	    // Si semaine paire, position +7 : lundi A = 0 , lundi B = 7 , dimanche B = 13
	    if($config['nb_semaine']=="2" and !($semaine%2)){
	      $jour+=7;
	    }
	    // Si utilisation de 3 plannings hebdo
	    elseif($config['nb_semaine']=="3"){
	      if($semaine3==2){
		$jour+=7;
	      }
	      elseif($semaine3==3){
		$jour+=14;
	      }
	    }

	    // Si l'emploi du temps est renseigné
	    if(!empty($temps) and array_key_exists($jour,$temps)){
	      // S'il y a une heure de début (matin ou midi)
	      if($temps[$jour][0] or $temps[$jour][2]){
		$heures=$temps[$jour];
	      }
	    }

	    // S'il y a des horaires correctement renseignés
	    $siteAgent=null;
	    if($heures and !in_array($elem['id'],$absents)){
	      if($config['Multisites-nombre']>1){
		if(isset($heures[4])){
		  $siteAgent=$config['Multisites-site'.$heures[4]];
		}
	      }
	      $siteAgent=$siteAgent?$siteAgent.", ":null;


	      $horaires=null;
	      if(!$heures[1] and !$heures[2]){		// Pas de pause le midi
		$horaires=heure2($heures[0])." - ".heure2($heures[3]);
	      }
	      elseif(!$heures[2] and !$heures[3]){	// matin seulement
		$horaires=heure2($heures[0])." - ".heure2($heures[1]);
	      }
	      elseif(!$heures[0] and !$heures[1]){	// après midi seulement
		$horaires=heure2($heures[2])." - ".heure2($heures[3]);
	      }
	      else{		// matin et après midi avec pause
		$horaires=heure2($heures[0])." - ".heure2($heures[1])." &amp; ".heure2($heures[2])." - ".heure2($heures[3]);
	      }
	      $presents[]=array("id"=>$elem['id'],"nom"=>$elem['nom']." ".$elem['prenom'],"site"=>$siteAgent,"heures"=>$horaires);
	    }
	  }
	}

	echo "<table class='tableauStandard'>\n";
	echo "<tr><td><h3 style='text-align:left;margin:40px 0 0 0;'>Liste des présents</h3></td>\n";
	if(!empty($absences_planning)){
	  echo "<td><h3 style='text-align:left;margin:40px 0 0 0;'>Liste des absents</h3></td>";
	}
	echo "</tr>\n";

	// Liste des présents
	echo "<tr style='vertical-align:top;'><td>";
	echo "<table cellspacing='0'> ";
	$class="tr1";
	foreach($presents as $elem){
	  $class=$class=="tr1"?"tr2":"tr1";
	  echo "<tr class='$class'><td>{$elem['nom']}</td><td style='padding-left:15px;'>{$elem['site']}{$elem['heures']}</td></tr>\n";
	}
	echo "</table>\n";
	echo "</td>\n";

	// Liste des absents
	echo "<td>";
	echo "<table cellspacing='0'>";
	$class="tr1";
	foreach($absences_planning as $elem){
          if($elem['valide'] <= 0 and $config['Absences-non-validees'] == 0){
            continue;
          }
          
	  $heures=null;
	  $debut=null;
	  $fin=null;
	  if($elem['debut']>"$date 00:00:00"){
	    $debut=substr($elem['debut'],-8);
	  }
	  if($elem['fin']<"$date 23:59:59"){
	    $fin=substr($elem['fin'],-8);
	  }
	  if($debut and $fin){
	    $heures=", ".heure2($debut)." - ".heure2($fin);
	  }
	  elseif($debut){
	    $heures=" à partir de ".heure2($debut);
	  }
	  elseif($fin){
	    $heures=" jusqu'à ".heure2($fin);
	  }

	  $class=$class=="tr1"?"tr2":"tr1";
	  
	  $bold = null;
          $nonValidee = null;
          
          if($config['Absences-non-validees'] == 1){
            if($elem['valide'] > 0){
              $bold = 'bold';
            }else{
              $nonValidee = " (non valid&eacute;e)";
            }
          }

	  echo "<tr class='$class $bold'><td>{$elem['nom']} {$elem['prenom']}</td><td style='padding-left:15px;'>{$elem['motif']}{$heures}{$nonValidee}</td></tr>\n";
	}
	echo "</table>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	break;

    }
  }
}
					//---------------	FIN Affichage des absences		-----------------//
?>
</div>
</div>