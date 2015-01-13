/*
Planning Biblio, Version 1.8.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/js/planning.js
Création : 2 juin 2014
Dernière modification : 13 janvier 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les scripts JS nécessaires à la page planning/poste/index.php (affichage et modification des plannings)
Fichier intégré par le fichier include/header.php avec la fonction getJSFiles.
*/

perso_id_origine=0;

// Chargement de la page
$(document).ready(function(){
  // Vérifions si un agent de catégorie A est placé en fin de service
  verif_categorieA();

  // DataTable (tableau des absences)
  $("#tableAbsences").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : 25,
    "oLanguage" : {"sUrl" : "vendor/dataTables.french.lang"}
  });

  if($("#pl-notes-text").text()){
    $("#pl-notes-button").val("Modifier le commentaire");
  }


  // Mise en forme des lignes du tableau planning
  // Pour chaque TD
  $(".tabsemaine1 td").each(function(i, index) {
    // Occuper toute la hauteur
    var nbDiv=$(index).find("div").length;
    if(nbDiv>0){
      var divHeight=$(index).height()/nbDiv;
      $(index).find("div").css("height",divHeight);
    }
    // Centrer verticalement les textes
    $(index).find("span").each(function(j,jtem){
      var top=(($(jtem).closest("div").height()-$(jtem).height())/2)-4;
      $(jtem).css("position","relative");
      $(jtem).css("top",top);
    });
  });
  
  
});

// Evénements JQuery
$(function() {
  // Calendar
  $("#pl-calendar").change(function(){
    var date=dateFr($(this).val());
    if($(this).attr("class").search("datepickerSemaine")>0){
      window.location.href="?page=planning/poste/semaine.php&date="+date;
    }else{
      window.location.href="?date="+date;
    }
  });
  
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
  
  
  $(".cellDiv").contextmenu(function(){
    $(this).closest("td").attr("data-perso-id",$(this).attr("data-perso-id"));
    perso_id_origine=$(this).attr("data-perso-id");
  });
  
  // Création du MenuDiv : menu affichant la liste des agents pour les placer dans les cellules
  $(".menuTrigger").contextmenu(function(e){
    cellule=$(this).attr("data-cell");
    date=$("#date").val();
    debut=$(this).attr("data-start");
    fin=$(this).attr("data-end");
    poste=$(this).attr("data-situation");
    perso_id=$(this).attr("data-perso-id");
    site=$("#site").val();

    $.ajax({
      url: "planning/poste/ajax.menudiv.php",
      data: "&cellule="+cellule+"&date="+date+"&debut="+debut+"&fin="+fin+"&poste="+poste+"&site="+site,
      type: "get",
      success: function(result){
	document.getElementById("menudiv").scrollTop=0;
	hauteur=146;
	document.getElementById("menudiv").innerHTML=result;

	if($(window).width()-e.clientX<320){
	  $("#menudiv").css("left",e.pageX-360);
	  $("#menudivtab").css("left",220);
	  $("#menudivtab2").css("left",0);
	}else{
	  $("#menudiv").css("left",e.pageX);
	}
	if($(window).height()-e.pageY<hauteur){
	  $("#menudiv").css("top",e.pageY-hauteur);
	}else{
	  $("#menudiv").css("top",e.pageY);
	}

	document.getElementById("menudiv").style.display = menudiv_display;
      },

      error: function(result){
	information("Impossible d'afficher le menu des agents.","error");
      }
    });
    return false ;
  });

  // Masque le menu lorsque l'on clique en dehors
  $("html").click(function(){
    $("#menudiv").hide();
  });

});


// Fonctions JavaScript

