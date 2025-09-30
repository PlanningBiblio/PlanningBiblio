/**
Description :
Fichier regroupant les scripts JS nécessaires à la page planning/volants/index.php (Gestion des agents volants)
*/


// Evénements JQuery
$(function() {
  // Calendar
  $("#pl-calendar").change(function(){
    var date=dateFr($(this).val());
    window.location.href= url('detached') + '?date=' + date;
  });


  // Bouton Ajouter
  $('#volants-add').click(function(){
    $('.volants-dispo:visible:selected').each(function(){

      var id = $(this).attr('data-id');
      $('.selected_'+id).show();
      $(this).removeAttr('selected');
      $(this).hide();
    });
  });

  // Bouton Ajouter tous
  $('#volants-add-all').click(function(){
    $('.volants-dispo:visible').each(function(){

      var id = $(this).attr('data-id');
      $('.selected_'+id).show();
      $(this).removeAttr('selected');
      $(this).hide();
    });
  });

  // Bouton Supprimer
  $('#volants-remove').click(function(){
    $('.volants-selectionnes:visible:selected').each(function(){

      var id = $(this).attr('data-id');
      $('.dispo_'+id).show();
      $(this).removeAttr('selected');
      $(this).hide();
    });
  });

  // Bouton Supprimer tous
  $('#volants-remove-all').click(function(){
    $('.volants-selectionnes:visible').each(function(){

      var id = $(this).attr('data-id');
      $('.dispo_'+id).show();
      $(this).removeAttr('selected');
      $(this).hide();
    });
  });
  
  // Validation
  $('#submit').click(function(){
    var ids = new Array();
    $('.volants-selectionnes:visible').each(function(){
      ids.push($(this).val());
    });

    ids = JSON.stringify(ids);

    $.ajax({
      url: url('detached/add'),
      type: 'post',
      dataType: 'json',
      data: {ids: ids, date: $('#date').val(), CSRFToken: $('#CSRFSession').val(), },
      success: function(result){
        if(result.error){
          CJInfo("Une erreur est survenue lors de l'enregistrement des informations", 'error');
        } else {
          CJInfo('Vos modifications ont été enregistrées avec succès', 'success');
        }
      },
      error: function(){
        CJInfo("Une erreur est survenue lors de l'enregistrement des informations", 'error');
      }
    });
    
  });

});
