/*
Planning Biblio, Version 1.8.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/modeles/js/modeles.js
Création : 24 juin 2014
Dernière modification : 24 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions JavaScript utiles à la gestion des modèles
*/

$(document).ready(function() {
  $("#tableModeles").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aaSorting" : [[1,"asc"],],
    "aoColumns" : [{"bSortable":false},{"bSortable":true},],
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : 25,
    "oLanguage" : {"sUrl" : "vendor/dataTables.french.lang"}
  });
});


