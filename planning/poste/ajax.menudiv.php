<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/poste/ajax.menudiv.php
Création : mai 2011
Dernière modification : 8 janvier 2016
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Christophe Le Guennec <Christophe.Leguennec@u-pem.fr>

Description :
Affiche le menu déroulant avec le nom des services et des agents dans la page planning/poste/index.php.
Permet de placer les agents dans les cellules du planning. Ecrit le nom des agents dans les cellules en JavaScript (innerHTML)
et met à jour la base de données en arrière plan avec la fonction JavaScript "bataille navale"

Cette page est appelée par la fonction ItemSelMenu(e) déclendhée lors d'un click-droit dans la page planning/poste/index.php
*/

session_start();

ini_set("display_error",0);

require_once "../../include/config.php";
require_once "../../plugins/plugins.php";
require_once "../../include/function.php";
require_once "../../include/horaires.php";
require_once "../../absences/class.absences.php";
require_once "../../personnel/class.personnel.php";
require_once "fonctions.php";
require_once "class.planning.php";

//	Initilisation des variables
$site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);
$date=filter_input(INPUT_GET,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));
$debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_time"));
$fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_time"));
$perso_nom=filter_input(INPUT_GET,"perso_nom", FILTER_SANITIZE_STRING);
$poste=filter_input(INPUT_GET,"poste",FILTER_SANITIZE_NUMBER_INT);

$login_id=$_SESSION['login_id'];
$tab_exclus=array(0);
$absents=array(0);
$agents_qualif=array(0);
$tab_deja_place=array(0);
$sr_init=null;
$motifExclusion=array();
$tableaux=array();

$d=new datePl($date);
$j1=$d->dates[0];
$j7=$d->dates[6];
$semaine=$d->semaine;
$semaine3=$d->semaine3;

// PlanningHebdo et EDTSamedi étant incompatibles, EDTSamedi est désactivé si PlanningHebdo est activé
if($config['PlanningHebdo']){
  $config['EDTSamedi']=0;
}
  
//			----------------		Vérification des droits d'accès		-----------------------------//
$url=explode("?",$_SERVER['REQUEST_URI']);
$url=$url[0];
if(!$_SESSION['login_id']){
  exit;
}
else{
  $autorisation=false;
  $db_admin=new db();			// Vérifions si l'utilisateur à les droits de modifier les plannings
  $db_admin->select2("personnel","droits",array("id"=>$login_id));
  $droits=unserialize($db_admin->result[0]['droits']);
  if(!in_array(12,$droits)){
    exit;
  }
}
//			----------------		FIN Vérification des droits d'accès		-----------------------------//


// nom et activités du poste
$db=new db;
$db->select2("postes",null,array("id"=>$poste));
$posteNom=$db->result[0]['nom'];
$activites=unserialize($db->result[0]['activites']);
$stat=$db->result[0]['statistiques'];
$bloquant=$db->result[0]['bloquant'];
$categories=is_serialized($db->result[0]['categories'])?unserialize($db->result[0]['categories']):array();

// Nom du site
$siteNom=null;
if($config['Multisites-nombre']>1){
  $siteNom=$config["Multisites-site$site"];
}

// Liste des statuts correspondant aux catégories nécessaires pour être placé sur le poste
$statuts=array();
if(!empty($categories)){
  $categories=join(",",$categories);
  $db=new db();
  $categories=$db->escapeString($categories);
  $db->select("select_statuts",null,"categorie IN ($categories)");
  if($db->result){
    foreach($db->result as $elem){
     $statuts[]=$elem['valeur'];
    }
  }
}

