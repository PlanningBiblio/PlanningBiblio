/**
Planning Biblio, Version 2.7.04
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : absences/js/modif.js
Création : 28 février 2014
Dernière modification : 1er novembre 2017
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
  });

  // Paramétrage de la boite de dialogue permettant la modification des motifs
  $("#add-motif-form").dialog({
    autoOpen: false,
    height: 480,
    width: 560,
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
 	  tab.push(new Array($("#valeur_"+id).text(), $(this).index(), $("#type_"+id+" option:selected").val()));
	});
        
        // Transmet le tableau à la page de validation ajax
	$.ajax({
	  url: "include/ajax.menus.php",
	  type: "post",
          dataType: "json",
	  data: {tab: tab, menu:"abs", option: "type", CSRFToken: $('#CSRFSession').val()},
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

              var option = "<option value='"+val+"' "+selected+" "+disabled+">"+padding+""+val+"</option>";
              
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
      +"<select id='type_"+number+"' style='position:absolute;left:330px;'>"
      +options
      +"</select>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
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
    if(confirm("Etes vous sûr de vouloir supprimer cette absence ?")){
      var CSRFToken = $('#CSRFSession').val();
      var id=$(this).attr("data-id");
      document.location.href="index.php?page=absences/delete.php&id="+id+"&CSRFToken="+CSRFToken;
    }
  });



  /* Récurence */

  // checkbox récurrence
  $('#recurrence-link').click(function(){
    $("#recurrence-form").dialog( "open" );
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

        $('#recurrence-summary').text(rrule);
        $('#recurrence-hidden').val(rrule);
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
    $('#recurrence-summary-form').text(rrule);
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

    $('#recurrence-hidden').val(rrule);
    $('#recurrence-summary').text(rrule);

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
    $('#recurrence-summary-form').text(rrule);
  });
  $('input[type=text].recurrence').keyup(function(){
    rrule = recurrenceRRule();
    $('#recurrence-summary-form').text(rrule);
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
        until = dateFrToICSGMT(until+" 23:59:59");
        end = 'UNTIL='+until;
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

  return rrule;
}

// Vérification des formulaires (ajouter et modifier)
function verif_absences(ctrl_form){
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

  var admin=$("#admin").val();
  var retour=true;

  $.ajax({
    url: "absences/ajax.control.php",
    type: "get",
    datatype: "json",
    data: {perso_ids: JSON.stringify(perso_ids), id: id, groupe: groupe, debut: debut, fin: fin},
    async: false,
    success: function(result){
      result=JSON.parse(result);

      // Contrôle si d'autres absences sont enregistrées
      autresAbsences = new Array();

      // Pour chaque agent
      for(i in result){
        // Contrôle si d'autres absences sont enregistrées
        if(result[i]["autresAbsences"].length){
          autresAbsences.push(result[i]);
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

      // Contrôle si les agents apparaissent dans des plannings validés
      // Pour chaque agent
      for(i in result){
	if(result[i]["planning"]){
	  if(admin==1){
	    if(!confirm("L'agent "+result[i]["nom"]+" apparaît dans des plannings validés : "+result[i]["planning"]+"\nVoulez-vous continuer ?")){
	      retour=false;
	    }
	  }
	  else{
	    CJInfo("Vous ne pouvez pas ajouter d'absences pour les dates suivantes<br/>car les plannings sont validés : "+result[i]["planning"]+"<br/>Veuillez modifier vos dates ou contacter le responsable du planning","error");
	    retour=false;
	  }
	}
      }

      // Contrôle si des plannings sont en cours d'élaboration
      if(result["planningsEnElaboration"]){
	if(admin==1){
	  if(!confirm("Vous essayer de placer une absence sur des plannings en cours d'élaboration : "+result["planningsEnElaboration"]+"\nVoulez-vous continuer ?")){
	    retour=false;
	  }
	}
	else{
	  CJInfo("Vous essayez de placer une absence sur des plannings en cours d'élaboration : "+result["planningsEnElaboration"],"error");
	  retour=false;
	}
      }
      
    },
    error: function(result){
      information("Une erreur est survenue.","error");
      retour=false;
    }
  });
  return retour;
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
