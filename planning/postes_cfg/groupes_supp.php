<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.7.2
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.md et LICENSE											*
* Copyright (C) 2011-2014 - Jérôme Combes											*
*																*
* Fichier : planning/postes_cfg/groupes_supp.php										*
* Création : 18 septembre 2012													*
* Dernière modification : 17 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Supprime un groupe de tableaux lors du click sur l'icône suppression de la page "planning/postes_cfg/index.php"		*
*********************************************************************************************************************************/

session_start();
require_once "../../include/config.php";
require_once "../../include/db.php";
require_once "../../include/function.php";
require_once "class.tableaux.php";

if(in_array(22,$_SESSION['droits'])){
  $t=new tableau();
  $t->deleteGroup($_GET['id']);
}

header("Location: ../../index.php?page=planning/postes_cfg/index.php");
?>