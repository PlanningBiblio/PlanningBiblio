<?php
/*
Planning Biblio, Version 1.5.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : personnel/index.php
Création : mai 2011
Dernière modification : 26 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le tableau des agents avec les filtres "service public - administratif - supprimé" et Nom

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

if(!array_key_exists('agent_nom',$_SESSION)){
  $_SESSION['agent_nom']=null;
}

$nom=isset($_GET['nom'])?$_GET['nom']:$_SESSION['agent_nom'];
$_SESSION['agent_nom']=$nom;
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
</td><td>
<br/>
Nom : 
</td><td>
<br/>
<?php echo "<input type='text' name='nom' value='$nom' size='8' />\n"; ?>
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

$tri=isset($_GET['tri'])?$_GET['tri']:"nom,prenom";
	
echo "<script type='text/JavaScript'>document.form2.actif.value='".$_GET['actif']."';</script>";

$p=new personnel();
$p->supprime=strstr($_GET['actif'],"Supprim")?array(1):array(0);
$p->fetch($tri,$_GET['actif'],$nom);
$agents=$p->elements;

echo "<form name='form' method='post' action='index.php' onsubmit='return confirm(\"Etes vous sûr de vouloir supprimer les agents sélectionnés ?\");'>\n";
echo "<table style='width:100%' cellspacing='0'>";
echo "<tr class='th'><td>";
echo "<input type='checkbox' onclick='checkall(\"form\",this);' />";
echo "</td><td>";
if(in_array(13,$droits)){
  echo "ID";
  echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=id'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
  echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=id%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
  echo "</td><td>";
}
echo "Nom";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=nom,prenom'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=nom%20desc,prenom%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Pr&#233;nom";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=prenom,nom'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=prenom%20desc,nom%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Heures";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=heuresHebdo'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=heuresHebdo%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Statut";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=statut'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=statut%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Service";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=service'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=service%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td>\n";
if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
  echo "<td>Site";
  echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=site'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
  echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=site%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
  echo "</td>\n";
}
echo "<td>";
echo "Arriv&#233;e";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=arrivee'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=arrivee%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "D&#233;part";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=depart'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=depart%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Accès";
echo "&nbsp;&nbsp;<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=last_login'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=personnel/index.php&amp;nom=$nom&amp;tri=last_login%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td></tr>";

$i=0;
$class="tr1";
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

  $class=$class=="tr2"?"tr1":"tr2";

  echo "<tr class='$class'><td>\n";
  echo "<input type='checkbox' name='chk$i' value='$id' />\n";
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
    $site=$agent['site']?$config["Multisites-site{$agent['site']}"]:"&nbsp;";
    echo "<td>$site</td>";
  }
  echo "<td>$arrivee</td>";
  echo "<td>$depart</td>";
  echo "<td>$last_login</td>";
  echo "</tr>";
  $i++;
}

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