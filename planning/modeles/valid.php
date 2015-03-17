<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/modeles/valid.php
Création : mai 2011
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Valide la modification d'un modèles de planning 
*/

require_once "class.modeles.php";

$nom=trim($_GET['nom']);
$origine=htmlentities($_GET['nom_origine'],ENT_QUOTES|ENT_IGNORE,"UTF-8");

$db=new db();
$db->update2("pl_poste_modeles",array("nom"=>$nom),array("nom"=>$origine));
$db=new db();
$db->update2("pl_poste_modeles_tab",array("nom"=>$nom),array("nom"=>$origine));
echo "<script type='text/JavaScript'>document.location.href='index.php?page=planning/modeles/index.php';</script>\n";
?>