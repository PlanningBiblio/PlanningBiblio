<?php
/*
Planning Biblio, Version 1.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/menudiv.php
Création : mai 2011
Dernière modification : 21 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le menu déroulant avec le nom des services et des agents dans la page planning/poste/index.php.
Permet de placer les agents dans les cellules du planning. Ecrit le nom des agents dans les cellules en JavaScript (innerHTML)
et met à jour la base de données en arrière plan avec la fonction JavaScript "bataille navale"

Cette page est appelée par la fonction ItemSelMenu(e) déclendhée lors d'un click-droit dans la page planning/poste/index.php
*/

require_once "fonctions.php";
require_once "class.planning.php";
include "include/horaires.php";
include "personnel/class.personnel.php";

//	Initilisation des variables
$site=$_SESSION['oups']['site'];
$date=$_GET['date'];
$poste=$_GET['poste'];
$debut=$_GET['debut'];
$fin=$_GET['fin'];
$sr=0;
$cellule_vide=true;
$max_perso=false;
$tab_exclus=array(0);
$absents=array(0);
$agents_qualif=array(0);
$tab_deja_place=array(0);
$sr_init=null;
$nbCol=0;
$hres_jour=0;
$hres_sem=0;

$d=new datePl($date);
$j1=$d->dates[0];
$j7=$d->dates[6];
$semaine=$d->semaine;
$semaine3=$d->semaine3;
$ligneAdd=0;

//			----------------		Vérification des droits d'accès		-----------------------------//
$url=explode("?",$_SERVER['REQUEST_URI']);
$url=$url[0];
if(!$_SESSION['login_id']){
  exit;
}
else{
  $autorisation=false;
  $db_admin=new db();			// Vérifions si l'utilisateur à les droits de modifier les plannings
  $db_admin->query("SELECT `droits` FROM `{$dbprefix}personnel` WHERE `id`={$_SESSION['login_id']};");
  $droits=unserialize($db_admin->result[0]['droits']);
  if(in_array(12,$droits)){
    $autorisation=true;
  }
  
  if(!$autorisation){			// redirection vers une page blanche (le menu ne sera pas affiché) s'il nest pas autorisé
    header("Location: /planning/lib/blank.php");
  }
}
//			----------------		FIN Vérification des droits d'accès		-----------------------------//


// nom et activités du poste
$db=new db;
$db->query("SELECT * FROM  `{$dbprefix}postes` WHERE `id`='$poste';");
$aff_poste=$db->result[0]['nom'];
$activites=unserialize($db->result[0]['activites']);
$stat=$db->result[0]['statistiques'];
$bloquant=$db->result[0]['bloquant'];

