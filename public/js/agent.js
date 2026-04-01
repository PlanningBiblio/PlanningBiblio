/**
Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des agents
*/
$(function() {

  var allFields = $( [] );

  $( "#dialog-form" ).dialog({
    autoOpen: false,
    height: 660,
    width: 1000,
    modal: true,
    buttons: {
      "Enregistrer": function() {

        // List
        var list = [];
        $('.checkbox:visible:checked').each(function(){
          list.push($(this).val());
        });
        list = JSON.stringify(list);

        $.ajax({
          url: url('agent/bulk/update'),
            type: 'post',
            dataType: 'json',
            data: {
              _token: $('input[name=_token]').val(),
               // Main tab
              actif: $('#actif').val(),
              contrat: $('#contrat').val(),
              heures_hebdo: $('#heures_hebdo').val(),
              heures_travail: $('#heures_travail').val(),
              service: $('#service').val(),
              statut: $('#statut').val(),
              // Skills tab
              postes: $('#postes').val(),
              list: list,
            },
            success: function(result){
              if (result=='ok') {
                var msg = 'Les agents ont été modifés avec succès';
                var msgType = 'success';
              } else {
                var msg = result;
                var msgType = 'error';
              }
              location.href = url('agent?msg=' + msg + '&msgType=' + msgType);
            },
            error: function(){
              location.href = url('agent?msg=Une erreur est survenue lors de la modification des agents&msgType=error');
            }
        });
      },

      "Annuler": function() {
        $( this ).dialog( "close" );
      }
    },

    close: function() {
      allFields.val( "" ).removeClass( "ui-state-error" );
      $('.validateTips').text("");
    }
  });
});

function agent_list() {

  if (!$('.checkbox:visible:checked').length) {
    alert('Veuillez sélectionner un ou plusieurs agents.');
    return false;
  }

  // Action
  var action = $('#action').val();

  switch(action) {

    case 'delete' :

      if (!confirm('Etes vous sûr(e) de vouloir suppimer les agents sélectionnés ?')) {
        break;
      }

      // List
      var list = [];
      $('.checkbox:visible:checked').each(function(){
        list.push($(this).val());
      });
      list = JSON.stringify(list);

      $.ajax({
        url: url('agent/bulk/delete'),
          type: 'delete',
          dataType: 'json',
          data: {
            _token: $('input[name=_token]').val(),
            CSRFToken: $('#CSRFSession').val(),
            list: list
          },
          success: function() {
            location.href = url('agent?msg=Les agents ont été supprimés avec succès&msgType=success');
          },
            error: function() {
              location.href = url('agent?msg=Une erreur est survenue lors de la suppresion des agents&msgType=error');
          }
      });

      break;

    case 'edit' :
      $( "#dialog-form" ).dialog( "open" );

      break;
  }
}

function deleteAgent() {
  var date = $('#delete-date').val();

  var data = {
    _token: $('input[name=_token]').val(),
    id: $('#agentId').val(),
  };

  if ($('#permanentDelete').val() == 0) {
    data.date = date;
  }

  $.ajax({
    url : url('agent'),
         type : 'DELETE',
         data : data,
         success: function(response) {
           if (response == "level 1 delete OK") {
             var msg = encodeURI("L'agent a bien été supprimé.");
             parent.location.href=url('agent') + '?msg=' + msg + '&msgType=success';
           } else if (response == "permanent delete OK") {
             var msg = encodeURI("L'agent a été supprimé définitivement.");
             parent.location.href=url('agent') + '?msg=' + msg + '&msgType=success';
           } else {
             var msg = encodeURI("Une erreur est survenue lors de la suppresion de l'agent.");
             parent.location.href=url('agent') + '?msg=' + msg + '&msgType=error';
           }
         },
         error: function() {
           var msg = encodeURI("Une erreur est survenue lors de la suppresion de l'agent.");
           parent.location.href=url('agent') + '?msg=' + msg + '&msgType=error';
         }
  });
}

function changeSelectSites(){
  // Tous les sites
  sites=new Array();
  $("input:checkbox[name^=sites]").each(function(){
    sites.push($(this).val());
  });
  
  // Sites sélectionnés
  sitesSelectionnes=new Array();
  $("input:checkbox[name^=sites]:checked").each(function(){
    sitesSelectionnes.push($(this).val());
  });

  if(sitesSelectionnes.length>1){
    $(".edt-site-0").show();
  }else{
    $(".edt-site-0").hide();
    $(".edt-site").val(sitesSelectionnes[0]);
  }
  
  for(i=0;i<sites.length;i++){
    $(".edt-site-"+sites[i]).hide();
  }
    
  for(i=0;i<sitesSelectionnes.length;i++){
    $(".edt-site-"+sitesSelectionnes[i]).show();
  }
  // Faire for (i=1, i<= nombre de site ...) .edt-site-i.hide
  // Puis foreach tab, .edt-site-tabIndex.show
}

