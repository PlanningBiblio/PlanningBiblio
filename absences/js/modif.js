/*
Planning Biblio, Version 1.7.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : absences/js/modif.js
Création : 28 février 2014
Dernière modification : 3 mars 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des agents (modif.php)
*/

// Paramétrage de la boite de dialogue permettant la modification des motifs
$(function() {
  $("#add-motif-form").dialog({
    autoOpen: false,
    height: 480,
    width: 560,
    modal: true,
    resizable: false,
    draggable: false,
    buttons: {
      Enregistrer: function() {
	// Supprime les lignes cachées lors du clic sur la corbeille
	$("#motifs-sortable li:hidden").each(function(){
	  $(this).remove();
	});
	
	// Enregistre les éléments du formulaire dans un tableau
	tab=new Array();
	$("#motifs-sortable li").each(function(){
	  var id=$(this).attr("id").replace("li_","");
 	  tab.push(new Array($("#valeur_"+id).text(), $("#type_"+id+" option:selected").val(),$(this).index()));
	});

	// Transmet le tableau à la page de validation ajax
	var jsonString = encodeURIComponent(JSON.stringify(tab));
	$.ajax({
	  url: "absences/ajax.motifs.php",
	  type: "post",
	  data: "tab="+jsonString,
	  success: function(){
	    location.reload(false);
	  },
	  error: function(){
	    alert("Erreur lors de l'enregistrement des modifications");
	  }
	});
      },
      Annuler: function() {
	$(this).dialog( "close" );
      },
    },
    close: function() {
      $("#motifs-sortable li:hidden").each(function(){
	$(this).show();
      });
    }
  });

  // Affiche la boite de dialogue permettant la modification des motifs
  $("#add-motif-button")
    .click(function() {
      $("#add-motif-form").dialog( "open" );
      return false;
    });

  // Permet de rendre la liste des motifs triable
  $( "#motifs-sortable" ).sortable({
    placeholder: "ui-state-highlight",
  });

  // Permet d'ajouter de nouveaux motifs (clic sur le bouton ajouter)
  $("#add-motif-button2").click(function(){
    // Récupère les options du premier select "type" pour les réutiliser lors d'un ajout
    var select=$("select[id^=type_]");
    var select_id=select.attr("id");
    var options="";
    $("#"+select_id+" option").each(function(){
      options+="<option value='"+$(this).val()+"'>"+$(this).text()+"</option>";
    });

    var randomnumber=Math.floor((Math.random()*10000)+100)
    $("#motifs-sortable").append("<li id='li_"+randomnumber+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+randomnumber+"'>"+$("#add-motif-text").val()+"</font>"
      +"<select id='type_"+randomnumber+"' style='position:absolute;left:330px;'>"
      +options
      +"</select>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-motif-text").val(null);
  });
  
    // Modifie la classe de la ligne lors du changement du select type (Boite de dialogue permettant de modifier la liste des motifs)
    $("select[id^='type']").change(function(){
    if($(this).val()==2){
      $(this).prev("font").removeClass("bold");
      $(this).prev("font").addClass("padding20");
    }else{
      $(this).prev("font").addClass("bold");
      $(this).prev("font").removeClass("padding20");
    }
  });

});