function bataille_navale(poste,date,debut,fin,perso_id,barrer,ajouter,site){
  /* 
  bataille_navale : menu contextuel : met à jour la base de données en arrière plan et affiche les modifs en JS dans le planning
  Récupére en Ajax les id, noms, prénom, service, statut dans agents placés
  Met à jour la base de données en arrière plan
  Refait ensuite l'affichage complet de la cellule. Efface est remplit la cellule avec les infos récupérées du fichier ajax.updateCell.php
  Les cellules sont identifiables, supprimables et modifiables indépendament des autres
  Les infos service et statut sont utilisées pour la mise en forme des cellules : utilisation des classes service_ et statut_
  */
  if(site==undefined || site==""){
    site=1;
  }

  $.ajax({
    url: "planning/poste/ajax.updateCell.php",
    type: "post",
    dataType: "json",
    data: {poste: poste, date: date, debut: debut, fin: fin, perso_id: perso_id, perso_id_origine: perso_id_origine, barrer: barrer, ajouter: ajouter, site: site},
    success: function(result){
      $("#td"+cellule).html("");
      
      // Suppression du sans repas sur les cellules ainsi marquée
      if(debut>="11:30:00" && fin <="14:30:00"){
	  $(".agent_"+perso_id_origine).each(function(){
	  var sr_debut=$(this).closest("td").data("start");
	  var sr_fin=$(this).closest("td").data("end");
	  if(sr_debut>="11:30:00" && sr_fin<="14:30:00"){
	    $(this).find(".sansRepas").remove();
	  }
	});
      }
      
      // Si pas de résultat (rien n'est affiché dans la cellule modifiée), 
      // on réinitialise perso_id_origine pour ne pas avoir de rémanence pour la gestion de SR et suppression
      if(!result){
	perso_id_origine=0;
      }

      for(i in result){
	// Exemple de cellule
	// <div id='cellule11_0' class='cellule statut_bibas service_permanent' >Christophe C.</div>

	// classes : A définir en fonction du statut, du service et des absences
	var classes="cellDiv";
	// Absences, suppression
	if(result[i]["absent"]=="1" || result[i]["supprime"]=="1"){
	  classes+=" red striped";
	}

	// Congés
	if(result[i]["conges"]){
	  classes+=" orange striped";
	}

	// Service et Statut
	classes+=" service_"+result[i]["service"].toLowerCase().replace(" ","_");
	classes+=" statut_"+result[i]["statut"].toLowerCase().replace(" ","_");
	
	var agent=result[i]["nom"]+" "+result[i]["prenom"].substr(0,1)+".";
	var perso_id=result[i]["perso_id"];

	// Sans Repas
	if(result[i]["sr"]){
	  // Ajout du sans repas sur la cellule modifiée
	  agent+="<label class='sansRepas'> (SR)</label>";
	  
	  // Ajout du sans repas sur les autres cellules concernées
	  $(".agent_"+perso_id).each(function(){
	    var sr_debut=$(this).closest("td").data("start");
	    var sr_fin=$(this).closest("td").data("end");
	    if(sr_debut>="11:30:00" && sr_fin<="14:30:00"){
	      if($(this).text().indexOf("(SR)")==-1){
		$(this).append("<label class='sansRepas'> (SR)</label>");
	      }
	    }
	  });
	}

	// Création d'une balise span avec les classes cellSpan et agent_ de façon à les repérer et agir dessus 
	debut=debut.replace(":","");
	fin=fin.replace(":","");
	var span="<span class='cellSpan agent_"+perso_id+"'>"+agent+"</span>";
	var div="<div id='cellule"+cellule+"_"+i+"' class='"+classes+"' data-perso-id='"+perso_id+"' oncontextmenu='perso_id_origine="+perso_id+";'>"+span+"</div>"
	// oncontextmenu='perso_id_origine="+perso_id+";' : necessaire car l'événement JQuery contextmenu sur .cellDiv ne marche pas sur les cellules modifiées
	$("#td"+cellule).append(div);
      }

      // Mise en forme de toute la ligne
      // Pour chaque TD
      $("#td"+cellule).closest("tr").find("td").each (function(i, index) {
	// Occuper toute la hauteur
	var nbDiv=$(index).find("div").length;
	if(nbDiv>0){
	  var divHeight=$(index).height()/nbDiv;
	  $(index).find("div").css("height",divHeight);
	}
	// Centrer verticalement les textes
	$(index).find("span").each(function(j,jtem){
	  var top=(($(jtem).closest("div").height()-$(jtem).height())/2)-4;
	  $(jtem).css("position","relative");
	  $(jtem).css("top",top);
	});
      });

      $("#menudiv").hide();				// cacher le menudiv

    },
    error: function(result){
      information("Une erreur est survenue lors de l'enregistrement du planning.","error");
    }
  });

/*
Exemple de valeur pour la variable result :

  [0] => Array (
    [nom] => Nom
    [prenom] => Prénom
    [statut] => Statut
    [service] => Service
    [perso_id] => 86
    [absent] => 0
    [supprime] => 0
    [sr] => 0
    )
  [1] => Array (
    ...
*/
  // Affiche un message en haut du planning si pas de catégorie A en fin de service 
  verif_categorieA();
}

