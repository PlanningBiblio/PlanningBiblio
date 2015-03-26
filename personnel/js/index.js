/*
Planning Biblio, Version 1.9.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : personnel/js/index.js
Création : 22 septembre 2014
Dernière modification : 26 mars 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions JavaScript utiles à l'affichage du tableau des agents (index.php)
*/

$(function() {
  $("#checkAll").click(function(){
    $(".checkAgent:visible").click();
  });
});