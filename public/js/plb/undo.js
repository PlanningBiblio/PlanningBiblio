$(document).ready(function(){
  $(document).keydown(function(event) {
    if (event.ctrlKey && event.which === 90) {
      undo();
    }
  });

  $('#undo-action').on('click', function() {
      undo();
  });
});

function undo() {
  datepl = $('#date').val();
  site = $('#site').val();
  var _token = $('input[name="_token"]').val();

  $.ajax({
    url: url('ajax/planningjob/undo'),
    type: 'post',
    dataType: 'json',
    data: {date: datepl, site: site, _token: _token},
    success: function(result){
      if (result.remaining_undo == 0) {
        disableUndo();
      }

      $.each(result.actions, function( index, action ) {
        cellid = getCellId(action);
        if (action.action == 'cross') {
          cancelCross(action, cellid);
        }
        if (action.action == 'put') {
          cancelPut(action, cellid);
        }
        if (action.action == 'delete') {
          cancelDelete(action, cellid);
        }
        if (action.action == 'disable') {
          cancelDisable(action, cellid);
        }
        if (action.action == 'add') {
          cancelAdd(action, cellid);
        }
      });
    },
    error: function(result){
      CJInfo('Impossible d\'annuler la dernière action. Une erreur s\'est produite','error');
    }
  });
}

function cancelAdd(action, cellid) {
  var site = $('#site').val();
  $.each(action.perso_ids, function( i, perso_id ) {
    majPersoOrigine(perso_id);
    bataille_navale(action.position,action.date,action.beginning,action.end,0,0,0, site, null, null, cellid,0);
  });
}

function cancelDisable(action, cellid) {
  var site = $('#site').val();
  bataille_navale(action.position,action.date,action.beginning,action.end,0,0,0, site,0,-1,cellid,0);
}

function cancelDelete(action, cellid) {
  var added = 0;
  var site = $('#site').val();
  $.each(action.perso_ids, function( i, perso_id ) {
    added = 1;
    bataille_navale(action.position,action.date,action.beginning,action.end,perso_id,0,added, site,null,null,cellid,0);
  });
}

function cancelPut(action, cellid) {
  var site = $('#site').val();
  $.each(action.perso_ids, function( i, perso_id ) {
    majPersoOrigine(perso_id);
    bataille_navale(action.position,action.date,action.beginning,action.end,0,0,0, site,null,null,cellid,0);
  });
}

function cancelCross(action, cellid) {
  var site = $('#site').val();
  $.each(action.perso_ids, function( i, perso_id ) {
    majPersoOrigine(perso_id);
    bataille_navale(action.position,action.date,action.beginning,action.end,0,-1,0, site,null,null,cellid,0);
  });
}

function disableUndo() {
  $('#undo-action').addClass('isDisabled');
}

function enableUndo() {
  $('#undo-action').removeClass('isDisabled');
}

function getCellId(action) {
  cellid = $( "td[data-start='" + action.beginning
    + "'][data-end='" + action.end
    + "'][data-situation='" + action.position + "']" ).data('cell');

  return cellid;
}
