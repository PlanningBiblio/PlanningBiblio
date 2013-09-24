<?php
/*
Planning Biblio, Version 1.5.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : admin/index.php
Création : mai 2011
Dernière modification : 24 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche les liens vers les différentes pages de configurations (activités, agents, postes, ...)

Page appelée par la page index.php
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}


echo "<h3>Administration</h3>\n";
echo "<ul>\n";
if(in_array(23,$droits))
  echo "<li><a href='index.php?page=infos/index.php'>Informations</a></li>\n";
if(in_array(5,$droits))
  echo "<li><a href='index.php?page=activites/index.php'>Les activités</a></li>\n";
if(in_array(4,$droits))
  echo "<li><a href='index.php?page=personnel/index.php'>Les agents</a></li>\n";
if(in_array(5,$droits))
  echo "<li><a href='index.php?page=postes/index.php'>Les postes</a></li>\n";
if(in_array(12,$droits))
  echo "<li><a href='index.php?page=planning/modeles/index.php'>Les modèles</a></li>\n";
if(in_array(22,$droits))
  echo "<li><a href='index.php?page=planning/postes_cfg/index.php'>Les tableaux</a></li>\n";
if(in_array(24,$droits))
  echo "<li><a href='index.php?page=admin/feries.php'>Jours feri&eacute;s</a></li>\n";
if(in_array(24,$droits))
  echo "<li><a href='index.php?page=plugins/planningHebdo/index.php'>Plannings de présence</a></li>\n";
if(in_array(20,$droits))
  echo "<li><a href='index.php?page=admin/config.php'>Configuration</a></li>\n";
echo "</ul>\n";
?>