$( document ).ready(function() {
  checkdate('start');
  sethours();
  calculCredit();
});

$(function(){
  $('#perso_id').on('change', function() {
    if (!window.location.href.includes('recup_pose.php')) {
      document.location.href="/holiday/new/" + this.value;
    }
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
    if (!$('input[name="halfday"]').is(':checked')) {
      calculCredit();
      return;
    }

    priority = 'start';
    if ($(this).attr('name') == 'end_halfday') {
      priority = 'end';
    }
    checkdate(priority);

    sethours();

    // WARNING : Keep the function calculCredit after last checkdate().
    calculCredit();
  });
});

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

function sethours() {
  // Return if halfday option is disabled
  if ($('select[name="start_halfday"]').length < 1) {
    return;
  }

  // Return if it's a comp-time
  if ($('#is-recover').val() == '1') {
    return;
  }

  // Set default values and return if halfday dropdowns are hidden
  if ($('select[name="start_halfday"]').is(':visible') == false) {
    $('#hre_debut_select').val('');
    $('#hre_fin_select').val('');
    return;
  }

  // Get values from DOM
  var start = dateFr($('#debut').val().trim());
  var end = dateFr($('#fin').val().trim());
  var start_half = $('select[name="start_halfday"]');
  var end_half = $('select[name="end_halfday"]');
  var agent = $('#perso_id').val();

  // Default values for morning_end and afternoon_start
  var morning_end = '12:00:00';
  var afternoon_start = '12:00:00';

  $.ajax({
    url: "/ajax/holiday-halfday-hours",
    data: {start: start, end: end, agent: agent},
    dataType: "json",
    type: "get",
    async: false,
    success: function(result) {
      morning_end = result['morning_end'];
      afternoon_start = result['afternoon_start'];
    },
    error: function(result) {
    }
  });

  // Set hours for 1 day
  if (start == end) {
    if (start_half.val() == 'fullday' ) {
      $('#hre_debut_select').val('');
      $('#hre_fin_select').val('');
    }
    if (start_half.val() == 'morning' ) {
      $('#hre_debut_select').val('');
      $('#hre_fin_select').val(morning_end);
    }
    if (start_half.val() == 'afternoon' ) {
      $('#hre_debut_select').val(afternoon_start);
      $('#hre_fin_select').val('');
    }
  // Set hours for several days
  } else {
    if (start_half.val() == 'afternoon' ) {
      $('#hre_debut_select').val(afternoon_start);
    } else {
      $('#hre_debut_select').val('');
    }
    if (end_half.val() == 'morning' ) {
      $('#hre_fin_select').val(morning_end);
    } else {
      $('#hre_fin_select').val('');
    }
  }
}