//	Recherche des services
$db=new db();
$db->query("SELECT `{$dbprefix}personnel`.`service` AS `service`, `{$dbprefix}select_services`.`couleur` AS `couleur` FROM `{$dbprefix}personnel` INNER JOIN `{$dbprefix}select_services`
	ON `{$dbprefix}personnel`.`service`=`{$dbprefix}select_services`.`valeur` WHERE `{$dbprefix}personnel`.`service`<>'' GROUP BY `service`;");
$services=$db->result;

//	Ne pas regarder les postes non-bloquant et ne pas regarder si le poste est non-bloquant
if($bloquant=='1'){
  $req="SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` WHERE ((`{$dbprefix}pl_poste`.`debut`>='$debut' AND `{$dbprefix}pl_poste`.`debut`<'$fin') OR (`{$dbprefix}pl_poste`.`fin`>'$debut' AND `{$dbprefix}pl_poste`.`fin`<='$fin')) AND `{$dbprefix}pl_poste`.`date`='$date' AND `{$dbprefix}postes`.`bloquant`='1'";
  $db=new db();
  $db->query($req);
  if($db->result)
  foreach($db->result as $elem){
    $tab_exclus[]=$elem['perso_id'];
    $tab_deja_place[]=$elem['perso_id'];
  }
}

// recherche des personnes à exclure (absents)
$db=new db();
$filter=$config['Absences-validation']?"AND `valide`>0":null;
$db->select("absences","perso_id","`debut`<'$date $fin' AND `fin` >'$date $debut' $filter ");
if($db->result){
  foreach($db->result as $elem){
    $tab_exclus[]=$elem['perso_id'];
    $absents[]=$elem['perso_id'];
  }
}

// recherche des personnes à exclure (congés)
if(in_array("conges",$plugins)){
  include "plugins/conges/menudiv.php";
}

// recherche des personnes à exclure (ne travaillant pas à cette heure)
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}personnel` WHERE `actif` LIKE 'Actif' AND (`depart` > $date OR `depart` = '0000-00-00');");

$verif=true;	// verification des heures des agents
if(!$config['ctrlHresAgents'] and ($d->position==6 or $d->position==0)){
  $verif=false; // on ne verifie pas les heures des agents le samedi et le dimanche (Si ctrlHresAgents est desactivé)
}
	
if($db->result and $verif)
foreach($db->result as $elem){
  $aExclure=false;
  // Si plugin PlanningHebdo : recherche des plannings correspondant à la date actuelle
  if(in_array("planningHebdo",$plugins)){
    include "plugins/planningHebdo/menudiv.php";
  }
  else{
    $temps=unserialize($elem['temps']);
  }

  $jour=$d->position-1;		// jour de la semaine lundi = 0 ,dimanche = 6
  if($jour==-1){
    $jour=6;
  }

  // Si utilisation de 2 plannings hebdo (semaine paire et semaine impaire)
  // Si semaine paire, position +=7 : lundi A = 0 , lundi B = 7 , dimanche B = 13
  if($config['nb_semaine']=="2" and !($semaine%2) and !$config['EDTSamedi']){
    $jour+=7;
  }
  // Si utilisation de 3 plannings hebdo
  elseif($config['nb_semaine']=="3" and !$config['EDTSamedi']){
    if($semaine3==2){
      $jour+=7;
    }
    elseif($semaine3==3){
      $jour+=14;
    }
  }

  // Si utilisation d'un planning pour les semaines sans samedi et un planning pour les semaines avec samedi travaillé
  if($config['EDTSamedi']){
    // Pour chaque agent, recherche si la semaine courante est avec samedi travaillé ou non
    $p=new personnel();
    $p->fetchEDTSamedi($elem['id'],$j1,$j1);
    // Si oui, utilisation du 2ème emploi du temps ($jour+=7)
    if(!empty($p->elements)){
      $jour+=7;
    }
  }

  if(!empty($temps) and array_key_exists($jour,$temps)){
    $heures=$temps[$jour];
    if($heures[0] and $heures[1] and !$heures[3]){ 	// Pour les agents ne travaillant que le matin
      $heures[3]=$heures[1];				// Fin de journée = fin de matinée
    }
    if($heures[2] and $heures[3] and !$heures[0]){ 	// Pour les agents ne travaillant que l'après midi
      $heures[0]=$heures[2];				// Début de journée = début d'après midi
    }
    if($heures[0]>$debut)			// Si l'agent commence le travail après l'heure de début du poste
      $aExclure=true;
    if($heures[3]<$fin)				// Si l'agent fini le travail avant l'heure de fin du poste
      $aExclure=true;
    if($heures[1]<$fin and $heures[2]>$debut) 	// Pdt la pause déjeuner : Si le debut de sa pause est avant l'heure de fin du poste et la fin de sa pause après le début du poste
      $aExclure=true;
  }
  else{
    $aExclure=true;
  }

  // Multisites : Contrôle si l'agent est prévu sur ce site si les agents sont autorisés à travailler sur plusieurs sites
  if($config['Multisites-nombre']>1 and $config['Multisites-agentsMultisites']){
    if(!isset($heures)){
      $aExclure=true;
    }
    elseif(!array_key_exists(4,$heures)){
      $aExclure=true;
    }
    elseif($heures[4]!=$site){
      $aExclure=true;
    }
  }
  // Multisites : Contrôle si l'agent est prévu sur ce site si les agents ne sont pas autorisés à travailler sur plusieurs sites
  if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
    if($elem['site']!=$site){
      $aExclure=true;
    }
  }

  if($aExclure){
    $tab_exclus[]=$elem['id'];
  }
}

$exclus=join($tab_exclus,",");

// Contrôle du personnel déjà placé dans la ligne
$deja=deja_place($date,$poste);

// Contrôle du personnel placé juste avant ou juste après la plage choisie
$deuxSP=deuxSP($date,$debut,$fin);

//		-----------------------------		Contrôle si la cellule est vide 		-----------------------------//
$db=new db();
$db->select("pl_poste",null,"`poste`='$poste' AND `debut`='$debut' AND `fin`='$fin' AND `date`='$date' AND `site`='$site'");
if($db->result and $db->result[0]['perso_id']!='0'){
  $cellule_vide=false;
}
if($db->nb>1){
  $max_perso=true;
}
//		-----------------------------		FIN Contrôle si la cellule est vide 		-----------------------------//

	//--------------		Liste du personnel disponible			---------------//

		// construction de la requete de sélection du personnel formé pour les activités demandées
if($poste!=0){		//	repas
  if(is_array($activites)){
    foreach($activites as $elem){
      $tab[]="`postes` LIKE '%\"$elem\"%'";
    }
    $req_poste="(".join($tab," AND ").") AND ";
  }
}

// Requete final sélection tous les agents formés aux activités demandées et disponible (non exclus)
// Multisites : Si les agents ne sont pas autorisés à travailler sur le site sélectionné, on les retire
if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
  $req_site=" AND `site`='$site' ";
}
else{
  $req_site=null;
}

$req="SELECT * FROM `{$dbprefix}personnel` WHERE $req_poste `actif` LIKE 'Actif' AND (`depart` > '$date' OR `depart` = '0000-00-00') AND `id` NOT IN ($exclus) $req_site ORDER BY `nom`,`prenom`;";
$db=new db();
$db->query($req);
$agents_dispo=$db->result;

	// requete "Agents indisponibles"
if(is_array($agents_dispo))
foreach($agents_dispo as $elem){
  $agents_qualif[]=$elem['id'];
}
$agents_qualif=join($agents_qualif,",");
$absents=join($absents,",");
$tab_deja_place=join($tab_deja_place,",");
$req="SELECT * FROM `{$dbprefix}personnel` WHERE `actif` LIKE 'Actif' AND (`depart` > $date OR `depart` = '0000-00-00') AND `id` NOT IN ($agents_qualif) AND `id` NOT IN ($tab_deja_place) AND `id` NOT IN ($absents)  $req_site ORDER BY `nom`,`prenom`;";
$db=new db();
$db->query($req);
$autres_agents=$db->result;

//		recherche des agents hors horaires qualifiés
/*
$horsHoraires=join($horsHoraires,",");
$req="SELECT * FROM `{$dbprefix}personnel` WHERE $req_poste `actif` LIKE 'Actif' AND (`depart` > $date OR `depart` = '0000-00-00') AND `id` IN ($horsHoraires) ORDER BY `nom`,`prenom`;";
$db=new db();
$db->query($req);
$agents_horsHoraires=$db->result;
*/
$agents_tous=$agents_dispo;
// if(is_array($agents_horsHoraires))
// foreach($agents_horsHoraires as $elem)
	// $agents_tous[]=$elem;
if(is_array($autres_agents)){
  foreach($autres_agents as $elem){
    $agents_tous[]=$elem;
  }
}

			// Creation des différentes listes (par service + liste des absents + liste des non qualifiés)
if($agents_dispo){
  foreach($agents_dispo as $elem){
    if($elem['id']!=2){
      $newtab[$elem['service']][]=$elem['id'];  		// BULAC AFFICHAGE par service
    }
  }
}

if($autres_agents){
  foreach($autres_agents as $elem){
    if($elem['id']!=2)
    $newtab["Autres"][]=$elem['id'];  		// Affichage des agents absents, hors horaires, non qualifiés
  }
}

$listparservices=Array();
if(is_array($services)){
  foreach($services as $elem){
    if(array_key_exists($elem['service'],$newtab)){
      $listparservices[]=join($newtab[$elem['service']],",");
    }
    else{
      $listparservices[]=null;
    }
  }
}

if(is_array($newtab['Autres'])){
  $listparservices[]=join($newtab['Autres'],",");
}
else{
  $listparservices[]=null;
}
$tab_agent=join($listparservices,";");
	
// début d'affichage
echo "<table frame='box' cellspacing='0' cellpadding='0' id='menudivtab' rules='rows' border='1'>\n";

	//		Affichage du nom du poste et des heures
echo "<tr class='menudiv-titre'><td colspan='2'>$aff_poste";
if(in_array(13,$droits)){
  echo " ($poste)";
}
echo "</td></tr>\n";
echo "<tr class='menudiv-titre'><td colspan='2'>".heure2($debut)." - ".heure2($fin)."</td></tr>\n";

//		-----------		Affichage de la liste des services		----------//
if($services and $config['ClasseParService']){
  $i=0;
  foreach($services as $elem){
    if(array_key_exists($elem['service'],$newtab)){
      echo "<tr onmouseover='$(this).addClass(\"menudiv-gris\");' onmouseout='$(this).removeClass(\"menudiv-gris\");'>\n";
      echo "<td colspan='2' style='width:200px;background:{$elem['couleur']};' onmouseover='groupe_tab($i,\"$tab_agent\",1);'>";
      echo $elem['service'];
      echo "</td></tr>\n";
    }
    $i++;
  }
}

//		-----------		Affichage de la liste des agents s'ils ne sont pas classés par services		----------//
if(!$config['ClasseParService']){
  $hide=false;
  $p=new planning();
  $p->menudivAfficheAgents($agents_dispo,$date,$debut,$fin,$deja,$stat,$cellule_vide,$max_perso,$sr_init,$hide,$deuxSP);
}

//		-----------		Affichage des agents indisponibles		----------//
if(count($newtab["Autres"]) and $config['agentsIndispo']){
  $i=count($services);
  $groupe_tab_hide=$config['ClasseParService']?1:0;
  echo "<tr onmouseover='$(this).addClass(\"menudiv-gris\");' onmouseout='$(this).removeClass(\"menudiv-gris\");'>\n";
  echo "<td colspan='2' style='width:200px;' onmouseover='groupe_tab($i,\"$tab_agent\",$groupe_tab_hide);' >";
  echo "Agents indisponibles";
  echo "</td></tr>\n";
}

//		-----------		Affichage de l'utilisateur "tout le monde"		----------//
if(!in_array(2,$tab_exclus) and $config['toutlemonde']){
  echo "<tr onmouseover='$(this).addClass(\"menudiv-gris\");groupe_tab_hide();' onmouseout='$(this).removeClass(\"menudiv-gris\");'>\n";
  echo "<td colspan='3' style='width:200px;color:black;' ";
  echo "onclick='bataille_navale(2,\"#FFFFFF\",\"Tout le monde\",0,0,\"\");'>Tout le monde</td></tr>\n";
  $nbCol++;
}
//~ -----				Affiche de la "Case vide"  (suppression)	--------------------------//
if(!$cellule_vide){
  $groupe_tab=$config['ClasseParService']?"groupe_tab(\"vide\",\"$tab_agent\",1);":null;
  echo "<tr onmouseover='$groupe_tab $(this).addClass(\"menudiv-gris\");' onmouseout='$(this).removeClass(\"menudiv-gris\");'>";
  echo "<td colspan='1' onclick='bataille_navale(0,\"#FFFFFF\",\"&nbsp;\",0,0,\"\");'>";
  echo "Supprimer</td><td>&nbsp;";
  echo "<a style='color:red' href='javascript:bataille_navale(0,\"#FFFFFF\",\"&nbsp;\",1,0,\"\");'>Barrer</a>&nbsp;&nbsp;</td></tr>";
  $nbCol++;
}
echo "</table>\n";

//	--------------		Affichage des agents			----------------//
echo "<table style='background:#FFFFFF;position:absolute;left:200px;top:8px;' frame='box' cellspacing='0' cellpadding='0' id='menudivtab2' rules='rows' border='1'>\n";

//		-----------		Affichage de la liste des agents s'ils sont classés par services		----------//
if($agents_tous and $config['ClasseParService']){
  $hide=true;
  $p=new planning();
  $p->menudivAfficheAgents($agents_tous,$date,$debut,$fin,$deja,$stat,$cellule_vide,$max_perso,$sr_init,$hide,$deuxSP);
}

//		-----------		Affichage de la liste des agents indisponibles 'ils ne sont pas classés par services	----------//
if($autres_agents and !$config['ClasseParService'] and $config['agentsIndispo']){
  $hide=true;
  $p=new planning();
  $p->menudivAfficheAgents($autres_agents,$date,$debut,$fin,$deja,$stat,$cellule_vide,$max_perso,$sr_init,$hide,$deuxSP);
}

echo "</table>";
// 	Les lignes suivantes permettent de compter le nombre de lignes afin de placer correctement le menu en fonction de sa hauteur
$nbCol=$db->nb+1;
$nbCol=($nbCol+$ligneAdd);
//--------------		FIN Liste du personnel disponible			---------------//
?>