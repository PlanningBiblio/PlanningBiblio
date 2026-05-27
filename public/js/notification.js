/**
Description :
Fichier regroupant les fonctions JavaScript utiles à la gestion des notifications
*/

$(function() {

  // Affiche la boite de dialogue permettant la modification des notifications
  $('#update-button').click(function() {

    if (!$('.checkboxes:visible:checked').length) {
      alert('Veuillez sélectionner un ou plusieurs agents.');
      return false;
    }

    // Liste des agents sélectionnés
    var tab = [];
    $('.checkboxes:checked:visible').each(function() {
      tab.push($(this).val());
    });

    updateFormOpen(tab);
  });

  $(".pl-icon-edit")
    .click(function() {

      var id = $(this).attr('data-id');
      updateFormOpen([id]);
    });

  $('#update-notification-form').on('submit', function(e) {
    e.preventDefault();

    // Liste des responsables
    var tab = [];
    $('.responsablesl1').each(function() {
      if($(this).val()) {
        tab.push($(this).val());
      }
    });

    responsables = JSON.stringify(tab);

    // Liste des responsables recevant les notifications
    var tab = [];
    $('.notificationsl1:checked').each(function() {
      var id = $(this).attr('data-id');
      tab.push($('#responsable-' + id).val());
    });

    notifications = JSON.stringify(tab);

    var tab = [];
    $('.responsablesl2').each(function() {
      if($(this).val()) {
        tab.push($(this).val());
      }
    });

    responsablesl2 = JSON.stringify(tab);

    // Liste des responsables recevant les notifications
    var tab = [];
    $('.notificationsl2:checked').each(function(){
      var id = $(this).attr('data-id');
      tab.push($('#responsablel2-'+id).val());
    });
    
    notificationsl2 = JSON.stringify(tab);

    // Enregistrement dans la base de données
    $.ajax({
      url: url('notification'),
      type: 'post',
      datatype: 'json',
      data: {
          _token: $('#_token').val(),
          agents: agents,
          responsables: responsables,
          responsablesl2: responsablesl2,
          notifications: notifications,
          notificationsl2: notificationsl2,
      },
      success: function(result) {
        window.location.reload();
      },
      error: function(result) {
        stackAlert('Une erreur est survenue lors de l\'enregistrement des responsables', 'error');
        $('#update-notification-modal').modal('hide');
      }
    });
  })
});

function updateFormOpen(tab){

  agents = JSON.stringify(tab);

  // Recherche des responsables cochés avant modification pour réinitialiser les champs
  var managerl1 = [];
  var managerl2 = [];
  var diff_l1 = false;
  var diff_l2 = false;

  for(i in tab){
    managerl1[i] = [];
    managerl2[i] = [];

    $('.managerl1_'+tab[i]).each(function(){
      var resp = $(this).attr('data-manager');
      var notif = $(this).attr('data-notif');
      managerl1[i].push([resp,notif]);
    });

    $('.managerl2_'+tab[i]).each(function(){
      var resp = $(this).attr('data-manager');
      var notif = $(this).attr('data-notif');
      managerl2[i].push([resp,notif]);
    });

    // Si tous les agents n'ont pas les mêmes valeurs, les champs ne seront pas remplis
    if(i>0 && JSON.stringify(managerl1[i]) != JSON.stringify(managerl1[i-1])){
      diff_l1 = true;
    }

    if(i>0 && JSON.stringify(managerl2[i]) != JSON.stringify(managerl2[i-1])){
      diff_l2 = true;
    }
  }

  // Réinitialise à zéro les tous les champs du formulaire
  for(i=0; i<9; i++){
    $('#responsable-'+i).val('');
    $('#responsablel2-'+i).val('');
    $('#notification-'+i).prop('checked', false);
    $('#notificationl2-'+i).prop('checked', false);
  }

  // S'il n'y a pas de différence, initialise les champs avec les valeurs avant modification
  if(!diff_l1){
    for(i in managerl1[0]){
      $('#responsable-'+i).val(managerl1[0][i][0]);
      if(managerl1[0][i][1] == 1){
        $('#notification-'+i).prop('checked', true);
      }
    }
  }

  if(!diff_l2){
    for(i in managerl2[0]){
      $('#responsablel2-'+i).val(managerl2[0][i][0]);
      if(managerl2[0][i][1] == 1){
        $('#notificationl2-'+i).prop('checked', true);
      }
    }
  }

  // Ouvre le formulaire
  $('#update-notification-modal').modal('show');
  return false;

}
