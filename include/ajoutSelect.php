<?php
/*
Planning Biblio, Version 1.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : include/ajoutSelect.php
Création : mai 2011
Dernière modification : 12 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'ajouter et de supprimer des éléments dans les menu déroulant (motif d'absence, statut, service de rattachement ...)
S'ouvre dans un cadre flottant à l'aide des fonctions JS ajoutSelect et popup

*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

$terme=$_GET['terme'];
$table=$_GET['table'];
if(isset($_GET['action']) and $_GET['action']=="ajout"){
  $nouveau=$_GET['nouveau'];
  $apres=$_GET['apres'];
  $db=new db();
  $db->query("update {$dbprefix}$table set rang=rang+1 where rang>$apres;");
  $rang=$apres+1;
  $db=new db();
  $db->insert2($table,array("valeur"=>$nouveau,"rang"=>$rang));
  echo "<script type='text/JavaScript'>parent.window.location.reload(false);</script>";
  echo "<script type='text/JavaScript'>popup_closed();</script>";
}
elseif(isset($_GET['action']) and $_GET['action']=="suppression"){
  $rang=$_GET['rang'];
  
  //		---------------		verifions si la valeur à supprimer et urilisée		----------------//
  $existe=false;
  $req="select valeur from {$dbprefix}$table where rang=$rang;";
  $db=new db();
  $db->query($req);
  $valeur=$db->result[0]['valeur'];

  if($table=="select_abs")
    $req="select * from {$dbprefix}absences where motif='$valeur';";
  elseif($table=="select_statuts")
    $req="select * from {$dbprefix}personnel where statut='$valeur';";
  elseif($table=="select_services")
    $req="select * from {$dbprefix}personnel where service='$valeur';";
  elseif($table=="select_etages")
    $req="select * from {$dbprefix}postes where etage='$valeur';";

  $db=new db();
  $db->query($req);
  if($db->result){		//		---------------		si la valeur à supprimer et urilisée		----------------//
    echo "<br/><font color='red'>Impossible de supprimer \"$valeur\" car cette valeur est utilisée</font><br/><br/>";
    echo "<a href='javascript:popup_closed();'>Fermer</a><br/><br/><hr>";
  }
  else{
    $db=new db();
    $db->query("delete from {$dbprefix}$table where rang=$rang;");
    $db=new db();
    $db->query("update {$dbprefix}$table set rang=rang-1 where rang>$rang;");
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
echo "Nouveau $terme :";
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
$db_select->query("select valeur,rang from {$dbprefix}$table order by rang;");
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
echo "$terme à supprimer : ";
?>
</td><td>

<?php
echo "<select name='rang' onchange='verif(\"suppression\");'>\n";
echo "<option value=''>-----------------------</option>\n";
$db_select=new db();
$db_select->query("select valeur,rang from {$dbprefix}$table order by rang;");
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