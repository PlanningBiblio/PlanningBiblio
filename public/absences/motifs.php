<?php
/*
Planning Biblio, Version 2.5.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : absences/motifs.php
Création : 28 février 2014
Dernière modification : 5 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'afficher et de modifier la liste des motifs d'absences
Intégrée par les pages absences/ajouter.php et absences/modif.php
La variable $motifs est réutilisée dans les pages absences/ajouter.php et absences/modif.php

Page appelée par la page index.php
*/

// Liste des motifs
$db_motifs=new db();
$db_motifs->select("select_abs", null, null, "order by rang");
$motifs=$db_motifs->result;

// Liste des motifs utilisés
$motifs_utilises=array();
$db_motifs=new db();
$db_motifs->select("absences", "motif", null, "group by motif");
if ($db_motifs->result) {
    foreach ($db_motifs->result as $elem) {
        $motifs_utilises[]=$elem['motif'];
    }
}

// Types de motifs
$motifs_types=array(array("id"=>0,"valeur"=>"N1 cliquable"),array("id"=>1,"valeur"=>"N1 non-cliquable"),array("id"=>2,"valeur"=>"N2"));
?>

<!--	Modification de la liste des motifs (Dialog Box) -->  
<div id="add-motif-form" title="Liste des motifs d'absences" class='noprint' style='display:none;'>
  <p class="validateTips">Ajoutez, supprimez et modifiez l'ordre des motifs dans le menu déroulant.</p>
  <form>
  <p><input type='text' id='add-motif-text' style='width:300px;'/>
    <input type='button' id='add-motif-button2' class='ui-button' value='Ajouter' style='margin-left:15px;'/></p>
  <fieldset>
    <ul id="motifs-sortable">
<?php
    if (is_array($motifs)) {
        foreach ($motifs as $elem) {
            $class=$elem['type']==2?"padding20":"bold";
            echo "<li class='ui-state-default' id='li_{$elem['id']}'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>\n";
            echo "<font class='$class' id='valeur_{$elem['id']}'>{$elem['valeur']}</font>\n";
            echo "<select id='type_{$elem['id']}' style='position:absolute;left:330px;'>\n";
            echo "<option value='0'>&nbsp;</option>\n";
            foreach ($motifs_types as $elem2) {
                $selected=$elem2['id']==$elem['type']?"selected='selected'":null;
                echo "<option value='{$elem2['id']}' $selected>{$elem2['valeur']}</option>\n";
            }
            echo "</select>\n";

            if (!in_array($elem['valeur'], $motifs_utilises)) {
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