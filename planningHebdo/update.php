<?php
/*
Planning Biblio, Plugin planningHebdo Version 1.3.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : plugins/planningHebdo/update.php
Création : 17 septembre 2013
Dernière modification : 4 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier permettant la mise à jour du plugin planningHebdo. Ajoute ou modifie des informations dans la base de données
*/

session_start();

// Sécurité
if($_SESSION['login_id']!=1){
  echo "<br/><br/><h3>Vous devez vous connecter au planning<br/>avec le login \"admin\" pour pouvoir installer ce plugin.</h3>\n";
  echo "<a href='../../index.php'>Retour au planning</a>\n";
  exit;
}

$version="1.3.9";
include_once "../../include/config.php";

$sql=array();

// Mise à jour du 17 septembre
// Droits d'accès à la page de suppression
// $sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`) VALUES ('Planning Hebdo - suppression','24','Validation des plannings de présences','plugins/planningHebdo/supprime.php');";

// Mise à jour du 4 octobre
// $sql[]="ALTER TABLE `{$dbprefix}planningHebdo` ADD `remplace` INT(11) NOT NULL DEFAULT '0';";

// Mise à jour du 4 février
// $sql[]="INSERT INTO `{$dbprefix}planningHebdoConfig` (`nom`,`valeur`) VALUES ('notifications','droit');";

// Mise à jour 1.3.9
$sql[]="DELETE FROM `{$dbprefix}acces`  WHERE `page`='plugins/planningHebdo/supprime.php';";


?>
<!-- Entête HTML -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Plugin Planning Hebdo - Mise à jour</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>

<?php
// Execution des requêtes
foreach($sql as $elem){
  $db=new db();
  $db->query($elem);
  if(!$db->error)
    echo "$elem : <font style='color:green;'>OK</font><br/>\n";
  else
    echo "$elem : <font style='color:red;'>Erreur</font><br/>\n";
}

echo "<br/><br/><a href='../../index.php'>Retour au planning</a>\n";
?>

</body>
</html>
