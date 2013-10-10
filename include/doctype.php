<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.5.9
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : include/doctype.php													*
* Création : mai 2011														*
* Dernière modification : 17 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Affiche le doctype HTML transitional												*
* Page appelée par index.php (sous conditions), authentification.php et admin/index.php						*
*********************************************************************************************************************************/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">