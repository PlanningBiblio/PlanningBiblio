<?php
/**
Planning Biblio, Version 2.3.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : personnel/index.php
Création : mai 2011
Dernière modification : 28 mai 2016
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Chritophe Le Guennec <christophe.leguennec@u-pem.fr>

Description :
Affiche le tableau des agents avec les filtres "service public - administratif - supprimé" et le filtre "Rechercher" du tableau
Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

// Initialisation des variables
$actif=filter_input(INPUT_GET,"actif",FILTER_SANITIZE_STRING);

if(!$actif and array_key_exists('perso_actif',$_SESSION)){
  $actif=$_SESSION['perso_actif'];
}
$_SESSION['perso_actif']=$actif;

?>

<form name='form2' action='index.php' method='get'>
<input type='hidden' name='page' value='personnel/index.php' />
<table style='margin-bottom:10px;'><tr style='vertical-align:center;'>
<td style='width:270px;'>
<h3 style='margin:0;'>Liste des agents</h3>
</td><td>
Voir les agents
</td><td style='width:150px;'>
<select name='actif' onchange='document.form2.submit();'  class='ui-widget-content ui-corner-all'>
<option value='Actif'>Service public</option>
<option value='Inactif'>Administratif</option>
<?php
if(in_array(21,$droits)){
  echo "<option value='Supprim&eacute;'>Supprim&eacute;</option>\n";
}
?>
</select>
</td><td style='width:80px;'>
</td><td>
<?php
if(in_array(21,$droits)){
  echo "<input type='button' value='Ajouter' onclick='location.href=\"index.php?page=personnel/modif.php\";' class='ui-button'/>\n";
  if($config['LDAP-Host'] and $config['LDAP-Suffix']){
    echo "<input type='button' value='Import LDAP' onclick='location.href=\"index.php?page=personnel/import.php\";' class='ui-button' style='margin-left:20px;'/>\n";
  }
}
?>
</td></tr></table>
</form>


<?php
//		Suppression des agents dont la date de départ est passée		//
$tab=array(0);
$db=new db();
$db->update("personnel","`supprime`='1', `actif`='Supprim&eacute;'","`depart`<CURDATE() AND `depart`<>'0000-00-00' and `actif` NOT LIKE 'Supprim%'");

echo "<script type='text/JavaScript'>document.form2.actif.value='$actif';</script>";

$p=new personnel();
$p->supprime=strstr($actif,"Supprim")?array(1):array(0);
$p->fetch("nom,prenom",$actif);
$agents=$p->elements;

echo "<form name='form' method='post' action='index.php' onsubmit='return confirm(\"Etes vous sûr de vouloir supprimer les agents sélectionnés ?\");'>\n";
echo "<table id='tableAgents' class='CJDataTable' data-sort='[[1,\"asc\"],[2,\"asc\"]]' >\n";
echo "<thead>\n";
echo "<tr><th class='dataTableNoSort aLeft' ><input type='checkbox' class='CJCheckAll'/></th>\n";

echo "<th>Nom</th>";
echo "<th>Pr&#233;nom</th>";
echo "<th class='dataTableHeureFR'>Heures</th>";
echo "<th>Statut</th>";
echo "<th>Service</th>";
if($config['Multisites-nombre']>1){
  echo "<th>Sites</th>\n";
}
echo "<th class='dataTableDateFR' >Arriv&#233;e</th>";
echo "<th class='dataTableDateFR' >D&#233;part</th>";
echo "<th class='dataTableDateFR' >Acc&egrave;s</th>";
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
  echo "<input type='checkbox' name='chk$i' value='$id' />\n";
  echo "<a href='index.php?page=personnel/modif.php&amp;id=$id'><span class='pl-icon pl-icon-edit' title='Modifier'></span></a>";
  if(in_array(21,$droits) and $id!=$_SESSION['login_id']){
    echo "<a href='javascript:popup(\"personnel/suppression.php&amp;id=".$id."\",450,250);'><span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>";
  }
  echo "</td>";
  echo "<td>{$agent['nom']}</td>";
  echo "<td>{$agent['prenom']}</td>";
  echo "<td>$heures</td>";
  echo "<td>{$agent['statut']}</td>";
  echo "<td>{$agent['service']}</td>";
  if($config['Multisites-nombre']>1){
    $tmp=array();
    if(!empty($agent['sites'])){
      foreach($agent['sites'] as $site){
	if($site){
	  $tmp[]=$config["Multisites-site{$site}"];
	}
      }
    }
    $sites=!empty($tmp)?join(", ",$tmp):null;
    echo "<td>$sites</td>";
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