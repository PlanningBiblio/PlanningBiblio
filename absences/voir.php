<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/voir.php
Création : mai 2011
Dernière modification : 11 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Affiche le tableau des absences avec formulaire permettant de recherche selon une date de début et de fin et selon
le nom de l'agent

Page appelée par la page index.php
*/

require_once "class.absences.php";
require_once "personnel/class.personnel.php";

// Initialisation des variables
$debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$reset=filter_input(INPUT_GET,"reset",FILTER_CALLBACK,array("options"=>"sanitize_on"));

$debut=$debut?$debut:(isset($_SESSION['oups']['absences_debut'])?$_SESSION['oups']['absences_debut']:null);
$fin=$fin?$fin:(isset($_SESSION['oups']['absences_fin'])?$_SESSION['oups']['absences_fin']:null);

echo "<h3>Liste des absences</h3>\n";

//	Initialisation des variables
$only_me=null;
$admin=in_array(1,$droits)?true:false;
if(!$admin){
  $only_me=" AND `{$dbprefix}personnel`.`id`='{$_SESSION['login_id']}' ";
}

if($admin){
  $perso_id=filter_input(INPUT_GET,"perso_id",FILTER_SANITIZE_NUMBER_INT);
  if($perso_id===null){
    $perso_id=isset($_SESSION['oups']['absences_perso_id'])?$_SESSION['oups']['absences_perso_id']:$_SESSION['login_id'];
  }
}
else{
  $perso_id=$_SESSION['login_id'];
}
if($reset){
  $perso_id=$_SESSION['login_id'];
}

$agents_supprimes=isset($_SESSION['oups']['absences_agents_supprimes'])?$_SESSION['oups']['absences_agents_supprimes']:false;
$agents_supprimes=(isset($_GET['debut']) and isset($_GET['supprimes']))?true:$agents_supprimes;
$agents_supprimes=(isset($_GET['debut']) and !isset($_GET['supprimes']))?false:$agents_supprimes;

if($reset){
  $debut=null;
  $fin=null;
  $agents_supprimes=false;
}

$_SESSION['oups']['absences_debut']=$debut;
$_SESSION['oups']['absences_fin']=$fin;
$_SESSION['oups']['absences_perso_id']=$perso_id;
$_SESSION['oups']['absences_agents_supprimes']=$agents_supprimes;

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);

// Multisites : filtre pour n'afficher que les agents du site voulu
$sites=null;
if($config['Multisites-nombre']>1){
  $sites=array();
  if(in_array(201,$droits)){
    $sites[]=1;
  }
  if(in_array(202,$droits)){
    $sites[]=2;
  }
}

$a=new absences();
if($agents_supprimes){
  $a->agents_supprimes=array(0,1);
}
$tri="`debut`,`fin`,`nom`,`prenom`";
$a->fetch($tri,$only_me,$perso_id,$debutSQL,$finSQL,$sites);
$absences=$a->elements;

// Recherche des agents
if($admin){
  $p=new personnel();
  if($agents_supprimes){
    $p->supprime=array(0,1);
  }
  $p->fetch();
  $agents=$p->elements;
}

// Tri par défaut du tableau
$sort="[[0],[1]]";
if($admin or (!$config['Absences-adminSeulement'] and in_array(6,$droits))){
  $sort="[[1],[2]]";
}

echo "<form name='form' method='get' action='index.php'>\n";
echo "<input type='hidden' name='page' value='absences/voir.php' />\n";
echo "<table class='tableauStandard'><tbody><tr>\n";
echo "<td style='vertical-align:middle;'><label class='intitule'>Début :</label> <input type='text' name='debut' value='$debut' class='datepicker'/></td>\n";
echo "<td style='vertical-align:middle;'><label class='intitule'>Fin :</label> <input type='text' name='fin' value='$fin'  class='datepicker'/></td>\n";

if($admin){
  echo "<td style='vertical-align:middle;text-align:left;'>\n";
  echo "<span style='padding:5px;'>\n";
  echo "<label class='intitule'>Agent :</label> ";
  echo "<select name='perso_id' id='perso_id' class='ui-widget-content ui-corner-all'>";
  $selected=$perso_id==0?"selected='selected'":null;
  echo "<option value='0' $selected >Tous</option>";
  foreach($agents as $agent){
    $selected=$agent['id']==$perso_id?"selected='selected'":null;
    echo "<option value='{$agent['id']}' $selected >{$agent['nom']} {$agent['prenom']}</option>";
  }
  echo "</select>\n";
  echo "</span>\n";

  $checked=$agents_supprimes?"checked='checked'":null;

  echo "<br/>\n";
  echo "<span style='padding:5px;'>Agents supprim&eacute;s : ";
  echo "<input type='checkbox' $checked name='supprimes' onclick='updateAgentsList(this,\"perso_id\");'/>\n";
  echo "</span>\n";
  echo "</td>\n";
}

