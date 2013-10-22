<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.6
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : postes/index.php													*
* Création : mai 2011														*
* Dernière modification : 16 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Affiche la liste des postes dans un tableau avec un filtre sur le nom des postes.						*
*																*
* Page appelée par le fichier index.php. Accessible à partir du menu "Administration / Les postes" 				*
*********************************************************************************************************************************/

require_once "class.postes.php";

//			Affichage de la liste des postes
$groupe="Tous";
$nom=isset($_GET['nom'])?$_GET['nom']:null;
$tri=isset($_GET['tri'])?$_GET['tri']:"etage,nom";

// 		Contrôle si le poste est utilisé dans pl_poste pour interdire sa suppression si tel est le cas
$postes_utilises=array();
$db=new db();
$db->query("SELECT `poste` FROM `{$dbprefix}pl_poste` GROUP BY `poste`;");
if($db->result){
  foreach($db->result as $elem){
    $postes_utilises[]=$elem['poste'];
  }
}

// 		Contrôle si le poste est utilisé dans pl_poste_lignes pour interdire sa suppression si tel est le cas
$db=new db();
$db->query("SELECT `poste` FROM `{$dbprefix}pl_poste_lignes` WHERE `type`='poste' GROUP BY `poste`;");
if($db->result){
  foreach($db->result as $elem){
    $postes_utilises[]=$elem['poste'];
  }
}
?>
<br/>

<form name="form" action="#">
<input type='hidden' name='page' value='postes/index.php' />
<table><tr valign='top'>
<td style='width:270px'>
<h3 style='margin-top:0px;'>Liste des postes</h3>
</td>
<td>
Rechercher : 
</td><td>
<?php echo "<input type='text' name='nom' size='8' value='$nom' />\n"; ?>
</td><td style='width:80px'>
<input type='submit' value='OK'/>
</td><td>
<input type="button" value="Ajouter" onclick='location.href="index.php?page=postes/modif.php"'/>
</td></tr></table>
</form>

<?php

echo "<script type='text/JavaScript'>document.form.groupe.value='$groupe';</script>";
$p=new postes();
$p->fetch($tri,$nom,$groupe);
$postes=$p->elements;

echo "<table style='width:100%;' cellspacing='0'>\n";
echo "<tr class='th'><td>";
echo "&nbsp;";
if(in_array(13,$droits)){
  echo "</td><td>";
  echo "ID\n";
}
echo "</td><td>";
echo "Etage\n";
echo "&nbsp;&nbsp;<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=etage'><img src='img/up.png' alt='+' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=etage%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Nom du poste\n";
echo "&nbsp;&nbsp;<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=nom'><img src='img/up.png' alt='+' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=nom%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Activités\n";
echo "</td><td>";
echo "Obligatoire/renfort\n";
echo "&nbsp;&nbsp;<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=obligatoire'><img src='img/up.png' alt='+' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=obligatoire%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>";
echo "Bloquant\n";
echo "&nbsp;&nbsp;<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=bloquant'><img src='img/up.png' alt='+' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=bloquant%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td><td>\n";
echo "Statistiques\n";
echo "&nbsp;&nbsp;<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=statistiques'><img src='img/up.png' alt='+' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=postes/index.php&amp;nom=$nom&amp;tri=statistiques%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td></tr>\n";

for($i=0;$i<count($postes);$i++){
  $id=$postes[$i]['id'];
  $class=$i%2?"tr1":"tr2";
  
  echo "<tr class='$class'><td>\n";
  echo "<a href='index.php?page=postes/modif.php&amp;id=$id'><img src='img/modif.png' border='0' alt='modif' /></a>\n";
  if(!in_array($id,$postes_utilises)){
    echo "&nbsp;&nbsp;";
    echo "<a href='javascript:supprime(\"postes\",$id);'><img src='img/suppr.png' border='0' alt='supp' /></a>\n";
  }
  if(in_array(13,$droits)){
    echo "</td><td>\n";
    echo $postes[$i]['id'];
  }
  echo "</td><td>\n";
  echo $postes[$i]['etage'];
  echo "</td><td>\n";
  echo $postes[$i]['nom'];
  echo "</td><td>\n";
  $activites=unserialize($postes[$i]['activites']);
  $liste=array();
  if(is_array($activites)){
    $activites=join($activites,",");
    $act=new db();
    $act->query("SELECT `nom` FROM `{$dbprefix}activites` WHERE `id` IN ($activites);");
    $j=0;
    if($act->result){
      echo "<div id='act$i' onmouseover='affiche_activites($i,\"affiche\");'>\n";		// mettre un timeout dans la fonction
      foreach($act->result as $elem){
	$liste[]=$elem['nom'];
	if($j++<3){
	  echo $elem['nom'].", ";
	}
      }
      echo "...</div>\n";
      echo "<div id='act{$i}b' style='display:none;' onmouseout='affiche_activites($i,\"cache\");'>";
      foreach($liste as $elem){
	echo $elem."<br/>\n";
      }
      echo "</div>\n";
    }
  }
  echo "</td><td>\n";
  echo $postes[$i]['obligatoire'];
  echo "</td><td>\n";
  echo $postes[$i]['bloquant']?"Oui":"Non";
  echo "</td><td>\n";
  echo $postes[$i]['statistiques']?"Oui":"Non";
  echo "</td></tr>\n";
}

echo "</table>\n";