// Select multiples
function select_add(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  attrib_new=new Array();
  dispo_new=new Array();
  tab_attrib=new Array();
  dispo=document.getElementById(select_dispo).options;
  attribues=document.getElementById(select_attrib).options;
  for(i=0;i<attribues.length;i++)
    attrib_new.push(attribues[i].value);
  for(i=0;i<dispo.length;i++)
    if(dispo[i].selected)
	attrib_new.push(dispo[i].value);
  for(i=0;i<complet.length;i++){
    var inArray=false;
    for(j=0;j<attrib_new.length;j++){
      if(complet[i][1]==attrib_new[j]){
	attrib_new[j]=complet[i];
	tab_attrib.push(complet[i][1]);
	inArray=true;
      }
    }
    if(!inArray){
      dispo_new.push(complet[i]);
    }
  }
  dispo_new.sort();
  attrib_new.sort();
  
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<attrib_new.length;i++)
    attrib_aff=attrib_aff+"<option value='"+attrib_new[i][1]+"'>"+attrib_new[i][0]+"</option>";
  attrib_aff=attrib_aff+"</select>";
  
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<dispo_new.length;i++)
    dispo_aff=dispo_aff+"<option value='"+dispo_new[i][1]+"'>"+dispo_new[i][0]+"</option>";
  dispo_aff=dispo_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value = JSON.stringify(tab_attrib);
}

function select_drop(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  dispo_new=new Array();
  attrib_new=new Array();
  tab_attrib=new Array();
  dispo=document.getElementById(select_dispo).options;
  attribues=document.getElementById(select_attrib).options;
  for(i=0;i<dispo.length;i++)
    dispo_new.push(dispo[i].value);
  for(i=0;i<attribues.length;i++)
    if(attribues[i].selected)
      dispo_new.push(attribues[i].value);
  for(i=0;i<complet.length;i++){
    var inArray=false;
    for(j=0;j<dispo_new.length;j++){
      if(complet[i][1]==dispo_new[j]){
	dispo_new[j]=complet[i];
	inArray=true;
      }
    }
    if(!inArray){
      attrib_new.push(complet[i]);
      tab_attrib.push(complet[i][1]);
    }
  }
  dispo_new.sort();
  attrib_new.sort();
  
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<attrib_new.length;i++)
    attrib_aff=attrib_aff+"<option value='"+attrib_new[i][1]+"'>"+attrib_new[i][0]+"</option>";
  attrib_aff=attrib_aff+"</select>";
  
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<dispo_new.length;i++)
    dispo_aff=dispo_aff+"<option value='"+dispo_new[i][1]+"'>"+dispo_new[i][0]+"</option>";
  dispo_aff=dispo_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value = JSON.stringify(tab_attrib);
}

function select_add_all(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  tab_attrib=new Array();
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<complet.length;i++){
    attrib_aff=attrib_aff+"<option value='"+complet[i][1]+"'>"+complet[i][0]+"</option>";
    tab_attrib.push(complet[i][1]);
  }
  attrib_aff=attrib_aff+"</select>";
  
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  dispo_aff=dispo_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value = JSON.stringify(tab_attrib);
}

function select_drop_all(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<complet.length;i++)
    dispo_aff=dispo_aff+"<option value='"+complet[i][1]+"'>"+complet[i][0]+"</option>";
  dispo_aff=dispo_aff+"</select>";
  
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  attrib_aff=attrib_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value = '[]';
}
// Fin Select Multpiles


// Envoi de l'URL ICS Planning Biblio par mail
function sendICSURL(){

  // Récupération des paramètres si l'agent logué a les droits d'administration
  if($('#nom').val() != undefined && $('#nom').val() != '' ){
    var nom = $('#nom').val();
    var prenom = $('#prenom').val();
    var mail = $('#mail').val();

  // Récupération des paramètres si l'agent logué n'a pas les droits d'administration
  } else {
    var nom = $('#nom').text();
    var prenom = $('#prenom').text();
    var mail = $('#mail').text();
  }

  var urlIcs = $('#urlIcs').text();
  var urlIcsWithAbsences = urlIcs + '&absences=1';

  var message = $('#ics-url-text').val();
  message = message.replace('[lastname]', nom);
  message = message.replace('[firstname]', prenom);
  message = message.replace('[urlIcs]', urlIcs);
  message = message.replace('[urlIcsWithAbsences]', urlIcsWithAbsences);

  $( "#ics-url-recipient" ).text(mail);
  $( "#ics-url-text" ).val(message);
  $( "#ics-url-form" ).dialog( 'open' );
}

