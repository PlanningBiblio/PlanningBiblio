/*
Planning Biblio, Version 1.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/js/planning.js
Création : 2 juin 2014
Dernière modification : 13 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les scripts JS nécessaires à la page planning/poste/index.php (affichage et modification des plannings)
Fichier intégré par le fichier include/header.php avec la fonction getJSFiles.
*/

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
    "oLanguage" : {"sUrl" : "js/dataTables/french.txt"}
  });

  if($("#pl-notes-text").text()){
    $("#pl-notes-button").val("Modifier le commentaire");
  }


  // Mise en forme des lignes du tableau planning
  // Pour chaque TD
  $("#tabsemaine1 td").each(function(i, index) {
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
  
  // Création du MenuDiv : menu affichant la liste des agents pour les placer dans les cellules
  $(".menuTrigger div").contextmenu(function(e){
    var dom=$(this).get( 0 );
    dom=dom.nodeName;
    if(dom=="TD"){
    cellule=$(this).attr("data-cell");
    debut=$(this).attr("data-start");
    fin=$(this).attr("data-end");
    poste=$(this).attr("data-situation");
    perso_id=0;
    }else{
      cellule=$(this).closest("td").attr("data-cell");
      debut=$(this).closest("td").attr("data-start");
      fin=$(this).closest("td").attr("data-end");
      poste=$(this).closest("td").attr("data-situation");
      perso_id=$(this).attr("data-perso_id");
    }

    $.ajax({
      url: "planning/poste/ajax.menudiv.php",
      data: "&cellule="+cellule+"&debut="+debut+"&fin="+fin+"&poste="+poste,
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
// 	bataille_navale : menu contextuel : met à jour la base de données en arrière plan et affiche les modifs en JS dans le planning
function bataille_navale(poste,debut,fin,perso_id,nom,barrer,ajouter,classe){

  // Récupérer en Ajax les id, noms, prénom, service, statut dans agents placés
  // Refaire la réquête précédente en $.ajax et y intégré la récupération des données
  // Refaire ensuite l'affichage complet de la cellule. Tout effacer est recommencer avec le résultat de 
    // la requête ajax.
  //Attention à bien remettre en place les classes, les SR et les DP
  // Pour les classes : les infos service et statut seront utilisées (voir s'il y a besoin d'autre chose : voir CSS)
  // Pour les DP, il faudra peut être faire une autre requête, essayer de tout traiter avec une seule transaction ajax
  // Pour les classes : elles devront couvrir toutes la largeur du TD : 
  // évitons de travailler sur la hauteur du TD : adaptation automatique : garder la hauteur déclarée en CSS en min-height plutôt que height
  
  // Les cellule doivent être identifiable, supprimable et modifiable indépendament des autres
  
  $.ajax({
    url: "planning/poste/ajax.updateCell.php",
    data: "&poste="+poste+"&debut="+debut+"&fin="+fin+"&perso_id="+perso_id+"&barrer="+barrer+"&ajouter="+ajouter,
    type: "get",
    success: function(result){
      if(result){
	result=JSON.parse(result);
      }

      $("#td"+cellule).html("");
      for(i in result){
	// Exemple de cellule
	// <div id='cellule11_0' class='cellule statut_bibas service_permanent' >Christophe C.</div>

	// classes : A définir en fonction du statut, du service et des absences
	var classes="cellDiv";
	// Absences, suppression
	if(result[i]["absent"]=="1" || result[i]["supprime"]=="1"){
	  classes+=" red striped";
	}

	// Congés : A CONTINUER
/*	if(result[i]["conges"]){
	  classes+=" orange striped";
	}*/

	// Service et Statut
	classes+=" service_"+result[i]["service"].toLowerCase().replace(" ","_");
	classes+=" statut_"+result[i]["statut"].toLowerCase().replace(" ","_");
	
	var agent=result[i]["nom"]+" "+result[i]["prenom"].substr(0,1)+".";
	var perso_id=result[i]["perso_id"];
	
	$("#td"+cellule).append("<div id='cellule"+cellule+"_"+i+"' class='"+classes+"' data-perso_id='"+perso_id+"'> <span class='cellSpan'>"+agent+"</span></div>");

      }
/*
$cellule="<td id='td{$GLOBALS['idCellule']}' colspan='$colspan' style='text-align:center;' class='$tdClass' 
    oncontextmenu='cellule={$GLOBALS['idCellule']}'
    data-start='$debut' data-end='$fin' data-situation='$poste' data-cell='{$GLOBALS['idCellule']}' >";
  for($i=0;$i<count($resultats);$i++){
    $cellule.="<div id='cellule{$GLOBALS['idCellule']}_$i' class='cellDiv menuTrigger {$classe[$i]}' data-perso_id='{$resultats[$i]['perso_id']}'>{$resultats[$i]['text']}</div>";
*/
    
    
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



/*      if(!perso_id && !barrer){			// Supprimer tout
	$("#cellule"+cellule+"_0").html("&nbsp;");
	$("#cellule"+cellule+"_0").css("textDecoration","");
	$("#cellule"+cellule+"_1").hide();
	$("#cellule"+cellule+"_0").removeClass();
	$("#td"+cellule).removeClass();
      }
      else if(!perso_id && barrer){			// Barrer l'(es) agent(s) placé(s)
	$("#cellule"+cellule+"_0").css("color","red");
	$("#cellule"+cellule+"_0").css("textDecoration","line-through");
	$("#cellule"+cellule+"_1").css("color","red");
	$("#cellule"+cellule+"_1").css("textDecoration","line-through");
      }
      else if(perso_id && !barrer && !ajouter){	// Remplacer l'agent placé par un autre
	$("#cellule"+cellule+"_0").text(nom);
	$("#cellule"+cellule+"_0").css("color","black");
	$("#cellule"+cellule+"_0").css("textDecoration","");
	$("#td"+cellule).attr("class",classe);
	$("#cellule"+cellule+"_0").attr("class","cellule "+classe);
	$("#cellule"+cellule+"_1").hide();
      }
      else if(perso_id && barrer){			// barrer et ajoute un autre
	$("#td"+cellule).removeClass();
	$("#cellule"+cellule+"_0").css("textDecoration","line-through");
	$("#cellule"+cellule+"_0").css("color","red");
	$("#cellule"+cellule+"_1").text(nom);
	$("#cellule"+cellule+"_1").attr("class","cellule "+classe);
	$("#cellule"+cellule+"_1").show();
      }
      else if(perso_id && ajouter){			// ajouter un agent
	if($("#cellule"+cellule+"_0").text()<nom){
	  var nom1=$("#cellule"+cellule+"_0").text();
	  var nom2=nom;
	  var classe1=$("#cellule"+cellule+"_0").attr("class");
	  var classe2=classe;
	}
	else{
	  var nom1=nom;
	  var nom2=$("#cellule"+cellule+"_0").text();
	  var classe1=classe;
	  var classe2=$("#cellule"+cellule+"_0").attr("class");
	}
	$("#td"+cellule).removeClass();
	$("#cellule"+cellule+"_0").text(nom1);
	$("#cellule"+cellule+"_1").text(nom2);
	$("#cellule"+cellule+"_0").attr("class",classe1);
	$("#cellule"+cellule+"_1").attr("class",classe2);
	$("#cellule"+cellule+"_1").css("color","black");
	$("#cellule"+cellule+"_1").css("textDecoration","");
	$("#cellule"+cellule+"_1").show();
      }*/
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
    )
  [1] => Array (
    ...
*/
/*
  if(!perso_id && !barrer){			// Supprimer tout
    $("#cellule"+cellule+"_0").html("&nbsp;");
    $("#cellule"+cellule+"_0").css("textDecoration","");
    $("#cellule"+cellule+"_1").hide();
    $("#cellule"+cellule+"_0").removeClass();
    $("#td"+cellule).removeClass();
  }
  else if(!perso_id && barrer){			// Barrer l'(es) agent(s) placé(s)
    $("#cellule"+cellule+"_0").css("color","red");
    $("#cellule"+cellule+"_0").css("textDecoration","line-through");
    $("#cellule"+cellule+"_1").css("color","red");
    $("#cellule"+cellule+"_1").css("textDecoration","line-through");
  }
  else if(perso_id && !barrer && !ajouter){	// Remplacer l'agent placé par un autre
    $("#cellule"+cellule+"_0").text(nom);
    $("#cellule"+cellule+"_0").css("color","black");
    $("#cellule"+cellule+"_0").css("textDecoration","");
    $("#td"+cellule).attr("class",classe);
    $("#cellule"+cellule+"_0").attr("class","cellule "+classe);
    $("#cellule"+cellule+"_1").hide();
  }
  else if(perso_id && barrer){			// barrer et ajoute un autre
    $("#td"+cellule).removeClass();
    $("#cellule"+cellule+"_0").css("textDecoration","line-through");
    $("#cellule"+cellule+"_0").css("color","red");
    $("#cellule"+cellule+"_1").text(nom);
    $("#cellule"+cellule+"_1").attr("class","cellule "+classe);
    $("#cellule"+cellule+"_1").show();
  }
  else if(perso_id && ajouter){			// ajouter un agent
    if($("#cellule"+cellule+"_0").text()<nom){
      var nom1=$("#cellule"+cellule+"_0").text();
      var nom2=nom;
      var classe1=$("#cellule"+cellule+"_0").attr("class");
      var classe2=classe;
    }
    else{
      var nom1=nom;
      var nom2=$("#cellule"+cellule+"_0").text();
      var classe1=classe;
      var classe2=$("#cellule"+cellule+"_0").attr("class");
    }
    $("#td"+cellule).removeClass();
    $("#cellule"+cellule+"_0").text(nom1);
    $("#cellule"+cellule+"_1").text(nom2);
    $("#cellule"+cellule+"_0").attr("class",classe1);
    $("#cellule"+cellule+"_1").attr("class",classe2);
    $("#cellule"+cellule+"_1").css("color","black");
    $("#cellule"+cellule+"_1").css("textDecoration","");
    $("#cellule"+cellule+"_1").show();
  }
  $("#menudiv").hide();				// cacher le menudiv
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
  db=file("index.php?page=planning/poste/validation.php&menu=off");
  db=db.split("###");
  db=db[1];
  if(db!=validation){
    window.location.reload(false);
  }
  else{
    setTimeout("refresh_poste('"+validation+"')",30000);
  }
}