$(document).ready(function(){
  $(document).keydown(function(event) {
    if (event.ctrlKey && event.which === 89) {
      redo();
    }
  });

  $('#redo-action').on('click', function() {
      redo();
  });
});

function redo() {
  datepl = $('#date').val();
  site = $('#site').val();
  CSRFToken = $('#CSRFSession').val();

  $.ajax({
    url: url('ajax/planningjob/redo'),
    type: 'post',
    dataType: 'json',
    data: {date: datepl, site: site, CSRFToken: CSRFToken},
    success: function(result){
      if (result.remaining_redo == 0) {
        disableRedo();
      }

      $.each(result.actions, function( index, action ) {
        cellid = getCellId(action);
        if (action.action == 'cross') {
          cross(action, cellid);
        }
        if (action.action == 'put') {
          put(action, cellid);
        }
        if (action.action == 'delete') {
          Delete(action, cellid);
        }
        if (action.action == 'disable') {
          disable(action, cellid);
        }
        if (action.action == 'add') {
          addone(action, cellid);
        }
      });
    },
    error: function(result){
      CJInfo('Impossible de répéter la dernière action. Une erreur s\'est produite','error');
    }
  });
}

function addone(action, cellid) {
  $.each(action.perso_ids, function( i, perso_id ) {
    bataille_navale(action.position,action.date,action.beginning,action.end,perso_id,0,1,"1",null,null,cellid,0);
  });
}

function disable(action, cellid) {
    bataille_navale(action.position,action.date,action.beginning,action.end,0,0,0,"1",1,1,cellid,0);
}

function Delete(action, cellid) {
  $.each(action.perso_ids, function( i, perso_id ) {
    majPersoOrigine(perso_id);
    bataille_navale(action.position,action.date,action.beginning,action.end,0,0,0,"1",null,null,cellid,0);
  });
}

function put(action, cellid) {
  $.each(action.perso_ids, function( i, perso_id ) {
    majPersoOrigine(perso_id);
    bataille_navale(action.position,action.date,action.beginning,action.end,perso_id,0,0,"1",null,null,cellid,0);
  });
}

function cross(action, cellid) {
  $.each(action.perso_ids, function( i, perso_id ) {
    majPersoOrigine(perso_id);
    bataille_navale(action.position,action.date,action.beginning,action.end,0,1,0,"1",null,null,cellid,0);
  });
}

function disableRedo() {
  $('#redo-action').addClass('isDisabled');
}

function enableRedo() {
  $('#redo-action').removeClass('isDisabled');
}
