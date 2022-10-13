/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

Fichier : public/personnel/js/index.js
@author Jérôme Combes <jerome@planningbiblio.fr>

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
