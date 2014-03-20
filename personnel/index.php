<?php
/*
Planning Biblio, Version 1.7.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : personnel/index.php
Création : mai 2011
Dernière modification : 18 mars 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le tableau des agents avec les filtres "service public - administratif - supprimé" et le filtre "Rechercher" du tableau
Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";
?>

<form name='form2' action='index.php' method='get'>
<input type='hidden' name='page' value='personnel/index.php' />
<table><tr valign='top'>
<td style='width:270px;'>
<h3>Liste des agents</h3>
</td><td>
<br/>
Voir les agents
</td><td style='width:150px;'>
<br/>
<select name='actif' onchange='document.form2.submit();'>
<option value='Actif'>Service public</option>
<option value='Inactif'>Administratif</option>
<?php
if(in_array(13,$droits)){
  echo "<option value='Supprim&eacute;'>Supprim&eacute;</option>\n";
}
?>
</select>
</td><td style='width:80px;'>
<br/>
<input type='submit' value='OK'/>
</td><td>
<br/>
<?php
if(in_array(21,$droits)){
  echo "<input type='button' value='Ajouter' onclick='location.href=\"index.php?page=personnel/modif.php\";'/>\n";
  if(in_array("ldap",$plugins)){
    echo "<input type='button' value='Import LDAP' onclick='location.href=\"index.php?page=personnel/import.php\";'/>\n";
  }
}
?>
</td></tr></table>
</form>


<?php
//		Suppression des agents dont la date de départ est passée		//
$tab=array(0);
$db=new db();
$db->query("UPDATE `{$dbprefix}personnel` SET `actif`='Supprim&eacute;' WHERE `depart`<SYSDATE() AND `depart`<>'0000-00-00' and `actif`<>'Supprimé';");


if(isset($_GET['actif']))
  $_SESSION['perso_actif']=$_GET['actif'];
elseif(array_key_exists('perso_actif',$_SESSION))
  $_GET['actif']=$_SESSION['perso_actif'];
else{
  $_GET['actif']='Actif';
  $_SESSION['perso_actif']='Actif';
}

echo "<script type='text/JavaScript'>document.form2.actif.value='".$_GET['actif']."';</script>";

$p=new personnel();
$p->supprime=strstr($_GET['actif'],"Supprim")?array(1):array(0);
$p->fetch("nom,prenom",$_GET['actif']);
$agents=$p->elements;

echo "<form name='form' method='post' action='index.php' onsubmit='return confirm(\"Etes vous sûr de vouloir supprimer les agents sélectionnés ?\");'>\n";
echo "<table id='table_agents'>\n";
echo "<thead>\n";
echo "<tr><th><input type='checkbox' id='checkAll'/></th>\n";

if(in_array(13,$droits)){
  echo "<th>ID</th>";
}
echo "<th>Nom</th>";
echo "<th>Pr&#233;nom</th>";
echo "<th>Heures</th>";
echo "<th>Statut</th>";
echo "<th>Service</th>";
if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
  echo "<th>Site</th>\n";
}
echo "<th>Arriv&#233;e</th>";
echo "<th>D&#233;part</th>";
echo "<th>Acc&egrave;s</th>";
echo "</thead>\n";
echo "<tbody>\n";
$i=0;
foreach($agents as $agent){
  $id=$agent['id'];
  
  $arrivee=date1($agent['arrivee']);
  $depart=date1($agent['depart']);
  $last_login=date_time($agent['last_login']);
  
  $heures=$agent['heuresHebdo']?$agent['heuresHebdo']:null;
  $heures=str_replace(array(".25",".5",".75"),array("h15","h30","h45"),$heures);
  if(is_numeric($heures)){
    $heures.="h00";
  }
  $agent['service']=str_replace("`","'",$agent['service']);

  echo "<tr><td style='white-space:nowrap;'>\n";
  echo "<input type='checkbox' name='chk$i' value='$id' class='checkAgent'/>\n";
  echo "<a href='index.php?page=personnel/modif.php&amp;id=$id'><img src='img/modif.png' border='0' alt='Modif' /></a>";
  if(in_array(21,$droits) and $id!=$_SESSION['login_id']){
    echo "&nbsp;";
    echo "<a href='javascript:popup(\"personnel/suppression.php&amp;id=".$id."\",450,250);'><img src='img/suppr.png' border='0' alt='Suppression' /></a>";
  }
  echo "</td>";
  if(in_array(13,$droits)){
    echo "<td>$id</td>";
  }
  echo "<td>{$agent['nom']}</td>";
  echo "<td>{$agent['prenom']}</td>";
  echo "<td>$heures</td>";
  echo "<td>{$agent['statut']}</td>";
  echo "<td>{$agent['service']}</td>";
  if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
    $site=$agent['sites'][0]?$config["Multisites-site{$agent['sites'][0]}"]:"&nbsp;";
    echo "<td>$site</td>";
  }
  echo "<td>$arrivee</td>";
  echo "<td>$depart</td>";
  echo "<td>$last_login</td>";
  echo "</tr>";
  $i++;
}

echo "</tbody>";
echo "</table>";
echo "<input type='hidden' name='page' value='personnel/suppression-liste.php' />\n";
echo "<input type='submit' value='Supprimer la sélection' />\n";
echo "</form>\n";

function date1($date){
  if($date=="0000-00-00")
    $date="";
  else{
    $date1=explode("-",$date);
    $date=$date1[2]."/".$date1[1]."/".$date1[0];
  }
  return $date;
}
?>
<script type='text/JavaScript'>
$(document).ready(function(){
  $("#table_agents").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : 25,
    "aaSorting" : [[2,"asc"],[3,"asc"]],
    "oLanguage" : {"sUrl" : "js/dataTables/french.txt"},
    "aoColumns" : [{"bSortable":false},{"bSortable":true},{"bSortable":true},{"bSortable":true},{"bSortable":true},
      {"bSortable":true},{"bSortable":true},{"sType": "date-fr"},{"sType": "date-fr"},{"sType": "date-fr"},
      <?php
      if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
	echo '{"bSortable":true},';
      }
      ?>
      ],
  });
});

$("#checkAll").click(function(){
  $(".checkAgent:visible").click();
});
</script>