$(document).ready(function() {
  $('#toggle-expected-staff').on('click', function() {
    $('body').toggleClass('show-expected-staff');
  });

  $(document).on('click', '.expected-staff-badge', function() {
    if (!$('body').hasClass('show-expected-staff') || $(this).find('input').length) {
      return;
    }

    var badge = $(this);
    var value = badge.text().trim();

    badge.html('<input type="number" min="0" step="1" class="expected-staff-input" value="' + value + '" />');
    var input = badge.find('input');
    input.focus().select();

    input.on('blur keydown', function(e) {
      if (e.type === 'keydown' && e.key !== 'Enter') {
        return;
      }

      var newValue = parseInt(input.val(), 10);
      if (isNaN(newValue) || newValue < 0) {
        newValue = parseInt(value, 10);
      }

      $.ajax({
        url: url('planning/update-expected-staff'),
        type: 'POST',
        data: {
          _token: $('input[name=_token]').val(),
          CSRFToken: $('#CSRFSession').val(),
          numero: badge.data('numero'),
          tableau: badge.data('tableau'),
          ligne: badge.data('ligne'),
          colonne: badge.data('colonne'),
          date: badge.data('date'),
          nb_attendu: newValue
        },
        success: function(response) {
          var result = JSON.parse(response);
          badge.text(result.nb_attendu !== undefined ? result.nb_attendu : newValue);
        },
        error: function() {
          badge.text(value);
        }
      });
    });
  });
});