//	groupe_tab : utiliser pour menudiv
function groupe_tab(id,tab,hide){			// améliorer les variables (tableaux) pour plus d'évolution
  if(hide==undefined){
    hide=1;
  }

  //		tab="1,2,3,4,5;6,7,8,9,10;11,12,13,14,15"
  tmp=tab.split(';');
  //		tmp=array("1,2,3,4,5","6,7,8,9,10","11,12,13,14,15")
  var tab=new Array();
  for(i=0;i<tmp.length;i++)
    tab.push(tmp[i].split(','));
    //		tab=array(array(1,2,3,4,5),array(6,7,8,9,10),array(11,12,13,14,15))
  
  //		On cache tout le sous-menu
  if(hide==1){
    for(i=0;i<tab.length;i++){
      if(tab[i][0]){
	for(j=0;j<tab[i].length;j++){
		document.getElementById("tr"+tab[i][j]).style.display="none";
	}
      }
    }
  }
	  
  //		On affiche les agents du service voulu dans le sous-menu	
  if(id!="vide" && tab[id][0]){
    for(i=0;i<tab[id].length;i++){
      document.getElementById("tr"+tab[id][i]).style.display="";
    }
  }
}

function groupe_tab_hide(){
  $(".tr_liste").each(function(){
    $(this).hide();
  });
}

function refresh_poste(validation){		// actualise le planning en cas de modification
  $.ajax({
    url: "planning/poste/ajax.refresh.php",
    type: "post",
    dataType: "json",
    data: {"date": $("#date").val(), "site": $("#site").val()},
    success: function(result){
      if(result!=validation){
	window.location.reload(false);
      }else{
	setTimeout("refresh_poste('"+validation+"')",30000);
      }
    },
    error: function(result){
      information(result.responseText,"error");
      setTimeout("refresh_poste('"+validation+"')",30000);
    }
  });
}

function verif_categorieA(){
  // Si div pl-verif-categorie-A n'existe pas (pas les droits admin ou pas demanandé dans la config)
  // OU si pas de tableau affiché (.tabsemaine1) = pas de vérification, 
  if($("#pl-verif-categorie-A").length<1 || $(".tabsemaine1").length<1){
    return;
  }

  var date=$("#date").val();
  var site=$("#site").val();

  $.ajax({
    url: "planning/poste/ajax.categorieA.php",
    datatype: "json",
    type: "post",
    data: {"date": date, "site": site},
    success: function(result){
      result=JSON.parse(result);
      if(result=="true"){
	$("#planningTips").hide();
      }
      else {
	$("#planningTips").html("<div class='noprint'>Attention, pas d&apos;agent de cat&eacute;gorie A en fin de service.</div>");
	$("#planningTips").show();
	errorHighlight($("#planningTips"),"error");
      }
    }
  });
}