<?php
/**
Planning Biblio, Version 2.5.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/modeles/valid.php
Création : mai 2011
Dernière modification : 9 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Valide la modification d'un modèles de planning 
*/

require_once "class.modeles.php";

$nom = filter_input(INPUT_GET, 'nom', FILTER_SANITIZE_STRING);
$origine = filter_input(INPUT_GET, 'nom_origine', FILTER_SANITIZE_STRING);
$CSRFToken = filter_input(INPUT_GET, 'CSRFToken', FILTER_SANITIZE_STRING);

$nom=htmlentities(trim($nom),ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
$origine=htmlentities($origine,ENT_QUOTES|ENT_IGNORE,"UTF-8",false);

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->update2("pl_poste_modeles",array("nom"=>$nom),array("nom"=>$origine));

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->update2("pl_poste_modeles_tab",array("nom"=>$nom),array("nom"=>$origine));
echo "<script type='text/JavaScript'>document.location.href='index.php?page=planning/modeles/index.php';</script>\n";
?>