<?php
/*
Planning Biblio, Version 1.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : planning/poste/majdb.php
Création : mai 2011
Dernière modification : 2 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet la mise à jour en arrière plan de la base de données (table pl_poste) lors de l'utilisation du menu contextuel de la 
page planning/poste/index.php pour placer les agents

Cette page est appelée par la function JavaScript "bataille_navale" utilisé par le fichier planning/poste/menudiv.php
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../../index.php");
}

//	Initialisation des variables
$site=$_SESSION['oups']['site'];
$ajouter=$_GET['ajouter'];
$perso_id=$_GET['perso_id'];
$date=$_GET['date'];
$debut=$_GET['debut'];
$fin=$_GET['fin'];
$absent=isset($_GET['absent'])?$_GET['absent']:"0";
$poste=$_GET['poste'];
$barrer=$_GET['barrer'];
	
if($perso_id==0){
  if($barrer)
    $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='1', `chgt_login`='{$_SESSION['login_id']}', `chgt_time`=SYSDATE() WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site';";
  else
    $req="DELETE FROM `{$dbprefix}pl_poste` WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site';";
}
else{
  if(!$barrer and !$ajouter){		// on remplace
    $db=new db();
    $db->query("DELETE FROM `{$dbprefix}pl_poste` WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site';");
    $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`debut`,`fin`,`poste`,`site`,`perso_id`,`chgt_login`,`chgt_time`) VALUES ('$date','$debut','$fin','$poste','$site','$perso_id','{$_SESSION['login_id']}',SYSDATE());";
  }
  elseif($barrer){			// on barre l'ancien et ajoute le nouveau
    $db=new db();
    $db->query("UPDATE `{$dbprefix}pl_poste` SET `absent`='1', `chgt_login`='{$_SESSION['login_id']}' WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site'");
    $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`debut`,`fin`,`poste`,`site`,`perso_id`,`chgt_login`,`chgt_time`) VALUES ('$date','$debut','$fin','$poste','$site','$perso_id','{$_SESSION['login_id']}',SYSDATE());";
  }
  elseif($ajouter){			// on ajoute
    $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`debut`,`fin`,`poste`,`site`,`perso_id`,`chgt_login`,`chgt_time`) 
	    VALUES ('$date','$debut','$fin','$poste','$site','$perso_id','{$_SESSION['login_id']}',SYSDATE());";
    }
}

$db=new db();
$db->query($req);
?>