<?php
/*
Planning Biblio, Version 1.5.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : planning/poste/menudiv.php
Création : mai 2011
Dernière modification : 23 août 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le menu déroulant avec le nom des services et des agents dans la page planning/poste/index.php.
Permet de placer les agents dans les cellules du planning. Ecrit le nom des agents dans les cellules en JavaScript (innerHTML)
et met à jour la base de données en arrière plan avec la fonction JavaScript "bataille navale"

Cette page est appelée par la fonction ItemSelMenu(e) déclendhée lors d'un click-droit dans la page planning/poste/index.php
*/

require_once "fonctions.php";
include "include/horaires.php";

//	Initilisation des variables
$site=$_SESSION['oups']['site'];
$date=$_GET['date'];
$poste=$_GET['poste'];
$debut=$_GET['debut'];
$fin=$_GET['fin'];
$sr=0;
$msg_deja_place="(DP)";
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

// recherche des personnes à exclure (déja placés)
//$req="SELECT `perso_id` FROM `{$dbprefix}pl_poste` WHERE ((`debut`>='$debut' AND `debut`<'$fin') OR (`fin`>'$debut' AND `fin`<='$fin')) AND `date`='$date'";

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
$req="SELECT `perso_id` FROM `{$dbprefix}absences` WHERE (`debut`<'$date $fin' AND `fin` >'$date $debut');";
$db->query($req);
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

  if(!empty($temps) and array_key_exists($jour,$temps)){
    $heures=$temps[$jour];
    if($heures[0]>$debut)				// Si l'agent commence le travail après l'heure de début du poste
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
	// requete final sélection tous les agents formés aux activités demandées et disponible (non exclus)
$req="SELECT * FROM `{$dbprefix}personnel` WHERE $req_poste `actif` LIKE 'Actif' AND (`depart` > $date OR `depart` = '0000-00-00') AND `id` NOT IN ($exclus) ORDER BY `nom`,`prenom`;";
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
$req="SELECT * FROM `{$dbprefix}personnel` WHERE `actif` LIKE 'Actif' AND (`depart` > $date OR `depart` = '0000-00-00') AND `id` NOT IN ($agents_qualif) AND `id` NOT IN ($tab_deja_place) AND `id` NOT IN ($absents) ORDER BY `nom`,`prenom`;";
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Planning, menudiv</title>
<?php echo "<script type='text/JavaScript' src='js/script.js'></script>\n"; ?>
<meta http-equiv="content-Type" content="text/html;CHARSET=UTF-8" />
</head>
<body style='background:#CCDDEE;'>
<table bgcolor='#FFFFFF' frame='box' cellspacing='0' cellpadding='0' id='menudivtab' rules='rows' border='1'>

<?php
	//		Affichage du nom du poste et des heures
echo "<tr class='menudiv-titre'><td colspan='2'>$aff_poste";
if(in_array(13,$droits)){
  echo " ($poste)";
}
echo "</td></tr>\n";
echo "<tr class='menudiv-titre'><td colspan='2'>".heure2($debut)." - ".heure2($fin)."</td></tr>\n";

//		-----------		Affichage de la liste des services		----------//
if($services){
  $i=0;
  foreach($services as $elem){
    if(array_key_exists($elem['service'],$newtab)){
      echo "<tr onmouseover='this.style.background=\"#7B7B7B\";' onmouseout='this.style.background=\"#FFFFFF\";'>\n";
      echo "<td colspan='2' style='width:200px;background:{$elem['couleur']};' onmouseover='groupe_tab($i,\"$tab_agent\");'>";
      echo $elem['service'];
      echo "</td></tr>\n";
    }
    $i++;
  }
}
//		-----------		Affichage des agents indisponibles		----------//
if(count($newtab["Autres"]) and $config['agentsIndispo']){
  $i=count($services);
  echo "<tr onmouseover='this.style.background=\"#7B7B7B\";' onmouseout='this.style.background=\"#FFFFFF\";'>\n";
  echo "<td colspan='2' style='width:200px;' onmouseover='groupe_tab($i,\"$tab_agent\");'>";
  echo "Agents indisponibles";
  echo "</td></tr>\n";
}

//		-----------		Affichage de l'utilisateur "tout le monde"		----------//
if(!in_array(2,$tab_exclus) and $config['toutlemonde']){
  echo "<tr onmouseover='this.style.background=\"#7B7B7B\";' onmouseout='this.style.background=\"#FFFFFF\";' >\n";
  echo "<td colspan='3' style='width:200px;color:$color;' ";
  echo "onclick='bataille_navale(2,\"#FFFFFF\",\"Tout le monde\",0,0,\"\");'>Tout le monde</td></tr>\n";
  $nbCol++;
}
//~ -----				Affiche de la "Case vide"  (suppression)	--------------------------//
if(!$cellule_vide){
  echo "<tr onmouseover='groupe_tab(\"vide\",\"$tab_agent\");this.style.background=\"#7B7B7B\";' onmouseout='this.style.background=\"#FFFFFF\";'>";
  echo "<td colspan='1' onclick='bataille_navale(0,\"#FFFFFF\",\"&nbsp;\",0,0,\"\");'>";
  echo "Supprimer</td><td>&nbsp;";
  echo "<a style='color:red' href='javascript:bataille_navale(0,\"#FFFFFF\",\"&nbsp;\",1,0,\"\");'>Barrer</a>&nbsp;&nbsp;</td></tr>";
  $nbCol++;
}
echo "</table>\n";
	
//	--------------		Affichage des agents			----------------//
echo "<table style='background:#FFFFFF;position:absolute;left:200px;top:8px;' frame='box' cellspacing='0' cellpadding='0' id='menudivtab2' rules='rows' border='1'>\n";
if($agents_tous){
  foreach($agents_tous as $elem){
    $newtab[$elem['service']][]=$elem['id'];  		// Affichage par service
    $color='black';
    $sr=0;
    $sr_cellule=null;
    $nom=$elem['nom'];
    if($elem['prenom']){
      $nom.=" ".substr($elem['prenom'],0,1).".";
    }
    
    //			----------------------		Sans repas		------------------------------------------//
    //			(Peut être amélioré : vérifie si l'agent est déjà placé entre 11h30 et 14h30 
    //			mais ne vérfie pas la continuité. Ne marque pas la 2ème cellule en javascript (rafraichissement OK))
    if($debut>="11:30:00" and $fin<="14:30:00"){
      $db_sr=new db();
      $db_sr->query("SELECT * FROM `{$dbprefix}pl_poste` WHERE `date`='$date' AND `perso_id`='{$elem['id']}' AND `debut` >='11:30:00' AND `fin`<='14:30:00';");
      if($db_sr->result){
	$sr=1;
	$nom.=" (SR)";
	$color='red';
      }
    }
	    
    $nom_menu=$nom;
    //			----------------------		Déjà placés		-----------------------------------------------------//
    if(in_array($elem['id'],$deja)){					//	Déjà placé pour ce poste
      $nom_menu.=" ".$msg_deja_place;
      $color='red';
    }
    //			----------------------		FIN Déjà placés		-----------------------------------------------------//
    
    //			Horaires tronqués (l'agent n'est pas disponible pendant toute la plage horaire
    //			A priori non utilisé par la BULAC, la requete de sélection des agents élimine ceux qui ne sont pas complétement dispo
/*
    $db_hrs=new db();
    $db_hrs->query("select * from {$dbprefix}pl_poste where date='$date' and perso_id='".$elem['id']."' and debut>='$debut' and fin<='$fin';");
    if($db_hrs->result)
	    {
	    $hrs="";
	    $horaires_a_soustraire=array();
	    foreach($db_hrs->result as $elem2)
		    {
		    if($debut!=$elem2['debut'])
			    $hrs.=heure3($debut)."->".heure3($elem2['debut']);
		    if($debut!=$elem2['debut'] and $elem2['fin']!=$fin)
			    $hrs.=" / ";
		    if($elem2['fin']!=$fin)
			    $hrs.=heure3($elem2['fin'])."->".heure3($fin);
		    $nom.="<br/>".$hrs;
		    $nom_menu.="<br/>".$hrs;
		    $ligneAdd++;
		    }
	    }
*/		
    // affihage des heures faites ce jour + les heures de la cellule
    $db_heures = new db();
    $db_heures->query("SELECT `{$dbprefix}pl_poste`.`debut` AS `debut`,`{$dbprefix}pl_poste`.`fin` AS `fin` FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` WHERE `{$dbprefix}pl_poste`.`perso_id`='{$elem['id']}' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`date`='$date' AND `{$dbprefix}postes`.`statistiques`='1';");
    if($stat){ 	// vérifier si le poste est compté dans les stats
      $hres_jour=diff_heures($debut,$fin,"decimal");
    }
    if($db_heures->result){
      foreach($db_heures->result as $hres){
	$hres_jour=$hres_jour+diff_heures($hres['debut'],$hres['fin'],"decimal");
      }
    }
    
    // affihage des heures faites cette semaine + les heures de la cellule
    $db_heures = new db();
    $db_heures->query("SELECT `{$dbprefix}pl_poste`.`debut` AS `debut`,`{$dbprefix}pl_poste`.`fin` AS `fin` FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` WHERE `{$dbprefix}pl_poste`.`perso_id`='{$elem['id']}' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`date` BETWEEN '$j1' AND '$j7' AND `{$dbprefix}postes`.`statistiques`='1';");

    if($stat){ 	// vérifier si le poste est compté dans les stats
      $hres_sem=diff_heures($debut,$fin,"decimal");
    }
    if($db_heures->result){
      foreach($db_heures->result as $hres){
	$hres_sem=$hres_sem+diff_heures($hres['debut'],$hres['fin'],"decimal");
      }
    }

    // affihage des heures faites les 4 dernières semaines + les heures de la cellule
    $hres_4sem=null;
    if($config['hres4semaines']){
      $date1=date("Y-m-d",strtotime("-3 weeks",strtotime($j1)));
      $date2=$j7;	// fin de semaine courante
      $db_hres4 = new db();
      $db_hres4->query("SELECT `{$dbprefix}pl_poste`.`debut` AS `debut`,`{$dbprefix}pl_poste`.`fin` AS `fin` FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` WHERE `{$dbprefix}pl_poste`.`perso_id`='{$elem['id']}' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`date` BETWEEN '$date1' AND '$date2' AND `{$dbprefix}postes`.`statistiques`='1';");
      if($stat){ 	// vérifier si le poste est compté dans les stats
	$hres_4sem=diff_heures($debut,$fin,"decimal");
      }
      if($db_hres4->result){
	foreach($db_hres4->result as $hres){
	  $hres_4sem=$hres_4sem+diff_heures($hres['debut'],$hres['fin'],"decimal");
	}
      }
      $hres_4sem=" / ".$hres_4sem;
    }

    //	Mise en forme de la ligne avec le nom et les heures et la couleur en fonction des heures faites
    $nom_menu.="&nbsp;$hres_jour / $hres_sem / {$elem['heuresHebdo']} $hres_4sem";
    if($hres_jour>7)			// plus de 7h:jour : rouge
      $nom_menu="<font style='color:red'>$nom_menu</font>\n";
    elseif(($elem['heuresHebdo']-$hres_sem)<=0.5 and ($hres_sem-$elem['heuresHebdo'])<=0.5)		// 0,5 du quota hebdo : vert
      $nom_menu="<font style='color:green'>$nom_menu</font>\n";
    elseif($hres_sem>$elem['heuresHebdo'])			// plus du quota hebdo : rouge
      $nom_menu="<font style='color:red'>$nom_menu</font>\n";
    
    
    //	Affichage des lignes
    $statut=strtolower(removeAccents($elem['statut']));
    echo "<tr id='tr{$elem['id']}' style='display:none;height:21px;' onmouseover='this.style.background=\"#7B7B7B\";' onmouseout='this.style.background=\"#FFFFFF\";'>\n";
    echo "<td style='width:200px;color:$color;' onclick='bataille_navale({$elem['id']},null,\"$nom\",0,0,\"$statut\");'>";
    echo $nom_menu;

    //	Afficher ici les horaires si besoin
    echo "</td><td style='text-align:right;width:20px'>";
    
    //	Affichage des liens d'ajout et de remplacement
    if(!$cellule_vide and !$max_perso and !$sr and !$sr_init)
      echo "<a href='javascript:bataille_navale(".$elem['id'].",null,\"$nom\",0,1,\"$statut\");'>+</a>";
    if(!$cellule_vide and !$max_perso)
      echo "&nbsp;<a style='color:red' href='javascript:bataille_navale(".$elem['id'].",null,\"$nom\",1,1,\"$statut\");'>x</a>&nbsp;";
    echo "</td></tr>\n";
    
  }	
}
echo "</table>";
echo "</body>";
echo "</html>";

// 	Les lignes suivantes permettent de compter le nombre de lignes afin de placer correctement le menu en fonction de sa hauteur
$nbCol=$db->nb+1;
$nbCol=($nbCol+$ligneAdd);
//--------------		FIN Liste du personnel disponible			---------------//
?>