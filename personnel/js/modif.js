/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : personnel/js/modif.js
Création : 3 mars 2014
Dernière modification : 9 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des agents (modif.php)
*/

// Contrôle des champs lors de la validation
function verif_form_agent(){
  erreur=false;
  message="Les champs suivant sont obligatoires :";
  if(!document.form.nom.value){
    erreur=true;
    message=message+"\n- Nom";
  }
  if(!document.form.prenom.value){
    erreur=true;
    message=message+"\n- prénom";
  }
  if(!document.form.mail.value){
    erreur=true;
    message=message+"\n- E-mail";
  }
  
  if(erreur)
    alert(message);
  else{
    if(!verif_mail(document.form.mail.value)){
      alert("Adresse e-mail invalide");
    }
    else{
      document.form.submit();
    }
  }
}

// Paramétrage de la boite de dialogue permettant la modification des statuts
$(function() {
  $("#add-statut-form").dialog({
    autoOpen: false,
    height: 480,
    width: 560,
    modal: true,
    resizable: false,
    draggable: false,
    buttons: {
      Enregistrer: function() {
	// Supprime les lignes cachées lors du clic sur la corbeille
	$("#statuts-sortable li:hidden").each(function(){
	  $(this).remove();
	});
	
	// Enregistre les éléments du formulaire dans un tableau
	tab=new Array();
	$("#statuts-sortable li").each(function(){
	  var id=$(this).attr("id").replace("li_","");
	  tab.push(new Array($("#valeur_"+id).text(), $("#categorie_"+id+" option:selected").val(),$(this).index()));
	});

	// Transmet le tableau à la page de validation ajax
	var jsonString = encodeURIComponent(JSON.stringify(tab));
	$.ajax({
	  url: "personnel/ajax.statuts.php",
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
      $("#statuts-sortable li:hidden").each(function(){
	$(this).show();
      });
    }
  });

  // Affiche la boite de dialogue permettant la modification des statuts
  $("#add-statut-button")
    .click(function() {
      $("#add-statut-form").dialog( "open" );
      return false;
    });

  // Permet de rendre la liste des statuts triable
  $( "#statuts-sortable" ).sortable({
    placeholder: "ui-state-highlight",
  });

  // Permet d'ajouter de nouveau statuts (click sur le bouton ajouter
  $("#add-statut-button2").click(function(){
    // Récupère les options du premier select "catégorie" pour les réutiliser lors d'un ajout
    var select=$("select[id^=categorie_]");
    var select_id=select.attr("id");
    var options="";
    $("#"+select_id+" option").each(function(){
      var val=sanitize_string($(this).val());
      var text=sanitize_string($(this).text());
      options+="<option value='"+val+"'>"+text+"</option>";
    });

    var text=sanitize_string($("#add-statut-text").val());
    if(!text){
      CJInfo("Donnée invalide","error");
      $("#add-statut-text").val();
      return;
    }

    var randomnumber=Math.floor((Math.random()*10000)+100)
    $("#statuts-sortable").append("<li id='li_"+randomnumber+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+randomnumber+"'>"+text+"</font>"
      +"<select id='categorie_"+randomnumber+"' style='position:absolute;left:330px;'>"
      +options
      +"</select>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-statut-text").val(null);
  });
});

$(document).ready(function(){
  // Met à jour les select site des emplois du temps si les sites ont changé dans les infos générales
  $("#personnel-a-li3").click(function(){
    changeSelectSites();
  });
});

function changeSelectSites(){
  // Tous les sites
  sites=new Array();
  $("input:checkbox[name^=sites]").each(function(){
    sites.push($(this).val());
  });
  
  // Sites sélectionnés
  sitesSelectionnes=new Array();
  $("input:checkbox[name^=sites]:checked").each(function(){
    sitesSelectionnes.push($(this).val());
  });

  if(sitesSelectionnes.length>1){
    $(".edt-site-0").show();
  }else{
    $(".edt-site-0").hide();
    $(".edt-site").val(sitesSelectionnes[0]);
  }
  
  for(i=0;i<sites.length;i++){
    $(".edt-site-"+sites[i]).hide();
  }
    
  for(i=0;i<sitesSelectionnes.length;i++){
    $(".edt-site-"+sitesSelectionnes[i]).show();
  }
  // Faire for (i=1, i<= nombre de site ...) .edt-site-i.hide
  // Puis foreach tab, .edt-site-tabIndex.show
}