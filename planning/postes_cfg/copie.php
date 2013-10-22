<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.6
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : planning/postes_cfg/copie.php											*
* Création : mai 2011														*
* Dernière modification : 16 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Permet de copier un tableau existant. Affiche un formulaire demandant le nom du nouveau tableau. Insère les informations	*
* dans la base de données après validation											*
*																*
* Page appelée par la fonction JavaScript "popup", qui ouvre cette page dans un cadre flottant, lors du click sur l'icône copie	*
* de la page "planning/postes_cfg/index.php"											*
*********************************************************************************************************************************/

require_once "class.tableaux.php";

$numero1=$_GET['numero'];
$retour=isset($_GET['retour'])?$_GET['retour']:"modif.php";

if(isset($_GET['confirm'])){
	  //		Copie des horaires
  $values=array();
  $db->query("SELECT `debut`,`fin`,`tableau` FROM `{$dbprefix}pl_poste_horaires` WHERE `numero`='$numero1' ORDER BY `tableau`,`debut`,`fin`;");
  if($db->result){
    echo "<br/><br/><b>Copie en cours. Veuillez patienter ...</b>\n";
    $db2=new db();
    $db2->query("SELECT MAX(`tableau`) AS `tableau` FROM `{$dbprefix}pl_poste_tab`;");
    $numero2=$db2->result[0]['tableau']+1;
    foreach($db->result as $elem){
      if(array_key_exists('tableau',$elem)){
	$values[]="('{$elem['debut']}','{$elem['fin']}','{$elem['tableau']}','$numero2')";
      }
    }
    $req="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`,`fin`,`tableau`,`numero`) VALUES ";
    $req.=join($values,",").";";
    $db2=new db();
    $db2->query($req);
    $nom=htmlentities($_GET['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8");
    $db2=new db();		//	Enregistrement du nouveau tableau
    $db2->query("INSERT INTO `{$dbprefix}pl_poste_tab` (`nom`,`tableau`) VALUES ('$nom','$numero2');");
  }
  else{		// par sécurité, si pas d'horaires à  copier, on stop le script pour éviter d'avoir une incohérence dans les numéros de tableaux
    echo "<script type='text/javaScript'>parent.location.href='index.php?page=planning/postes_cfg/modif.php&cfg-type=horaires&numero=$numero';</script>\n";
    exit;
  }

	  //		Copie des lignes
  $values=array();
  $db->query("SELECT `tableau`,`ligne`,`poste`,`type` FROM `{$dbprefix}pl_poste_lignes` WHERE `numero`='$numero1' ORDER BY `tableau`,`ligne`;");
  if($db->result){
    foreach($db->result as $elem){
      if(array_key_exists('ligne',$elem)){
	$values[]="('{$elem['tableau']}','{$elem['ligne']}','{$elem['poste']}','{$elem['type']}','$numero2')";
      }
    }
    $req="INSERT INTO `{$dbprefix}pl_poste_lignes` (`tableau`,`ligne`,`poste`,`type`,`numero`) VALUES ";
    $req.=join($values,",").";";
    $db2=new db();
    $db2->query($req);
  }

	  //		Copie des cellules grises
  $values=array();
  $db->query("SELECT `ligne`,`colonne`,`tableau` FROM `{$dbprefix}pl_poste_cellules` WHERE `numero`='$numero1' ORDER BY `tableau`,`ligne`,`colonne`;");
  if($db->result){
    foreach($db->result as $elem){
      if(array_key_exists('ligne',$elem) and array_key_exists('colonne',$elem)){
	$values[]="('{$elem['ligne']}','{$elem['colonne']}','{$elem['tableau']}','$numero2')";
      }
    }
    $req="INSERT INTO `{$dbprefix}pl_poste_cellules` (`ligne`,`colonne`,`tableau`,`numero`) VALUES ";
    $req.=join($values,",").";";
    $db2=new db();
    $db2->query($req);
  }

	  //		Retour à  la page principale
  echo "<script type='text/javaScript'>parent.location.href='index.php?page=planning/postes_cfg/$retour&cfg-type=horaires&numero=$numero2';</script>\n";
}
else{
  echo "<h3>Copie du tableau</h3>\n";
  echo "<form name='form' action='index.php' method='get'>\n";
  echo "<input type='hidden' name='page' value='planning/postes_cfg/copie.php' />\n";
  echo "<input type='hidden' name='menu' value='off' />\n";
  echo "<input type='hidden' name='confirm' value='on' />\n";
  echo "<input type='hidden' name='numero' value='$numero1' />\n";
  echo "<input type='hidden' name='retour' value='$retour' />\n";
  echo "Nom du nouveau tableau<br/>\n";
  echo "<input type='text' name='nom' />\n";
  echo "<br/><br/><br/>\n";
  echo "<input type='button' value='Annuler' onclick='popup_closed();'/>\n";
  echo "&nbsp;&nbsp;<input type='submit' value='Copier' />\n";
  echo "</form>\n";
}
?>