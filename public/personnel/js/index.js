/**
Planno
Licence GNU/GPL (version 2 et au dela)
@see README.md et LICENSE

@file public/personnel/js/index.js

Description :
Fichier regroupant les fonctions JavaScript utiles à la gestion des agents (index.php)
*/


$(function() {

    var allFields = $( [] );

    $( "#dialog-form" ).dialog({
        autoOpen: false,
        height: 660,
        width: 1000,
        modal: true,
        buttons: {
            "Enregistrer": function() {

                // List
                var list = [];
                $('.checkbox:visible:checked').each(function(){
                    list.push($(this).val());
                });
                list = JSON.stringify(list);

                $.ajax({
                    url: url('personnel/ajax.update.php'),
                    type: "post",
                    dataType: "json",
                    data: {
                        CSRFToken: $('#CSRFSession').val(),
                        // Main tab
                        actif: $('#actif').val(),
                        contrat: $('#contrat').val(),
                        heures_hebdo: $('#heures_hebdo').val(),
                        heures_travail: $('#heures_travail').val(),
                        service: $('#service').val(),
                        statut: $('#statut').val(),
                        // Skills tab
                        postes: $('#postes').val(),
                        list: list,
                        },
                    success: function(result){
                        if (result=='ok') {
                            var msg = 'Les agents ont été modifés avec succès';
                            var msgType = 'success';
                        } else {
                            var msg = result;
                            var msgType = 'error';
                        }
                        location.href = url('agent?msg=' + msg + '&msgType=' + msgType);
                        },
                    error: function(){
                        location.href = url('agent?msg=Une erreur est survenue lors de la modification des agents&msgType=error');
                        }
                    });
                },

            "Annuler": function() {
                $( this ).dialog( "close" );
            }
        },

        close: function() {
            allFields.val( "" ).removeClass( "ui-state-error" );
            $('.validateTips').text("");
        }
    });
});

function agent_list() {

    if (!$('.checkbox:visible:checked').length) {
        alert('Veuillez sélectionner un ou plusieurs agents.');
        return false;
    }

    // Action
    var action = $('#action').val();

    switch(action) {

        case 'delete' :

            if (!confirm('Etes vous sûr(e) de vouloir suppimer les agents sélectionnés ?')) {
                break;
            }

            // List
            var list = [];
            $('.checkbox:visible:checked').each(function(){
                list.push($(this).val());
            });
            list = JSON.stringify(list);

            $.ajax({
                url: url('personnel/ajax.delete.php'),
                type: "post",
                dataType: "json",
                data: {list: list, CSRFToken: $('#CSRFSession').val()},
                success: function(){
                    location.href = url('agent?msg=Les agents ont été supprimés avec succès&msgType=success');
                    },
                error: function(){
                    location.href = url('agent?msg=Une erreur est survenue lors de la suppresion des agents&msgType=error');
                    }
                });

            break;

        case 'edit' :
           $( "#dialog-form" ).dialog( "open" );

            break;
    }
}

function deleteAgent() {
    var agentId = $('#agentId').val();
    var date = $('#delete-date').val();
    var CSRFToken = $('#CSRFSession').val();

    var data = {id: agentId, CSRFToken: CSRFToken};
    if ($('#permanentDelete').val() == 0) {
        data.date = date;
    }

    $.ajax({
        url : url('agent'),
        type : 'DELETE',
        data : data,
        success: function(response) {
            if (response == "level 1 delete OK") {
                var msg = encodeURI("L'agent a bien été supprimé.");
                parent.location.href=url('agent') + '?msg=' + msg + '&msgType=success';
            } else if (response == "permanent delete OK") {
                var msg = encodeURI("L'agent a été supprimé définitivement.");
                parent.location.href=url('agent') + '?msg=' + msg + '&msgType=success';
            } else {
                var msg = encodeURI("Une erreur est survenue lors de la suppresion de l'agent.");
                parent.location.href=url('agent') + '?msg=' + msg + '&msgType=error';
            }
        },
        error: function() {
           var msg = encodeURI("Une erreur est survenue lors de la suppresion de l'agent.");
           parent.location.href=url('agent') + '?msg=' + msg + '&msgType=error';
        }
    });
}

$(document).ready(function(){

    $('.delete-agent').on('click',function(){ 
        var agentId = $(this).data('id');
        var agentName = $(this).data('name');

        $('#agentId').val(agentId);
        $('#deleteDialog').dialog('open');

        if ($('#showAgentSelect').val() != 'Supprimé') {
            $('#permanentDelete').val(0);
            $('#c-text').text("Êtes-vous sûr(e) de vouloir supprimer " + agentName.toString() + " ?");
        } else {
            $('#permanentDelete').val(1);
            $('#c-text').text("Êtes-vous sûr(e) de vouloir supprimer définitivement " + agentName.toString() + " ?");
        }
    });

    $('#deleteDialog').dialog({
        autoOpen: false,
        modal: true,
        width: 400,
        height: 260,
        buttons: {
            Non: function() {
                $(this).dialog('close');
            },
            Oui: function(e) {
                e.preventDefault();
   
                  if ($('#permanentDelete').val() == 0) {
                      $("#deleteDialog").dialog('close');
                      $("#deleteStep2Dialog").dialog('open');
                  } else {
                      deleteAgent();
                  }
            }
        }
    });

    $('#deleteStep2Dialog').dialog({
        autoOpen: false,
        modal: true,
        width: 400,
        height: 260,
        buttons: {
            Annuler: function() {
                $(this).dialog('close');
            },
            Supprimer: function() {
                deleteAgent();
            }
        }
   });
});
