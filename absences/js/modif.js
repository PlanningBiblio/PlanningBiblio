/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : absences/js/modif.js
Création : 28 février 2014
Dernière modification : 9 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à l'ajout et la modification des agents (modif.php)
*/

$(function() {
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
      var val=sanitize_string($(this).val());
      var text=sanitize_string($(this).text());
      options+="<option value='"+val+"'>"+text+"</option>";
    });

    var text=sanitize_string($("#add-motif-text").val());
    if(!text){
      CJInfo("Donnée invalide","error");
      $("#add-statut-text").val();
      return;
    }
    
    var randomnumber=Math.floor((Math.random()*10000)+100)
    $("#motifs-sortable").append("<li id='li_"+randomnumber+"' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>"
      +"<font id='valeur_"+randomnumber+"'>"+text+"</font>"
      +"<select id='type_"+randomnumber+"' style='position:absolute;left:330px;'>"
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
});

// Vérification des formulaires (ajouter et modifier)
function verif_absences(ctrl_form){
  if(!verif_form(ctrl_form))
    return false;

  if($("select[name=motif] option:selected").attr("disabled")=="disabled"){
    alert("Le motif sélectionné n'est pas valide.\nVeuillez le modifier s'il vous plaît.");
    return false;
  }
  
  if($("select[name=motif]").val().toLowerCase()=="autre" || $("select[name=motif]").val().toLowerCase()=="other"){
    if($("input[name=motif_autre]").val()==""){
      alert("Veuillez choisir un motif.");
      return false;
    }
  }
 
  perso_id=document.form.perso_id.value;
  id=document.form.id.value;
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
    data: {perso_id: perso_id, id: id, debut: debut, fin: fin},
    async: false,
    success: function(result){
      result=JSON.parse(result);
      if(result["planningVide"]!=0){
	information("Vous essayez de placer une absence sur un planning en cours d'élaboration","error");
	retour=false;
      }
      else if(result["autreAbsence"]){
	information("Une absence est déjà enregistrée pour cet agent entre le "+result["autreAbsence"]+"<br/>Veuillez modifier les dates et horaires.","error");
	retour=false;
      }
      else if(result["planning"]){
	if(admin==1){
	  if(!confirm("Attention, l'agent sélectionné apparaît dans des plannings validés : "+result["planning"]+"\nVoulez vous continuer ?")){
	    retour=false;
	  }
	}
	else{
	  information("Vous ne pouvez pas ajouter d'absences pour les dates suivantes<br/>car les plannings sont validés : "+result["planning"]+"<br/>Veuillez modifier vos dates ou contacter le responsable du planning","error");
	  retour=false;
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
