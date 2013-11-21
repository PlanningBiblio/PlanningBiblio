<?php
/*
Planning Biblio, Version 1.6.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : absences/voir.php
Création : mai 2011
Dernière modification : 21 novembre 2013
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

echo "<br/>\n";
echo "<table id='tableAbsences'>\n";
echo "<thead><tr>\n";
echo "<td>&nbsp;</th>\n";
echo "<th>Début</th>\n";
echo "<th>Fin</th>\n";
if($admin){
  echo "<th>Nom</th>\n";
}
echo "<th>&Eacute;tat</th>\n";
echo "<th>Motif</th>\n";
echo "<th>Commentaires</th>\n";
echo "</tr></thead>\n";
echo "<tbody>\n";

$i=0;
if($absences){
  foreach($absences as $elem){
    $etat=$elem['valide']>0?"Valid&eacute;":"Demand&eacute";
    $etat=$elem['valide']<0?"Refus&eacute;":$etat;

    echo "<tr>\n";
    if($admin or in_array(6,$droits)){
      echo "<td><a href='index.php?page=absences/modif.php&amp;id=".$elem['id']."'>\n";
      echo "<img border='0' src='img/modif.png' alt='Modif' /></a></td>\n";
    }
    else{
      echo "<td>&nbsp;</td>\n";
    }
    echo "<td>".dateFr($elem['debut'],true)."</td>";
    echo "<td>".datefr($elem['fin'],true)."</td>";
    if($admin){
      echo "<td>{$elem['nom']} {$elem['prenom']}</td>";
    }
    echo "<td>$etat</td>\n";
    echo "<td>{$elem['motif']}</td>\n";
    echo "<td>{$elem['commentaires']}</td></tr>\n";
    $i++;
  }
}
echo "</tbody></table>";
echo "<br/><a href='index.php?page=absences/index.php'>Retour</a>";
?>

<script type='text/JavaScript'>
$(document).ready(function() {
  $("#tableAbsences").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aaSorting" : [[1,"asc"],[2,"asc"]],
    "aoColumns" : [{"bSortable":false},{"bSortable":true},{"bSortable":true},{"bSortable":true},{"bSortable":true},
      {"bSortable":true},
      <?php
      if($admin){
	echo '{"bSortable":true},';
      }
      ?>
      ],
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : 25,
    "oLanguage" : {"sUrl" : "js/dataTables/french.txt"}
  });

  $(document).tooltip();
  $("#ajouter").button();
});
</script>