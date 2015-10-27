<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : statistiques/index.php
Création : mai 2011
Dernière modification : 17 septembre 2013
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Affiche les liens vers les différentes pages de statistiques.

Page appelée par le fichier index.php, accessible par le menu statistiques
*/

require_once "class.statistiques.php";

?>
<h3>Statistiques</h3>
<ul> 
<li><a href='index.php?page=statistiques/temps.php'>Feuille de temps</a></li>
<li><a href='index.php?page=statistiques/agents.php'>Par agent</a></li>
<li><a href='index.php?page=statistiques/service.php'>Par service</a></li>
<li><a href='index.php?page=statistiques/statut.php'>Par statut</a></li>
<li><a href='index.php?page=statistiques/postes.php'>Par poste</a></li>
<li><a href='index.php?page=statistiques/postes_synthese.php'>Par poste (Synth&egrave;se)</a></li>
<li><a href='index.php?page=statistiques/postes_renfort.php'>Postes de renfort</a></li>
</ul>