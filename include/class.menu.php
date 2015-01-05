<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/class.menu.inc
Création : 22 juillet 2013
Dernière modification : 22 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions permettant de construire le menu principal.

Ce fichier est appelé par le fichier include/menu.php
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}


class menu{
  public $elements=array();

  public function menu(){
  }

  public function fetch(){
    $menu=array();
    $db=new db();
    $db->select("menu",null,null,"ORDER BY `niveau1`,`niveau2`");
    foreach($db->result as $elem){
      $menu[$elem['niveau1']][$elem['niveau2']]['titre']=$elem['titre'];
      $menu[$elem['niveau1']][$elem['niveau2']]['url']=$elem['url'];
    }

    if($GLOBALS['config']['Multisites-nombre']>1){
      for($i=0;$i<$GLOBALS['config']['Multisites-nombre'];$i++){
	$j=$i+1;
	$menu[30][$j]['titre']=$GLOBALS['config']["Multisites-site".$j];
	$menu[30][$j]['url']="planning/poste/index.php&amp;site=$j";
      }
    }

    $this->elements=$menu;
  }
}
?>