echo "<td><input type='submit' value='OK' class='ui-button'/></td>\n";
echo "<td><input type='button' value='Effacer' onclick='location.href=\"index.php?page=absences/voir.php&amp;reset=1\"'  class='ui-button' /></td>\n";
echo "</tr></tbody></table>\n";
echo "</form>\n";

echo "<br/>\n";
echo "<table id='tableAbsencesVoir' class='CJDataTable' data-sort='$sort' >\n";
echo "<thead><tr>\n";
if($admin or (!$config['Absences-adminSeulement'] and in_array(6,$droits))){
  echo "<th class='dataTableNoSort' >&nbsp;</th>\n";
}
echo "<th class='dataTableDateFR' >Début</th>\n";
echo "<th class='dataTableDateFR-fin' >Fin</th>\n";
if($admin){
  echo "<th id='thNom'>Nom</th>\n";
}
if($config['Absences-validation']){
  echo "<th id='thValidation'>&Eacute;tat</th>\n";
}
echo "<th>Motif</th>\n";
echo "<th>Commentaires</th>\n";
echo "<th class='dataTableDateFR' >Demande</th>\n";

if(in_array(701,$droits)){
  echo "<th id='thPiecesJustif' class='dataTableNoSort' >\n";
  echo "<label style='white-space:nowrap'>Pi&egrave;ces justificatives</label><br/>\n";
  echo "<div class='absences-pj'>PJ 1</div><div class='absences-pj'>PJ 2</div><div class='absences-pj'>SO</div></th>\n";
}

echo "</tr></thead>\n";
echo "<tbody>\n";

$i=0;
if($absences){
  foreach($absences as $elem){
    $id=$elem['id'];

    $etat="Demand&eacute;e";
    $etat=$elem['valideN1']>0?"En attente de validation hierarchique, ".nom($elem['valideN1']).", ".dateFr($elem['validationN1'],true):$etat;
    $etat=$elem['valideN1']<0?"En attente de validation hierarchique, ".nom(-$elem['valideN1']).", ".dateFr($elem['validationN1'],true):$etat;
    $etat=$elem['valide']>0?"Valid&eacute;e, ".nom($elem['valide']).", ".dateFr($elem['validation'],true):$etat;
    $etat=$elem['valide']<0?"Refus&eacute;e, ".nom(-$elem['valide']).", ".dateFr($elem['validation'],true):$etat;
    $etatStyle=$elem['valide']==0?"font-weight:bold;":null;
    $etatStyle=$elem['valide']<0?"color:red;":$etatStyle;

    $pj1Checked=$elem['pj1']?"checked='checked'":null;
    $pj2Checked=$elem['pj2']?"checked='checked'":null;
    $soChecked=$elem['so']?"checked='checked'":null;

    echo "<tr>\n";
    if($admin or (!$config['Absences-adminSeulement'] and in_array(6,$droits))){
      echo "<td><a href='index.php?page=absences/modif.php&amp;id=$id'>\n";
      echo "<span class='pl-icon pl-icon-edit' title='Voir'></span></a></td>\n";
    }
    echo "<td>".dateFr($elem['debut'],true)."</td>";
    echo "<td>".datefr($elem['fin'],true)."</td>";
    if($admin){
      echo "<td>{$elem['nom']} {$elem['prenom']}</td>";
    }
    if($config['Absences-validation']){
      echo "<td style='$etatStyle'>$etat</td>\n";
    }
    echo "<td>{$elem['motif']}</td>\n";
    echo "<td title='{$elem['commentaires']}'><div style='height:20px;overflow:hidden;'>{$elem['commentaires']}</div></td>\n";
    echo "<td>".dateFr($elem['demande'],true)."</td>\n";

    if(in_array(701,$droits)){
      echo "<td style='text-align:center;'>";
      echo "<div class='absences-pj'><input type='checkbox' id='pj1-$id' $pj1Checked /></div>\n";
      echo "<div class='absences-pj'><input type='checkbox' id='pj2-$id' $pj2Checked /></div>\n";
      echo "<div class='absences-pj'><input type='checkbox' id='so-$id'  $soChecked  /></div>\n";
      echo "</td>\n";
    }
    echo "</tr>\n";
    $i++;
  }
}
echo "</tbody></table>";
?>