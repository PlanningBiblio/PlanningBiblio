<?php
/*
Planning Biblio, Version 1.7.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : absences/motifs.php
Création : 28 février 2014
Dernière modification : 28 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'afficher et de modifier la liste des motifs d'absences
Intégrée par les pages absences/ajouter.php et absences/modif.php
La variable $motifs est réutilisée dans les pages absences/ajouter.php et absences/modif.php

Page appelée par la page index.php
*/

// Liste des motifs
$db_motifs=new db();
$db_motifs->select("select_abs",null,null,"order by rang");
$motifs=$db_motifs->result;

// Liste des motifs utilisés
$motifs_utilises=array();
$db_motifs=new db();
$db_motifs->select("absences","motif",null,"group by motif");
if($db_motifs->result){
  foreach($db_motifs->result as $elem){
    $motifs_utilises[]=$elem['motif'];
  }
}
?>

<!--	Modification de la liste des motifs (Dialog Box) -->  
<div id="add-motif-form" title="Liste des motifs d'absences" class='noprint' style='display:none;'>
  <p class="validateTips">Ajoutez, supprimez et modifier l'ordre des motifs dans les menus déroulant.</p>
  <form>
  <p><input type='text' id='add-motif-text' style='width:300px;'/>
    <input type='button' id='add-motif-button2' class='ui-button' value='Ajouter' style='margin-left:15px;'/></p>
  <fieldset>
    <ul id="motifs-sortable">
<?php
    if(is_array($motifs)){
      foreach($motifs as $elem){
	echo "<li class='ui-state-default' id='li_{$elem['id']}'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>\n";
	echo "<font id='valeur_{$elem['id']}'>{$elem['valeur']}</font>\n";
	if(!in_array($elem['valeur'],$motifs_utilises)){
	  echo "<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>\n";
	}
	echo "</li>\n";
      }
    }
?>
    </ul>
  </fieldset>
  </form>
</div>