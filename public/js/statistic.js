/**
Planning Biblio

@file public/js/statitics.js
@author Jérôme Combes <jerome@planningbiblio.fr>

@desc Javascript functions used to display statistics
*/

function statisticsGetDefaultHours() {
  var hours = $('#statisticsDefaultHours').val();
  $('#statisticsHours').val(hours);
}

function verif_select(nom){
  if(document.form.elements[nom+'[]'].value=="Tous"){
    for(i=document.form.elements[nom+'[]'].length-1;i>0;i--){
      document.form.elements[nom+'[]'][i].selected=true;
    }
    document.form.elements[nom+'[]'][0].selected=false;
  }
}
