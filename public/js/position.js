/**
@desc Javascript functions used by position's pages
*/

$(function() {

  // Permet de rendre la liste des étages triable
  if($('#floors_sortable').length) {
    Sortable.create(floors_sortable, {ghostClass: 'bg-blue', animation: 150}); 
  }

  $('#arrange-floors').on('submit', function(e) {
    e.preventDefault();
    // Supprime les lignes cachées lors du clic sur la corbeille
    $('#floors_sortable li:hidden').each(function() {
      $(this).remove();
    });

    // Enregistre les éléments du formulaire dans un tableau
    tab = new Array();
    $('#floors_sortable li').each(function() {
      var id = $(this).attr('id').replace('li_', '');
      tab.push({
        id: id,
        value: $("#valeur_"+id).text(),
        place: $(this).index()
      });
    });

    tab = JSON.stringify(tab);

    // Transmet le tableau à la page de validation ajax
    var _token = $('input[name=_token]').val();

    $.ajax({
      url: url('ajax/update-select-options'),
      type: 'post',
      dataType: 'json',
      data: {
        _token: _token,
        CSRFToken: $('#CSRFSession').val(),
        menu: 'etages',
        tab: tab,
      },

      success: function() {
        var current_val = $('#etage').val();
        $('#etage').empty();
        $('#etage').append('<option value=""></option>');

        $('#floors_sortable li').each(function() {
          var id = $(this).attr('id').replace('li_', '');
          var val = $('#valeur_' + id).text();
          var selected = id == current_val;
          var option = new Option(val, id, selected, selected);
          $('#etage').append(option);
        });

        $('#add-floor-modal').modal('hide');
        $('#etage').effect('highlight', null, 2000);
      },

      error: function() {
        alert('Erreur lors de l\'enregistrement des modifications');
      },
    });
  });

  $('#add-floor-modal').on('hidden.bs.modal', function() {
    $('#floors_sortable li:hidden').each(function() {
      $(this).show();
    });
  });

  // Suppression message invalidité lors du changement d'input
  $('#add-floor-text').on('input', function(e) {
    $('#add-floor-text').removeClass('is-invalid');
  })

  // Permet d'ajouter de nouveaux etages (clic sur le bouton ajouter)
  $('#add-floor').on('submit', function(e) {
    e.preventDefault();
    $('.invalid-feedback').text('Etage invalide');

    var text = sanitize_string($('#add-floor-text').val());
    if(!text){
      $('#add-floor-text').addClass('is-invalid');
      return;
    }

    // Vérifie si l'étage existe déjà
    var exist = false;
    $('#floors_sortable > li > span').each(function() {
      if($(this).text().toLowerCase() == text.toLowerCase()){
        $('.invalid-feedback').text('Un étage avec ce nom existe déjà.');
        $('#add-floor-text').addClass('is-invalid');
        exist = true;
        return;
      }
    });

    if(exist) {
      return;
    }

    var number = 1;
    while($('#li_'+number).length){
      number++;
    }
    $('#floors_sortable').append(
       '<li class="row row-sortable" id="li_' + number + '"> <i class="col-auto p-0 ps-2 bi bi-arrow-down-up"></i>'
      + '<span class="col-10 p-2" id="valeur_' + number + '">' + text + '</span>'
      + '<span class="col-1 ps-5" onclick="$(this).closest(\'li\').hide();"><i class="bi bi-trash3-fill"></i></span>'
      + '</li>');

    // Reset du champ texte une fois l'ajout effectué
    $('#add-floor-text').val(null);
    $(this).removeClass('was-validated');
  });

  // Permet de rendre la liste des groupes triable
  if($('#groups_sortable').length) {
    Sortable.create(groups_sortable, {ghostClass: 'bg-blue', animation: 150}); 
  }

  $('#arrange-groups').on('submit', function(e) {
    e.preventDefault();
    // Supprime les lignes cachées lors du clic sur la corbeille
    $('#groups_sortable li:hidden').each(function() {
      $(this).remove();
    });

    // Enregistre les éléments du formulaire dans un tableau
    tab = new Array();
    $('#groups_sortable li').each(function() {
      var id = $(this).attr('id').replace('li_', '');
        tab.push({
          id: id,
          value: $("#valeur_"+id).text(),
          place: $(this).index()
        });
    });

    tab = JSON.stringify(tab);

    // Transmet le tableau à la page de validation ajax
    var _token = $('input[name=_token]').val();

    $.ajax({
      url: url('ajax/update-select-options'),
      type: 'post',
      dataType: 'json',
      data: {
        _token: _token,
        CSRFToken: $('#CSRFSession').val(),
        menu: 'groupes',
        tab: tab,
      },

      success: function() {
        var current_val = $('#groupe').val();
        $('#groupe').empty();
        $('#groupe').append('<option value=""></option>');
        
        $('#groups_sortable li').each(function() {
          var id=$(this).attr('id').replace('li_', '');
          var val = $('#valeur_' + id).text();
          var selected = id == current_val;
          var option = new Option(val, id, selected, selected);
          $('#groupe').append(option);
        });
        $('#add-group-modal').modal('hide');
        $('#groupe').effect('highlight', null, 2000);
      },

      error: function() {
        alert('Erreur lors de l\'enregistrement des modifications');
      }
    });
  });

  $('#add-group-modal').on('hidden.bs.modal', function() {
    $('#groups_sortable li:hidden').each(function() {
      $(this).show();
    });
  });

  // Suppression message invalidité lors du changement d'input
  $('#add-group-text').on('input', function(e) {
    $('#add-group-text').removeClass('is-invalid');
  })

  // Permet d'ajouter de nouveaux groupe (clic sur le bouton ajouter)
  $('#add-group').on('submit', function(e) {
    e.preventDefault();
    $('.invalid-feedback').text('Groupe invalide.');

    var text=sanitize_string($("#add-group-text").val());
    if(!text){
      $('#add-group-text').addClass('is-invalid');
      return;
    }

    // Vérifie si le groupe existe déjà
    var exist = false;

    $('#groups_sortable > li > span').each(function() {
      if($(this).text().toLowerCase() == text.toLowerCase()){
        $('.invalid-feedback').text('Un groupe avec ce nom existe déjà.');
        $('#add-group-text').addClass('is-invalid');
        exist = true;
        return;
      }
    });

    if(exist) {
      return;
    }

    var number = 1;
    while($('#li_'+number).length){
      number++;
    }

    $('#groups_sortable').append(
       '<li class="row row-sortable" id="li_' + number + '">'
      + '<i class="col-auto p-0 ps-2 bi bi-arrow-down-up"></i>'
      + '<span class="col-10 p-2" id="valeur_' + number + '">' + text + '</span>'
      + '<span class="col-1 ps-5" onclick="$(this).closest(\'li\').hide();">'
      + '<i class="bi bi-trash3-fill"></i></span>'
      + '</li>');

    // Reset du champ texte une fois l'ajout effectué
    $('#add-group-text').val(null);
    $(this).removeClass('was-validated');
  });

  $('.form-check-input').on('change', function(e){
    $('.form-check-input.is-invalid').removeClass('is-invalid');
  })

  $('#edit-position-form').on('submit', function(e) {

    // Check that the parameters are compatible with each other before submitting the form and displays an error message if they are not
    if ($('#lunch1').prop('checked')) {
      if ($('#statistics1').prop('checked')) {
        $('#lunch1').addClass('is-invalid');
        $('#statistics1').addClass('is-invalid');
        return false;
      }
      if ($('#quota_sp1').prop('checked')) {
        $('#lunch1').addClass('is-invalid');
        $('#quota_sp1').addClass('is-invalid');
        return false;
      }
    }

    if ($('#bloq2').prop('checked')) {
      if ($('#statistics1').prop('checked')) {
        $('#bloq2').addClass('is-invalid');
        $('#statistics1').addClass('is-invalid');
        return false;
      }
      if ($('#quota_sp1').prop('checked')) {
        $('#bloq2').addClass('is-invalid');
        $('#quota_sp1').addClass('is-invalid');
        return false;
      }
    }
    return true;

    /*
    TODO JS : 
    * Générer les messages d'alertes en anglais, et les traduire en français
    * Remplacer le CJInfo par les alertes BootStrap
    * Gérer les cas suivants en reprenant l'exemple ci-dessus.

    Incompatibilités :
    * Bloquant = non
      * Quota de SP doit être à non
      * statistiques doit être à non
      * --> l'alerte doit être au niveau du paramètre "bloquant"

    * Poste repas = oui
      * Quota de SP doit être à non
      * statistiques doit être à non
      * --> l'alerte doit être au niveau du paramètre "sans repas"

    TODO templates :
    * Ajouter les balises "label" avec la propriété "title" (voir ce qui est fait sur le paramètre "poste repas")
    * Générer les messages en anglais et la traduction en français pour les proriétés title et le contenu des labels.
    */
  });

});