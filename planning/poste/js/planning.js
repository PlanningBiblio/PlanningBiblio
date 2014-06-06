/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/js/planning.js
Création : 2 juin 2014
Dernière modification : 6 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les scripts JS nécessaires à la page planning/poste/index.php (affichage et modification des plannings)
Fichier intégré par le fichier include/header.php avec la fonction getJSFiles.
*/

$(document).ready(function(){
  if($("#pl-notes-text").text()){
    $("#pl-notes-button").val("Modifier le commentaire");
  }
});


$(function() {
  // Notes
  var text=$("#pl-notes-text"),
    date=$("#date"),
    site=$("#site");
  allFields=$([]).add(text);

  // Bouton Notes
  $("#pl-notes-button").click(function() {
    $( "#pl-notes-form" ).dialog( "open" );
    return false;
  });

  // Formulaire Notes
  $( "#pl-notes-form" ).dialog({
    autoOpen: false,
    height: 480,
    width: 650,
    modal: true,
    buttons: {
      "Enregistrer": function() {
	allFields.removeClass( "ui-state-error" );
	var bValid = true;

	if ( bValid ) {
	  // Enregistre le commentaire
	  text.val(text.val().trim());
	  var text2=text.val().replace(/\n/g,"<br/>");
	  $.ajax({
	    url: "planning/poste/ajax.notes.php",
	    type: "get",
	    data: "date="+date.val()+"&site="+site.val()+"&text="+encodeURIComponent(text2),
	    success: function(){
	      if(text2){
		$("#pl-notes-button").val("Modifier le commentaire");
	      }else{
		$("#pl-notes-button").val("Ajouter un commentaire");
	      }	
	      // Met à jour le texte affiché en bas du planning
	      $("#pl-notes-div1").html(text2);
	      // Ferme le dialog
	      $("#pl-notes-form").dialog( "close" );
	    },
	    error: function(){
	      updateTips("Une erreur est survenue lors de l'enregistrement du commentaire");
	    }
	  });
	}
      },

      Annuler: function() {
	$( this ).dialog( "close" );
      }
    },

    close: function() {
      allFields.removeClass( "ui-state-error" );
    }
  });
});