/**
Planning Biblio, Version 2.5.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : personnel/js/modif.js
Création : 3 mars 2014
Dernière modification : 5 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des agents (modif.php)
*/

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

// Select multiples
function select_add(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  attrib_new=new Array();
  dispo_new=new Array();
  tab_attrib=new Array();
  dispo=document.getElementById(select_dispo).options;
  attribues=document.getElementById(select_attrib).options;
  for(i=0;i<attribues.length;i++)
    attrib_new.push(attribues[i].value);
  for(i=0;i<dispo.length;i++)
    if(dispo[i].selected)
	attrib_new.push(dispo[i].value);
  for(i=0;i<complet.length;i++){
    var inArray=false;
    for(j=0;j<attrib_new.length;j++){
      if(complet[i][1]==attrib_new[j]){
	attrib_new[j]=complet[i];
	tab_attrib.push(complet[i][1]);
	inArray=true;
      }
    }
    if(!inArray){
      dispo_new.push(complet[i]);
    }
  }
  dispo_new.sort();
  attrib_new.sort();
  
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<attrib_new.length;i++)
    attrib_aff=attrib_aff+"<option value='"+attrib_new[i][1]+"'>"+attrib_new[i][0]+"</option>";
  attrib_aff=attrib_aff+"</select>";
  
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<dispo_new.length;i++)
    dispo_aff=dispo_aff+"<option value='"+dispo_new[i][1]+"'>"+dispo_new[i][0]+"</option>";
  dispo_aff=dispo_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value=tab_attrib.toString();
}

function select_drop(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  dispo_new=new Array();
  attrib_new=new Array();
  tab_attrib=new Array();
  dispo=document.getElementById(select_dispo).options;
  attribues=document.getElementById(select_attrib).options;
  for(i=0;i<dispo.length;i++)
    dispo_new.push(dispo[i].value);
  for(i=0;i<attribues.length;i++)
    if(attribues[i].selected)
      dispo_new.push(attribues[i].value);
  for(i=0;i<complet.length;i++){
    var inArray=false;
    for(j=0;j<dispo_new.length;j++){
      if(complet[i][1]==dispo_new[j]){
	dispo_new[j]=complet[i];
	inArray=true;
      }
    }
    if(!inArray){
      attrib_new.push(complet[i]);
      tab_attrib.push(complet[i][1]);
    }
  }
  dispo_new.sort();
  attrib_new.sort();
  
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<attrib_new.length;i++)
    attrib_aff=attrib_aff+"<option value='"+attrib_new[i][1]+"'>"+attrib_new[i][0]+"</option>";
  attrib_aff=attrib_aff+"</select>";
  
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<dispo_new.length;i++)
    dispo_aff=dispo_aff+"<option value='"+dispo_new[i][1]+"'>"+dispo_new[i][0]+"</option>";
  dispo_aff=dispo_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value=tab_attrib.toString();
}

function select_add_all(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  tab_attrib=new Array();
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<complet.length;i++){
    attrib_aff=attrib_aff+"<option value='"+complet[i][1]+"'>"+complet[i][0]+"</option>";
    tab_attrib.push(complet[i][1]);
  }
  attrib_aff=attrib_aff+"</select>";
  
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  dispo_aff=dispo_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value=tab_attrib.toString();
}

function select_drop_all(select_dispo,select_attrib,hidden_attrib,width){	// Attribution des postes / modification du personnel
  complet.sort();
  var dispo_aff="<select id='"+select_dispo+"' name='"+select_dispo+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  for(i=0;i<complet.length;i++)
    dispo_aff=dispo_aff+"<option value='"+complet[i][1]+"'>"+complet[i][0]+"</option>";
  dispo_aff=dispo_aff+"</select>";
  
  var attrib_aff="<select id='"+select_attrib+"' name='"+select_attrib+"' style='width:"+width+"px;' size='20' multiple='multiple'>";
  attrib_aff=attrib_aff+"</select>";
  
  document.getElementById("attrib_div").innerHTML=attrib_aff;
  document.getElementById("dispo_div").innerHTML=dispo_aff;
  document.getElementById(hidden_attrib).value='';
}
// Fin Select Multpiles


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

$(function() {
  // Paramétrage de la boite de dialogue permettant la modification des statuts
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
	  tab.push(new Array($("#valeur_"+id).text(), $(this).index(), $("#categorie_"+id+" option:selected").val()));
	});

	// Transmet le tableau à la page de validation ajax
	$.ajax({
          url: "include/ajax.menus.php",
	  type: "post",
          dataType: "json",
	  data: {tab: tab, menu: "statuts" , option: "categorie"},
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

    var number = 1;
    while($('#li_'+number).length){
      number++;
    }

    $("#statuts-sortable").append("<li id='li_"+number+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+number+"'>"+text+"</font>"
      +"<select id='categorie_"+number+"' style='position:absolute;left:330px;'>"
      +options
      +"</select>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-statut-text").val(null);
  });
  
  // Paramétrage de la boite de dialogue permettant la modification des services
  $("#add-service-form").dialog({
    autoOpen: false,
    height: 480,
    width: 560,
    modal: true,
    resizable: false,
    draggable: false,
    buttons: {
      Enregistrer: function() {
	// Supprime les lignes cachées lors du clic sur la corbeille
	$("#services-sortable li:hidden").each(function(){
	  $(this).remove();
	});
	
	// Enregistre les éléments du formulaire dans un tableau
	tab=new Array();
	$("#services-sortable li").each(function(){
	  var id=$(this).attr("id").replace("li_","");
 	  tab.push(new Array($("#valeur_"+id).text(), $(this).index()));
	});

	// Transmet le tableau à la page de validation ajax
	$.ajax({
	  url: "include/ajax.menus.php",
	  type: "post",
          dataType: "json",
	  data: {tab: tab, menu: "services"},
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
      $("#services-sortable li:hidden").each(function(){
	$(this).show();
      });
    }
  });

  // Affiche la boite de dialogue permettant la modification des services
  $("#add-service-button").click(function() {
      $("#add-service-form").dialog( "open" );
      return false;
    });

  // Permet de rendre la liste des services triable
  $("#services-sortable" ).sortable({
    placeholder: "ui-state-highlight",
  });

  // Permet d'ajouter de nouveaux services (clic sur le bouton ajouter)
  $("#add-service-button2").click(function(){
    var text=sanitize_string($("#add-service-text").val());
    if(!text){
      CJInfo("Donnée invalide","error");
      $("#add-service-text").val();
      return;
    }
    
    // Vérifie si l'étage existe déjà
    var exist = false;
    $('#services-sortable > li > font').each(function(){
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
    $("#services-sortable").append("<li id='li_"+number+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+number+"'>"+text+"</font>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-service-text").val(null);
  });
  


});

$(document).ready(function(){
  // Met à jour les select site des emplois du temps si les sites ont changé dans les infos générales
  $("#personnel-a-li3").click(function(){
    changeSelectSites();
  });
});