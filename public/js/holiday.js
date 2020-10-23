$( document ).ready(function() {
  checkdate('start');
  calculCredit();
  calculRestes();

  $('#perso_id').on('change', function() {
    document.location.href="/holiday/add/" + this.value;
  });

  $('select[name="debit"]').on('change', function() {
    calculRestes();
  });

  $('input[name="allday"]').on('click', function() {
    all_day();
  });

  $('input[name="halfday"]').on('click', function() {
    if ($(this).is(':checked')) {
      $('select[name="start_halfday"]').show();
      $('select[name="end_halfday"]').show();
    } else {
      $('select[name="start_halfday"]').hide();
      $('select[name="end_halfday"]').hide();
    }
  });

  $('#cancel').on('click', function() {
    document.location.href="/holiday";
  });

  $('#validate').on('click', function() {
    verifConges();
  });

  $('.checkdate').on('change', function() {
    if (!$('input[name="halfday"]').is(':checked')) {
      return;
    }

    priority = 'start';
    if ($(this).attr('name') == 'end_halfday') {
      priority = 'end';
    }
    checkdate(priority);
  });
});

function checkdate(priority) {
  debut = ddmmyyyy_to_date($('input[name="debut"]').val());
  fin = ddmmyyyy_to_date($('input[name="fin"]').val());

  if (!fin || !debut) {
    return;
  }

  start_half = $('select[name="start_halfday"]');
  end_half = $('select[name="end_halfday"]');

  if (debut.getTime() === fin.getTime()) {
    resetSelect();

    if (priority == 'end') {
      start_half.val(end_half.val());
    }
    end_half.val(start_half.val());
    return;
  }

  if (debut.getTime() < fin.getTime()) {
    $('select[name="start_halfday"] option[value="morning"]').remove();
    $('select[name="end_halfday"] option[value="afternoon"]').remove();
    return;
  }
}

function resetSelect() {
    start_val = $('select[name="start_halfday"]').val();
    $('select[name="start_halfday"]')
      .find('option')
      .remove()
      .end()
      .append('<option value="fullday">Journée complète</option>')
      .append('<option value="morning">Matin</option>')
      .append('<option value="afternoon">Après-midi</option>')
      .val(start_val);

    end_val = $('select[name="end_halfday"]').val();
    $('select[name="end_halfday"]')
      .find('option')
      .remove()
      .end()
      .append('<option value="fullday">Journée complète</option>')
      .append('<option value="morning">Matin</option>')
      .append('<option value="afternoon">Après-midi</option>')
      .val(end_val);
}
