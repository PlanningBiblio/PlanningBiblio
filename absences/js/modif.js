/*
Planning Biblio, Version 2.5.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : absences/js/modif.js
Création : 28 février 2014
Dernière modification : 5 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des agents (modif.php)
*/

$(function() {
  
  $(document).ready(function(){
    absencesAligneSuppression();
  });

  // Paramétrage de la boite de dialogue permettant la modification des motifs
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
 	  tab.push(new Array($("#valeur_"+id).text(), $(this).index(), $("#type_"+id+" option:selected").val()));
	});

	// Transmet le tableau à la page de validation ajax
	$.ajax({
	  url: "include/ajax.menus.php",
	  type: "post",
          dataType: "json",
	  data: {tab: tab, menu:"abs", option: "type"},
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
      var val=sanitize_string($(this).val());
      var text=sanitize_string($(this).text());
      options+="<option value='"+val+"'>"+text+"</option>";
    });

    var text=sanitize_string($("#add-motif-text").val());
    if(!text){
      CJInfo("Donnée invalide","error");
      $("#add-motif-text").val();
      return;
    }
    
    var number = 1;
    while($('#li_'+number).length){
      number++;
    }

    $("#motifs-sortable").append("<li id='li_"+number+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+number+"'>"+text+"</font>"
      +"<select id='type_"+number+"' style='position:absolute;left:330px;'>"
      +options
      +"</select>"
      +"<span class='ui-icon ui-icon-trash' style='position:relative;left:455px;top:-20px;cursor:pointer;' onclick='$(this).closest(\"li\").hide();'></span>"
      +"</li>");

    // Reset du champ texte une fois l'ajout effectué
    $("#add-motif-text").val(null);
  });
  
  // Modifie la classe de la ligne lors du changement du select type (Boite de dialogue permettant de modifier la liste des motifs)
  $("select[id^=type]").change(function(){
    if($(this).val()==2){
      $(this).prev("font").removeClass("bold");
      $(this).prev("font").addClass("padding20");
    }else{
      $(this).prev("font").addClass("bold");
      $(this).prev("font").removeClass("padding20");
    }
  });

  // Affiche ou masque le champ motif_autre en fonction de la valeur du select motif
  $("select[name=motif]").change(function(){
    if($(this).val().toLowerCase()=="autre" || $(this).val().toLowerCase()=="other"){
      $("#tr_motif_autre").show();
    }else{
      $("#tr_motif_autre").hide();
      $("input[name=motif_autre]").val("");
    }
  });
  
  /**
   * Agents multiples
   * Permet d'ajouter plusieurs agents sur une même absence (réunion, formation)
   * Lors du changement du <select perso_ids>, ajout du nom des agents dans <ul perso_ul> et leurs id dans <input perso_ids[]>
   */
  $("#perso_ids").change(function(){
    // Variables
    var id=$(this).val();

    // Ajout des champs hidden permettant la validation des agents
    $(this).before("<input type='hidden' name='perso_ids[]' value='"+id+"' id='hidden"+id+"' class='perso_ids_hidden'/>\n");

    // Création de la liste : balises <ul>
    if(!$("#perso_ul").length){
      $(this).before("<ul id='perso_ul'></ul>\n");
    }
    
    // Affichage des agents sélectionnés avec tri alphabétique
    var tab=[];
    $(".perso_ids_hidden").each(function(){
      var id=$(this).val();
      var name=$("#perso_ids option[value='"+id+"']").text();
      tab.push([name,id]);
    });

    tab.sort();
    
    $(".perso_ids_li").remove();
    for(i in tab){
      var li="<li id='li"+tab[i][1]+"' class='perso_ids_li'>"+tab[i][0]+"<span class='perso-drop' style='margin-left:5px;' onclick='supprimeAgent("+tab[i][1]+");' ><span class='pl-icon pl-icon-drop'></span></span></li>\n";
      $("#perso_ul").append(li);
    }
    

    absencesAligneSuppression();
    
    $("#perso_ids :selected").hide();

    
    $(this).val(0);

    
  });

  
  $("#absence-bouton-supprimer").click(function(){
    if(confirm("Etes vous sûr de vouloir supprimer cette absence ?")){
      var id=$(this).attr("data-id");
      document.location.href="index.php?page=absences/delete.php&id="+id;
    }
  });
  
  
});

