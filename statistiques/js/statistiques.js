/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : statistiques/js/statistiques.js
Création : 15 mai 2014
Dernière modification : 30 mai 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fonctions JS necessaires à l'affichage de la page Statistiques
Initilisation des dataTables

Page appelée par le fichier include/header.php
*/

$(document).ready(function(){

  // DataTable Absences
  if($("#dataTableStatAbsences").length){
    // Définition des propriétés des colonnes
    var nbCol=$("#dataTableStatAbsences tfoot th").length;
    var columns=[{"bSortable":true}];
    for(i=1;i<(nbCol/2);i++){
      columns.push({"bSortable":true});
      columns.push({"sType":"heure-fr"});
    }

    // DataTable
    var absencesTable=$("#dataTableStatAbsences").dataTable({
      "bJQueryUI": true,
      "sPaginationType": "full_numbers",
      "bStateSave": true,
      "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
      "iDisplayLength" : -1,
      "oLanguage" : {"sUrl" : "js/dataTables/french.txt"},
      "sScrollX": "100%",
      "aoColumns" : columns,
      "columnDefs": [{"visible": false, "targets": -1}]
    });
    
    // Colonnes fixes
    new FixedColumns( absencesTable, {
      "iLeftColumns" : 3
    });
  }


  // DataTable Temps
  if($("#table_temps").length){
    var tempsTable=$("#table_temps").dataTable({
      "bJQueryUI": true,
      "sPaginationType": "full_numbers",
      "bStateSave": true,
      "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
      "iDisplayLength" : -1,
      "oLanguage" : {"sUrl" : "js/dataTables/french.txt"},
      "sScrollX": "100%",
    });

    // Colonnes fixes
    new FixedColumns( tempsTable, {
      "iLeftColumns" : 2
    });
  }
});