function sendPassword() {
  $.ajax({
    url: url('agent/send-password'),
    type: 'post',
    dataType: 'json',
    data: {
      _token: $('input[name=_token]').val(),
      id: $('input[name=id]').val(),
    },
    success: function(result) {
      CJInfo(result[0], result[1]);
    },
    error: function() {
      CJInfo('Une errreur est survenue lors de l\'envoi du mot de passe', 'error');
    }
  });
}

// Contrôle des champs lors de la validation
function verif_form_agent(){

  erreur = false;
  message = "Les champs suivants sont obligatoires :";

  if(!document.form.nom.value) {
    erreur = true;
    message = message + "\n- Nom";
  }
  if(!document.form.prenom.value) {
    erreur = true;
    message = message + "\n- prénom";
  }
  if(!document.form.mail.value) {
    erreur = true;
    message = message + "\n- E-mail";
  }
  
  if(erreur) {
    CJInfo(message);
    return false;
  }

  if(!verif_mail(document.form.mail.value)) {
    CJInfo("Adresse e-mail invalide");
    return false;
  }

  if ($('.invalid').length) {
    CJInfo("Des valeurs sont invalides dans l'onglet \"Congés\"");
    return false;
  }

  document.form.submit();
}

function control_credits_min(o) {
  id = o.attr('id');
  errorElem = id + '_error';
  minutes = o.val();

  $('#' + errorElem).remove();

  if (minutes) {
    minutes = Number(minutes);
    if (
        !Number.isInteger(minutes) ||
        minutes < 0 ||
        minutes > 59
       ) {
        o.closest('tr').after('<tr id="' + errorElem + '"><td colspan="2" class="aRight important invalid">Le nombre de minutes doit être un entier compris entre 0 et 59</td></tr>');
    }
  }
}

function control_credits_hours(o) {
  id = o.attr('id');
  errorElem = id + '_error';
  hours = o.val();

  $('#' + errorElem).remove();

  if (hours) {
    hours = Number(hours);
    if (id == 'comp_time_hours') {
      if (!Number.isInteger(hours)) {
          o.closest('tr').after('<tr id="' + errorElem + '"><td colspan="2" class="aRight important invalid">Le nombre d\'heures doit être un entier</td></tr>');
      }
    } else {
      if (!Number.isInteger(hours) ||
          hours < 0
         ) {
          o.closest('tr').after('<tr id="' + errorElem + '"><td colspan="2" class="aRight important invalid">Le nombre d\'heures doit être un entier positif</td></tr>');
      }
    }
  }
}