// Vérification des formulaires (ajouter et modifier)
function verif_absences(ctrl_form){
  if(!verif_form(ctrl_form))
    return false;

  if($("select[name=motif] option:selected").attr("disabled")=="disabled"){
    CJInfo("Le motif sélectionné n'est pas valide.\nVeuillez le modifier s'il vous plaît.","error");
    return false;
  }
  
  if($("select[name=motif]").val().toLowerCase()=="autre" || $("select[name=motif]").val().toLowerCase()=="other"){
    if($("input[name=motif_autre]").val()==""){
      CJInfo("Veuillez choisir un motif.","error");
      return false;
    }
  }
 
  // ID des agents
  perso_ids=[];
  $(".perso_ids_hidden").each(function(){
    perso_ids.push($(this).val());
  });

  // Si aucun agent n'est sélectionné, on quitte en affichant "Veuillez sélectionner ..."
  if(perso_ids.length<1){
    CJInfo("Veuillez sélectionner un ou plusieurs agents","error");
    return false;
  }

  id=document.form.id.value;
  var groupe = $("#groupe").val();
  debut=document.form.debut.value;
  fin=document.form.fin.value;
  fin=fin?fin:debut;
  debut=debut.replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");
  fin=fin.replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");

  hre_debut=document.form.hre_debut.value;
  hre_fin=document.form.hre_fin.value;
  hre_debut=hre_debut?hre_debut:"00:00:00";
  hre_fin=hre_fin?hre_fin:"23:59:59";
  debut=debut+" "+hre_debut;
  fin=fin+" "+hre_fin;

  var admin=$("#admin").val();
  var retour=true;

  $.ajax({
    url: "absences/ajax.control.php",
    type: "get",
    datatype: "json",
    data: {perso_ids: JSON.stringify(perso_ids), id: id, groupe: groupe, debut: debut, fin: fin},
    async: false,
    success: function(result){
      result=JSON.parse(result);
      
      // Pour chaque agent
      for(i in result){
	// Contrôle s'il y a une autre absence enregistrée
	if(result[i]["autreAbsence"]){
	  if(perso_ids.length>1){
	    var message="Une absence est déjà enregistrée pour l'agent "+result[i]["nom"]+" entre le "+result[i]["autreAbsence"]+"<br/>Veuillez modifier la liste des agents, les dates ou les horaires.";
	  }else{
	    var message="Une absence est déjà enregistrée pour l'agent "+result[i]["nom"]+" entre le "+result[i]["autreAbsence"]+"<br/>Veuillez modifier les dates ou les horaires.";
	  }
	  CJInfo(message,"error");
	  retour=false;
	}
	
	// Contrôle s'il apparaît dans des plannings validés
	else if(result[i]["planning"]){
	  if(admin==1){
	    if(!confirm("L'agent "+result[i]["nom"]+" apparaît dans des plannings validés : "+result[i]["planning"]+"\nVoulez-vous continuer ?")){
	      retour=false;
	    }
	  }
	  else{
	    CJInfo("Vous ne pouvez pas ajouter d'absences pour les dates suivantes<br/>car les plannings sont validés : "+result[i]["planning"]+"<br/>Veuillez modifier vos dates ou contacter le responsable du planning","error");
	    retour=false;
	  }
	}

	// Contrôle des options Absences-PlanningVide et des plugins éventuels
	else if(result[i]["info"] && result[i]["admin"]){
	    if(admin==1) {
		if(!confirm(result[i]["admin"])) retour=false;
	    }
	    else{
		CJInfo(result[i]["info"]);
		retour=false;
	    }
	}
      }

    },
    error: function(result){
      information("Une erreur est survenue.","error");
      retour=false;
    }
  });
  return retour;
}


// Alignement des icônes de suppression
function absencesAligneSuppression(){
  if($(".perso-drop").length){
    var left=0;
    $(".perso-drop").each(function(){
      if($(this).position().left>left){
	left=$(this).position().left;
      }
    });
    
    $(".perso-drop").css("position","absolute");
    $(".perso-drop").css("left",left);
  }
}
/**
 * supprimeAgent
 * supprime les agents de la sélection lors de l'ajout ou modification d'une absence
 */
function supprimeAgent(id){
  $("#option"+id).show();
  $("#li"+id).remove();
  $("#hidden"+id).remove();
  absencesAligneSuppression();
}
