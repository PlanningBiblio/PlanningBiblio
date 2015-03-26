/*
Planning Biblio, Version 1.9.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/js/voir.js
Création : 5 novembre 2014
Dernière modification : 26 mars 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions JavaScript utiles à l'affichage des absences (voir.php)
*/

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