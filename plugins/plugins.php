<?php
/*
Planning Biblio, Version 1.5.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : plugins/plugins.php
Création : 26 juin 2013
Dernière modification : 3 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Liste des plugins installés
Inclus dans le fichier index.php
*/

$plugins=array();
$db=new db();
$db->select("plugins");
if($db->result){
  foreach($db->result as $elem){
    $plugins[]=$elem['nom'];
  }
}
?>