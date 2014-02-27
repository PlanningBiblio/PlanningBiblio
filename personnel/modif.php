<?php
/*
Planning Biblio, Version 1.7.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : personnel/modif.php
Création : mai 2011
Dernière modification : 27 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le formulaire permettant d'ajouter ou de modifier les agents.
Page séparée en 4 <div> (Général, Activités, Emploi du temps, Droits d'accès. Ces <div> s'affichent lors des click sur
les onglets (fonction JS show).
Ce formulaire est soumis au fichier personnel/valid.php

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";
// NB : le champ poste et les fonctions postes_... sont utilisés pour l'attribution des activités (qualification)
$db_groupes=new db();		// contrôle des droits d'accès à cette page (sera amélioré prochaienement)
$db_groupes->query("select groupe_id,groupe from {$dbprefix}acces where groupe_id not in (99,100) group by groupe;");
$db=new db();
$db->select("select_statuts",null,null,"order by rang");
$statuts=$db->result;
$db=new db();
$db->select("select_categories",null,null,"order by rang");
$categories=$db->result;
$db=new db();
$db->select("personnel","statut",null,"group by statut");
$statuts_utilises=array();
if($db->result){
  foreach($db->result as $elem){
    $statuts_utilises[]=$elem['statut'];
  }
}
$db_services=new db();
$db_services->query("SELECT * FROM `{$dbprefix}select_services` ORDER BY `rang`;");

$acces=array();
$postes_attribues=array();
$recupAgents=array("Prime","Temps");

if(isset($_GET['id'])){		//	récupération des infos de l'agent en cas de modif
  $id=$_GET['id'];
  $db=new db();
  $db->query("select * from {$dbprefix}personnel where id=$id");
  $actif=$db->result[0]['actif'];
  $nom=$db->result[0]['nom'];
  $prenom=$db->result[0]['prenom'];
  $mail=$db->result[0]['mail'];
  $statut=$db->result[0]['statut'];
  $categorie=$db->result[0]['categorie'];
  $service=$db->result[0]['service'];
  $heuresHebdo=$db->result[0]['heuresHebdo'];
  $heuresTravail=$db->result[0]['heuresTravail'];
  $arrivee=$db->result[0]['arrivee'];
  $depart=$db->result[0]['depart'];
  $login=$db->result[0]['login'];
  $temps=unserialize($db->result[0]['temps']);
  $postes_attribues=unserialize($db->result[0]['postes']);
  if(is_array($postes_attribues))
    sort($postes_attribues);
  $acces=unserialize($db->result[0]['droits']);
  $matricule=$db->result[0]['matricule'];
  $mailResponsable=$db->result[0]['mailResponsable'];
  $informations=stripslashes($db->result[0]['informations']);
  $recup=stripslashes($db->result[0]['recup']);
  $siteAffect=$db->result[0]['site'];
  $action="modif";
  $titre=$nom." ".$prenom;
}
else{		// pas d'id, donc ajout d'un agent
  $id=null;
  $nom=null;
  $prenom=null;
  $mail=null;
  $statut=null;
  $categorie=null;
  $service=null;
  $heuresHebdo=null;
  $heuresTravail=null;
  $arrivee=null;
  $depart=null;
  $login=null;
  $temps=null;
  $postes_attribues=array();
  $access=array();
  $matricule=null;
  $mailResponsable=null;
  $informations=null;
  $recup=null;
  $siteAffect=null;
  $titre="Ajout d'un agent";
  $action="ajout";
  if($_SESSION['perso_actif'] and $_SESSION['perso_actif']!="Supprim&eacute;")
    $actif=$_SESSION['perso_actif'];			// vérifie dans quel tableau on se trouve pour la valeur par défaut
}

$jours=Array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
global $temps;
$contrats=array("Titulaire","Contractuel");

//		--------------		Début listes des activités		---------------------//	
$db=new db();			//	toutes les activités
$db->query("SELECT `id`,`nom` FROM `{$dbprefix}activites` ORDER BY `id`;");
if($db->result)
foreach($db->result as $elem){
  $postes_completNoms[]=array($elem['nom'],$elem['id']);
  $postes_complet[]=$elem['id'];
}

$postes_dispo=array();		// les activités non attribuées (disponibles)
if($postes_attribues){
  $postes=join($postes_attribues,",");	//	activités attribuées séparées par des virgules (valeur transmise à valid.php) 	
  if(is_array($postes_complet))
  foreach($postes_complet as $elem){
    if(!in_array($elem,$postes_attribues))
      $postes_dispo[]=$elem;
  }
}
else{
  $postes="";	//	activités attribuées séparées par des virgules (valeur transmise à valid.php) 	
  $postes_dispo=$postes_complet;
}
echo "<script type='text/JavaScript'>\n<!--\n";		// traduction en JavaScript du tableau postes_completNoms
echo php2js($postes_completNoms,"complet");
echo "\n-->\n</script>\n";


	//	Ajout des noms dans les tableaux postes attribués et dispo
function postesNoms($postes,$tab_noms){
  $tmp=array();
  if(is_array($postes))
  foreach($postes as $elem){
    if(is_array($tab_noms))
    foreach($tab_noms as $noms){
      if($elem==$noms[1]){
	$tmp[]=array($elem,$noms[0]);
	break;
      }
    }
  }
  usort($tmp,"cmp_1");
  return $tmp;
}
$postes_attribues=postesNoms($postes_attribues,$postes_completNoms);
$postes_dispo=postesNoms($postes_dispo,$postes_completNoms);
//		--------------		Fin listes des postes		---------------------//

//		--------------		Début d'affichage			---------------------//
?>
<!--		Menu						-->
<div id='onglets'>
<font id='titre'><?php echo $titre; ?></font>
<ul>		
<li id='current'><a href='javascript:show("main","qualif,access,temps","li1");'>Infos générales</a></li>
<li id='li2'><a href='javascript:show("qualif","main,access,temps","li2");'>Activités</a></li>
<li id='li3'><a href='javascript:show("temps","main,qualif,access","li3");'>Emploi du temps</a></li>
<li id='li4'><a href='javascript:show("access","main,qualif,temps","li4");'>Droits d'accès</a></li>
<?php
if(in_array(21,$droits)){
  echo "<li id='li_annul'><a href='javascript:retour(\"personnel/index.php\");'>Annuler</a></li>\n";
  echo "<li id='li_valid'><a href='javascript:verif_form_agent();'>Valider</a></li>\n";
}
else
  echo "<li id='li_valid'><a href='javascript:location.href=\"index.php?page=personnel/index.php\";'>Fermer</a></li>\n";
?>
</ul>
</div>
<br/>
<br/>
<br/>

<?php
echo "<form method='post' action='index.php' name='form'>\n";
echo "<input type='hidden' name='page' value='personnel/valid.php' />\n";
//			Début Infos générales	
echo "<div id='main' style='margin-left:80px;padding-top:30px;'>\n";
echo "<input type='hidden' value='$action' name='action' />";
echo "<input type='hidden' value='$id' name='id' />";

echo "<table style='width:90%;'>";
echo "<tr valign='top'><td style='width:350px'>";
echo "Nom :";
echo "</td><td>";
echo in_array(21,$droits)?"<input type='text' value='$nom' name='nom' style='width:400px' />":$nom;
echo "</td></tr>";

echo "<tr><td>";
echo "Prénom :";
echo "</td><td>";
echo in_array(21,$droits)?"<input type='text' value='$prenom' name='prenom' style='width:400px' />":$prenom;
echo "</td></tr>";

echo "<tr><td>";
echo "E-mail : ";
if(in_array(21,$droits))
	echo "<a href='mailto:$mail'>$mail</a>";
echo "</td><td>";
echo in_array(21,$droits)?"<input type='text' value='$mail' name='mail' style='width:400px' />":"<a href='mailto:$mail'>$mail</a>";
echo "</td></tr>";

echo "<tr><td>";
echo "Statut :";
echo "</td><td>";
if(in_array(21,$droits)){
  echo "<select name='statut' id='statut' style='width:405px'>\n";
  echo "<option value=''>Aucun</option>\n";
  foreach($statuts as $elem){
    $select1=$elem['valeur']==$statut?"selected='selected'":null;
    echo "<option $select1 value='".$elem['valeur']."'>".$elem['valeur']."</option>\n";
  }
  echo "</select>\n";
  echo "<img src='img/add.gif' alt='*' style=width:15px;cursor:pointer;' id='add-statut-button'/>\n";
}
else{
  echo $statut;
}
echo "</td></tr>";

echo "<tr><td>";
echo "Catégorie :";
echo "</td><td>";
if(in_array(21,$droits)){
  echo "<select name='categorie' id='categorie' style='width:405px'>\n";
  echo "<option value=''>Aucune</option>\n";
  foreach($contrats as $elem){
    $select1=$elem==$categorie?"selected='selected'":null;
    echo "<option $select1 value='{$elem}'>{$elem}</option>\n";
  }
  echo "</select>\n";
}
else{
  echo $categorie;
}
echo "</td></tr>";

echo "<tr><td>";
echo "Service de rattachement:";
echo "</td><td>";
if(in_array(21,$droits)){
  echo "<select name='service' id='service' style='width:405px'>\n";
  echo "<option value=''>Aucun</option>\n";
  foreach($db_services->result as $elem){
    $select1=$elem['valeur']==$service?"selected='selected'":null;
    echo "<option $select1 value='".$elem['valeur']."'>".$elem['valeur']."</option>\n";
  }
  echo "</select>\n";
  echo "<a href='javascript:popup(\"include/ajoutSelect.php&amp;table=select_services&amp;terme=service\",400,400);'>\n";
  echo "<img src='img/add.gif' alt='*' style=width:15px;'/></a>\n";
}
else{
  echo $service;
}
echo "</td></tr>";
	

echo "<tr><td>";
echo "Heures de service public par semaine:";
echo "</td><td>";
if(in_array(21,$droits)){
  echo "<select name='heuresHebdo' style='width:405px'>\n";
  echo "<option value='0'>&nbsp;</option>\n";
  for($i=1;$i<40;$i++){
    $j=array();
    if($config['heuresPrecision']=="quart d&apos;heure"){
      $j[]=array($i,$i."h00");
      $j[]=array($i.".25",$i."h15");
      $j[]=array($i.".5",$i."h30");
      $j[]=array($i.".75",$i."h45");
    }
    elseif($config['heuresPrecision']=="demi-heure"){
      $j[]=array($i,$i."h00");
      $j[]=array($i.".5",$i."h30");
    }
    else{
      $j[]=array($i,$i."h00");
    }
    foreach($j as $elem){
      $select=$elem[0]==$heuresHebdo?"selected='selected'":"";
      echo "<option $select value='{$elem[0]}'>{$elem[1]}</option>\n";
    }
  }
  echo "</select>\n";
}
else
  echo $heuresHebdo." heures";
echo "</td></tr>";


echo "<tr><td>";
echo "Heures de travail par semaine:";
echo "</td><td>";
if(in_array(21,$droits)){
  echo "<select name='heuresTravail' style='width:405px'>\n";
  echo "<option value='0'>&nbsp;</option>\n";
  for($i=1;$i<40;$i++){
    $j=array();
    if($config['heuresPrecision']=="quart d&apos;heure"){
      $j[]=array($i,$i."h00");
      $j[]=array($i.".25",$i."h15");
      $j[]=array($i.".5",$i."h30");
      $j[]=array($i.".75",$i."h45");
    }
    elseif($config['heuresPrecision']=="demi-heure"){
      $j[]=array($i,$i."h00");
      $j[]=array($i.".5",$i."h30");
    }
    else{
      $j[]=array($i,$i."h00");
    }
    foreach($j as $elem){
      $select=$elem[0]==$heuresTravail?"selected='selected'":"";
      echo "<option $select value='{$elem[0]}'>{$elem[1]}</option>\n";
    }
  }
  echo "</select>\n";
}
else
  echo $heuresTravail." heures";
echo "</td></tr>";

if(in_array("conges",$plugins)){
  include "plugins/conges/ficheAgent.php";
}

$select1=null;
$select2=null;
$select3=null;
switch($actif){
  case "Actif" :		$select1="selected='selected'"; $actif2="Service public";	$display="style='display:none;'";	break;
  case "Inactif" :		$select2="selected='selected'"; $actif2="Administratif";	$display="style='display:none;'";	break;
  case "Supprim&eacute;" :	$select3="selected='selected'";	$actif2="Supprim&eacute;";	break;
}
echo "<tr><td>";
echo "Service public / Administratif :";
echo "</td><td>";
if(in_array(21,$droits)){
  echo "<select name='actif' style='width:405px'>\n";
  echo "<option $select1 value='Actif'>Service public</option>\n";
  echo "<option $select2 value='Inactif'>Administratif</option>\n";
  echo "<option $select3 value='Supprim&eacute;' $display>Supprim&eacute;</option>\n";
  echo "</select>\n";
}
else{
  echo $actif2;
}
echo "</td></tr>";

// Multisites si les agents ne sont pas autorisés à travailler sur plusieurs sites
if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
  echo "<tr><td>Site :</td><td>";
  if(in_array(21,$droits)){
    $select1=$siteAffect==1?"selected='selected'":null;
    $select2=$siteAffect==2?"selected='selected'":null;
    echo "<select name='site' style='width:405px'>";
    echo "<option value=''>&nbsp;</option>\n";
    echo "<option value='1' $select1 >{$config['Multisites-site1']}</option>\n";
    echo "<option value='2' $select2 >{$config['Multisites-site2']}</option>\n";
    echo "</select>";
  }
  else{
    echo $siteAffect?$config["Multisites-site{$siteAffect}"]:"Non défini";
  }
  echo "</td></tr>\n";
}

echo "<tr><td>";
echo "Date d'arrivée ";
if(in_array(21,$droits)){
  echo "(AAAA-MM-JJ) :";
  echo "</td><td>";
  echo "<input type='text' value='$arrivee' name='arrivee' style='width:400px' />";
  echo "&nbsp;&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"arrivee\");' alt='arrivée' />";
}
else
  echo "</td><td>".dateFr($arrivee);
echo "</td></tr>";

echo "<tr><td>";
echo "Date de départ ";
if(in_array(21,$droits)){
  echo "(AAAA-MM-JJ) :";
  echo "</td><td>";
  echo "<input type='text' value='$depart' name='depart' style='width:400px' />";
  echo "&nbsp;&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"depart\");' alt='départ' />";
}
else
  echo "</td><td>".dateFr($depart);
echo "</td></tr>";

echo "<tr><td>";
echo "Matricule : ";
echo "</td><td>";
echo in_array(21,$droits)?"<input type='text' value='$matricule' name='matricule' style='width:400px' />":"$matricule</a>";
echo "</td></tr>";

echo "<tr><td>";
echo "E-mail du responsable: ";
if(in_array(21,$droits))
	echo "<a href='mailto:$mailResponsable'>$mailResponsable</a>";
echo "</td><td>";
echo in_array(21,$droits)?"<input type='text' value='$mailResponsable' name='mailResponsable' style='width:400px' />":"<a href='mailto:$mailResponsable'>$mail</a>";
echo "</td></tr>";

echo "<tr style='vertical-align:top;'><td>";
echo "Informations :";
echo "</td><td>";
echo in_array(21,$droits)?"<textarea name='informations' style='width:400px' cols='10' rows='4'>$informations</textarea>":str_replace("\n","<br/>",$informations);
echo "</td></tr>";

if($config['Recup-Agent']){
  echo "<tr style='vertical-align:top;'><td>";
  echo "Récupération du samedi :";
  echo "</td><td>";
  if($config['Recup-Agent']=="Texte" and in_array(21,$droits)){
    echo "<textarea name='recup' style='width:400px' cols='10' rows='4'>$recup</textarea>";
  }
  if(htmlentities($config['Recup-Agent'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false)=="Menu d&eacute;roulant" and in_array(21,$droits)){
    echo "<select name='recup' style='width:400px'>\n";
    echo "<option value=''>&nbsp;</option>\n";
    foreach($recupAgents as $elem){
      $selected=$recup==$elem?"selected='selected'":null;
      echo "<option value='$elem' $selected>$elem</option>\n";
    }
    echo "</select>\n";
  }
  if(!in_array(21,$droits)){
    echo str_replace("\n","<br/>",$recup);
  }
  echo "</td></tr>";
}

if($id){
  echo "<tr><td>\n";
  echo "Login :";
  echo "</td><td>";
  echo $login;
  echo "</td></tr>";
  if(in_array(21,$droits)){
    echo "<tr><td>\n";
    echo "<a href='javascript:modif_mdp();'>Changer le mot de passe</a>";
    echo "</td></tr>";
  }
}
?>
</table>
</div>
<!--	Fin Info générales	-->

<!--	Début Qualif	-->
<div id='qualif' style='margin-left:80px;display:none;padding-top:30px;'>
<table style='width:90%;'>
<tr style='vertical-align:top;'><td>
<b>Activités disponibles</b><br/>
<div id='dispo_div'>
<?php
if(in_array(21,$droits)){
  echo "<select id='postes_dispo' name='postes_dispo' style='width:300px;' size='20' multiple='multiple'>\n";
  foreach($postes_dispo as $elem)
    echo "<option value='{$elem[0]}'>{$elem[1]}</option>\n";
  echo "</select>\n";
}
else{
  echo "<ul>\n";
  foreach($postes_dispo as $elem)
    echo "<li>{$elem[1]}</li>\n";
  echo "</ul>\n";
}	
?>
</div>
<?php
if(in_array(21,$droits)){
  echo "</td><td style='text-align:center;padding-top:100px;'>\n";
  echo "<input type='button' style='width:200px' value='Attribuer >>' onclick='select_add(\"postes_dispo\",\"postes_attribues\",\"postes\",300);' /><br/><br/>\n";
  echo "<input type='button' style='width:200px' value='Attribuer Tout >>' onclick='select_add_all(\"postes_dispo\",\"postes_attribues\",\"postes\",300);' /><br/><br/>\n";
  echo "<input type='button' style='width:200px' value='<< Supprimer' onclick='select_drop(\"postes_dispo\",\"postes_attribues\",\"postes\",300);' /><br/><br/>\n";
  echo "<input type='button' style='width:200px' value='<< Supprimer Tout' onclick='select_drop_all(\"postes_dispo\",\"postes_attribues\",\"postes\",300);' /><br/><br/>\n";
}
?>
</td><td>
<b>Activités attribu&eacute;es</b><br/>
<div id='attrib_div'>
<?php
if(in_array(21,$droits)){
  echo "<select id='postes_attribues' name='postes_attribues' style='width:300px;' size='20' multiple='multiple'>\n";
  foreach($postes_attribues as $elem)
    echo "<option value='{$elem[0]}'>{$elem[1]}</option>\n";
  echo "</select>\n";
}
else{
  echo "<ul>\n";
  foreach($postes_attribues as $elem)
    echo "<li>{$elem[1]}</li>\n";
  echo "</ul>\n";
}	
?>
</div>
<input type='hidden' name='postes' id='postes' value='<?php echo $postes;?>'/>
</td></tr>
</table>
</div>
<!--	FIN Qualif	-->

<!--	Emploi du temps		-->
<div id='temps' style='margin-left:80px;display:none;padding-top:30px;'>
<?php
switch($config['nb_semaine']){
  case 2	: $cellule=array("Semaine Impaire","Semaine Paire");		break;
  case 3	: $cellule=array("Semaine 1","Semaine 2","Semaine 3");		break;
  default 	: $cellule=array("Jour");					break;
}
$fin=$config['Dimanche']?array(8,15,22):array(7,14,21);
$debut=array(1,8,15);

if($config['EDTSamedi']){
  $config['nb_semaine']=2;
  $cellule=array("Semaine standard","Semaine avec samedi");
}

for($j=0;$j<$config['nb_semaine'];$j++){
  if($config['EDTSamedi']){
    echo $j==0?"<br/><b>Emploi du temps standard</b>":"<br/><b>Emploi du temps des semaines avec samedi travaillé</b>";
  }
  echo "<table border='1' cellspacing='0'>\n";
  echo "<tr style='text-align:center;'><td style='width:150px;'>{$cellule[$j]}</td><td style='width:150px;'>Heure d'arrivée</td>";
  echo "<td style='width:150px;'>Début de pause</td><td style='width:150px;'>Fin de pause</td>";
  echo "<td style='width:150px;'>Heure de départ</td>";
  if($config['Multisites-nombre']>1 and $config['Multisites-agentsMultisites']){
    echo "<td>Site</td>";
  }
  echo "<td style='width:150px;'>Temps</td>";
    echo "</tr>\n";
  for($i=$debut[$j];$i<$fin[$j];$i++){
    $k=$i-($j*7)-1;
    if(in_array(21,$droits) and !in_array("planningHebdo",$plugins)){
      echo "<tr><td>{$jours[$k]}</td><td>".selectTemps($i-1,0,null,"select$j")."</td><td>".selectTemps($i-1,1,null,"select$j")."</td>";
      echo "<td>".selectTemps($i-1,2,null,"select$j")."</td><td>".selectTemps($i-1,3,null,"select$j")."</td>";
      if($config['Multisites-nombre']>1 and $config['Multisites-agentsMultisites']){
	$select1=$temps[$i-1][4]==1?"selected='selected'":null;
	$select2=$temps[$i-1][4]==2?"selected='selected'":null;
	echo "<td><select name='temps[".($i-1)."][4]'><option value=''>&nbsp;</option>\n";
	echo "<option value='1' $select1 >{$config['Multisites-site1']}</option>\n";
	echo "<option value='2' $select2 >{$config['Multisites-site2']}</option>\n";
	echo "</select></td>";
      }
      echo "<td id='heures_{$j}_$i'></td>\n";
      echo "</tr>\n";
    }
    else{
      echo "<tr><td>{$jours[$k]}</td>\n";
      echo "<td id='temps_".($i-1)."_0'>".heure2($temps[$i-1][0])."</td>\n";
      echo "<td id='temps_".($i-1)."_1'>".heure2($temps[$i-1][1])."</td>\n";
      echo "<td id='temps_".($i-1)."_2'>".heure2($temps[$i-1][2])."</td>\n";
      echo "<td id='temps_".($i-1)."_3'>".heure2($temps[$i-1][3])."</td>\n";
      if($config['Multisites-nombre']>1 and $config['Multisites-agentsMultisites']){
	$site=null;
	if($temps[$i-1][4]){
	  $site="Multisites-site".$temps[$i-1][4];
	  $site=$config[$site];
	}
	echo "<td>$site</td>";
      }
      echo "<td id='heures_{$j}_$i'></td>\n";
      echo "</tr>\n";
    }
  }
  echo "</table>\n";
  echo "Total : <font style='font-weight:bold;' id='heures$j'></font><br/><br/>\n";
}

// EDTSamedi : emploi du temps différents les semaines avec samedi travaillé
// Choix des semaines avec samedi travaillé
if($config['EDTSamedi']){
  // Recherche des semaines avec samedi travaillé entre le 1er septembre de N-1 et le 31 août de N+3
  $d=new datePl((date("Y")-1)."-09-01");
  $premierLundi=$d->dates[0];
  $d=new datePl((date("Y")+3)."-08-31");
  $dernierLundi=$d->dates[0];

  $p=new personnel();
  $p->fetchEDTSamedi($id,$premierLundi,$dernierLundi);
  $edt=$p->elements;

  // inputs premierLundi et dernierLundi pour mise à jour (validation=suppression et insertion des nouveaux élements)
  echo "<input type='hidden' name='premierLundi' value='$premierLundi' />\n";
  echo "<input type='hidden' name='dernierLundi' value='$dernierLundi' />\n";
  echo "<div id='EDTChoix'>\n";
  echo "<h3>Choix des emplois du temps</h3>\n";
  echo "<p>Cochez les semaines avec le samedi travaill&eacute;</p>\n";

  echo "<div id='EDTTabs'>\n";
  echo "<ul>";
  for($i=0;$i<4;$i++){
    $annee=(date("Y")+$i-1)."-".(date("Y")+$i);
    echo "<li><a href='#EDTTabs-$i' id='EDTA-$i'>Année $annee</a></li>\n";
  }
  echo "</ul>\n";

  for($i=0;$i<4;$i++){
    $d=new datePl((date("Y")-1+$i)."-09-01");
    $premierLundi=$d->dates[0];
    $d=new datePl((date("Y")+$i)."-08-31");
    $dernierLundi=$d->dates[0];

    if(date("Y-m-d")>=$premierLundi and date("Y-m-d")<=$dernierLundi){
      $currentTab="#EDTA-$i";
    }
    $current=$premierLundi;
    $j=0;

    echo "<div id=EDTTabs-$i>";
    echo "<table class='tableauStandard'>";
    echo "<tr><td>";

    while($current<=$dernierLundi){
      // Evite de mettre la même semaine (fin août - début septembre) dans 2 années universitaires
      if(isset($last) and $current==$last){
	$last=$current;
	$current=date("Y-m-d",strtotime("+7 day",strtotime($current)));
	continue;
      }
      $lundi=date("d/m/Y",strtotime($current));
      $dimanche=date("d/m/Y",strtotime("+6 day",strtotime($current)));
      $semaine=date("W",strtotime($current));
      $checked=in_array($current,$edt)?"checked='checked'":null;
      echo "S$semaine : $lundi &rarr; $dimanche";
      echo "<input type='checkbox' value='$current' name='EDTSamedi[]' $checked /><br/>\n";
	
      if($j==17 or $j==35){
	echo "</td><td>";
      }
      $j++;
      $last=$current;
      $current=date("Y-m-d",strtotime("+7 day",strtotime($current)));
    }
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</div>\n";
  }
  echo "</div>\n";
  echo "</div>\n";
}
?>

</div>
<!--	FIN Emploi du temps-->

<!--	Droits d'accès		-->
<div id='access' style='margin-left:80px;display:none;padding-top:30px;'>
<?php
foreach($db_groupes->result as $elem){		// gestion des droits d'accès au planning
  $disabled=in_array(21,$droits)?null:"disabled='disabled'";	// désactive la modif des accès pour l'affichage en lecture seule
  $disabled=$elem['groupe_id']==20?"disabled='disabled'":$disabled;  // désactive la modif de l'accès à config avancée pour tous 
  

  //	Affichage des lignes avec checkboxes
  //	Gestion des absences si plusieurs sites
  if($elem['groupe_id']==1 and $config['Multisites-nombre']>1){
    for($i=1;$i<$config['Multisites-nombre']+1;$i++){
      $site=$config['Multisites-site'.$i];
      $groupe_id=200+$i;
      if(is_array($acces)){
	$checked=in_array($groupe_id,$acces)?"checked='checked'":null;
      }
      echo "<input type='checkbox' name='droits[]' $checked value='$groupe_id' $disabled />{$elem['groupe']} $site<br/>\n";
    }
  }
  //	Gestion des congés si plusieurs sites
  elseif($elem['groupe_id']==2 and $config['Multisites-nombre']>1){
    for($i=1;$i<$config['Multisites-nombre']+1;$i++){
      $site=$config['Multisites-site'.$i];
      $groupe_id=400+$i;
      if(is_array($acces)){
	$checked=in_array($groupe_id,$acces)?"checked='checked'":null;
      }
      echo "<input type='checkbox' name='droits[]' $checked value='$groupe_id' $disabled />{$elem['groupe']} $site<br/>\n";
    }
  }
  //	Modification des plannings si plusieurs sites
  elseif($elem['groupe_id']==12 and $config['Multisites-nombre']>1){
    for($i=1;$i<$config['Multisites-nombre']+1;$i++){
      $site=$config['Multisites-site'.$i];
      $groupe_id=300+$i;
      if(is_array($acces)){
	$checked=in_array($groupe_id,$acces)?"checked='checked'":null;
      }
      echo "<input type='checkbox' name='droits[]' $checked value='$groupe_id' $disabled />{$elem['groupe']} $site<br/>\n";
    }
  }
  else{
    if(is_array($acces)){
      $checked=in_array($elem['groupe_id'],$acces)?"checked='checked'":null;
    }
    echo "<input type='checkbox' name='droits[]' $checked value='{$elem['groupe_id']}' $disabled />{$elem['groupe']}<br/>\n";
  }
}
?>
</div>
<!--	FIN Droits d'accès		-->
</form>


<!--	Modification de la liste des statuts (Dialog Box) -->  
<div id="add-statut-form" title="Liste des statuts" class='noprint'>
  <p class="validateTips">Ajoutez, supprimez des statuts. Modifiez leur catégorie. Modifiez l'ordre des statuts dans les menus déroulant.</p>
  <form>
  <p><input type='text' id='add-statut-text' style='width:300px;'/>
    <input type='button' id='add-statut-button2' class='ui-button' value='Ajouter' style='margin-left:15px;'/></p>
  <fieldset>
    <ul id="statuts-sortable">
<?php
    if(is_array($statuts)){
      foreach($statuts as $elem){
	echo "<li class='ui-state-default' id='li_{$elem['id']}'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>\n";
	echo "<font id='valeur_{$elem['id']}'>{$elem['valeur']}</font>\n";
	echo "<select id='categorie_{$elem['id']}' style='position:absolute;left:330px;'>\n";
	echo "<option value='0'>&nbsp;</option>\n";
	foreach($categories as $elem2){
	  $selected=$elem2['id']==$elem['categorie']?"selected='selected'":null;
	  echo "<option value='{$elem2['id']}' $selected>{$elem2['valeur']}</option>\n";
	}
	echo "</select>\n";
	if(!in_array($elem['valeur'],$statuts_utilises)){
	  echo "<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>\n";
	}
	echo "</li>\n";
      }
    }
?>
    </ul>
  </fieldset>
  </form>
</div>


<script type='text/JavaScript'>
<!--
// Contrôle des champs lors de la validation
function verif_form_agent(){
  erreur=false;
  message="Les champs suivant sont obligatoires :";
  if(!document.form.nom.value){
    erreur=true;
    message=message+"\n- Nom";
  }
  if(!document.form.prenom.value){
    erreur=true;
    message=message+"\n- prénom";
  }
  if(!document.form.mail.value){
    erreur=true;
    message=message+"\n- E-mail";
  }
  
  if(erreur)
    alert(message);
  else{
    if(!verif_mail(document.form.mail.value)){
      alert("Adresse e-mail invalide");
    }
    else{
      document.form.submit();
    }
  }
}

// Affichage du choix des semaines avec samedi travaillé avec onglets
// Et sélection de l'onglet correspondant à l'année en cours
<?php
if($config['EDTSamedi']){
  echo "$(\"#EDTTabs\").tabs();\n";
  echo "$(\"$currentTab\").click();\n";
}

// Affichage du nombre d'heures correspondant à chaque emploi du temps
for($i=0;$i<$config['nb_semaine'];$i++){
  echo "$(\".select$i\").change(function(){calculHeures($(this),\"\",\"form\",\"heures$i\",$i);});\n";
  echo "$(\"document\").ready(function(){calculHeures($(this),\"\",\"form\",\"heures$i\",$i);});\n";
}
?>


// Paramétrage de la boite de dialogue permettant la modification des statuts
$(function() {
  $("#add-statut-form").dialog({
    autoOpen: false,
    height: 480,
    width: 560,
    modal: true,
    resizable: false,
    draggable: false,
    buttons: {
      Enregistrer: function() {
	// Supprime les lignes cachées lors du clic sur la corbeille
	$("#statuts-sortable li:hidden").each(function(){
	  $(this).remove();
	});
	
	// Enregistre les éléments du formulaire dans un tableau
	tab=new Array();
	$("#statuts-sortable li").each(function(){
	  var id=$(this).attr("id").replace("li_","");
	  tab.push(new Array($("#valeur_"+id).text(), $("#categorie_"+id+" option:selected").val(),$(this).index()));
	});

	// Transmet le tableau à la page de validation ajax
	var jsonString = encodeURIComponent(JSON.stringify(tab));
	$.ajax({
	  url: "personnel/ajax.statuts.php",
	  type: "post",
	  data: "tab="+jsonString,
	  success: function(){
	    location.reload(false);
	  },
	  error: function(){
	    alert("Erreur lors de l'enregistrement des modifications");
	  }
	});
      },
      Annuler: function() {
	$(this).dialog( "close" );
      },
    },
    close: function() {
      $("#statuts-sortable li:hidden").each(function(){
	$(this).show();
      });
    }
  });

  // Affiche la boite de dialogue permettant la modification des statuts
  $("#add-statut-button")
    .click(function() {
      $("#add-statut-form").dialog( "open" );
      return false;
    });

  // Permet de rendre la liste des statuts triable
  $( "#statuts-sortable" ).sortable({
    placeholder: "ui-state-highlight",
  });

  // Permet d'ajouter de nouveau statuts (click sur le bouton ajouter
  $("#add-statut-button2").click(function(){
    var randomnumber=Math.floor((Math.random()*10000)+100)
    $("#statuts-sortable").append("<li id='li_"+randomnumber+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+randomnumber+"'>"+$("#add-statut-text").val()+"</font>"
      +"<select id='categorie_"+randomnumber+"' style='position:absolute;left:330px;'>"
      +"<option value='0'>&nbsp;</option>"
      <?php
      foreach($categories as $elem2){
	echo "+\"<option value='{$elem2['id']}' >{$elem2['valeur']}</option>\"";
      }
      ?>
      +"</select>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:463px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-statut-text").val(null);
  });
});

-->
</script>