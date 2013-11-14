<?php
/*
Planning Biblio, Version 1.6.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : activites/index.php
Création : mai 2011
Dernière modification : 26 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche la liste des activités, avec filtre sur le nom de l'activité

Page appelée par la page index.php
*/

require_once "class.activites.php";

//		Initialisation des variables
$nom=isset($_GET['nom'])?$_GET['nom']:null;
$tri=isset($_GET['tri'])?$_GET['tri']:"nom";
$class=null;

//		Recherche des activités
$a=new activites();
$a->fetch($tri,$nom);
$activites=$a->elements;

// 		Contrôle si l'activité est attribuée à un poste pour en interdire la suppression
$activites_utilisees=array();
$tab=array();
$db=new db();
$db->query("SELECT `activites` FROM `{$dbprefix}postes` GROUP BY `activites`;");
if($db->result){
  foreach($db->result as $elem){
    $tab[]=unserialize($elem['activites']);
  }
}

// 		Contrôle si l'activité est attribuée à un agent pour en interdire la suppression
$db=new db();
$db->query("SELECT `postes` FROM `{$dbprefix}personnel` WHERE `supprime`<>'2' GROUP BY `postes`;");
if($db->result){
  foreach($db->result as $elem){
    $tab[]=unserialize($elem['postes']);
  }
}

if($tab[0]){
  foreach($tab as $elem){
    if(is_array($elem)){
      foreach($elem as $act){
	if(!in_array($act,$activites_utilisees)){
	  $activites_utilisees[]=$act;
	}
      }
    }
  }
}

?>
<br/>

<form name="form" action="index.php">
<input type='hidden' name='page' value='activites/index.php' />
<table><tr valign='top'>
<td style='width:270px'>
<h3 style='margin-top:0px;'>Liste des activités</h3>
</td>
<td>
Rechercher : 
</td><td>
<?php echo "<input type='text' name='nom' size='8' value='$nom' />\n"; ?>
</td><td style='width:80px'>
<input type='submit' value='OK'/>
</td><td>
<input type="button" value="Ajouter" onclick='location.href="index.php?page=activites/modif.php"'/>
</td></tr></table>
</form>


<?php
echo "<table style='width:100%;' cellspacing='0'>\n";
echo "<tr class='th'><td>";
echo "&nbsp;";
if(in_array(13,$droits)){
  echo "</td><td>";
  echo "ID\n";
}
echo "</td><td>";
echo "Nom de l'activités\n";
echo "&nbsp;&nbsp;<a href='index.php?page=activites/index.php&amp;nom=$nom&amp;tri=nom'><img src='img/up.png' alt='+' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=activites/index.php&amp;nom=$nom&amp;tri=nom%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td></tr>\n";

foreach($activites as $elem){
  $class=$class=="tr1"?"tr2":"tr1";
  echo "<tr class='$class'><td>\n";
  echo "<a href='index.php?page=activites/modif.php&amp;id={$elem['id']}'>\n";
  echo "<img src='img/modif.png' border='0' alt='modif' /></a>\n";
  if(!in_array($elem['id'],$activites_utilisees)){
    echo "&nbsp;&nbsp;";
    echo "<a href='javascript:supprime(\"activites\",{$elem['id']});'>";
    echo "<img src='img/suppr.png' border='0' alt='supp' /></a>\n";
  }
  echo "</td>\n";
  if(in_array(13,$droits))
    echo "<td>{$elem['id']}</td>\n";
  echo "<td>{$elem['nom']}</td>\n";
  echo "</tr>\n";
}
echo "</table>\n";
?>