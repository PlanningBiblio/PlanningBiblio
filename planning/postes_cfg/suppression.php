<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/suppression.php
Création : 10 septembre 2012
Dernière modification : 7 avril 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime complétement un tableau. Supprime les horaires, cellules grisées, lignes et l'identifiant du tableau (table pl_poste_tab).

Page appelée en arrière plan par la fonction JavaScript "popup" en cas de click sur l'icône suppression
*/

require_once "class.tableaux.php";

// Initialisation des variables
$confirm=filter_input(INPUT_GET,"confirm",FILTER_CALLBACK,array("options"=>"sanitize_on"));
$numero=filter_input(INPUT_GET,"numero",FILTER_SANITIZE_NUMBER_INT);

echo "<div style='text-align:center'>\n";
if($confirm){
  $db=new db();
  $db->delete2("pl_poste_horaires", array("numero"=>$numero));
  $db=new db();
  $db->delete2("pl_poste_cellules", array("numero"=>$numero));
  $db=new db();
  $db->delete2("pl_poste_lignes", array("numero"=>$numero));
  $db=new db();
  $db->delete2("pl_poste_tab", array("tableau"=>$numero));
  
  echo "<br/>Le tableau a été supprimé.<br/><br/>\n";
  echo "<a href='javascript:parent.location.href=\"index.php?page=planning/postes_cfg/index.php&cfg-type=horaires\";'>Fermer</a>&nbsp;&nbsp;&nbsp;\n";
}
else{
  echo "<br/>Etes vous sûr(e) de vouloir supprimer ce tableau ?<br/><br/>\n";
  echo "<a href='javascript:popup_closed();'>Non</a>&nbsp;&nbsp;&nbsp;\n";
  echo "<a href='index.php?page=planning/postes_cfg/suppression.php&amp;menu=off&amp;confirm=confirm&amp;numero=$numero'>Oui</a>&nbsp;&nbsp;&nbsp;\n";
}
echo "</div>\n";
?>