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

  $( "#confirm-lock" ).dialog({
    autoOpen: false,
    resizable: false,
    width: 400,
    modal: true,
    buttons: {
      "Continuer": function() {
        lockPlanning();
        $( this ).dialog( "close" );
      },
      "Annuler": function() {
        $('#empty-plannings').empty();
        $('#confirm-lock p.warning').hide();
        $( this ).dialog( "close" );
      }
    }
  });

  $( "#confirm-unlock" ).dialog({
    autoOpen: false,
    resizable: false,
    width: 400,
    modal: true,
    buttons: {
      "Continuer": function() {
        unlockPlanning();
        $( this ).dialog( "close" );
      },
      "Annuler": function() {
        $( this ).dialog( "close" );
      }
    }
  });

  $('#lock').on('click', function() {
    date = $('input[name="date"]').val();

    $.ajax({
      url: '/ajax/statedweek/emptyplanning',
      type: 'post',
      data: { date: date },
      success: function(data) {
        if (data.length > 0) {
          $.each(data, function(index, value) {
            $("#empty-plannings").append('<li>' + value + '</li>');
            $("#confirm-lock p.warning").show();
          });

        }
        $("#confirm-lock").dialog( "open" );
      },
      error: function() {
        alert("Une erreur est survenue lors de la vérification des plannings");
      }
    });
  });

  $('#unlock').on('click', function() {
      $("#confirm-unlock").dialog( "open" );
  });

  $('.edit-time').on('click', function() {
    if ($('input[name="locked"]').val() == '1') {
      return;
    }

    $(this).hide();
    $(this).parent().find('span.editable').show();
    $(this).parent().find('span.view-time').hide();
  });

  $('.valid-time').on('click', function() {
    from = $(this).parent().find('input.time-from').val();
    to = $(this).parent().find('input.time-to').val();
    breaktime = $(this).parent().find('input.time-break').val();
    date = $('input[name="date"]').val();
    agent_id = $('td[data-timeid="' + $(this).parent().parent().attr('id') + '"]').data('agent');
    job_name = $('td[data-timeid="' + $(this).parent().parent().attr('id') + '"]').data('job');

    data = {
      agent_id: agent_id,
      date: date,
      job_name: job_name,
      from: from,
      to: to,
      breaktime: breaktime
    };

    $.ajax({
      url: '/ajax/statedweekjob/update',
      type: 'post',
      data: data,
      error: function() {
        alert("Une erreur est survenue lors de la mise à jour du planning");
      }
    });

    from = from ? from : '--:--';
    to = to ? to : '--:--';

    content = from + ' - ' + to;
    if (breaktime) {
      content += ' (' + breaktime + ')';
    }

    $(this).parent().parent().find('span.view-time').html(content).show();
    $(this).parent().parent().find('.edit-time').show();
    $(this).parent().hide();
  });

  $("#pl-calendar").change(function(){
    var date = dateFr($(this).val());
    window.location.href="?date="+date;
  });

  $('.statedweek-table td').bind("contextmenu", function (event) {
    event.preventDefault();

    if ($('input[name="locked"]').val() == '1') {
      return;
    }

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
    $('#' + cell_id).append($(this).html());
    $('#' + cell_id).attr('data-agent', agent_id);

    if ($(this).hasClass('absent')) {
      $('#' + cell_id).addClass('absent');
    }

    if ($(this).hasClass('partially-absent')) {
      $('#' + cell_id).addClass('partially-absent');
    }

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

  function maxJobsReached(agent_id) {
    if ($('.table-placed td[data-agent="' + agent_id + '"]').length) {
      return true;
    }

    pause2 = $('input[name="pause2"]').val();
    nb_job = $('.table-placed-job td[data-agent="' + agent_id + '"]').length

    if (pause2 == 1 && nb_job > 2) {
      return true;
    }

    if (pause2 == 0 && nb_job > 1) {
      return true;
    }

    return false;

  }

  function agentAlreadyPlaced(agent_id, job_name) {
    if (job_name) {
      return maxJobsReached(agent_id);
    }

    if ($('.table-placed td[data-agent="' + agent_id + '"]').length) {
      return true;
    }

    if ($('.table-placed-job td[data-agent="' + agent_id + '"]').length) {
      return true;
    }

    return false;
  }

  function showAvailables(cell) {
    date = $('input[name="date"]').val();
    from = cell.data('from');
    to = cell.data('to');
    job_name = cell.data('job');

    $.ajax({
      url: '/ajax/statedweek/availables',
      type: 'post',
      dataType: 'json',
      data: {date: date, from: from, to: to, job_name: job_name},
      success: function(data) {
        $.each(data, function(index, agent) {
          if (agentAlreadyPlaced(agent.id, job_name)) {
            return;
          }

          item = $("<li></li>");
          item.attr('data-agent', agent.id);
          item.attr('data-cell', cell.attr('id'));

          if (job_name) {
            item.attr('data-job', cell.data('job'));
          }

          item.addClass('add-agent');
          item.append('<span>' + agent.fullname + '</span>');

          if (agent.absent) {
            item.addClass('absent');
            item.children('span').addClass('absent');
            item.append('<i> - absent(e)</i>');
          }

          if (agent.partially_absent) {
            item.addClass('partially-absent');
            item.children('span').addClass('partially-absent');
            item.append('<i> - absent(e)</i>');
            $.each(agent.partially_absent, function(index, absence) {
              item.children('i').append(' de ' + absence.from + ' à ' + absence.to);
            });
          }

          if (agent.holiday) {
            item.addClass('absent');
            item.children('span').addClass('absent');
            item.append('<i> - Congés</i>');
          }

          $('.context-list').append(item);

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

    if (cell.data('jobdesc')) {
      $('.context-menu-title').html(cell.data('jobdesc'));
    }
  }

  function addWorkingHours(cell_id) {
    cell = $('#' + cell_id);
    agent_id = cell.data('agent');
    hour_from = cell.data('from');
    hour_to = cell.data('to');
    job_name = cell.data('job');
    pause = cell.data('pause');
    date = $('input[name="date"]').val();

    url = '/ajax/statedweek/add';
    data = {agent_id: agent_id, from: hour_from, to: hour_to, date: date};

    var time_cell;
    if (job_name) {
      url = '/ajax/statedweekjob/add';
      time_cell_id = cell.data('timeid');
      time_cell = $('#' + time_cell_id);

      data = {
        agent_id: agent_id,
        job_name: job_name,
        date: date,
        from: time_cell.find('input.time-from').val(),
        to: time_cell.find('input.time-to').val(),
        breaktime: time_cell.find('input.time-break').val()
      };
    }

    if (pause) {
      url = '/ajax/statedweekpause/add';
      data = {agent_id: agent_id, date: date};
    }

    $.ajax({
      url: url,
      type: 'post',
      data: data,
      success: function() {
        if (time_cell) {
          time_cell.find('a.edit-time').show();
        }
      },
      error: function() {
        cell.empty();
        cell.removeAttr('data-agent');
        alert("Une erreur est survenue lors de la mise à jour du planning");
      }
    });

    countByPlace();
  }

  function removeWorkingHours(cell_id) {
    cell = $('#' + cell_id);
    agent_id = cell.data('agent');
    job_name = cell.data('job');
    pause = cell.data('pause');
    date = $('input[name="date"]').val();

    url = '/ajax/statedweek/remove';
    data = {agent_id: agent_id, date: date};

    if (job_name) {
      url = '/ajax/statedweekjob/remove';
      data = {agent_id: agent_id, job_name: job_name, date: date};
    }

    if (pause) {
      url = '/ajax/statedweekpause/remove';
      data = {agent_id: agent_id, date: date};
    }

    $.ajax({
      url: url,
      type: 'post',
      data: data,
      success: function() {
        cell.empty();
        cell.removeAttr('data-agent');
        timeid = cell.data('timeid');
        $('#' + timeid + ' span.view-time').text('');
        $('#' + timeid + ' a.edit-time').hide();
      },
      error: function() {
        alert("Une erreur est survenue lors de la mise à jour du planning");
      }
    }).done(function() {
      countByPlace();
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
        countByPlace();
      },
      error: function() {
        alert("Une erreur est survenue lors de la récupération du planning");
      }
    });
  }

  function placeAgent(agent) {
    if (agent.place == 'planning') {
      placeOnPlanning(agent);
    }

    if (agent.place == 'job') {
      placeOnJob(agent);
    }

    if (agent.place == 'pause') {
      placeOnPause(agent);
    }
  }

  function placeOnPause(agent) {
    id = agent.id;
    name = agent.name;

    $('#statedweek-poste td[data-pause="1"]').each(function() {
      if ($(this).is(':empty')) {
        item = $('<span></span>');
        item.append(name);

        $(this).append(item);
        $(this).attr('data-agent', id);

        return false;
      }
    });
  }

  function placeOnJob(agent) {
    id = agent.id;
    name = agent.name;
    job_name= agent.job_name;
    from = agent.from;
    to = agent.to;
    breaktime = agent.breaktime;

    $('#statedweek-poste td[data-job="' + job_name + '"]').each(function() {
      cell = $(this);
      if (cell.is(':empty')) {
        item = $('<span></span>');
        item.append(name);

        cell.append(item);
        cell.attr('data-agent', id);

        if (agent.absent) {
          cell.addClass('absent');
          cell.append(' <i> - absent(e)</<i>');
        }

        if (agent.partially_absent) {
          cell.addClass('partially-absent');
          cell.append(' <i> - absent(e)</<i>');
          $.each(agent.partially_absent, function(index, absence) {
            cell.children('i').append(' de ' + absence.from + ' à ' + absence.to);
          });
        }

        if (agent.holiday) {
          cell.addClass('absent');
          cell.children('span').addClass('absent');
          cell.append('<i> - Congés</i>');
        }

        //Working hours.
        time_cell_id = cell.data('timeid');
        time_cell = $('#' + time_cell_id);

        if (from) {
          time_cell.find('input.time-from').val(from);
        }

        if (to) {
          time_cell.find('input.time-to').val(to);
        }


        from = from ? from : '--:--';
        to = to ? to : '--:--';

        content = from + ' - ' + to;
        if (breaktime) {
          content += ' (' + breaktime + ')';
          time_cell.find('input.time-break').val(breaktime);
        }

        time_cell.find('span.view-time').html(content);
        time_cell.find('a.edit-time').show();

        return false;
      }
    });
  }

  function placeOnPlanning(agent) {
    id = agent.id;
    name = agent.name;
    from = agent.from;
    to = agent.to;

    $('#statedweek-planning td[data-from="' + from + '"][data-to="' + to +'"]').each(function() {
      cell = $(this);
      if (cell.is(':empty')) {
        item = $('<span></span>');
        item.append(name);

        cell.append(item);
        cell.attr('data-agent', id);

        if (agent.absent) {
          cell.addClass('absent');
          cell.append(' <i> - absent(e)</<i>');
        }

        if (agent.partially_absent) {
          cell.addClass('partially-absent');
          cell.append(' <i> - absent(e)</<i>');
          $.each(agent.partially_absent, function(index, absence) {
            cell.children('i').append(' de ' + absence.from + ' à ' + absence.to);
          });
        }

        if (agent.holiday) {
          cell.addClass('absent');
          cell.children('span').addClass('absent');
          cell.append('<i> - Congés</i>');
        }

        return false;
      }
    });
  }

  function countByPlace() {
    slots = [
      'first-slot', 'second-slot', 'third-slot',
      'first-job', 'second-job', 'third-job'
    ];

    slots.forEach(function(slot) {
      count = 0;
      $('.' + slot).each(function() {
        agent_id = $(this).attr('data-agent');
        absent = $(this).hasClass('absent');
        if (!absent && agent_id) {
          count++;
        }
      });
      $('#' + slot + '-count').html('&nbsp;(' + count + ')');
    });
  }

  function lockPlanning() {
    date = $('input[name="date"]').val();

    $.ajax({
      url: '/ajax/statedweek/lock',
      type: 'post',
      data: {date: date, lock: true},
      success: function() {
        $('#lock').hide();
        $('#unlock').show();
        $('input[name="locked"]').val('1');
      },
      error: function() {
        alert("Une erreur est survenue lors du vérouillage des plannings");
      }
    });
  }

  function unlockPlanning() {
    date = $('input[name="date"]').val();

    $.ajax({
      url: '/ajax/statedweek/lock',
      type: 'post',
      data: {date: date},
      success: function() {
        $('#unlock').hide();
        $('#lock').show();
        $('input[name="locked"]').val('0');
      },
      error: function() {
        alert("Une erreur est survenue lors du déverouillage des plannings");
      }
    });
  }
});
