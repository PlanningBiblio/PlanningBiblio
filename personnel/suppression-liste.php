<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : personnel/suppression-liste.php
Création : mai 2011
Dernière modification : 2 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime les agents sélectionnés à partir de la liste des agents (fichier personnel/index.php).
Les agents ne sont pas supprimés définitivement, ils sont marqués comme supprimés dans la table personnel (champ supprime=1)

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

$keys=array_keys($_POST);
for($i=0;$i<count($keys);$i++){
  if(substr($keys[$i],0,3)=="chk")
    $liste[]=$_POST[$keys[$i]];
}
$liste=join($liste,",");
if($_SESSION['perso_actif']=="Supprimé"){
  $p=new personnel();
  $p->delete($liste);
}
else{
  $db=new db();
  $liste=$db->escapeString($liste);
  $req="UPDATE `{$dbprefix}personnel` SET `supprime`='1', `actif`='Supprim&eacute;' WHERE `id` IN ($liste);";
  $db->query($req);
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php';</script>\n";
?>