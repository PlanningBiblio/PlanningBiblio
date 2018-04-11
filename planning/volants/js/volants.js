/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/js/planning.js
Création : 7 avril 2018
Dernière modification : 7 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les scripts JS nécessaires à la page planning/volants/index.php (Gestion des agents volants)
Fichier intégré par le fichier include/header.php avec la fonction getJSFiles.
*/


// Evénements JQuery
$(function() {
  // Calendar
  $("#pl-calendar").change(function(){
    var date=dateFr($(this).val());
    window.location.href="?page=planning/volants/index.php&date="+date;
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
      url: 'planning/volants/ajax.validation.php',
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