$( document ).ready(function() {
  checkdate('start');
  calculCredit();
});

$(function(){
  $('select[name="perso_id"]').on('change', function() {
    if (window.location.href.includes('recup_pose.php')) {
      return;
    }

    id = $('#id').val();
    // Mode edition. No reload.
    if (id) {
      return;
    }

    document.location.href="/holiday/new/" + this.value;
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
    document.location.href="/holiday/index";
  });

  $('#validate').on('click', function() {
    verifConges();
  });

  $('input[name=halfday]').change(function() {
    if ($(this).is(':checked') == false) {
      $('#hre_debut_select').val('');
      $('#hre_fin_select').val('');
    }
  });
  
  $('.checkdate').on('change', function() {
    dateChange(this);
  });
});

function dateChange(obj) {
    if (!$('input[name="halfday"]').is(':checked')) {
      calculCredit();
      return;
    }

    priority = 'start';
    if ($(obj).attr('name') == 'end_halfday') {
      priority = 'end';
    }
    checkdate(priority);


    // WARNING : Keep the function calculCredit after last checkdate().
    calculCredit();
}

function checkdate(priority) {
  debut = ddmmyyyy_to_date($('input[name="debut"]').val());
  fin = ddmmyyyy_to_date($('input[name="fin"]').val());

  // Return if the first date is not given
  if (!debut) {
    return;
  }

  // If end date is not given, end = start
  if (!fin) {
    fin = debut;
    $('#fin').val($('#debut').val());
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
