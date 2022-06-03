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

  $("#absencesListForm").submit(function( event ) {
    var start = $("#debut").datepicker("getDate");
    var end = $("#fin").datepicker("getDate");
    if (start || end) {
       if (!start) {
         start = new Date();
       }
       if (!end) {
         end = new Date();
       }
       var number_of_days = (end - start) / (1000 * 60 * 60 * 24);
       if (number_of_days > 365) {
         alert('Veuillez sélectionner un intervalle inférieur à une année.');
         event.preventDefault();
       }
    }
  });

});

function absences_reinit(){
  // TODO : réinitialiser le filtre du tableau
//   $('#tableAbsencesVoir_filter > label > input[type="search"]').val(null);
  location.href = url('absence?reset=1');
}
