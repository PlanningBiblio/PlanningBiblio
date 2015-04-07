<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : personnel/modif.php
Création : mai 2011
Dernière modification : 7 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le formulaire permettant d'ajouter ou de modifier les agents.
Page séparée en 4 <div> (Général, Activités, Emploi du temps, Droits d'accès. Ces <div> s'affichent lors des click sur
les onglets.
Ce formulaire est soumis au fichier personnel/valid.php

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

// Initialisation des variables
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);

$admin=in_array(21,$droits)?true:false;
// NB : le champ poste et les fonctions postes_... sont utilisés pour l'attribution des activités (qualification)

// Gestion des droits d'accés
$db_groupes=new db();
$db_groupes->select2("acces",array("groupe_id","groupe"),"groupe_id not in (99,100)","group by groupe");

// Tous les droits d'accés
$groupes=array();
if($db_groupes->result){
  foreach($db_groupes->result as $elem){
    $groupes[$elem['groupe_id']]=$elem;
  }
}

// Si multisites, les droits de gestion des absences, congés et modification planning dépendent des sites : 
// on les places dans un autre tableau pour simplifier l'affichage
$groupes_sites=array();
if($config['Multisites-nombre']>1){  
  $groupes_sites[1]=$groupes[1];	// Absences, validation N1
  unset($groupes[1]);
  $groupes_sites[8]=$groupes[8];	// Absences, validation N2
  unset($groupes[8]);
  if(array_key_exists(7,$groupes)){	// Congés, validation N1
    $groupes_sites[7]=$groupes[7];
    unset($groupes[7]);
  }
  if(array_key_exists(7,$groupes)){	// Congés, validation N2
    $groupes_sites[2]=$groupes[2];
    unset($groupes[2]);
  }
  $groupes_sites[12]=$groupes[12];	// Modification des plannings
  unset($groupes[12]);
}

$db=new db();
$db->select2("select_statuts",null,null,"order by rang");
$statuts=$db->result;
$db=new db();
$db->select2("select_categories",null,null,"order by rang");
$categories=$db->result;
$db=new db();
$db->select2("personnel","statut",null,"group by statut");
$statuts_utilises=array();
if($db->result){
  foreach($db->result as $elem){
    $statuts_utilises[]=$elem['statut'];
  }
}

$db_services=new db();
$db_services->select2("select_services",null,null,"ORDER BY `rang`");

$acces=array();
$postes_attribues=array();
$recupAgents=array("Prime","Temps");

