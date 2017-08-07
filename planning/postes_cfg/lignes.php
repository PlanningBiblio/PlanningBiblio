<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/postes_cfg/lignes.php
Création : mai 2011
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de modifier les lignes d'un tableau. Affichage d'un tableau avec les horaires en colonnes, les postes dans des menus 
déroulant en lignes. Permet également de griser des cellules avec les cases à cocher "G"
Affichage et validation

Page incluse dans le fichier "planning/postes_cfg/modif.php"
*/

require_once "class.tableaux.php";
require_once "postes/class.postes.php";

// Liste des postes
$p=new postes();
if($config['Multisites-nombre']>1){
  $p->sites=$site;
}
$p->fetch("nom");
$postes=$p->elements;

// Liste des lignes de séparation
$db=new db();
$db->select("lignes",null,null,"ORDER BY nom");
$lignes_sep=$db->result;


// Le tableau (contenant les sous-tableaux)
$t=new tableau();
$t->id=$tableauNumero;
$t->get();
$tabs=$t->elements;

// Affichage du tableau :

echo "<div style='min-height:350px;'>\n";
echo "<form name='form4' action='index.php' method='post'>\n";
echo "<input type='hidden' name='page' value='planning/postes_cfg/modif.php' />\n";
echo "<input type='hidden' name='cfg-type' value='lignes' />\n";
echo "<input type='hidden' name='numero' value='$tableauNumero' />\n";

echo "<h3>Configuration des lignes</h3>\n";

if($tableauNumero){
  echo "<table style='min-width:1250px; width:100%;' cellspacing='0' cellpadding='0' border='1' >\n";
  foreach($tabs as $tab){
    // Lignes Titre et Horaires
    echo "<tr class='tr_horaires' style='text-align:center;'>\n";
    echo "<td style='white-space:nowrap;text-align:left;'>\n";
    echo "Titre <input type='text' name='select_{$tab['nom']}Titre_0' class='tr_horaires select_titre' style='text-align:center;white-space:nowrap;' value='{$tab['titre']}'/>&nbsp;\n";
    echo "Classe<sup>*</sup> <input type='text' name='select_{$tab['nom']}Classe_0' class='tr_horaires select_titre' style='text-align:center;width:120px;' value='{$tab['classe']}'/>\n";
    echo "<a href='javascript:ajout(\"select_{$tab["nom"]}_\",-1);'><span class='pl-icon pl-icon-add' title='Ajouter'></span></a></td>\n";
    $colspan=0;
    foreach($tab['horaires'] as $horaire){
      echo "<td colspan='".nb30($horaire['debut'],$horaire['fin'])."'>".heure3($horaire['debut'])."-".heure3($horaire['fin'])."</td>";
      $colspan+=nb30($horaire['debut'],$horaire['fin']);
    }
    echo "</tr>\n";
    
    // Lignes Postes et Lignes de séparation
    for($i=0;$i<100;$i++){
      $display=array_key_exists($i,$tab['lignes'])?null:"style='display:none;'";
      echo "<tr id='tr_select_{$tab['nom']}_$i' $display>\n";
      // Première colonne
      echo "<td id='td_select_{$tab['nom']}_{$i}_0' style='white-space:nowrap;'>\n";
      // Sélection des postes et des lignes de séparation
      echo "<select name='select_{$tab['nom']}_$i' style='width:200px;color:black;font-weight:normal;' class='tab_select'>\n";
      echo "<option value=''>&nbsp;</option>\n";
      // Les postes
      if(is_array($postes)){
	foreach($postes as $poste){
	  $class=$poste['obligatoire']=="Obligatoire"?"td_obligatoire":"td_renfort";
	  $selected=null;
	  if(array_key_exists($i,$tab['lignes'])){
	    $selected=($tab['lignes'][$i] and $tab['lignes'][$i]['type']=="poste" and $poste['id']==$tab['lignes'][$i]['poste'])?"selected='selected'":null;
	  }
	  echo "<option value='{$poste['id']}' $selected class='$class'>{$poste['nom']} ({$poste['etage']})</option>\n";
	}
      }
      // Les lignes de séparation
      foreach($lignes_sep as $ligne_sep){
	$selected=null;
	if(array_key_exists($i,$tab['lignes'])){
	  $selected=($tab['lignes'][$i]['type']=="ligne" and $ligne_sep['id']==$tab['lignes'][$i]['poste'])?"selected='selected'":null;
	}
	echo "<option value='{$ligne_sep['id']}Ligne' class='tr_horaires' $selected style='font-weight:normal;'>{$ligne_sep['nom']}</option>\n";
      }
      echo "</select>&nbsp;&nbsp;\n";
      // Boutons ajout et suppression
      echo "<a href='javascript:ajout(\"select_{$tab["nom"]}_\",$i);'><span class='pl-icon pl-icon-add' title='Ajouter'></span></a>\n";
      echo "<a href='javascript:supprime_tab(\"{$tab["nom"]}_\",$i);'><span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>\n";
      echo "</td>\n";

      // Cellules (grises ou non)
      $j=1;
      foreach($tab['horaires'] as $horaire){
	$class=null;
	$checked=null;
	if(in_array("{$i}_{$j}",$tab['cellules_grises'])){
	  $class="class='cellule_grise'";
	  $checked="checked='checked'";
	}
	echo "<td id='td_select_{$tab['nom']}_{$i}_$j' $class colspan='".nb30($horaire['debut'],$horaire['fin'])."' style='text-align:center;'>\n";
	echo "<input type='checkbox' name='checkbox_{$tab['nom']}_{$i}_$j' $checked onclick='couleur2(this,\"td_select_{$tab['nom']}_{$i}_$j\");'/> G\n";
	echo "</td>\n";
	$j++;
      }
      echo "<td id='td_select_{$tab['nom']}_$i' colspan='$colspan' style='display:none;'>\n";
      echo "</tr>\n"; 
    }
    echo "<tr class='tr_espace'><td></td></tr>\n";
  }
  echo "</table>\n";
}
echo "</form>\n";
echo "</div>\n";
?>

