<?php
/*
Planning Biblio, Version 1.5.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : absences/voir.php
Création : mai 2011
Dernière modification : 29 août 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le tableau des absences avec formulaire permettant de recherche selon une date de début et de fin et selon
le nom de l'agent

Page appelée par la page index.php
*/

require_once "class.absences.php";
echo "<h3>Liste des absences</h3>\n";

//	Initialisation des variables
$only_me=null;
$admin=in_array(1,$droits)?true:false;
if(!$admin){
  $only_me=" AND `{$dbprefix}personnel`.`id`='{$_SESSION['login_id']}' ";
}

$agent=isset($_GET['agent'])?$_GET['agent']:null;
$tri=isset($_GET['tri'])?$_GET['tri']:"`debut`,`fin`,`nom`,`prenom`";
$debut=isset($_GET['debut'])?$_GET['debut']:null;
$fin=isset($_GET['fin'])?$_GET['fin']:null;

// Multisites : filtre pour n'afficher que les agents du site voulu
$sites=null;
if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
  $sites=array();
  if(in_array(201,$droits)){
    $sites[]=1;
  }
  if(in_array(202,$droits)){
    $sites[]=2;
  }
}

$a=new absences();
$a->fetch($tri,$only_me,$agent,$debut,$fin,$sites);
$absences=$a->elements;
  
echo "<form name='form' method='get' action='index.php'>\n";
echo "<input type='hidden' name='page' value='absences/voir.php' />\n";
echo "Début : <input type='text' name='debut' value='$debut' />&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"debut\");' alt='calendrier' />\n";
echo "&nbsp;&nbsp;Fin : <input type='text' name='fin' value='$fin' />&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"fin\");' alt='calendrier' />\n";

if($admin){
  echo "&nbsp;&nbsp;Agent : <input type='text' name='agent' value='$agent' />\n";
}
echo "&nbsp;&nbsp;<input type='submit' value='OK' />\n";
echo "&nbsp;&nbsp;<input type='button' value='Effacer' onclick='location.href=\"index.php?page=absences/voir.php\"' />\n";
echo "</form>\n";

echo "<table class='tableauStandard'>\n";
echo "<tr class='th'>\n";
echo "<td style='width:20px'>&nbsp;</td>\n";
echo "<td>Début\n";
echo "&nbsp;&nbsp;<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=debut,fin,nom,prenom'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=debut%20desc,fin%20desc,nom,prenom'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td>\n";
echo "<td>Fin\n";
echo "&nbsp;&nbsp;<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=fin,debut,nom,prenom'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=fin%20desc,debut%20desc,nom,prenom'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td>\n";
echo "<td>Nom\n";
echo "&nbsp;&nbsp;<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=nom,prenom,debut,fin'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=nom%20desc,prenom%20desc,debut,fin'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td>\n";
echo "<td>Motif\n";
echo "&nbsp;&nbsp;<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=motif,debut,fin,nom,prenom'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=absences/voir.php&amp;debut=$debut&amp;fin=$fin&amp;tri=motif%20desc,debut,fin,nom,prenom'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td>\n";
echo "<td>Commentaires</td>\n";
echo "</tr>\n";

$i=0;
if($absences){
  foreach($absences as $elem){
    $class=$i%2?"tr1":"tr2";
    echo "<tr class='$class'>\n";
    if($admin or in_array(6,$droits)){
      echo "<td><a href='index.php?page=absences/modif.php&amp;id=".$elem['id']."'>\n";
      echo "<img border='0' src='img/modif.png' alt='Modif' /></a></td>\n";
    }
    else{
      echo "<td>&nbsp;</td>\n";
    }
    echo "<td>".dateFr($elem['debut'],true)."</td>";
    echo "<td>".datefr($elem['fin'],true)."</td>";
    echo "<td>{$elem['nom']} {$elem['prenom']}</td>";
    echo "<td>{$elem['motif']}</td>\n";
    echo "<td>{$elem['commentaires']}</td></tr>\n";
    $i++;
  }
}
echo "</table>";
echo "<br/><a href='index.php?page=absences/index.php'>Retour</a>";
?>