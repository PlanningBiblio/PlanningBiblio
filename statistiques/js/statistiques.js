/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : statistiques/js/statistiques.js
Création : 15 mai 2014
Dernière modification : 16 mai 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fonctions JS necessaires à l'affichage de la page Statistiques
Initilisation des dataTables

Page appelée par le fichier include/header.php
*/

// Absences
$(document).ready(function(){
  var absencesTable=$("#dataTableStatAbsences").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : -1,
    "oLanguage" : {"sUrl" : "js/dataTables/french.txt"},
    "sScrollX": "100%",
  });
  if($("#dataTableStatAbsences").length){
    new FixedColumns( absencesTable, {
      "iLeftColumns" : 2
    });
  }
});

// Temps
$(document).ready(function(){
  var tempsTable=$("#table_temps").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : -1,
    "oLanguage" : {"sUrl" : "js/dataTables/french.txt"},
    "sScrollX": "100%",
  });
  new FixedColumns( tempsTable, {
    "iLeftColumns" : 2
  });
});