function generationFormatDateFr(isoDate) {
  var parts = isoDate.split('-');
  return parts[2] + '/' + parts[1] + '/' + parts[0];
}

function generationResetWizard() {
  $('#generation-step-1, #generation-step-2, #generation-step-2b, #generation-step-3, #generation-step-manual').hide();
  $('#generation-step-1').show();
  $('#generation-next-1').show();
  $('#generation-next-2').hide();
  $('#generation-launch').hide();
  $('#generation-launch-manual').hide();
  $('#generation-step-3-content').empty();
  $('#generation-manual-json').val('');
}

$(document).ready(function() {
  $('#generer-planning-btn').on('click', function() {
    generationResetWizard();
    $('#generation-wizard-modal').modal('show');
  });

  $('#generation-next-1').on('click', function() {
    var debut = $('#generation-date-debut').val();
    var fin = $('#generation-date-fin').val();

    if (!debut || !fin) {
      stackAlert('Merci de renseigner les deux dates.', 'error');
      return;
    }

    if (fin < debut) {
      stackAlert('La date de fin doit être postérieure à la date de début.', 'error');
      return;
    }

    $('#generation-step-2-range').text(generationFormatDateFr(debut) + ' - ' + generationFormatDateFr(fin));
    $('#generation-step-1').hide();
    $('#generation-step-2').show();
    $('#generation-next-1').hide();
    $('#generation-next-2').show();
  });

  $('#generation-next-2').on('click', function() {
    $('#generation-step-2').hide();
    $('#generation-step-2b').show();
    $('#generation-next-2').hide();
  });

  $('#generation-mode-auto').on('click', function() {
    var debut = $('#generation-date-debut').val();
    var fin = $('#generation-date-fin').val();
    var site = $('#generation-site').val();

    $.ajax({
      url: url('admin/planning-generation/active-tables'),
      type: 'GET',
      data: { date_debut: debut, date_fin: fin, site: site },
      dataType: 'json',
      success: function(tableaux) {
        var content = $('#generation-step-3-content');
        content.empty();

        if (!tableaux.length) {
          content.append('<p class="important">Aucun tableau actif trouvé sur cette période.</p>');
        } else {
          tableaux.forEach(function(tableau) {
            var block = $('<div class="mb-3"></div>');
            block.append('<h5>' + tableau.nom + '</h5>');
            $.each(tableau.postes, function(posteId, posteNom) {
              block.append(
                '<div class="form-check">' +
                '<input class="form-check-input generation-poste-check" type="checkbox" checked ' +
                'data-numero="' + tableau.numero + '" data-poste-id="' + posteId + '" id="generation-poste-' + tableau.numero + '-' + posteId + '">' +
                '<label class="form-check-label" for="generation-poste-' + tableau.numero + '-' + posteId + '">' + posteNom + '</label>' +
                '</div>'
              );
            });
            content.append(block);
          });
        }

        $('#generation-step-2b').hide();
        $('#generation-step-3').show();
        $('#generation-launch').show();
      },
      error: function() {
        stackAlert('Impossible de récupérer les tableaux actifs sur cette période.', 'error');
      }
    });
  });

  $('#generation-mode-manual').on('click', function() {
    $('#generation-step-2b').hide();
    $('#generation-step-manual').show();
    $('#generation-launch-manual').show();
  });

  $('#generation-launch').on('click', function() {
    var debut = $('#generation-date-debut').val();
    var fin = $('#generation-date-fin').val();
    var site = $('#generation-site').val();

    var excluded = {};
    $('.generation-poste-check:not(:checked)').each(function() {
      var numero = $(this).data('numero');
      var posteId = $(this).data('poste-id');
      excluded[numero] = excluded[numero] || [];
      excluded[numero].push(posteId);
    });

    $('#generation-launch').prop('disabled', true);

    $.ajax({
      url: url('admin/planning-generation/generate'),
      type: 'POST',
      data: {
        _token: $('input[name="_token"]').val(),
        CSRFToken: $('input[name="CSRFToken"]').val(),
        date_debut: debut,
        date_fin: fin,
        site: site,
        excluded_postes: JSON.stringify(excluded)
      },
      dataType: 'json',
      success: function(result) {
        if (result.error) {
          stackAlert(result.error, 'error');
          $('#generation-launch').prop('disabled', false);
          return;
        }
        $('#generation-wizard-modal').modal('hide');
        window.location.reload();
      },
      error: function(jqXHR) {
        var message = 'Impossible de lancer la génération du planning.';
        try {
          var parsed = JSON.parse(jqXHR.responseText);
          if (parsed.error) {
            message = parsed.error;
          }
        } catch (e) {}
        stackAlert(message, 'error');
        $('#generation-launch').prop('disabled', false);
      }
    });
  });

  $('#generation-launch-manual').on('click', function() {
    var debut = $('#generation-date-debut').val();
    var fin = $('#generation-date-fin').val();
    var site = $('#generation-site').val();
    var manualJson = $('#generation-manual-json').val();

    try {
      JSON.parse(manualJson);
    } catch (e) {
      stackAlert('Le JSON saisi est invalide.', 'error');
      return;
    }

    $('#generation-launch-manual').prop('disabled', true);

    $.ajax({
      url: url('admin/planning-generation/generate'),
      type: 'POST',
      data: {
        _token: $('input[name="_token"]').val(),
        CSRFToken: $('input[name="CSRFToken"]').val(),
        date_debut: debut,
        date_fin: fin,
        site: site,
        manual_json: manualJson
      },
      dataType: 'json',
      success: function(result) {
        if (result.error) {
          stackAlert(result.error, 'error');
          $('#generation-launch-manual').prop('disabled', false);
          return;
        }
        $('#generation-wizard-modal').modal('hide');
        window.location.reload();
      },
      error: function(jqXHR) {
        var message = 'Impossible de lancer la génération du planning.';
        try {
          var parsed = JSON.parse(jqXHR.responseText);
          if (parsed.error) {
            message = parsed.error;
          }
        } catch (e) {}
        stackAlert(message, 'error');
        $('#generation-launch-manual').prop('disabled', false);
      }
    });
  });

  // Rafraîchit automatiquement la page tant qu'une génération est en cours,
  // pour refléter le passage à "succès"/"échec" une fois le traitement asynchrone terminé.
  if ($('.generation-status-en_cours').length) {
    setTimeout(function() {
      window.location.reload();
    }, 5000);
  }

  $('.generation-delete-btn').on('click', function() {
    var id = $(this).data('id');

    if (!window.confirm('Voulez-vous vraiment supprimer ce planning généré ?')) {
      return;
    }

    $.ajax({
      url: url('admin/planning-generation/' + id),
      type: 'DELETE',
      data: {
        _token: $('input[name="_token"]').val(),
        CSRFToken: $('input[name="CSRFToken"]').val()
      },
      success: function() {
        window.location.reload();
      },
      error: function(jqXHR) {
        var message = 'Impossible de supprimer ce planning généré.';
        try {
          var parsed = JSON.parse(jqXHR.responseText);
          if (parsed.error) {
            message = parsed.error;
          }
        } catch (e) {}
        stackAlert(message, 'error');
      }
    });
  });
});