<div class='highlight' style='margin-top:40px;'>
<p style='margin-left:30px;'>
<sup>* Classe CSS appliqu&eacute;e sur le tableau. Permet d'en personnaliser l&apos;affichage.</sup><br/>
</p>
</div>

<script type='text/JavaScript'>
// Applique la même class que l'option selectionnée au select et au td pour chaque select poste lors du chargement
$("document").ready(function(){
  $(".tab_select").each(function(){
    var myClass=$(this).find(":selected").attr("class");
    $(this).removeClass();
    $(this).addClass("tab_select");
    $(this).addClass(myClass);
    $(this).closest("td").removeClass();
    $(this).closest("td").addClass(myClass);
  });
});

// Change la class du select et du td lorsque l'on change d'option dans les listes des postes
$(".tab_select").change(function(){
  var myClass=$(this).find(":selected").attr("class");
  $(this).removeClass();
  $(this).addClass("tab_select");
  $(this).addClass(myClass);
  $(this).closest("td").removeClass();
  $(this).closest("td").addClass(myClass);
});

// Validation AJAX pour éviter le problème de limitation à 1000 éléments en post
// N'envoie que les éléments sélectionnés et visibles
function configLignes(){
  tab=new Array();
  // Récupération des titres
  $(".select_titre").each(function(){
    tab.push($(this).attr("name")+"="+$(this).val());
  });

  // Récupération des postes
  $(".tab_select:visible").each(function(){
    tab.push($(this).attr("name")+"="+$(this).val());
  });

  // Récupération des cellules grises
  $("input[type=checkbox]:checked:visible").each(function(){
    tab.push($(this).attr("name")+"="+$(this).val());
  });

  // La variable data contient tous les éléments à enregistrer
  var data="id="+$("#id").val();
  data+="&CSRFToken="+$('#CSRFSession').val();
  for(elem in tab){
    data+="&"+tab[elem];
  }

  // Enregistrement des données en ajax (fichier ajax.lignes.php)
  $.ajax({
    url: "planning/postes_cfg/ajax.lignes.php",
    type: "post",
    data: data,
    success: function(){
      CJInfo("Le tableau a été enregistré","highlight");
      return true;
    },
    error: function(){
      CJInfo("Une erreur est survenue lors de l'enregistrement du tableau.","error");
      return false;
    }
  });
}
</script>