$(function() {

  if($('#statuses_sortable').length) {
      Sortable.create(statuses_sortable, {ghostClass: 'bg-blue', animation: 150}); 
  }

  if($('#services_sortable').length) {
      Sortable.create(services_sortable, {ghostClass: 'bg-blue', animation: 150}); 
  }

  // Formulaire d'ajout de nouveau statut
  $('#add-status').on('submit', function(e) {
    e.preventDefault();
    $('#invalid-status').text('Statut invalide');
    // Récupère les options du premier select "catégorie" pour les réutiliser lors d'un ajout
    var select=$('select[id^=categorie_]');
    var select_id=select.attr('id');
    var options = '<option hidden disabled selected value=""></option>';
    $('#'+select_id+' option').each(function() {
      var val=sanitize_string($(this).val());
      var text=sanitize_string($(this).text());
      options+='<option value="'+val+'">'+text+'</option>';
    });

    var text=sanitize_string($('#add-status-text').val());
    if(!text){
      $('#add-status-text').addClass('is-invalid');
      return;
    }

    // Vérifie si le statut existe déjà
    var exist = false;
    $('#statuses_sortable > li > span').each(function(){
      if($(this).text().toLowerCase() == text.toLowerCase()){
        $('#invalid-status').text('Un statut avec ce nom existe déjà.')
        $('#add-status-text').addClass('is-invalid');
        exist = true;
        return;
      }
    });
    
    if(exist){
      return;
    }

    var number = 1;
    while($('#li_'+number).length){
      number++;
    }

    $('#statuses_sortable').append('<li class="row row-sortable" id="li_'+number+'"> <i class="col-auto p-0 ps-2 bi bi-arrow-down-up"></i>'
      +'<span class="col-6 p-2" id="valeur_'+ number + '">'+text+'</span>'
      +'<div class="col-4">'
      +'<select id="categorie_'+number+'" class="form-control form-select form-select-sm" aria-label="Séléction Catégorie">'
      +options
      +'</select></div>'
      +'<span class="col-1 ps-5" onclick="$(this).closest(\'li\').hide();"> <i class="bi bi-trash3-fill"></i></span>'
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-status-text").val(null);
  });

  // Formulaire de modification des statuts (ordre, catégorie, suppression...)
  $('#arrange-statuses').on('submit', function(e) {
    e.preventDefault();
    // Supprime les lignes cachées lors du clic sur la corbeille
    $('#statuses_sortable li:hidden').each(function() {
	    $(this).remove();
	  });

    // Enregistre les éléments du formulaire dans un tableau
    tab = new Array();
    $('#statuses_sortable li').each(function() {
      var id = $(this).attr('id').replace('li_', '');
      tab.push(new Array(
        $(this).find('#valeur_' + id).text(),
        $(this).index(),
        $(this).find('#categorie_' + id + ' option:selected').val()
      ));
    });

    // Transmet le tableau à la page de validation ajax
    var _token = $('input[name=_token]').val();

    $.ajax({
      url: url('ajax/update-select-options'),
      type: 'post',
      dataType: 'json',
      data: {
        _token: _token,
        CSRFToken: $('#CSRFSession').val(),
        menu: 'statuts',
        option: 'categorie',
        tab: tab,
      },

      success: function() {
        var current_val = $('#statut').val();
        $('#statut').empty();
        $('#statut').append('<option value="">Aucun</option>');

        $('#statuses_sortable li').each(function() {
          var id = $(this).attr('id').replace('li_','');
          var val = $(this).find('#valeur_' + id).text();
          var selected = val == current_val;
          var option = new Option(val, val, selected, selected);
          $('#statut').append(option);
        });

        $('#add-status-modal').modal('hide');
        $('#statut').effect('highlight', null, 2000);
      },

      error: function(){
        alert('Erreur lors de l\'enregistrement des modifications');
      }
    });
  });

  // Suppression message invalidité lors du changement d'input de statut
  $('#add-status-text').on('input', function(e) {
    if($(this).hasClass('is-invalid')) {
      $(this).removeClass('is-invalid');
    }
  })

  // Restaure les éléments supprimés mais non validés (pour les services et les statuts)
  $('[id^=add-][id$=-modal]').on('hidden.bs.modal', function() {
    $('[id$=_sortable] li:hidden').each(function() {
	    $(this).show();
    });
  });

  // Formulaire d'ajout de nouveaux services
  $('#add-service').on('submit', function(e) {
    e.preventDefault();
    $('#invalid-service').text('Service invalide');
    var text=sanitize_string($('#add-service-text').val());
    if(!text) {
      $('#add-service-text').addClass('is-invalid');
      return;
    }
    
    // Vérifie si le service existe déjà
    var exist = false;
    $('#services_sortable > li > span').each(function(){
      if($(this).text().toLowerCase() == text.toLowerCase()){
        $('#invalid-service').text('Un service avec ce nom existe déjà.')
        $('#add-service-text').addClass('is-invalid');
        exist = true;
        return;
      }
    });
    
    if(exist){
      return;
    }
    
    var number = 1;
    while($('#li_'+number).length){
      number++;
    }
    $('#services_sortable').append('<li class="row row-sortable" id="li_'+number+'"><i class="col-auto p-0 ps-2 bi bi-arrow-down-up"></i>'
      +'<span class="col-10 p-2" id="valeur_'+number +'">'+text+'</span>'
      +'<span class="col-1 ps-5" onclick="$(this).closest(\'li\').hide();"><i class="bi bi-trash3-fill"></i></span>'
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-service-text").val(null);
  });

  // Formulaire de modification des services (ordre, suppression...)
  $('#arrange-services').on('submit', function(e) {
    e.preventDefault();
    // Supprime les lignes cachées lors du clic sur la corbeille
    $('#services_sortable li:hidden').each(function() {
      $(this).remove();
    });

    // Enregistre les éléments du formulaire dans un tableau
    tab = new Array();
    $('#services_sortable li').each(function() {
      var id = $(this).attr('id').replace('li_','');
      tab.push(new Array(
        $(this).find('#valeur_' + id).text(),
        $(this).index()
      ));
    });

    // Transmet le tableau à la page de validation ajax
    var _token = $('input[name=_token]').val();

    $.ajax({
      url: url('ajax/update-select-options'),
      type: 'post',
      dataType: 'json',
      data: {
        _token: _token,
        CSRFToken: $('#CSRFSession').val(),
        menu: 'services',
        tab: tab,
      },

      success: function(){
        var current_val = $('#service').val();
        $('#service').empty();
        $('#service').append('<option value="">Aucun</option>');

        $('#services_sortable li').each(function() {
          var id = $(this).attr('id').replace('li_', '');
          var val = $(this).find('#valeur_' + id).text();
          var selected = val == current_val;
          var option = new Option(val, val, selected, selected);
          $('#service').append(option);
        });
        $('#add-service-modal').modal('hide');
        $('#service').effect('highlight', null, 2000);
      },

      error: function(){
        alert('Erreur lors de l\'enregistrement des modifications');
      }
    });
  });

  // Suppression message invalidité lors du changement d'input de service
  $('#add-service-text').on('input', function(e) {
    if($(this).hasClass('is-invalid')) {
      $(this).removeClass('is-invalid');
    }
  })
  
  $('#ics-url-form').dialog({
    autoOpen: false,
    height: 525,
    width: 650,
    modal: true,
    buttons: {
      'Envoyer': function() {

        // Envoi le mail
        var recipient = $('#ics-url-recipient').text();
        var subject = $('#ics-url-subject').val();
        var message = $('#ics-url-text').val();
        var _token = $('input[name=_token]').val();

        $.ajax({
          dataType: 'json',
          url: url('agent/ics/send-url'),
          type: 'post',
          data: {
            _token: _token,
            message: message,
            recipient: recipient,
            subject: subject,
          },
          success: function(result){
            if(result.error) {
              updateTips(result.error, 'error');
            } else {
              CJInfo('L\'e-mail a bien été envoyé', 'success');
              $('#ics-url-form').dialog('close');
            }
          },
          error: function(){
            updateTips('Une erreur est survenue lors de l\'envoi de l\'e-mail', 'error');
          }
        });
      },

      Annuler: function() {
        $( this ).dialog('close');
      }
    },

    close: function() {
      $('.validateTips').text('Envoyez à l\'agent les URL de ses agendas Planno.');
    }
  });
  
  $('#conges_annuel_hours').on('keyup', function(){
    control_credits_hours($(this));
  });

  $('#conges_anticipation_hours').on('keyup', function(){
    control_credits_hours($(this));
  });

  $('#conges_credit_hours').on('keyup', function(){
    control_credits_hours($(this));
  });

  $('#conges_reliquat_hours').on('keyup', function(){
    control_credits_hours($(this));
  });

  $('#comp_time_hours').on('keyup', function(){
    control_credits_hours($(this));
  });

  $('#conges_annuel_min').on('keyup', function(){
    control_credits_min($(this));
  });

  $('#conges_anticipation_min').on('keyup', function(){
    control_credits_min($(this));
  });

  $('#conges_credit_min').on('keyup', function(){
    control_credits_min($(this));
  });

  $('#conges_reliquat_min').on('keyup', function(){
    control_credits_min($(this));
  });

  $('#comp_time_min').on('keyup', function(){
    control_credits_min($(this));
  });

});

$(document).ready(function(){
  // Met à jour les select site des emplois du temps si les sites ont changé dans les infos générales
  $("#personnel-a-li3").click(function(){
    changeSelectSites();
  });
  $("#post_form_agent").click(function() {
    verif_form_agent();
  });

  $('.delete-agent').on('click',function(){
    var agentId = $(this).data('id');
    var agentName = $(this).data('name');

    $('#agentId').val(agentId);
    $('#deleteDialog').dialog('open');

    if ($('#showAgentSelect').val() != 'Supprimé') {
      $('#permanentDelete').val(0);
      $('#c-text').text("Êtes-vous sûr(e) de vouloir supprimer " + agentName.toString() + " ?");
    } else {
      $('#permanentDelete').val(1);
      $('#c-text').text("Êtes-vous sûr(e) de vouloir supprimer définitivement " + agentName.toString() + " ?");
    }
  });

  $('#deleteDialog').dialog({
    autoOpen: false,
    modal: true,
    width: 400,
    height: 260,
    buttons: {
      Non: function() {
        $(this).dialog('close');
      },
      Oui: function(e) {
        e.preventDefault();

        if ($('#permanentDelete').val() == 0) {
          $("#deleteDialog").dialog('close');
          $("#deleteStep2Dialog").dialog('open');
        } else {
          deleteAgent();
        }
      }
    }
  });

  $('#deleteStep2Dialog').dialog({
    autoOpen: false,
    modal: true,
    width: 400,
    height: 260,
    buttons: {
      Annuler: function() {
        $(this).dialog('close');
      },
      Supprimer: function() {
        deleteAgent();
      }
    }
  });
});
