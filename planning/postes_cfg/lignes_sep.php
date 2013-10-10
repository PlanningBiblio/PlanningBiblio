<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.5.9
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : planning/postes_cfg/lignes_sep.php											*
* Création : 13 septembre 2012													*
* Dernière modification : 17 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Permet d'ajouter, modifier et supprimer les lignes de séparation. Affichage des formulaires d'ajout et de modification	*
* Validation de l'ajout, de la modification et de la suppression								*
*																*
* Page appelée par le fichier index.php, accessible à partir de la page planning/postes_cfg/index.php				*
*********************************************************************************************************************************/

require_once "class.tableaux.php";

$action=isset($_REQUEST['action'])?$_REQUEST['action']:null;

switch($action){		//	Operations de mise a jour
  case "modif2" :
    $nom=trim($_GET['nom']);
    $db=new db();
    $db->update2("lignes",array("nom"=>$nom),array("id"=>$_GET['id']));
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=planning/postes_cfg/index.php'</script>\n";
    break;

  case "ajout2" :
    $nom=trim($_GET['nom']);
    $db=new db();
    $db->insert2("lignes",array("nom"=>$nom));
    unset($_REQUEST['action']);
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=planning/postes_cfg/index.php'</script>\n";
    break;

  case "suppr" :
    $db=new db();
    $db->delete("lignes","id='{$_GET['id']}'");
    break;
}


switch($action){		//	Affichages
  case "modif" :
    $db=new db();
    $db->select("lignes","nom","id='{$_GET['id']}'");
    
    echo <<<EOD
    <h3>Lignes de séparation</h3>
    <b>Modification du nom</b><br/><br/>
    <form action='index.php' method='get' >
    <input type='hidden' name='page' value='planning/postes_cfg/lignes_sep.php' />
    <input type='hidden' name='action' value='modif2' />
    <input type='hidden' name='cfg-type' value='lignes_sep' />
    <input type='hidden' name='id' value='{$_GET['id']}' />
    <table>
    <tr><td style='width:100px;'>Nom</td>
      <td><input type='text' name='nom' value='{$db->result[0]['nom']}' style='width:300px;' /></td></tr>
    <tr><td colspan='2' style=text-align:center;padding-top:20px;'>
      <input type='button' value='Annuler' onclick='history.back();' />
      <input type='submit' value='Valider' /></td></tr>
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
    <table>
    <tr><td style='width:100px;'>Nom</td>
      <td><input type='text' name='nom' style='width:300px;'/></td></tr>
    <tr><td colspan='2' style=text-align:center;padding-top:20px;'>
      <input type='button' value='Annuler' onclick='history.back();' />
      <input type='submit' value='Valider' /></td></tr>
    </table>
    </form>
EOD;
    break;
}
?>