if($id){		//	récupération des infos de l'agent en cas de modif
  $db=new db();
  $db->select2("personnel","*",array("id"=>$id));
  $actif=$db->result[0]['actif'];
  $nom=$db->result[0]['nom'];
  $prenom=$db->result[0]['prenom'];
  $mail=$db->result[0]['mail'];
  $statut=$db->result[0]['statut'];
  $categorie=$db->result[0]['categorie'];
  $service=$db->result[0]['service'];
  $heuresHebdo=$db->result[0]['heuresHebdo'];
  $heuresTravail=$db->result[0]['heuresTravail'];
  $arrivee=dateFr($db->result[0]['arrivee']);
  $depart=dateFr($db->result[0]['depart']);
  $login=$db->result[0]['login'];
  $temps=unserialize($db->result[0]['temps']);
  $postes_attribues=unserialize($db->result[0]['postes']);
  if(is_array($postes_attribues))
    sort($postes_attribues);
  $acces=unserialize($db->result[0]['droits']);
  $matricule=$db->result[0]['matricule'];
  $mailsResponsables=explode(";",html_entity_decode($db->result[0]['mailsResponsables'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  // $mailsResponsables : html_entity_decode necéssaire sinon ajoute des espaces après les accents ($mailsResponsables=join("; ",$mailsResponsables);)
  $informations=stripslashes($db->result[0]['informations']);
  $recup=stripslashes($db->result[0]['recup']);
  $sites=$db->result[0]['sites'];
  $sites=is_serialized($sites)?unserialize($sites):array();
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
  $mailsResponsables=array();
  $informations=null;
  $recup=null;
  $sites=array();
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
$db->select2("activites",array("id","nom"),null,"ORDER BY `id`");
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
<h3><?php echo $titre; ?></h3>
<!--		Menu						-->
<div class='ui-tabs'>
<ul>		
<li><a href='#main'>Infos générales</a></li>
<li><a href='#qualif'>Activités</a></li>
<li><a href='#temps' id='personnel-a-li3'>Emploi du temps</a></li>
<?php
if(in_array("conges",$plugins)){
  echo "<li><a href='#conges'>Cong&eacute;s</a></li>";
}
?>
<li><a href='#access'>Droits d'accès</a></li>
<?php
if(in_array(21,$droits)){
  echo "<li class='ui-tab-cancel'><a href='index.php?page=personnel/index.php'>Annuler</a></li>\n";
  echo "<li class='ui-tab-submit'><a href='javascript:verif_form_agent();'>Valider</a></li>\n";
}
else{
  echo "<li class='ui-tab-cancel'><a href='index.php?page=personnel/index.php'>Fermer</a></li>\n";
}
?>
</ul>

<?php
echo "<form method='post' action='index.php' name='form'>\n";
echo "<input type='hidden' name='page' value='personnel/valid.php' />\n";
//			Début Infos générales	
echo "<div id='main' style='margin-left:70px;padding-top:30px;'>\n";
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
echo "</td><td style='white-space:nowrap'>";
if(in_array(21,$droits)){
  echo "<select name='statut' id='statut' style='width:405px'>\n";
  echo "<option value=''>Aucun</option>\n";
  foreach($statuts as $elem){
    $select1=$elem['valeur']==$statut?"selected='selected'":null;
    echo "<option $select1 value='".$elem['valeur']."'>".$elem['valeur']."</option>\n";
  }
  echo "</select>\n";
  echo "<span class='pl-icon pl-icon-add' title='Ajouter' style='cursor:pointer;' id='add-statut-button'></span>\n";
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
echo "</td><td style='white-space:nowrap'>";
if(in_array(21,$droits)){
  echo "<select name='service' id='service' style='width:405px'>\n";
  echo "<option value=''>Aucun</option>\n";
  foreach($db_services->result as $elem){
    $select1=$elem['valeur']==$service?"selected='selected'":null;
    echo "<option $select1 value='".$elem['valeur']."'>".$elem['valeur']."</option>\n";
  }
  echo "</select>\n";
  echo "<a href='javascript:popup(\"include/ajoutSelect.php&amp;table=select_services&amp;terme=service\",400,400);'>\n";
  echo "<span class='pl-icon pl-icon-add' title='Ajouter' ></span></a>\n";
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

// Multi-sites
if($config['Multisites-nombre']>1){
  echo "<tr style='vertical-align:top;'><td>Sites :</td><td>";
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    if(in_array(21,$droits)){
      $checked=in_array($i,$sites)?"checked='checked'":null;
      echo "<input type='checkbox' name='sites[]' value='$i' $checked >{$config["Multisites-site$i"]}<br/>";
    }
    else{
      if(in_array($i,$sites)){
	echo $config["Multisites-site{$i}"]."<br/>";;
      }
    }
  }
  echo "</td></tr>\n";
}

echo "<tr><td>";
echo "Date d'arrivée ";
if(in_array(21,$droits)){
  echo "</td><td>";
  echo "<input type='text' value='$arrivee' name='arrivee' style='width:400px' class='datepicker'/>";
}
else
  echo "</td><td>".$arrivee;
echo "</td></tr>";

echo "<tr><td>";
echo "Date de départ ";
if(in_array(21,$droits)){
  echo "</td><td>";
  echo "<input type='text' value='$depart' name='depart' style='width:400px'  class='datepicker'/>";
}
else
  echo "</td><td>".$depart;
echo "</td></tr>";

echo "<tr><td>";
echo "Matricule : ";
echo "</td><td>";
echo in_array(21,$droits)?"<input type='text' value='$matricule' name='matricule' style='width:400px' />":"$matricule</a>";
echo "</td></tr>";

echo "<tr><td>";
echo "E-mails des responsables : ";
if(in_array(21,$droits)){
  foreach($mailsResponsables as $elem){
    $elem=trim($elem);
    echo "<br/><a href='mailto:$elem' style='margin-left:30px;'>$elem</a>";
  }
}
echo "</td><td>";
if(in_array(21,$droits)){
  $mailsResponsables=join("; ",$mailsResponsables);
  echo "<textarea name='mailsResponsables' style='width:400px' cols='10' rows='4'>$mailsResponsables</textarea>";
}else{
  foreach($mailsResponsables as $elem){
    $elem=trim($elem);
    echo "<a href='mailto:$elem' style='margin-left:30px;'>$elem</a><br/>";
  }
}
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
<div id='qualif' style='margin-left:70px;display:none;padding-top:30px;'>
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
<div id='temps' style='margin-left:70px;display:none;padding-top:30px;'>
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
  if($config['Multisites-nombre']>1){
    echo "<td>Site</td>";
  }
  echo "<td style='width:150px;'>Temps</td>";
    echo "</tr>\n";
  for($i=$debut[$j];$i<$fin[$j];$i++){
    $k=$i-($j*7)-1;
    if(in_array(21,$droits) and !in_array("planningHebdo",$plugins)){
      echo "<tr><td>{$jours[$k]}</td><td>".selectTemps($i-1,0,null,"select$j")."</td><td>".selectTemps($i-1,1,null,"select$j")."</td>";
      echo "<td>".selectTemps($i-1,2,null,"select$j")."</td><td>".selectTemps($i-1,3,null,"select$j")."</td>";
      if($config['Multisites-nombre']>1){
	echo "<td><select name='temps[".($i-1)."][4]' class='edt-site'>\n";
	echo "<option value='' class='edt-site-0'>&nbsp;</option>\n";
	for($l=1;$l<=$config['Multisites-nombre'];$l++){
	  $selected=$temps[$i-1][4]==$l?"selected='selected'":null;
	  echo "<option value='$l' $selected class='edt-site-$l'>{$config["Multisites-site{$l}"]}</option>\n";
	}
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
      if($config['Multisites-nombre']>1){
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
<div id='access' style='margin-left:70px;display:none;padding-top:30px;'>
<?php
if(!$admin){
  echo "<ul>\n";
}

// Affichage de tous les droits d'accès si un seul site ou des droits d'accès ne dépendant pas des sites
foreach($groupes as $elem){
  // N'affiche pas les droits d'accès à la configuration (réservée au compte admin)
  if($elem['groupe_id']==20){
    continue;
  }

  //	Affichage des lignes avec checkboxes
  if(is_array($acces)){
    $checked=in_array($elem['groupe_id'],$acces)?"checked='checked'":null;
    $checked2=$checked?"Oui":"Non";
    $class=$checked?"green bold":"red";
  }
  if($admin){
    echo "<input type='checkbox' name='droits[]' $checked value='{$elem['groupe_id']}' style='margin-right:10px;'/>{$elem['groupe']}<br/>\n";
  }else{
    echo "<li>{$elem['groupe']} <label class='agent-acces-checked2 $class'>$checked2</label></li>\n";
  }
}
if(!$admin){
  echo "</ul>\n";
}

// Affichage des droits d'accès dépendant des sites (si plusieurs sites)
if($config['Multisites-nombre']>1){
  echo "<table style='margin-top:50px;'><thead><tr><th>&nbsp;</th>\n";
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    echo "<th class='center' style='padding:0 10px;'>{$config["Multisites-site$i"]}</th>\n";
  }
  echo "</tr></thead>\n";
  echo "<tbody>\n";

  foreach($groupes_sites as $elem){
    $groupe=ucfirst(str_replace("Gestion des ","",$elem['groupe']));
    echo "<tr><td>$groupe</td>\n";

    for($i=1;$i<$config['Multisites-nombre']+1;$i++){
      $site=$config['Multisites-site'.$i];

      // Gestion des absences N1
      if($elem['groupe_id']==1){
	$groupe_id=200+$i;
      }

      // Gestion des congés validation N2
      elseif($elem['groupe_id']==2){
	$groupe_id=600+$i;
      }

      // Gestion des congés N1
      elseif($elem['groupe_id']==7){
	$groupe_id=400+$i;
      }

      // Gestion des absences validation N2
      elseif($elem['groupe_id']==8){
	$groupe_id=500+$i;
      }

      // Modification des plannings si plusieurs sites
      elseif($elem['groupe_id']==12){
	$groupe_id=300+$i;
      }

      $checked=null;
      $checked="Non";
      if(is_array($acces)){
	$checked=in_array($groupe_id,$acces)?"checked='checked'":null;
	$checked2=$checked?"Oui":"Non";
	$class=$checked?"green bold":"red";
      }

      if($admin){
	echo "<td class='center'><input type='checkbox' name='droits[]' $checked value='$groupe_id' /></td>\n";
      }else{
	echo "<td class='center $class'>$checked2</td>\n";
      }
    }
    echo "</tr>\n";
  }
  echo "<tbody></table>\n";
}


?>
</div>
<!--	FIN Droits d'accès		-->

<?php
if(in_array("conges",$plugins)){
  include "plugins/conges/ficheAgent.php";
}
?>
</div>	<!-- .ui-tabs	-->
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
-->
</script>