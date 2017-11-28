<?php
/**
Planning Biblio, Version 2.7.05
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : absences/voir.php
Création : mai 2011
Dernière modification : 28 novembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche le tableau des absences avec formulaire permettant de recherche selon une date de début et de fin et selon
le nom de l'agent

Page appelée par la page index.php
*/

require_once "class.absences.php";

// Initialisation des variables
$debut=filter_input(INPUT_GET,"debut",FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_GET,"fin",FILTER_SANITIZE_STRING);
$reset=filter_input(INPUT_GET,"reset",FILTER_SANITIZE_STRING);

// Contrôle sanitize_dateFr en 2 temps pour éviter les erreurs CheckMarx
$debut=filter_var($debut,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));


$debut=$debut?$debut:(isset($_SESSION['oups']['absences_debut'])?$_SESSION['oups']['absences_debut']:null);
$fin=$fin?$fin:(isset($_SESSION['oups']['absences_fin'])?$_SESSION['oups']['absences_fin']:null);

$p = new personnel();
$p->supprime=array(0,1,2);
$p->fetch();
$agents = $p->elements;

echo "<h3>Liste des absences</h3>\n";

//	Initialisation des variables
$admin = in_array(1, $droits);

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
  for($i=1; $i<11; $i++){
    if(in_array((200 + $i), $droits)){
      $sites[]=$i;
    }
  }
}

$a=new absences();
$a->groupe=true;
if($agents_supprimes){
  $a->agents_supprimes=array(0,1);
}
$a->fetch(null,$perso_id,$debutSQL,$finSQL,$sites);
$absences=$a->elements;

// Tri par défaut du tableau
$sort="[[0],[1]]";
if($admin or (!$config['Absences-adminSeulement'] and in_array(6,$droits))){
  $sort="[[1],[2]]";
}

echo "<form name='form' method='get' action='index.php'>\n";
echo "<input type='hidden' name='page' value='absences/voir.php' />\n";
echo "<span style='float:left; vertical-align:top; margin-bottom:20px;'>\n";
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
  
  $p = new personnel();
  if($agents_supprimes){
    $p->supprime = array(0,1);
  }
  $p->fetch();
  $agents_menu = $p->elements;
  
  foreach($agents_menu as $agent){
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

echo "<td><input type='submit' value='Rechercher' class='ui-button' style='margin-right:20px;' />\n";
echo "<input type='button' value='Réinitialiser' onclick='absences_reinit();'  class='ui-button' /></td>\n";
echo "</tr></tbody></table>\n";
echo "</span>\n";
echo "<span style='float:right; vertical-align:top; margin:10px 5px;'>\n";
echo "<a href='index.php?page=absences/ajouter.php' class='ui-button'>Ajouter</a>\n";
echo "</span>\n";
echo "</form>\n";

echo "<table id='tableAbsencesVoir' class='CJDataTable' data-sort='$sort' >\n";
echo "<thead><tr>\n";
echo "<th class='dataTableNoSort' >&nbsp;</th>\n";
echo "<th class='dataTableDateFR' >Début</th>\n";
echo "<th class='dataTableDateFR-fin' >Fin</th>\n";
echo "<th id='thNom'>Agents</th>\n";
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

    $nom_n1a = $elem['valide_n1'] != 99999 ? nom($elem['valide_n1'],'nom p',$agents).", " : null;
    $nom_n1b = $elem['valide_n1'] != -99999 ? nom(-$elem['valide_n1'],'nom p',$agents).", " : null;
    $nom_n2a = $elem['valide'] != 99999 ? nom($elem['valide'],'nom p',$agents).", " : null;
    $nom_n2b = $elem['valide'] != -99999 ? nom(-$elem['valide'],'nom p',$agents).", " : null;
    $etat="Demand&eacute;e";
    $etat=$elem['valide_n1']>0?"En attente de validation hierarchique, $nom_n1a".dateFr($elem['validation_n1'],true):$etat;
    $etat=$elem['valide_n1']<0?"En attente de validation hierarchique, $nom_n1b".dateFr($elem['validation_n1'],true):$etat;
    $etat=$elem['valide']>0?"Valid&eacute;e, $nom_n2a".dateFr($elem['validation'],true):$etat;
    $etat=$elem['valide']<0?"Refus&eacute;e, $nom_n2b".dateFr($elem['validation'],true):$etat;
    $etatStyle=$elem['valide']==0?"font-weight:bold;":null;
    $etatStyle=$elem['valide']<0?"color:red;":$etatStyle;

    $pj1Checked=$elem['pj1']?"checked='checked'":null;
    $pj2Checked=$elem['pj2']?"checked='checked'":null;
    $soChecked=$elem['so']?"checked='checked'":null;

    echo "<tr>\n";
    echo "<td style='white-space: nowrap;'>\n";
    if($admin or (!$config['Absences-adminSeulement'] and in_array(6,$droits))){
      echo "<a href='index.php?page=absences/modif.php&amp;id=$id'>\n";
      echo "<span class='pl-icon pl-icon-edit' title='Voir'></span></a>\n";
    }
    if($elem['rrule']){
      echo "<span class='pl-icon pl-icon-recurring' title='R&eacute;currence'></span>\n";
    }
    echo "</td>\n";

    echo "<td>".dateFr($elem['debut'],true)."</td>";
    echo "<td>".datefr($elem['fin'],true)."</td>";
    echo "<td>";
    echo implode($elem['agents'],", ");
    echo "</td>\n";
    
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