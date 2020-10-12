$( document ).ready(function() {
  $( "#datepicker" ).datepicker();

  $('input[name="date"]').on('change', function() {
    date = dateFr($(this).val());

    empty_time();
    empty_agents();

    $.ajax({
      url: '/ajax/interchange/slots',
      type: 'get',
      data: {date: date},
      success: function(data) {
        add_times_options(data.available_times);
        $('select[name="column"]').trigger( "change" );
      },
      error: function(xhr, ajaxOptions, thrownError) {
        if (xhr.responseText == 'no planning') {
          information("Aucun planning à cette date pour l'instant.<br/>Saisissez une autre date.", 'error');
        }

        if (xhr.responseText == 'no time') {
          information("Vous n'êtes pas affecté(e) au planning à cette date.<br/>Saisissez une autre date.", 'error');
        }

        if (xhr.responseText == 'existing_requester') {
          information("Vous avez déjà effectué une demande ce jour", 'error');
        }

        if (xhr.responseText == 'existing_asked') {
          information("Une demande vous concernant existe déjà ce jour", 'error');
        }
      }
    });

  });

  $('select[name="column"]').on('change', function() {
    column_id = $(this).val();

    empty_agents();

    $.ajax({
      url: '/ajax/interchange/agents',
      type: 'get',
      data: {column_id: column_id},
      success: function(data) {
        add_agent(data);
        $('.agents').show();
      },
      error: function(xhr, ajaxOptions, thrownError) {
        if (xhr.responseText == 'no agent') {
          information("Aucun agent sur ce créneau horaire", 'error');
        } else {
          information("Une erreur s'est produite lors de la récupération des agents", 'error');
        }
      }
    });
  });

  $('#interchange').on('submit', function() {
    if ($('input[name="id"]').val()) {
      return;
    }
    time_id = $('input[name="agent"]:checked').val();
    if (!time_id) {
      information('Choisisser un agent.', 'error');
      event.preventDefault();
    }
  });
});

function empty_time() {
  select = $('select[name="column"]');
  select.empty();
  select.prop('disabled', 'disabled');
}

function empty_agents() {
  $('.agents').hide();
  ul = $('#agents');
  ul.empty();
}

function add_agent(agents) {
  ul = $('#agents');
  $.each(agents, function(index, agent) {
    description = agent.name + ' ';
    if (agent.statut || agent.service) {
      description += '(';
      if (agent.statut) {
        description += agent.statut;
      }
      if (agent.service) {
        description += ' - ' + agent.service;
      }
      description += ')';
    }
    ul.append('<li><input type="radio" name="agent" value="' + agent.time_id + '"/>' + description + '</li>');
  });
}

function add_times_options(times) {
  select = $('select[name="column"]');
  $.each(times, function(index, time) {
    select.append('<option value="' + time.id + '">' + time.from + ' - ' + time.to + '</option>');
  });
  select.removeAttr('disabled');
}
