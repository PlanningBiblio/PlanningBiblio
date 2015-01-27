/*
Planning Biblio, Version 1.9.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/js/voir.js
Création : 5 novembre 2014
Dernière modification : 27 janvier 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions JavaScript utiles à l'affichage des absences (voir.php)
*/

$(document).ready(function() {
  // DataTable : configuration des colonnes
  var aoColumns=[{"bSortable":false},{"sType": "date-fr"},{"sType": "date-fr-fin"}];
  if($("#thNom").length){
   aoColumns.push({"bSortable":true});
  }
  if($("#thValidation").length){
   aoColumns.push({"bSortable":true});
  }
  // Motif, Commentaires, Demande
  aoColumns.push({"bSortable":true});
  aoColumns.push({"bSortable":true});
  aoColumns.push({"sType": "date-fr"});
  
  if($("#thPiecesJustif").length){
   aoColumns.push({"bSortable":false});
  }

  $("#tableAbsences").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aaSorting" : [[1,"asc"],[2,"asc"]],
    "aoColumns" : aoColumns,
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : 25,
    "oLanguage" : {"sUrl" : "vendor/dataTables.french.lang"}
  });

  $(document).tooltip();
});


$(function(){
  $(".absences-pj input[type=checkbox]").click(function(){
    var tmp=$(this).attr("id").split("-");
    var pj=tmp[0];
    var id=tmp[1];
    var checked=$(this).prop("checked")?1:0;

    $.ajax({
      url: "absences/ajax.piecesJustif.php",
      data: "id="+id+"&pj="+pj+"&checked="+checked,
      success: function(){
	information("Modification enregistr&eacute;e","highlight");
      },
      error: function(){
	information("Attention, la modification n&apos;a pas pu &ecirc;tre enregistr&eacute;e","error");
      }
    });
  });
  
  
});