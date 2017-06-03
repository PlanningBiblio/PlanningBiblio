/**
Planning Biblio, Version 2.6.91
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : postes/js/modif.js
Création : 5 février 2017
Dernière modification : 2 juin 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des postes (modif.php)
*/

$(function() {

  // Paramétrage de la boite de dialogue permettant la modification des étages
  $("#add-etage-form").dialog({
    autoOpen: false,
    height: 480,
    width: 560,
    modal: true,
    resizable: false,
    draggable: false,
    buttons: {
      Enregistrer: function() {
	// Supprime les lignes cachées lors du clic sur la corbeille
	$("#etages-sortable li:hidden").each(function(){
	  $(this).remove();
	});
	
	// Enregistre les éléments du formulaire dans un tableau
	tab=new Array();
	$("#etages-sortable li").each(function(){
	  var id=$(this).attr("id").replace("li_","");
 	  tab.push(new Array($("#valeur_"+id).text(), $(this).index()));
	});

	// Transmet le tableau à la page de validation ajax
	$.ajax({
	  url: "include/ajax.menus.php",
	  type: "post",
          dataType: "json",
	  data: {tab: tab, menu: "etages"},
	  success: function(){
            var current_val = $('#etage').val();
            $('#etage').empty();
            $('#etage').append("<option value=''>&nbsp;</option>");

            $("#etages-sortable li").each(function(){
              var id=$(this).attr("id").replace("li_","");
              var val = $("#valeur_"+id).text();
              var selected = (val == current_val) ? "selected='selected'" : "";
              var option = "<option value='"+val+"' "+selected+">"+val+"</option>";
              $('#etage').append(option);
            });
            $("#add-etage-form").dialog( "close" );
            $('#etage').effect("highlight",null,2000);
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
      $("#etages-sortable li:hidden").each(function(){
	$(this).show();
      });
    }
  });

  // Affiche la boite de dialogue permettant la modification des etages
  $("#add-etage-button").click(function() {
      $("#add-etage-form").dialog( "open" );
      return false;
    });

  // Permet de rendre la liste des etages triable
  $("#etages-sortable" ).sortable({
    placeholder: "ui-state-highlight",
  });

  // Permet d'ajouter de nouveaux etages (clic sur le bouton ajouter)
  $("#add-etage-button2").click(function(){
    var text=sanitize_string($("#add-etage-text").val());
    if(!text){
      CJInfo("Donnée invalide","error");
      $("#add-etage-text").val();
      return;
    }
    
    // Vérifie si l'étage existe déjà
    var exist = false;
    $('#etages-sortable > li > font').each(function(){
      if($(this).text().toLowerCase() == text.toLowerCase()){
        CJInfo("Cette valeur existe déjà.","error");
        exist = true;
        return;
      }
    });
    
    if(exist){
      return;
    }
    
    var number = 1;
    while($('#li_'+number).length){
      number++;
    }
    $("#etages-sortable").append("<li id='li_"+number+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+number+"'>"+text+"</font>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-etage-text").val(null);
  });
  

  // Paramétrage de la boite de dialogue permettant la modification des groupes
  $("#add-group-form").dialog({
    autoOpen: false,
    height: 480,
    width: 560,
    modal: true,
    resizable: false,
    draggable: false,
    buttons: {
      Enregistrer: function() {
	// Supprime les lignes cachées lors du clic sur la corbeille
	$("#groups-sortable li:hidden").each(function(){
	  $(this).remove();
	});
	
	// Enregistre les éléments du formulaire dans un tableau
	tab=new Array();
	$("#groups-sortable li").each(function(){
	  var id=$(this).attr("id").replace("li_","");
 	  tab.push(new Array($("#valeur_"+id).text(), $(this).index()));
	});

	// Transmet le tableau à la page de validation ajax
	$.ajax({
	  url: "include/ajax.menus.php",
	  type: "post",
          dataType: "json",
	  data: {tab: tab, menu: "groupes"},
	  success: function(){
            var current_val = $('#groupe').val();
            $('#groupe').empty();
            $('#groupe').append("<option value=''>&nbsp;</option>");

            $("#groups-sortable li").each(function(){
              var id=$(this).attr("id").replace("li_","");
              var val = $("#valeur_"+id).text();
              var selected = (val == current_val) ? "selected='selected'" : "";
              var option = "<option value='"+val+"' "+selected+">"+val+"</option>";
              $('#groupe').append(option);
            });
            $("#add-group-form").dialog( "close" );
            $('#groupe').effect("highlight",null,2000);

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
      $("#groups-sortable li:hidden").each(function(){
	$(this).show();
      });
    }
  });

  // Affiche la boite de dialogue permettant la modification des groupes
  $("#add-group-button").click(function() {
      $("#add-group-form").dialog( "open" );
      return false;
    });

  // Permet de rendre la liste des groupes triable
  $("#groups-sortable" ).sortable({
    placeholder: "ui-state-highlight",
  });

  // Permet d'ajouter de nouveaux groupes (clic sur le bouton ajouter)
  $("#add-group-button2").click(function(){
    var text=sanitize_string($("#add-group-text").val());
    if(!text){
      CJInfo("Donnée invalide","error");
      $("#add-group-text").val();
      return;
    }
    
    // Vérifie si le groupe existe déjà
    var exist = false;
    $('#groups-sortable > li > font').each(function(){
      if($(this).text().toLowerCase() == text.toLowerCase()){
        CJInfo("Cette valeur existe déjà.","error");
        exist = true;
        return;
      }
    });
    
    if(exist){
      return;
    }
    
    var number = 1;
    while($('#li_'+number).length){
      number++;
    }
    $("#groups-sortable").append("<li id='li_"+number+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+number+"'>"+text+"</font>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-group-text").val(null);
  });
  
});