/**
Planning Biblio

@file public/js/plb/statitics.js
@author Jérôme Combes <jerome@planningbiblio.fr>

@desc Javascript functions used to display statistics
*/

$(function(){
  $('#statistiques_heures_defaut_lien').click(function(){
    $('#statistiques_heures_defaut_hidden').val('1');
    $('#form').submit();
  });
});
