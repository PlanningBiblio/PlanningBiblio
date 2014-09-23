/*
Planning Biblio, Version 1.8.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : personnel/js/index.js
Création : 22 septembre 2014
Dernière modification : 22 septembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions JavaScript utiles à l'affichage du tableau des agents (index.php)
*/

$(document).ready(function(){

  if($("#table_agents").length){
    // Définition des tris des colonnes de la table table_agents
    var aoCols=[{"bSortable":false},{"bSortable":true},{"bSortable":true},{"bSortable":true},{"bSortable":true},{"bSortable":true},
      {"bSortable":true},{"sType": "date-fr"},{"sType": "date-fr"},{"sType": "date-fr"}];
    // Si multisites, une colonne en plus
    if($("#table_agents thead th").length>10){
      aoCols.push({"bSortable":true});
    }

    // Mise en forme de la table table_agents (dataTable)
    $("#table_agents").dataTable({
      "bJQueryUI": true,
      "sPaginationType": "full_numbers",
      "bStateSave": true,
      "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
      "iDisplayLength" : 25,
      "aaSorting" : [[2,"asc"],[3,"asc"]],
      "oLanguage" : {"sUrl" : "js/dataTables/french.txt"},
      "aoColumns" : aoCols,
    });
  }
});


$(function() {
  $("#checkAll").click(function(){
    $(".checkAgent:visible").click();
  });
});