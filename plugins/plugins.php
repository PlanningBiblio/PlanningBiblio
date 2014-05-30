<?php
/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : plugins/plugins.php
Création : 26 juin 2013
Dernière modification : 26 mai 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Liste des plugins installés
Inclus dans le fichier index.php
*/

global $plugins;
$plugins=array();
$db=new db();
$db->select("plugins");
if($db->result){
  foreach($db->result as $elem){
    $plugins[]=$elem['nom'];
  }
}
?>