/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/js/plb/notifications.js
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à la gestion des notifications
*/

$(function() {

  // Affiche la boite de dialogue permettant la modification des notifications
  $("#update-button")
    .click(function() {

      // Liste des agents sélectionnés
      var tab = [];
      $('.checkboxes:checked:visible').each(function(){
        tab.push($(this).val());
      });

      updateFormOpen(tab);
    });

  $(".pl-icon-edit")
    .click(function() {

      var id = $(this).attr('data-id');
      updateFormOpen([id]);
    });

  // Formulaire récurrence
  $("#update-form").dialog({
    autoOpen: false,
    height: 480,
    width: 650,
    modal: true,
    buttons: {
      "Enregistrer": function() {

        $('.update').removeClass( "ui-state-error" );

        // Liste des responsables
        var tab = [];
        $('.responsables').each(function(){
          if($(this).val()){
            tab.push($(this).val());
          }
        });
        
        responsables = JSON.stringify(tab);

        // Liste des responsables recevant les notifications
        var tab = [];
        $('.notifications:checked').each(function(){
          var id = $(this).attr('data-id');
          tab.push($('#responsable-'+id).val());
        });
        
        notifications = JSON.stringify(tab);

        // Enregistrement dans la base de données
        $.ajax({
          url: url('notification'),
          type: "post",
          datatype: "json",
          data: {agents: agents, responsables: responsables, notifications: notifications, CSRFToken: $('#CSRFToken').val()},
          success: function(result){
            window.location.reload();
          },
          error: function(result){
            CJInfo("Une erreur est survenue lors de l'enregistrement des responsables", "error");
          }
        });
     

        $( this ).dialog( "close" );
      },

      Annuler: function() {
        if(!$('#update-hidden').val()){
          $('#update-checkbox').prop('checked', false);
        }
	$( this ).dialog( "close" );
      }
    },

    close: function() {
    }
  });

});

function updateFormOpen(tab){

  agents = JSON.stringify(tab);

  // Recherche des responsables cochés avant modification pour réinitialiser les champs
  var data = [];
  var diff = false;

  for(i in tab){
    data[i] = [];

    $('.resp_'+tab[i]).each(function(){
      var resp = $(this).attr('data-resp');
      var notif = $(this).attr('data-notif');
      data[i].push([resp,notif]);

    });
    // Si tous les agents n'ont pas les mêmes valeurs, les champs ne seront pas remplis
    if(i>0 && JSON.stringify(data[i]) != JSON.stringify(data[i-1])){
      diff = true;
    }
  }

  // Réinitialise à zéro les tous les champs du formulaire
  for(i=0; i<5; i++){
    $('#responsable-'+i).val('');
    $('#notification-'+i).prop('checked', false);
  }

  // S'il n'y a pas de différence, initialise les champs avec les valeurs avant modification
  if(!diff){
    for(i in data[0]){
      $('#responsable-'+i).val(data[0][i][0]);
      if(data[0][i][1] == 1){
        $('#notification-'+i).prop('checked', true);
      }
    }
  }

  // Ouvre le formulaire
  $("#update-form").dialog( "open" );
  return false;

}
