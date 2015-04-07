<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/lignes_sep.php
Création : 13 septembre 2012
Dernière modification : 7 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'ajouter, modifier et supprimer les lignes de séparation. Affichage des formulaires d'ajout et de modification
Validation de l'ajout, de la modification et de la suppression

Page appelée par le fichier index.php, accessible à partir de la page planning/postes_cfg/index.php
*/

require_once "class.tableaux.php";

// Initialisation des variables
$action=filter_input(INPUT_GET,"action",FILTER_SANITIZE_STRING);
$id=trim(filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT));
$nom=trim(filter_input(INPUT_GET,"nom",FILTER_SANITIZE_STRING));

switch($action){		//	Operations de mise a jour
  case "modif2" :
    $db=new db();
    $db->update2("lignes",array("nom"=>$nom),array("id"=>$id));
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=planning/postes_cfg/index.php'</script>\n";
    break;

  case "ajout2" :
    $db=new db();
    $db->insert2("lignes",array("nom"=>$nom));
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=planning/postes_cfg/index.php'</script>\n";
    break;

  case "suppr" :
    $db=new db();
    $db->delete2("lignes",array("id"=>$id));
    break;
}


switch($action){		//	Affichages
  case "modif" :
    $db=new db();
    $db->select2("lignes","nom",array("id"=>$id));
    
    echo <<<EOD
    <h3>Lignes de séparation</h3>
    <b>Modification du nom</b><br/><br/>
    <form action='index.php' method='get' >
    <input type='hidden' name='page' value='planning/postes_cfg/lignes_sep.php' />
    <input type='hidden' name='action' value='modif2' />
    <input type='hidden' name='cfg-type' value='lignes_sep' />
    <input type='hidden' name='id' value='$id' />
    <table class='tableauFiches'>
    <tr><td class='intitule'>Nom</td>
      <td><input type='text' name='nom' value='{$db->result[0]['nom']}' class='ui-widget-content ui-corner-all'/></td></tr>
    <tr><td colspan='2' style=text-align:center;padding-top:20px;'>
      <input type='button' value='Annuler' onclick='history.back();' class='ui-button'/>
      <input type='submit' value='Valider' class='ui-button'/></td></tr>
    </table>
    </form>
EOD;
    break;

  case "ajout" :
    echo <<<EOD
    <h3>Lignes de séparation</h3>
    <b>Ajout d'une nouvelle ligne</b><br/><br/>
    <form action='index.php' method='get' >
    <input type='hidden' name='page' value='planning/postes_cfg/lignes_sep.php' />
    <input type='hidden' name='action' value='ajout2' />
    <input type='hidden' name='cfg-type' value='lignes_sep' />
    <table class='tableauFiches'>
    <tr><td class='intitule'>Nom</td>
      <td><input type='text' name='nom' class='ui-widget-content ui-corner-all'/></td></tr>
    <tr><td colspan='2' style=text-align:center;padding-top:20px;'>
      <input type='button' value='Annuler' onclick='history.back();' class='ui-button' />
      <input type='submit' value='Valider' class='ui-button' /></td></tr>
    </table>
    </form>
EOD;
    break;
}
?>