<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.7.1
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2014 - Jérôme Combes											*
*																*
* Fichier : planning/postes_cfg/supp_lignes.php											*
* Création : 10 septembre 2012													*
* Dernière modification : 13 décembre 2012											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Supprime une ligne de séparation d'un tableau. Appelée par la fonction JavaScript "supprime_ligne" lors du click sur une 	*
* icône de suppression du tableau "Lignes de séparation".									*
*																*
* Page appelée en arrière plan par la fonction JavaScript "supprime_ligne"							*
*********************************************************************************************************************************/

include "../../include/config.php";
$db=new db();
$db->delete("lignes","id='{$_GET['id']}'");
?>