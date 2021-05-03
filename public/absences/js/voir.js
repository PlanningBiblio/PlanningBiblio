/**
Planning Biblio, Version 2.7.06
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : absences/js/voir.js
Création : 5 novembre 2014
Dernière modification : 30 novembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à l'affichage des absences (voir.php)
*/

$(function(){
  $(".absences-pj input[type=checkbox]").click(function(){
    var tmp=$(this).attr("id").split("-");
    var pj=tmp[0];
    var id=tmp[1];
    var checked=$(this).prop("checked")?1:0;
    var CSRFToken=$('#CSRFSession').val();

    $.ajax({
      url: "absences/ajax.piecesJustif.php",
      data: "id="+id+"&pj="+pj+"&checked="+checked+"&CSRFToken="+CSRFToken,
      success: function(){
	information("Modification enregistr&eacute;e","highlight");
      },
      error: function(){
	information("Attention, la modification n&apos;a pas pu &ecirc;tre enregistr&eacute;e","error");
      }
    });
  });

});

function absences_reinit(){
  // TODO : réinitialiser le filtre du tableau
//   $('#tableAbsencesVoir_filter > label > input[type="search"]').val(null);
  var baseURL = $("#baseURL").val();
  location.href = baseURL + "/absence?reset=1";
}