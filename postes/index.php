<?php
/*
Planning Biblio, Version 1.6.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : postes/index.php
Création : mai 2011
Dernière modification : 16 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche la liste des postes dans un tableau avec un filtre sur le nom des postes.

Page appelée par le fichier index.php. Accessible à partir du menu "Administration / Les postes" 
*/

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

// Sélection des activités
$activitesTab=array();
$db=new db();
$db->select("activites");
if($db->result){
  foreach($db->result as $elem){
    $activitesTab[$elem["id"]]=$elem["nom"];
  }
}

?>
<br/>

<form name="form" action="#">
<input type='hidden' name='page' value='postes/index.php' />
<table><tr valign='top'>
<td style='width:270px'><h3 style='margin-top:0px;'>Liste des postes</h3></td>
<td><input type="button" value="Ajouter" id="ajouter" onclick='location.href="index.php?page=postes/modif.php"'/>
</td></tr></table>
</form>

<?php

echo "<script type='text/JavaScript'>document.form.groupe.value='$groupe';</script>";
$p=new postes();
$p->fetch($tri,$nom,$groupe);
$postes=$p->elements;

echo "<table id='tablePostes'>\n";
echo "<thead><tr><th>&nbsp;</th>\n";
if(in_array(13,$droits)){
  echo "<th>ID</th>";
}
echo "<th>Nom du poste</th>\n";
if($config['Multisites-nombre']>1){
  echo "<th>Site</th>\n";
}
echo <<<EOD
  <th>Etage</th>
  <th>Activités</th>
  <th>Obligatoire/renfort</th>
  <th>Bloquant</th>
  <th>Statistiques</th>
  </tr></thead>
  <tbody>
EOD;

for($i=0;$i<count($postes);$i++){
  $id=$postes[$i]['id'];

  // Affichage des 3 premières activités dans le tableau, toutes les activités dans l'infobulle
  $activites=array();
  $activitesAffichees=array();
  $activitesPoste=unserialize($postes[$i]['activites']);
  if(is_array($activitesPoste)){
    foreach($activitesPoste as $act){
      if(array_key_exists($act,$activitesTab)){
	$activites[]=$activitesTab[$act];
	if(count($activitesAffichees)<3){
	  $activitesAffichees[]=$activitesTab[$act];
	}
      }
    }
  }
  $activites=join(", ",$activites);
  $activitesAffichees=join(", ",$activitesAffichees);
  if(count($activitesPoste)>3){
    $activitesAffichees.=" ...";
  }

  echo "<tr><td style='white-space:nowrap;'>\n";
  echo "<a href='index.php?page=postes/modif.php&amp;id=$id'><img src='img/modif.png' border='0' alt='modif' /></a>\n";
  if(!in_array($id,$postes_utilises)){
    echo "&nbsp;<a href='javascript:supprime(\"postes\",$id);'><img src='img/suppr.png' border='0' alt='supp' /></a></td>\n";
  }
  if(in_array(13,$droits)){
    echo "<td>{$postes[$i]['id']}</td>\n";
  }
  echo "<td>{$postes[$i]['nom']}</td>\n";
  if($config['Multisites-nombre']>1){
    echo "<td>".$config["Multisites-site{$postes[$i]['site']}"]."</td>\n";
  }
  echo "<td>{$postes[$i]['etage']}</td>\n";
  echo "<td title='$activites' size='100'>$activitesAffichees</td>\n";
  echo "<td>{$postes[$i]['obligatoire']}</td>\n";
  echo "<td>".($postes[$i]['bloquant']?"Oui":"Non")."</td>\n";
  echo "<td>".($postes[$i]['statistiques']?"Oui":"Non")."</td>\n";
  echo "</tr>\n";
}

echo "</tbody>\n";
echo "</table>\n";
?>

<script type='text/JavaScript'>
$(document).ready(function() {
  $("#tablePostes").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": false,
    "aaSorting" : [[2,"asc"],[3,"asc"],[4,"asc"]],
    "aoColumns" : [{"bSortable":false},{"bSortable":true},{"bSortable":true},{"bSortable":true},{"bSortable":true},
      {"bSortable":true},{"bSortable":true},{"bSortable":true},
      <?php
      if($config['Multisites-nombre']>1){
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