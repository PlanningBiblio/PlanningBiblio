<?php
/**
Planning Biblio, Version 2.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : include/ajoutSelect.php
Création : mai 2011
Dernière modification : 2 novembre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'ajouter et de supprimer des éléments dans les menu déroulant (service de rattachement, étage (pour les postes) ...)
S'ouvre dans un cadre flottant à l'aide des fonctions JS ajoutSelect et popup
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if(__FILE__ == $_SERVER['SCRIPT_FILENAME']){
  include_once "accessDenied.php";
  exit;
}

// Initialisation des variables
$action=filter_input(INPUT_GET,"action",FILTER_SANITIZE_STRING);
$apres=filter_input(INPUT_GET,"apres",FILTER_SANITIZE_NUMBER_INT);
$nouveau=filter_input(INPUT_GET,"nouveau",FILTER_SANITIZE_STRING);
$rang=filter_input(INPUT_GET,"rang",FILTER_SANITIZE_NUMBER_INT);
$table=filter_input(INPUT_GET,"table",FILTER_SANITIZE_STRING);
$terme=filter_input(INPUT_GET,"terme",FILTER_SANITIZE_STRING);

if($action=="ajout"){
  $db=new db();
  $apres=$db->escapeString($apres);
  $tableSQL=$db->escapeString($table);
  $db->query("update {$dbprefix}$tableSQL set rang=rang+1 where rang>$apres;");
  $rang=$apres+1;
  $db=new db();
  $db->insert2($table,array("valeur"=>$nouveau,"rang"=>$rang));
  echo "<script type='text/JavaScript'>parent.window.location.reload(false);</script>";
  echo "<script type='text/JavaScript'>popup_closed();</script>";
}
elseif($action=="suppression"){
  //		---------------		verifions si la valeur à supprimer et urilisée		----------------//
  $existe=false;
  $db=new db();
  $tableSQL=$db->escapeString($table);
  $db->select2($tableSQL,"valeur",array("rang"=>$rang));
  $valeur=$db->result[0]['valeur'];

  if($table=="select_services"){
    $db=new db();
    $db->select2("personnel","*",array("service"=>$valeur));
  }elseif($table=="select_etages"){
    $db=new db();
    $db->select2("postes","*",array("etage"=>$valeur, "supprime"=>null));
  }elseif($table=="select_groupes"){
    $db=new db();
    $db->select2("postes","*",array("groupe"=>$valeur));
  }

  if($db->result){		//		---------------		si la valeur à supprimer et urilisée		----------------//
    echo "<br/><font color='red'>Impossible de supprimer \"$valeur\" car cette valeur est utilisée</font><br/><br/>";
    echo "<a href='javascript:popup_closed();'>Fermer</a><br/><br/><hr>";
  }
  else{
    $db=new db();
    $tableSQL=$db->escapeString($table);
    $db->delete2($tableSQL,array("rang"=>$rang));
    $db=new db();
    $rangSQL=$db->escapeString($rang);
    $tableSQL=$db->escapeString($table);
    $db->query("update {$dbprefix}$tableSQL set rang=rang-1 where rang>$rangSQL;");
    echo "<script type='text/JavaScript'>parent.window.location.reload(false);</script>";
    echo "<script type='text/JavaScript'>popup_closed();</script>";
  }
}
?>
<script type='text/JavaScript'>
<!--
function valider(action){
  if(action=="ajout" && document.form.apres.value==0){
    document.form.apres.value=rang;
  }
  document.form.action.value=action;
  document.form.submit();
}

function verif(objet){
  if(objet=="suppression"){
    if(document.form.rang.value!="")
      document.form.supprimer.style.display="";
    else
      document.form.supprimer.style.display="none";
  }
  else if(objet=="ajout"){
    if(document.form.nouveau.value!="")
      document.form.ajouter.style.display="";
    else
      document.form.ajouter.style.display="none";
    }
}

-->
</script>
<?php
echo "<h3>Ajout d'un $terme</h3>";
?>
<br/>
<form method='get' action='#' name='form' >
<input type='hidden' name='page' value='include/ajoutSelect.php'/>
<input type='hidden' name='menu' value='off'/>
<input type='hidden' name='action' value=''/>
<table>
<tr><td>
<?php
if(strtolower($terme) == 'étage'){
  echo "Nouvel $terme :";
}else{
  echo "Nouveau $terme :";
}
?>
</td><td>
<input type='text' name='nouveau' onkeyup='verif("ajout");' onblur='verif("ajout");' />
</td></tr>
<tr><td>
Placer après : 
</td><td>

<?php
echo "<input type='hidden' name='terme' value='$terme'/>";
echo "<input type='hidden' name='table' value='$table'/>";
echo "<select name='apres' onchange='verif(\"ajout\");'>\n";
echo "<option value='0'>-----------------------</option>\n";
$db_select=new db();
$tableSQL=$db_select->escapeString($table);
$db_select->select2($tableSQL,array("valeur","rang"),"1","order by rang");
foreach($db_select->result as $elem){
  echo "<option value='".$elem['rang']."'>".$elem['valeur']."</option>\n";
  $rang=$elem['rang'];
}
echo "</select>\n";
if(!$rang){
  $rang=0;
}
echo "<script type='text/JavaScript'>rang=$rang;</script>\n";
?>

</td></tr>
<tr><td>
<br/><input type='button' value='Annuler' onclick='popup_closed();' />
</td><td>
<br/><input type='button' value='Ajouter' onclick='valider("ajout");' name='ajouter' style='display:none' />
</td></tr>
</table>
<br/>
<hr/>
<?php
echo "<h3>Suppression d'un $terme</h3>";
?>
<br/>
<table>
<tr><td>
<?php
if($terme == 'étage'){
  $terme = '&Eacute;tage';
}
echo ucfirst($terme).' à supprimer : ';
?>
</td><td>

<?php
echo "<select name='rang' onchange='verif(\"suppression\");'>\n";
echo "<option value=''>-----------------------</option>\n";
$db_select=new db();
$tableSQL=$db_select->escapeString($table);
$db_select->select2($tableSQL,array("valeur","rang"),"1","order by rang");
foreach($db_select->result as $elem){
  echo "<option value='".$elem['rang']."'>".$elem['valeur']."</option>\n";
}
echo "</select>\n";
?>

</td></tr>
<tr><td>
<br/><input type='button' value='Annuler' onclick='popup_closed();' />
</td><td>
<br/><input type='button' value='Supprimer' onclick='valider("suppression");' name='supprimer' style='display:none'/>
</td></tr>
</table>
</form>
