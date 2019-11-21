function resize_td() {
  body_width = $(window).width() - 80;
  td_width = Math.round(body_width / 5);

  $('.statedweek-table td').width(Math.round(body_width / 5) + 'px');
}

$( document ).ready(function() {
  resize_td();

  initializePlanning();

  $( window ).resize(function() {
    resize_td();
  });

  $("#pl-calendar").change(function(){
    var date = dateFr($(this).val());
    window.location.href="?date="+date;
  });

  $('.statedweek-table td').bind("contextmenu", function (event) {
    event.preventDefault();

    $('.context-list').empty();
    $('.context-menu-title').empty();
    setContextMenuTitle($(this));

    if ($(this).hasClass('time-slot')) {
      if (hasAgent($(this))) {
        setContextMenuEditOptions($(this));
      } else {
        showAvailables($(this));
      }
    }

    $(".context-menu").finish().toggle(100).css({
        top: event.pageY + "px",
        left: event.pageX + "px"
    });

  });

  $(document).bind("mousedown", function (e) {
    if (!$(e.target).parents(".context-menu").length > 0) {
      $(".context-menu").hide(100);
    }
  });

  $(document).on('click', '.add-agent', function() {
    cell_id = $(this).data('cell');
    agent_id = $(this).data('agent');
    $('#' + cell_id).append('<span>' + $(this).html() + '</span>');
    $('#' + cell_id).attr('data-agent', agent_id);

    addWorkingHours(cell_id);

    $(".context-menu").hide(100);
  });

  $(document).on('click', '.delete-agent', function() {
    cell_id = $(this).data('cell');

    removeWorkingHours(cell_id);

    $(".context-menu").hide(100);
  });

  function hasAgent(cell) {
    if (cell.has( "span" ).length) {
      return true;
    }

    return false;
  }

  function agentAlreadyPlaced(agent_id) {
    if ($('.table-placed td[data-agent="' + agent_id + '"]').length) {
      return true;
    }

    return false;
  }

  function showAvailables(cell) {
    $.ajax({
      url: '/ajax/statedweek/availables',
      type: 'post',
      dataType: 'json',
      success: function(data) {
        $.each(data, function(index, agent) {
          if (agentAlreadyPlaced(agent.id)) {
            return;
          }
          $('.context-list').append('<li data-agent="' + agent.id + '" data-cell="' + cell.attr('id') + '" class="add-agent">' + agent.fullname + '</li>');
        });
      },
      error: function() {
        console.log('error');
      }
    });
  }

  function setContextMenuEditOptions(cell) {
    agent_name = cell.children('span').html();
    agent_id = cell.data('agent');
    $('.context-list').append('<li data-agent="' + agent_id + '" data-cell="' + cell.attr('id') + '" class="delete-agent">Supprimer ' + agent_name + '</li>');
  }

  function setContextMenuTitle(cell) {
    if (cell.data('from') && cell.data('to')) {
      $('.context-menu-title').html(heureFr(cell.data('from')) + ' - ' + heureFr(cell.data('to')));
    }
  }

  function addWorkingHours(cell_id) {
    cell = $('#' + cell_id);
    agent_id = cell.data('agent');
    hour_from = cell.data('from');
    hour_to = cell.data('to');
    date = $('input[name="date"]').val();
    CSRFToken = $('input[name="CSRFToken"]').val();

    $.ajax({
      url: '/ajax/statedweek/add',
      type: 'post',
      data: {agent_id: agent_id, from: hour_from, to: hour_to, date: date, CSRFToken: CSRFToken},
      error: function() {
        cell.empty();
        cell.removeAttr('data-agent');
        alert("Une erreur est survenue lors de la mise à jour du planning");
      }
    });
  }

  function removeWorkingHours(cell_id) {
    cell = $('#' + cell_id);
    agent_id = cell.data('agent');
    date = $('input[name="date"]').val();
    CSRFToken = $('input[name="CSRFToken"]').val();

    $.ajax({
      url: '/ajax/statedweek/remove',
      type: 'post',
      data: {agent_id: agent_id, date: date, CSRFToken: CSRFToken},
      success: function() {
        cell.empty();
        cell.removeAttr('data-agent');
      },
      error: function() {
        alert("Une erreur est survenue lors de la mise à jour du planning");
      }
    });
  }

  function initializePlanning() {
    date = $('input[name="date"]').val();
    $.ajax({
      url: '/ajax/statedweek/placed',
      type: 'post',
      data: {date: date},
      success: function(agents) {
        $.each(agents, function(index, agent) {
          placeAgent(agent);
        });
      },
      error: function() {
        alert("Une erreur est survenue lors de la récupération du planning");
      }
    });
  }

  function placeAgent(agent) {
    id = agent.id;
    name = agent.name;
    from = agent.from;
    to = agent.to;

    $('#statedweek-planning td[data-from="' + from + '"][data-to="' + to +'"]').each(function() {
      if ($(this).is(':empty')) {
        $(this).append('<span>' + name + '</span>');
        $(this).attr('data-agent', id);
        return false;
      }
    });
  }
});