//	Recherche des services
$db=new db();
$db->query("SELECT `{$dbprefix}personnel`.`service` AS `service`, `{$dbprefix}select_services`.`couleur` AS `couleur` FROM `{$dbprefix}personnel` INNER JOIN `{$dbprefix}select_services`
	ON `{$dbprefix}personnel`.`service`=`{$dbprefix}select_services`.`valeur` WHERE `{$dbprefix}personnel`.`service`<>'' GROUP BY `service`;");
$services=$db->result;
$services[]=array("service"=>"Sans service");

//	Ne pas regarder les postes non-bloquant et ne pas regarder si le poste est non-bloquant
if($bloquant=='1'){
  $db=new db();
  $dateSQL=$db->escapeString($date);
  $debutSQL=$db->escapeString($debut);
  $finSQL=$db->escapeString($fin);

  $req="SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` FROM `{$dbprefix}pl_poste` "
  	."INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
  	."WHERE `{$dbprefix}pl_poste`.`debut`<'$finSQL' AND `{$dbprefix}pl_poste`.`fin`>'$debutSQL' "
        ."AND `{$dbprefix}pl_poste`.`date`='$dateSQL' AND `{$dbprefix}postes`.`bloquant`='1'";
  	
  $db->query($req);
  if($db->result)
  foreach($db->result as $elem){
    $tab_exclus[]=$elem['perso_id'];
    $tab_deja_place[]=$elem['perso_id'];
  }
}

// recherche des personnes à exclure (absents)
$filter=$config['Absences-validation']?"AND `valide`>0":null;

$db=new db();
$dateSQL=$db->escapeString($date);
$debutSQL=$db->escapeString($debut);
$finSQL=$db->escapeString($fin);

$db->select("absences","perso_id","`debut`<'$dateSQL $finSQL' AND `fin` >'$dateSQL $debutSQL' $filter ");

if($db->result){
  foreach($db->result as $elem){
    $tab_exclus[]=$elem['perso_id'];
    $absents[]=$elem['perso_id'];
  }
}

// recherche des personnes à exclure (congés)
if(in_array("conges",$plugins)){
  include "../../plugins/conges/menudiv.php";
}

// recherche des personnes à exclure (ne travaillant pas à cette heure)
$db=new db();
$dateSQL=$db->escapeString($date);

$db->query("SELECT * FROM `{$dbprefix}personnel` WHERE `actif` LIKE 'Actif' AND (`depart` > $dateSQL OR `depart` = '0000-00-00');");

$verif=true;	// verification des heures des agents
if(!$config['ctrlHresAgents'] and ($d->position==6 or $d->position==0)){
  $verif=false; // on ne verifie pas les heures des agents le samedi et le dimanche (Si ctrlHresAgents est desactivé)
}

// Si module PlanningHebdo : recherche des plannings correspondant à la date actuelle
if($config['PlanningHebdo']){
  require_once "../../planningHebdo/class.planningHebdo.php";
  $p=new planningHebdo();
  $p->debut=$date;
  $p->fin=$date;
  $p->valide=true;
  $p->fetch();

  $tempsPlanningHebdo=array();

  if(!empty($p->elements)){
    foreach($p->elements as $elem){
      $tempsPlanningHebdo[$elem["perso_id"]]=$elem["temps"];
    }
  }

}

if($db->result and $verif)
foreach($db->result as $elem){
  $aExclure=false;

  // Récupération du planning de présence
  $temps=array();

  // Si module PlanningHebdo : emploi du temps récupéré à partir de planningHebdo
  if($config['PlanningHebdo']){
    if(array_key_exists($elem['id'],$tempsPlanningHebdo)){
      $temps=$tempsPlanningHebdo[$elem['id']];
    }
  }else{
    // Emploi du temps récupéré à partir de la table personnel
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
  if($config['Multisites-nombre']>1){
    if(!isset($heures)){
      $aExclure=true;
    }
    elseif(!array_key_exists(4,$heures)){
      $aExclure=true;
    }
    elseif($heures[4]!=$site){
      $aExclure=true;
      $motifExclusion[$elem['id']][]="Autre site";
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

// Récupère le nombre d'agents déjà placés dans la cellule
$db=new db();
$dateSQL=$db->escapeString($date);
$debutSQL=$db->escapeString($debut);
$finSQL=$db->escapeString($fin);
$posteSQL=$db->escapeString($poste);
$siteSQL=$db->escapeString($site);

$db->select("pl_poste",null,"`poste`='$posteSQL' AND `debut`='$debutSQL' AND `fin`='$finSQL' AND `date`='$dateSQL' AND `site`='$siteSQL' AND `perso_id`>0");

$nbAgents=$db->nb;

//--------------		Liste du personnel disponible			---------------//

		// construction de la requete de sélection du personnel formé pour les activités demandées
$db=new db();
$dateSQL=$db->escapeString($date);

$req_poste=null;
$req_statut=null;

if($poste!=0){		//	repas
  if(is_array($activites)){
    foreach($activites as $elem){
      $tab[]="`postes` LIKE '%\"$elem\"%'";
    }
    $req_poste="(".join($tab," AND ").") AND ";
  }
  if(!empty($statuts)){
    $req_statut="`statut` IN ('".join("','",$statuts)."') AND ";
  }
}
// Requete final sélection tous les agents formés aux activités demandées et disponible (non exclus)
// Multisites : Si les agents ne sont pas autorisés à travailler sur le site sélectionné, on les retire
$req_site=null;
if($config['Multisites-nombre']>1){
  $siteSQL=$db->escapeString($site);
  $req_site=" AND `sites` LIKE '%\"$siteSQL\"%' ";
}


$req="SELECT * FROM `{$dbprefix}personnel` "
  ."WHERE $req_poste $req_statut `actif` LIKE 'Actif' AND `arrivee` <= '$dateSQL' AND (`depart` > '$dateSQL' OR `depart` = '0000-00-00') "
  ."AND `id` NOT IN ($exclus) $req_site ORDER BY `nom`,`prenom`;";

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

$db=new db();
$dateSQL=$db->escapeString($date);

$req="SELECT * FROM `{$dbprefix}personnel` "
  ."WHERE `actif` LIKE 'Actif' AND `arrivee` <= '$dateSQL' AND (`depart` > $dateSQL OR `depart` = '0000-00-00') AND `id` NOT IN ($agents_qualif) "
  ."AND `id` NOT IN ($tab_deja_place) AND `id` NOT IN ($absents)  $req_site ORDER BY `nom`,`prenom`;";

$db->query($req);
$autres_agents=$db->result;

$agents_tous=$agents_dispo;
if(is_array($autres_agents)){
  foreach($autres_agents as $elem){
    $agents_tous[]=$elem;
  }
}

			// Creation des différentes listes (par service + liste des absents + liste des non qualifiés)
// Affichage par service
$newtab=array();
if($agents_dispo){
  foreach($agents_dispo as $elem){
    if($elem['id']!=2){
      if(!trim($elem['service'])){
	$newtab["Sans service"][]=$elem['id'];
      }else{
	$newtab[$elem['service']][]=$elem['id'];
      }
    }
  }
}

if($autres_agents){
  foreach($autres_agents as $elem){
    if($elem['id']!=2)
      $newtab["Autres"][]=$elem['id'];  		// Affichage des agents hors horaires, non qualifiés
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

if(array_key_exists("Autres",$newtab)){
  $listparservices[]=join($newtab['Autres'],",");
}
else{
  $listparservices[]=null;
}
$tab_agent=join($listparservices,";");
	
// début d'affichage
$tableaux[0]="<table frame='box' cellspacing='0' cellpadding='0' id='menudivtab1' rules='rows' border='1'>\n";

	//		Affichage du nom du poste et des heures
$tableaux[0].="<tr class='menudiv-titre'><td colspan='2'>$posteNom";
if(in_array(13,$droits)){
  $tableaux[0].=" ($poste)";
}
$tableaux[0].="</td></tr>\n";
$tableaux[0].="<tr class='menudiv-titre'><td colspan='2'>".heure2($debut)." - ".heure2($fin)."</td></tr>\n";

//		-----------		Affichage de la liste des services		----------//
if($services and $config['ClasseParService']){
  $i=0;
  foreach($services as $elem){
    $class="service_".strtolower(removeAccents(str_replace(" ","_",$elem['service'])));
    if(array_key_exists($elem['service'],$newtab)){
      $tableaux[0].="<tr class='$class menudiv-tr'>\n";
      $tableaux[0].="<td colspan='2' style='width:200px;' onmouseover='groupe_tab($i,\"$tab_agent\",1,$(this));'>";
      $tableaux[0].=$elem['service'];
      $tableaux[0].="</td></tr>\n";
    }
    $i++;
  }
}

//		-----------		Affichage de la liste des agents s'ils ne sont pas classés par services		----------//
if(!$config['ClasseParService']){
  $hide=false;
  $p=new planning();
  $p->site=$site;
  $p->menudivAfficheAgents($poste,$agents_dispo,$date,$debut,$fin,$deja,$stat,$nbAgents,$sr_init,$hide,$deuxSP,$motifExclusion);
  $tableaux[0].=$p->menudiv;
}

//		-----------		Affichage des agents indisponibles		----------//
if(array_key_exists("Autres",$newtab) and $config['agentsIndispo']){
  $i=count($services);
  $groupe_tab_hide=$config['ClasseParService']?1:0;
  $tableaux[0].="<tr class='menudiv-tr'>\n";
  $tableaux[0].="<td colspan='2' style='width:200px;' onmouseover='groupe_tab($i,\"$tab_agent\",$groupe_tab_hide,$(this));' >";
  $tableaux[0].="Agents indisponibles";
  $tableaux[0].="</td></tr>\n";
}

//		-----------		Affichage de l'utilisateur "tout le monde"		----------//
if($config['toutlemonde']){
  $tableaux[0].="<tr onmouseover='groupe_tab_hide();' class='menudiv-tr' >\n";
  $tableaux[0].="<td colspan='3' style='width:200px;color:black;' ";
  $tableaux[0].="onclick='bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",2,0,0,\"$site\");'>Tout le monde</td></tr>\n";
}
//~ -----				Affiche de la "Case vide"  (suppression)	--------------------------//
if($nbAgents>0){
  $groupe_tab=$config['ClasseParService']?"groupe_tab(\"vide\",\"$tab_agent\",1,$(this));":null;
  $tableaux[0].="<tr onmouseover='$groupe_tab groupe_tab_hide();' class='menudiv-tr'>";
  $tableaux[0].="<td colspan='2' onclick='bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",0,0,0,\"$site\");'>";
  $tableaux[0].="Supprimer $perso_nom</td><tr>\n";
  $tableaux[0].="<tr onmouseover='$groupe_tab groupe_tab_hide();' class='menudiv-tr'>";
  $tableaux[0].="<td colspan='2' onclick='bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",0,1,0,\"$site\");' class='red'>";
  $tableaux[0].="Barrer $perso_nom</td></tr>";

  // Ne pas afficher les lignes suivantes si un seul agent dans la cellule
  if($nbAgents>1){
    $tableaux[0].="<tr onmouseover='$groupe_tab groupe_tab_hide();' class='menudiv-tr'>";
    $tableaux[0].="<td colspan='2' onclick='bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",0,0,0,\"$site\",1);'>";
    $tableaux[0].="Tout supprimer</td><tr>\n";
    $tableaux[0].="<tr onmouseover='$groupe_tab groupe_tab_hide();' class='menudiv-tr'>";
    $tableaux[0].="<td colspan='2' onclick='bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",0,1,0,\"$site\",1);' class='red'>";
    $tableaux[0].="Tout barrer</td></tr>";
  }
}

// Ajout du lien pour les appels à disponibilité
if($config['Planning-AppelDispo']){
  // Consulte la base de données pour savoir si un mail a déjà été envoyé
  $db=new db();
  $db->select2("appelDispo",null,array("site"=>$site,"poste"=>$poste,"date"=>$date,"debut"=>$debut,"fin"=>$fin),"ORDER BY `timestamp` desc");
  $nbEnvoi=$db->nb;
  if($db->result){
    $dateEnvoi=dateFr($db->result[0]['timestamp']);
    $heureEnvoi=heure2(substr($db->result[0]['timestamp'],11,5));
    $destinataires=count(explode(";",$db->result[0]['destinataires']));
    $s=$destinataires>1?"s":null;

    $nbEnvoiInfo="L&apos;appel &agrave; disponibilit&eacute; a d&eacute;j&agrave; &eacute;t&eacute; envoy&eacute; $nbEnvoi fois&#013;";
    $nbEnvoiInfo.="Dernier envoi le $dateEnvoi &agrave; $heureEnvoi&#013;";
    $nbEnvoiInfo.="$destinataires personne{$s} contact&eacute;e{$s}";
  }

  $agents=addslashes(json_encode($agents_dispo));
  $tableaux[0].="<tr onmouseover='groupe_tab_hide();' class='menudiv-tr'>";
  $tableaux[0].="<td colspan='2' onclick='appelDispo(\"$site\",\"$siteNom\",\"$poste\",\"$posteNom\",\"$date\",\"$debut\",\"$fin\",\"$agents\");'>";
  $tableaux[0].="Appel &agrave; disponibilit&eacute;\n";
  if($nbEnvoi){
    $tableaux[0].="<span title='$nbEnvoiInfo' style='position:absolute; right:5px;'><strong>$nbEnvoi</strong></span>\n";
  }
  $tableaux[0].="</td><tr>\n";
}

$tableaux[0].="</table>\n";

//	--------------		Affichage des agents			----------------//
$tableaux[1]="<table cellspacing='0' cellpadding='0' id='menudivtab2' rules='rows' border='1'>\n";

//		-----------		Affichage de la liste des agents s'ils sont classés par services		----------//
if($agents_tous and $config['ClasseParService']){
  $hide=true;
  $p=new planning();
  $p->site=$site;
  $p->menudivAfficheAgents($poste,$agents_tous,$date,$debut,$fin,$deja,$stat,$nbAgents,$sr_init,$hide,$deuxSP,$motifExclusion);
  $tableaux[1].=$p->menudiv;
}

//		-----------		Affichage de la liste des agents indisponibles 'ils ne sont pas classés par services	----------//
if($autres_agents and !$config['ClasseParService'] and $config['agentsIndispo']){
  $hide=true;
  $p=new planning();
  $p->site=$site;
  $p->menudivAfficheAgents($poste,$autres_agents,$date,$debut,$fin,$deja,$stat,$nbAgents,$sr_init,$hide,$deuxSP,$motifExclusion);
  $tableaux[1].=$p->menudiv;
}

$tableaux[1].="</table>";
//--------------		FIN Liste du personnel disponible			---------------//
echo json_encode($tableaux);
?>
