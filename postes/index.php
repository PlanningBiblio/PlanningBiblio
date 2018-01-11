<?php
/**
Planning Biblio, Version 2.6.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : postes/index.php
Création : mai 2011
Dernière modification : 12 mai 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la liste des postes dans un tableau avec un filtre sur le nom des postes.

Page appelée par le fichier index.php. Accessible à partir du menu "Administration / Les postes" 
*/

require_once "class.postes.php";

//			Affichage de la liste des postes
$groupe="Tous";
$nom=filter_input(INPUT_GET,"nom",FILTER_SANITIZE_STRING);

// Contrôle si le poste est utilisé dans un tableau non-supprimé (tables pl_poste_lignes et pl_poste_tab)
$postes_utilises=array();

$db=new db();
$db->selectInnerJoin(array("pl_poste_lignes","numero"), array("pl_poste_tab","tableau"), array(array("name"=>"poste", "as"=>"poste")), array(), array("type"=>"poste"), array("supprime"=>null));
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
<form name="form" action="#">
<input type='hidden' name='page' value='postes/index.php' />
<table style='margin:20px 0;'><tr valign='top'>
<td style='width:270px'><h3 style='margin-top:0px;'>Liste des postes</h3></td>
<td>
<input type="button" value="Ajouter" id="ajouter" onclick='location.href="index.php?page=postes/modif.php"' class='ui-button'/>
</td></tr></table>
</form>

<?php
echo "<script type='text/JavaScript'>document.form.groupe.value='$groupe';</script>";
$p=new postes();
$p->fetch("nom",$nom,$groupe);
$postes=$p->elements;

$sort=in_array(13,$droits)?"[[2],[3]]":"[[1],[2]]";
echo "<table id='tablePostes' class='CJDataTable' data-sort='$sort'>\n";
echo "<thead><tr><th class='dataTableNoSort'>&nbsp;</th>\n";
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
  <th>Groupe</th>
  <th>Obligatoire/renfort</th>
  <th>Bloquant</th>
  <th>Statistiques</th>
  </tr></thead>
  <tbody>
EOD;

foreach($postes as $id => $value){
  // Affichage des 3 premières activités dans le tableau, toutes les activités dans l'infobulle
  $activites=array();
  $activitesAffichees=array();
  $activitesPoste=json_decode(html_entity_decode($value['activites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
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
  echo "<a href='index.php?page=postes/modif.php&amp;id=$id'><span class='pl-icon pl-icon-edit' title='Modifier' ></span></a>\n";
  if(!in_array($id,$postes_utilises)){
    echo "&nbsp;<a href='javascript:supprime(\"postes\",$id,\"$CSRFSession\");'><span class='pl-icon pl-icon-drop' title='Supprimer'></span></a></td>\n";
  }
  if(in_array(13,$droits)){
    echo "<td>$id</td>\n";
  }
  echo "<td>{$value['nom']}</td>\n";
  if($config['Multisites-nombre']>1){
    $site=array_key_exists("Multisites-site{$value['site']}",$config)?$config["Multisites-site{$value['site']}"]:"-";
    echo "<td>$site</td>\n";
  }
  echo "<td>{$value['etage']}</td>\n";
  echo "<td title='$activites' size='100'>$activitesAffichees</td>\n";
  echo "<td>{$value['groupe']}</td>\n";
  echo "<td>{$value['obligatoire']}</td>\n";
  echo "<td>".($value['bloquant']?"Oui":"Non")."</td>\n";
  echo "<td>".($value['statistiques']?"Oui":"Non")."</td>\n";
  echo "</tr>\n";
}

echo "</tbody>\n";
echo "</table>\n";
?>
