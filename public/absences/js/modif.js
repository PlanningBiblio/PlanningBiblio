/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/absences/js/modif.js
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des agents (modif.php)
*/

$(function() {
  
  $(document).ready(function(){
    
    // Affichage de la liste des agents sélectionnés lors du chargement de la page modif.php
    if($('.perso_ul').length){
      affiche_perso_ul();
    }

    // Affichage des récurrences lors de la modification d'une absence
    if($('#rrule').val()){
      var text = recurrenceRRuleText2($('#rrule').val());
      $('#recurrence-summary').html(text);
      $('#recurrence-info').show();
      $('#recurrence-checkbox').attr('checked','checked');
    }
  });

  // Paramétrage de la boite de dialogue permettant la modification des motifs
  $("#add-motif-form").dialog({
    autoOpen: false,
    height: 600,
    width: 900,
    modal: true,
    resizable: false,
    draggable: false,
    buttons: {
      Enregistrer: function() {
	// Supprime les lignes cachées lors du clic sur la corbeille
	$("#motifs-sortable li:hidden").each(function(){
	  $(this).remove();
	});
	
	// Enregistre les éléments du formulaire dans un tableau
	tab=new Array();
	$("#motifs-sortable li").each(function(){
	  var id=$(this).attr("id").replace("li_","");
      var teleworking = $("#teleworking_" + id).prop('checked') ? 1 : 0;
      tab.push(new Array(
        $("#valeur_"+id).text(),
        $(this).index(),
        $("#type_"+id+" option:selected").val(),
        $("#notification-workflow_" + id).val(),
        teleworking,
      ));
    });

        // Transmet le tableau à la page de validation ajax
	$.ajax({
	  url: "/ajax/edit-absence-reasons",
	  type: "post",
          dataType: "json",
	  data: {data: tab, menu:"abs", option: "type", CSRFToken: $('#CSRFSession').val()},
	  success: function(){
            var current_val = $('#motif').val();
            $('#motif').empty();
            $('#motif').append("<option value=''>&nbsp;</option>");
            
            $("#motifs-sortable li").each(function(){
              var id=$(this).attr("id").replace("li_","");
              var val = $("#valeur_"+id).text();
              var type = $("#type_"+id+" option:selected").val();

              var disabled = (type == 1) ? "disabled='disabled'" : "";
              var padding = (type == 2) ? "&nbsp;&nbsp;&nbsp;" : "" ;
              var selected = (val == current_val) ? "selected='selected'" : "";

              var option = "<option value=\""+val+"\" "+selected+" "+disabled+">"+padding+""+val+"</option>";
              
              $('#motif').append(option);
            });
            $("#add-motif-form").dialog( "close" );
            $('#motif').effect("highlight",null,2000);
	  },
	  error: function(){
	    alert("Erreur lors de l'enregistrement des modifications");
	  }
	});
      },
      Annuler: function() {
	$(this).dialog( "close" );
      },
    },
    close: function() {
      $("#motifs-sortable li:hidden").each(function(){
	$(this).show();
      });
    }
  });

  // Affiche la boite de dialogue permettant la modification des motifs
  $("#add-motif-button")
    .click(function() {
      $("#add-motif-form").dialog( "open" );
      return false;
    });

  // Permet de rendre la liste des motifs triable
  $( "#motifs-sortable" ).sortable({
    placeholder: "ui-state-highlight",
  });

  // Permet d'ajouter de nouveaux motifs (clic sur le bouton ajouter)
  $("#add-motif-button2").click(function(){
    // Récupère les options du premier select "type" pour les réutiliser lors d'un ajout
    var select=$("select[id^=type_]");
    var select_id=select.attr("id");
    var options="";
    $("#"+select_id+" option").each(function(){
      var val=sanitize_string($(this).val());
      var text=sanitize_string($(this).text());
      options+="<option value='"+val+"'>"+text+"</option>";
    });

    var select_wf = $("select[id^=notification-workflow_]");
    var select_id_wf = select_wf.attr("id");
    var options_wf = "";
    $("#" + select_id_wf + " option").each(function() {
      var val=sanitize_string($(this).val());
      var text=sanitize_string($(this).text());
      options_wf+="<option value='"+val+"'>"+text+"</option>";
    });

    var text=sanitize_string($("#add-motif-text").val());
    if(!text){
      CJInfo("Donnée invalide","error");
      $("#add-motif-text").val();
      return;
    }
    
    var number = 1;
    while($('#li_'+number).length){
      number++;
    }

    $("#motifs-sortable").append("<li id='li_"+number+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+number+"'>"+text+"</font>"
      +"<select id='type_"+number+"' style='position:absolute;width:170px;left:330px;'>"
      +options
      +"</select>"
      +"<select id='notification-workflow_"+number+"' style='position:absolute;width:170px;left:510px;'>"
      +options_wf
      +"</select>"
      +"<input type='checkbox' id='teleworking_"+number+"' style='position:absolute; left:700px;' />"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:750px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-motif-text").val(null);
  });
  
  // Modifie la classe de la ligne lors du changement du select type (Boite de dialogue permettant de modifier la liste des motifs)
  $("select[id^=type]").change(function(){
    if($(this).val()==2){
      $(this).prev("font").removeClass("bold");
      $(this).prev("font").addClass("padding20");
    }else{
      $(this).prev("font").addClass("bold");
      $(this).prev("font").removeClass("padding20");
    }
  });

  // Affiche ou masque le champ motif_autre en fonction de la valeur du select motif
  $("select[name=motif]").change(function(){
    if($(this).val().toLowerCase()=="autre" || $(this).val().toLowerCase()=="other"){
      $("#tr_motif_autre").show();
    }else{
      $("#tr_motif_autre").hide();
      $("input[name=motif_autre]").val("");
    }
  });
  
  
  /**
   * Agents multiples
   * Permet d'ajouter plusieurs agents sur une même absence (réunion, formation)
   * Lors du changement du <select perso_ids>, ajout du nom des agents dans <ul perso_ul> et leurs id dans <input perso_ids[]>
   */
  $("#perso_ids").change(function(){
    // Variables
    var id=$(this).val();
    
    // Si sélection de "tous" dans le menu déroulant des agents, ajoute tous les id non-sélectionnés
    if(id == 'tous'){
      $("#perso_ids > option").each(function(){
        var id = $(this).val();
        if(id != 'tous' && id != 0 && $('#hidden'+id).length == 0){
          change_select_perso_ids(id);
        }
      });
      
    } else {
      // Ajoute l'agent choisi dans la liste
      change_select_perso_ids(id);
    }

    // Réinitialise le menu déroulant
    $("#perso_ids").val(0);
    
  });

  
  $("#absence-bouton-supprimer").click(function(){

    // Suppression d'une absence récurrente
    if($('#rrule').val() && !$('#recurrence-modif').val()){
      $("#recurrence-alert-suppression").dialog('open');
      return false;
    }
    
    if(confirm("Etes vous sûr de vouloir supprimer cette absence ?")){
      var CSRFToken = $('#CSRFSession').val();
      var id=$("#absence-bouton-supprimer").attr("data-id");
      delete_absence(CSRFToken, id, null);
    }
  });



  /* Récurence */

  // checkbox récurrence
  $('#recurrence-link').click(function(){
    if($("#recurrence-form").length){
      $("#recurrence-form").dialog( "open" );
    } else if($("#recurrence-form-tmp").length){
      $("#recurrence-form-tmp").dialog( "open" );
    }
  });

  $("#recurrence-checkbox").change(function() {
    if($("#recurrence-checkbox").prop('checked')){
      if($('#recurrence-hidden').val()){
        $('#recurrence-info').show();
      } else {
        $("#recurrence-form").dialog( "open" );
      }
    } else {
      $('#recurrence-info').hide();
    }
  });

  /* Si champ date de début modifiable */
  $('.recurrence-start').change(function(){
    var date = $(this).val();
    date = date.replace(/(\d*)\/(\d*)\/(\d*)/,"$2/$1/$3");
    var d = new Date(date);
    var n = d.getDay();
    $('.recurrence-by-day').prop('checked',false);
    $('.recurrence-by-day'+n).prop('checked',true);
  });


  // Formulaire récurrence
  $("#recurrence-form").dialog({
    autoOpen: false,
    height: 480,
    width: 650,
    modal: true,
    buttons: {
      "Enregistrer": function() {

        $('.recurrence').removeClass( "ui-state-error" );

        rrule = recurrenceRRule();

        $('#recurrence-summary').html(rrule[1]);
        $('#recurrence-hidden').val(rrule[0]);
        $('#recurrence-info').show();

        $( this ).dialog( "close" );
      },

      Annuler: function() {
        if(!$('#recurrence-hidden').val()){
          $('#recurrence-checkbox').prop('checked', false);
        }
	$( this ).dialog( "close" );
      }
    },

    close: function() {

      /** Réinitialise les champs du formulaire de façon à se qu'ils soient cohérents avec les derniers choix validés.
       *  Utile si le formulaire est modifié sans être validé puis ouvert de nouveau
       */

      $('.recurrence').removeClass( "ui-state-error" );

      var rrule = $('#recurrence-hidden').val();
      if(!rrule){
        return false;
      }

      var freq = rrule.replace(/.*FREQ=(\w*).*/,"$1");
      var interval = rrule.indexOf('INTERVAL') > 0 ? rrule.replace(/.*INTERVAL=(\d*).*/,"$1") : 1;
      var count = rrule.indexOf('COUNT') > 0 ? rrule.replace(/.*COUNT=(\d*).*/,"$1") : null;
      var until = rrule.indexOf('UNTIL') > 0 ? rrule.replace(/.*UNTIL=(\w*).*/,"$1") : null;
      var byday = rrule.indexOf('BYDAY') > 0 ;

      if(freq){
        $('#recurrence-freq').val(freq);
        $('#recurrence-freq').change();
      }

      if(freq == 'MONTHLY' && byday){
        $('#recurrence-repet-mois2').click();
      }

      if(interval){
        $('#recurrence-interval').val(interval);
      }

      $('#recurrence-end1').click();

      if(count){
        $('#recurrence-end2').click();
        $('#recurrence-count').val(count);
      }

      if(until){
        until = dateICSGMTToFr(until);
        until = until.substr(0,10);

        $('#recurrence-end3').click();
        $('#recurrence-until').val(until);
      }
    }
  });

  $('#recurrence-form').on('dialogclose', function(){
    if(!$('#recurrence-hidden').val()){
      $('#recurrence-checkbox').prop('checked', false);
    }
  });

  $('#recurrence-form').on('dialogopen', function(){
    rrule = recurrenceRRule();
    $('#recurrence-summary-form').html(rrule[1]);
  });

  // Formulaire récurrence pour la modification (temporaire, lorsque sera terminé, utiliser #recurrence-form)
  // TODO : A continuer
  $("#recurrence-form-tmp").dialog({
    autoOpen: false,
    height: 250,
    width: 650,
    modal: true,
    buttons: {
      "Fermer": function() {
        $( this ).dialog( "close" );
      }
    }
  });

  $('#recurrence-freq').change(function(){
    switch($(this).val()){
      case 'DAILY' : $('#recurrence-repet-freq').text('jours'); break;
      case 'WEEKLY' : $('#recurrence-repet-freq').text('semaines'); break;
      case 'MONTHLY' : $('#recurrence-repet-freq').text('mois'); break;
    }

    if($(this).val() == 'WEEKLY'){
      $('#recurrence-tr-semaine').show();
    } else {
      $('#recurrence-tr-semaine').hide();
    }

    if($(this).val() == 'MONTHLY'){
      $('#recurrence-tr-mois').show();
    } else {
      $('#recurrence-tr-mois').hide();
    }

  });

  $('#absence-start').change(function(){
    var date = $(this).val();

    // Affichage de la date de début dans le formulaire "récurrence"
//     $('#recurrence-start').val(date);   /* Si champ date de début modifiable */
    $('#recurrence-start').text(date);

    // Modification de la récurrence si la date de début a changé
    var rrule = $('#recurrence-hidden').val();

    // S'il s'agit d'une récurrence hebdomadaire avec le paramètre BYDAY
    if(rrule.indexOf('WEEKLY') > 0 ){
      byday = recurrenceWeeklyByDay(date);
      rrule = rrule.replace(/BYDAY=([A-Z, ]*)/,'BYDAY='+byday);
      if(rrule.indexOf('BYDAY') < 0 ){
        rrule += ';BYDAY='+byday;
      }
    }

    // S'il s'agit d'une récurrence mensuel avec le paramètre BYMONTHDAY
    if(rrule.indexOf('MONTHLY') > 0 && rrule.indexOf('BYDAY') < 0 ){
      var bymonthday = parseInt(date.substr(0,2));
      rrule = rrule.replace(/BYMONTHDAY=(\d*)/,'BYMONTHDAY='+bymonthday);
      if(rrule.indexOf('BYMONTHDAY') < 0 ){
        rrule += ';BYMONTHDAY='+bymonthday;
      }
    }

    // S'il s'agit d'une récurrence mensuel avec le paramètre BYDAY
    if(rrule.indexOf('MONTHLY') > 0 && rrule.indexOf('BYDAY') > 0 ){
      byday = recurrenceMonthlyByDay(date);
      rrule = rrule.replace(/BYDAY=([0-9A-Z-, ]*)/,'BYDAY='+byday);
    }

    var text = recurrenceRRuleText2(rrule);

    $('#recurrence-hidden').val(rrule);
    $('#recurrence-summary').html(text);

  });

  $('.recurrence-end').change(function(){
    if($('#recurrence-end2').is(':checked')){
      $('#recurrence-count').val(30);
    } else {
      $('#recurrence-count').val(null);
    }
    if($('#recurrence-end3').is(':checked')){
    } else {
      $('#recurrence-until').val(null);
    }
  });
  
  $('#recurrence-count').click(function(){
    $('#recurrence-end2').click();
  });

  $('#recurrence-until').click(function(){
    $('#recurrence-end3').click();
  });

  // Détecte les modifications du formulaire pour adapter la règle ICS
  $('.recurrence').change(function(){
    rrule = recurrenceRRule();
    $('#recurrence-summary-form').html(rrule[1]);
  });
  $('input[type=text].recurrence').keyup(function(){
    rrule = recurrenceRRule();
    $('#recurrence-summary-form').html(rrule[1]);
  });

  // Récurrences : alerte lors de la modification d'une absence récurrente
  $("#recurrence-alert").dialog({
    autoOpen: false,
    height: 220,
    width: 1000,
    modal: true,
    buttons: {

      "Uniquement cet événement": function() {
        $('#recurrence-modif').val('current');
        $('#form').submit();
        $( this ).dialog( "close" );
      },

      "Cet événement et les suivants": function() {
        $('#recurrence-modif').val('next');
        $('#form').submit();
        $( this ).dialog( "close" );
      },

      "Tous les événements": function() {
        $('#recurrence-modif').val('all');
        $('#form').submit();
        $( this ).dialog( "close" );
      },

      Annuler: function() {
	$( this ).dialog( "close" );
      }
    },
    close: function() {
      $('.recurrence').removeClass( "ui-state-error" );
    },

  });
  
  // Récurrences : alerte lors de la suppression d'une absence récurrente
  $("#recurrence-alert-suppression").dialog({
    autoOpen: false,
    height: 220,
    width: 1000,
    modal: true,
    buttons: {

      "Uniquement cet événement": function() {
        var CSRFToken = $('#CSRFSession').val();
        var id=$("#absence-bouton-supprimer").attr("data-id");
        $( this ).dialog( "close" );
        delete_absence(CSRFToken, id, 'current');
      },

      "Cet événement et les suivants": function() {
        var CSRFToken = $('#CSRFSession').val();
        var id=$("#absence-bouton-supprimer").attr("data-id");
        $( this ).dialog( "close" );
        delete_absence(CSRFToken, id, 'next');
      },

      "Tous les événements": function() {
        var CSRFToken = $('#CSRFSession').val();
        var id=$("#absence-bouton-supprimer").attr("data-id");
        $( this ).dialog( "close" );
        delete_absence(CSRFToken, id, 'all');
      },

      Annuler: function() {
	$( this ).dialog( "close" );
      }
    },
    close: function() {
      $('.recurrence').removeClass( "ui-state-error" );
    },

  });

});


/**
  * Agents multiples
  * Permet d'ajouter plusieurs agents sur une même absence (réunion, formation)
  * Lors du changement du <select perso_ids>, ajout du nom des agents dans <ul perso_ul> et leurs id dans <input perso_ids[]>
  */
function change_select_perso_ids(id){
  // Ajout des champs hidden permettant la validation des agents
  $('#perso_ids').before("<input type='hidden' name='perso_ids[]' value='"+id+"' id='hidden"+id+"' class='perso_ids_hidden'/>\n");

  $("#option"+id).hide();
  
  // Affichage des agents sélectionnés avec tri alphabétique
  affiche_perso_ul();
}

/**
 * Affichage des agents sélectionnés avec tri alphabétique
 */
function affiche_perso_ul(){
  var tab=[];
  $(".perso_ids_hidden").each(function(){
    var id=$(this).val();
    var name=$("#perso_ids option[value='"+id+"']").text();
    tab.push([name,id]);
  });

  tab.sort(function (a, b) {
    return a[0].toLowerCase().localeCompare(b[0].toLowerCase());
  });
  
  $(".perso_ids_li").remove();
  
  // Réparti l'affichage des agents sélectionnés sur 5 colonnes de 10 (ou plus)
  var nb = Math.ceil(tab.length / 5);
  if(nb<10){
    nb=10;
  }
  
  for(i in tab){
    var li="<li id='li"+tab[i][1]+"' class='perso_ids_li' data-id='"+tab[i][1]+"'>"+tab[i][0];

    if( $('#admin').val() == 1 || tab[i][1] != $('#login_id').val() ){
      li+="<span class='perso-drop' onclick='supprimeAgent("+tab[i][1]+");' ><span class='pl-icon pl-icon-drop'></span></span>";
    }

    li+="</li>\n";
    
    if(i < nb){
      $("#perso_ul1").append(li);
    } else if(i < (2*nb)){
      $("#perso_ul2").append(li);
    } else if(i < (3*nb)){
      $("#perso_ul3").append(li);
    } else if(i < (4*nb)){
      $("#perso_ul4").append(li);
    } else{
      $("#perso_ul5").append(li);
    }
  }
}

function delete_absence(CSRFToken, id, recurrence) {
  $.ajax({
    url: url('absence'),
    data: {id: id, CSRFToken: CSRFToken, rec: recurrence},
    dataType: "json",
    type: "delete",
    async: false,
    success: function(result){
      msg = result['msg'];
      msgType = result['msgType'];
      url = url('absence?msg=' + msg + '&msgType=' + msgType);
      if (result['msg2'] !== undefined) {
        url += '&msg2=' + result['msg2'] + '&msg2Type=' + result['msg2Type'];
      }
      document.location.href = url;
    },
    error: function(xhr, ajaxOptions, thrownError) {
      msg = encodeURI('Une erreur s\'est produite lors de la suppression');
      document.location.href = url('absence?msg=' + msg + '&msgType=error');
    }
  });
}


function recurrenceMonthlyByDay(date){
  if(!date){
    return false;
  }

  // Day of Week
  var tab = ['SU','MO','TU','WE','TH','FR','SA'];
  date = date.replace(/(\d*)\/(\d*)\/(\d*)/,'$2/$1/$3');
  d = new Date(date);
  var n = d.getDay();
  var day = tab[n];

  // Week of month
  var wom=0;
  var date = d.getDate();
  if(date<8){
    wom = 1;
  } else if(date<15){
    wom = 2;
  } else if (date<22){
    wom = 3;
  } else if (date<29){
    wom = 4;
  } else {
    wom = -1;
  }

  // NOTE : Variante dernière semaine prioritaire sur la 4ème semaine (ex : -1SA au lieu de 4SA)
  //     var lastDay = daysInMonth(d.getMonth()+1,d.getFullYear());
  //     if(date > (lastDay - 7)){
  //       wom = -1;
  //     }

  byday = wom.toString()+day.toString();

  return byday;
}

function recurrenceWeeklyByDay(date){
  var tab = ['SU','MO','TU','WE','TH','FR','SA'];
  date = date.replace(/(\d*)\/(\d*)\/(\d*)/, '$2/$1/$3');
  d = new Date(date);
  var byday = tab[d.getDay()];

  return byday;
}



/** function recurrenceRRule
 * Fabrique la règle de récurrence rrule en fonction des informations saisie dans le formulaire recurrence-form (dialog box)
 * rrule permettra d'écrire un événement au format ICS
 */
function recurrenceRRule(){
  var byday = null;
  var bymonthday = null;
  var end = null;
  var rrule = null;

  // FREQ
  freq = $('#recurrence-freq').val();

  // BYDAY / WEEKLY
  $('.recurrence-by-day:visible:checked').each(function(){
    byday = (byday == null) ? $(this).val() : byday+=','+$(this).val();
  });

  // BYMONTHDAY
  if($('#recurrence-repet-mois1:visible:checked').length > 0){
    bymonthday = parseInt($('#absence-start').val().substr(0,2));
  }

  // BYDAY / MONTHLY
  if($('#recurrence-repet-mois2:visible:checked').length > 0){
    var date = $('#absence-start').val();
    byday = recurrenceMonthlyByDay(date);
  }

  // COUNT && UNTIL
  switch($('.recurrence-end:checked').val()){
    // COUNT
    case 'count' :
      var count = $('#recurrence-count').val();
      end = 'COUNT='+count;
      break;

    // UNTIL
    case 'until' :
      var until = $('#recurrence-until').val();

      if(until){
        // Conversion date ICS sur fuseau GMT
        untilGMT = dateFrToICSGMT(until+" 23:59:59");
        end = 'UNTIL='+untilGMT;
      }
      break;
  }

  // INTERVAL
  interval = $('#recurrence-interval').val() == 1 ? null : $('#recurrence-interval').val();

  // RRULE
  rrule='FREQ='+freq+';WKST=MO';
  if(interval){ rrule += ';INTERVAL='+interval; }
  if(byday){ rrule += ';BYDAY='+byday; }
  if(bymonthday){ rrule += ';BYMONTHDAY='+bymonthday; }
  if(end){ rrule += ';'+end; }


  // Affichage de la règle, format humain
  var text = recurrenceRRuleText(freq, interval, byday, bymonthday, until, count);

  return [rrule,text];
}

/**
 * @function recurrenceRRuleText
 * @param string freq : fréquence de la récurrence (DAILY, WEEKLY, MONTHLY)
 * @param int interval : intervalle
 * @param string byday : jours pour les récurrences WEEKLY et MONTHLY : MO, TU, 1WE, -1TH, etc.
 * @param int bymonthday : jour du mois pour les récurrences MONTHLY : 1,2,3, etc.
 * @param string until : date de fin au format DD/MM/YYYY, fuseau horaire local
 * @param int count : nombre d'occurences
 * @return string text : règle de récurrence au format humain (FR)
 * @description : Ecrit la règle de récurrence au format humain (FR) en fonction des paramètres freq, interval, byday, etc.)
 */
function recurrenceRRuleText(freq, interval, byday, bymonthday, until, count){
  switch(freq){
    case 'DAILY' :
      if(interval == 1 || interval == null){
        var text = 'Tous les jours';
      } else {
        var text = 'Tous les '+interval+' jours';
      }

      break;

    case 'WEEKLY' :
      if(interval == 1 || interval == null){
        var text = 'Chaque semaine';
      } else {
        var text = 'Toutes les '+interval+' semaines';
      }

      if(byday){
        days = byday.replace('MO', ' lundis').replace('TU', ' mardis').replace('WE', ' mercredis').replace('TH', ' jeudis').replace('FR', ' vendredis').replace('SA', ' samedis').replace('SU', ' dimanches');
        days = days.replace(/(.*),(.[^,]*)$/, "$1 et $2");
        text += ', les'+days;
      }

      break;

    case 'MONTHLY' :
      if(interval == 1 || interval == null){
        var text = 'Tous les mois';
      } else {
        var text = 'Tous les '+interval+' mois';
      }

      if(byday){
        if(byday.substring(0,2) == '-1'){
          var n = 'Le dernier ';
          var d = byday.substring(2);
        } else {
          var n = byday.substring(0,1);
          var d = byday.substring(1);
          n = n == 1 ? 'Le 1<sup>er</sup> ' : 'Le '+n+'<sup>&egrave;me</sup> ';
        }
        day = d.replace('MO', ' lundi').replace('TU', ' mardi').replace('WE', ' mercredi').replace('TH', ' jeudi').replace('FR', ' vendredi').replace('SA', ' samedi').replace('SU', ' dimanche');

        text = text == 'Tous les mois' ? n+day+' de chaque mois' : n+day+', tous les '+interval+' mois';
      }

      if(bymonthday){
        var n = bymonthday;
        n = n == 1 ? 'Le 1<sup>er</sup>' : 'Le '+n;
        text = text == 'Tous les mois' ? n+' de chaque mois' : n+', tous les '+interval+' mois';
      }

      break;
  }

  if(until){
    text += " jusqu'au "+until;
  } else if(count){
    text += ', '+count+' fois';
  }
  return text;

}

/**
 * @function recurrenceRRuleText2
 * @param string rrule : règle de récurrence au format ICS
 * @return string text : règle de récurrence au format humain (FR)
 * @description : Ecrit la règle de récurrence au format humain (FR) en fonction du paramètre rrule (règle au format ICS).
 */
function recurrenceRRuleText2(rrule){
  var freq = rrule.replace(/.*FREQ=(\w*).*/,"$1");
  var interval = rrule.indexOf('INTERVAL') > 0 ? rrule.replace(/.*INTERVAL=(\d*).*/,"$1") : 1;
  var count = rrule.indexOf('COUNT') > 0 ? rrule.replace(/.*COUNT=(\d*).*/,"$1") : null;
  var until = rrule.indexOf('UNTIL') > 0 ? rrule.replace(/.*UNTIL=(\w*).*/,"$1") : null;
  var byday = rrule.indexOf('BYDAY') > 0  ? rrule.replace(/.*BYDAY=([0-9A-Z-,]*).*/,"$1") : null;
  var bymonthday = rrule.indexOf('BYMONTHDAY') > 0 ? rrule.replace(/.*BYMONTHDAY=(\d*).*/,"$1") : null;

  console.log(byday);
  if(until){
    until = dateICSGMTToFr(until);
    until = until.substr(0,10);
  }

  var text = recurrenceRRuleText(freq, interval, byday, bymonthday, until, count);
  return text;
}

// Vérification des formulaires (ajouter et modifier)
function verif_absences(ctrl_form){
  
  // Ceci évite d'avoir 2 fois les popup de vérification lors de la modification d'absences récurrentes. Le popup n'est affiché qu'une seule fois, avant le choix des occurrences à modifier
  if($('#recurrence-modif').val()){
    return true;
  }
  
  if(!verif_form(ctrl_form))
    return false;

  if($("select[name=motif] option:selected").attr("disabled")=="disabled"){
    CJInfo("Le motif sélectionné n'est pas valide.\nVeuillez le modifier s'il vous plaît.","error");
    return false;
  }
  
  if($("select[name=motif]").val().toLowerCase()=="autre" || $("select[name=motif]").val().toLowerCase()=="other"){
    if($("input[name=motif_autre]").val()==""){
      CJInfo("Veuillez choisir un motif.","error");
      return false;
    }
  }
 
  // ID des agents
  perso_ids=[];
  $(".perso_ids_hidden").each(function(){
    perso_ids.push($(this).val());
  });

  // Si aucun agent n'est sélectionné, on quitte en affichant "Veuillez sélectionner ..."
  if(perso_ids.length<1){
    CJInfo("Veuillez sélectionner un ou plusieurs agents","error");
    return false;
  }

  id=document.form.id.value;
  var groupe = $("#groupe").val();
  debut=document.form.debut.value;
  fin=document.form.fin.value;
  fin=fin?fin:debut;
  debut=debut.replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");
  fin=fin.replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");

  hre_debut=document.form.hre_debut.value;
  hre_fin=document.form.hre_fin.value;
  hre_debut=hre_debut?hre_debut:"00:00:00";
  hre_fin=hre_fin?hre_fin:"23:59:59";
  debut=debut+" "+hre_debut;
  fin=fin+" "+hre_fin;

  var retour=true;

  $.ajax({
    url: url('ajax/holiday-absence-control'),
    type: "get",
    datatype: "json",
    data: {perso_ids: JSON.stringify(perso_ids), id: id, groupe: groupe, debut: debut, fin: fin, type:'absence'},
    async: false,
    success: function(result){
      result=JSON.parse(result);
      var admin = result['admin'];

      // Contrôle si d'autres absences sont enregistrées
      autresAbsences = new Array();

      // Pour chaque agent
      for(i in result['users']){
        // Contrôle si d'autres absences sont enregistrées
        if(result['users'][i]["autresAbsences"] && result['users'][i]["autresAbsences"].length){
          autresAbsences.push(result['users'][i]);
        }
      }

      if(autresAbsences.length == 1){
        if(autresAbsences[0]["autresAbsences"].length == 1){
          var message = "Une absence est déjà enregistrée pour l'agent "+autresAbsences[0]["nom"]+" "+autresAbsences[0]["autresAbsences"][0]+"\nVoulez-vous continuer ?";
        } else {
          var message = "Des absences sont déjà enregistrées pour l'agent "+autresAbsences[0]["nom"]+" :\n";
          for(i in autresAbsences[0]["autresAbsences"]){
            message += "- "+autresAbsences[0]["autresAbsences"][i]+"\n";
          }
          message += "Voulez-vous continuer ?";
        }
      } else if(autresAbsences.length > 1){
        var message = "Des absences sont déjà enregistrées pour les agents suivants :\n";
        for(i in autresAbsences){
          if(autresAbsences[i]["autresAbsences"].length == 1){
            message += "- "+autresAbsences[i]["nom"]+" "+autresAbsences[i]["autresAbsences"][0]+"\n";
          } else {
            message += "- "+autresAbsences[i]["nom"]+"\n";
            for(j in autresAbsences[i]["autresAbsences"]){
               message += "-- "+autresAbsences[i]["autresAbsences"][j]+"\n";
            }
          }
        }
        message += "Voulez-vous continuer ?";
      }
      if(autresAbsences.length > 0){
        if(!confirm(message)){
          retour=false;
        }
      }

      // Contrôle si des plannings sont en cours d'élaboration
      if(result["planning_started"] && retour == true){
        if(admin == true){
          if(!confirm("Vous essayer de placer une absence sur des plannings en cours d'élaboration : "+result["planning_started"]+"\nVoulez-vous continuer ?")){
            retour=false;
          }
        } else {
          CJInfo("Vous ne pouvez pas enregistrer d'absences pour les dates suivantes car les plannings sont en cours d'élaboration :#BR#"+result["planning_started"], "error");
          retour=false;
        }
      }

      // Contrôle si les agents apparaissent dans des plannings validés
      // Pour chaque agent
      if (retour == true) {
        var planning_validated = [];
        for (i in result['users']) {
          if(result['users'][i]["planning_validated"]){
            planning_validated.push("\n- " + result['users'][i]['nom'] + "\n-- " + result['users'][i]['planning_validated'].replace(';', "\n-- "));
          }
        }

        if (planning_validated.length) {
          if (planning_validated.length == 1) {
            var message = "L'agent suivant apparaît dans des plannings validés :";
            message += planning_validated[0];
          } else if (planning_validated.length > 1) {
            var message = "Les agents suivants apparaissent dans des plannings validés :";
            for (i in planning_validated) {
              message += planning_validated[i];
            }
          }
        if(admin == true){
          if(!confirm(message +"\nVoulez-vous continuer ?"))
            retour=false;
          } else {
            CJInfo("Vous ne pouvez pas ajouter d'absences car " + message.replace("\n", "#BR#"), "error");
            retour=false;
          }
        }
      }
    },
    error: function(result){
      information("Une erreur est survenue.","error");
      retour=false;
    }
  });
  
  // Modification d'une absence récurrente
  if($('#rrule').val() && !$('#recurrence-modif').val() && retour){
    $("#recurrence-alert").dialog('open');
    return false;
  } else {
    return retour;
  }
}


/**
 * supprimeAgent
 * supprime les agents de la sélection lors de l'ajout ou modification d'une absence
 */
function supprimeAgent(id){
  $("#option"+id).show();
  $("#li"+id).remove();
  $("#hidden"+id).remove();
  affiche_perso_ul();
}
