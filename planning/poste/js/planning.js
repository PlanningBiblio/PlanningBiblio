/**
Planning Biblio, Version 2.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/poste/js/planning.js
Création : 2 juin 2014
Dernière modification : 17 novembre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les scripts JS nécessaires à la page planning/poste/index.php (affichage et modification des plannings)
Fichier intégré par le fichier include/header.php avec la fonction getJSFiles.
*/

/* Variables globales :
- perso_id_origine transmise à ajax.updateCell.php pour maj de la base de données pour remplacer, barrer ou supprimer l'agent cliqué
- perso_nom_origine transmise à ajax.menudiv.php pour affichage du nom cliqué pour supprimer M. xxx, Barrer M. xxx
*/
perso_id_origine=0;
perso_nom_origine=null;

// Chargement de la page
$(document).ready(function(){
  // Vérifions si un agent de catégorie A est placé en fin de service
  verif_categorieA();

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
    // Centrer verticalement les textes (span sauf .pl-icon-hide)
    $(index).find("span:not(.pl-icon-hide)").each(function(j,jtem){
      var top=(($(jtem).closest("div").height()-$(jtem).height())/2)-4;
      $(jtem).css("position","relative");
      $(jtem).css("top",top);
    });
  });
  
  // Masque les tableaux selon l'information garder en session
  var tableId=$("#tableau").attr("data-tableId");
  if(tableId){
    $.ajax({
      url: "planning/poste/ajax.getHiddenTables.php",
      type: "post",
      dataType: "json",
      data: {tableId: tableId},
      success: function(result){
	if(!result){
	  return;
	}

	result=JSON.parse(result);
	for(i in result){
	  $(".tableau"+result[i]).hide();
	}
	afficheTableauxDiv();
      },
      error: function(result){
	CJInfo(result.responseText,"error");
      }
    });
  }

});

// Evénements JQuery
$(function() {
  
  // Déverrouillage du planning
  $("#icon-lock").click(function(){
    var date=$(this).attr("data-date");
    var site=$(this).attr("data-site");
    
    $.ajax({
      url: "planning/poste/ajax.validation.php",
      dataType: "json",
      data: {date: date, site: site, verrou: 0 },
      type: "get",
      success: function(result){
	if(result[1]=="highlight"){
	  $("#icon-lock").hide();
	  $(".pl-validation").hide();
	  $("#icon-unlock").show();
	  // data-verrou : pour activer le menudiv
	  $("#planning-data").attr("data-verrou",0);
	}
	
	// Affichage des lignes vides
	$(".pl-line").show();
	CJInfo(result[0],result[1]);
      },
      error: function(result){
	CJInfo("Erreur lors du dev&eacute;rrouillage du planning","error");
      }
    });
  });

  // Validation du planning
  $("#icon-unlock").click(function(){
    var date=$(this).attr("data-date");
    var site=$(this).attr("data-site");

    $.ajax({
      url: "planning/poste/ajax.validation.php",
      dataType: "json",
      data: {date: date, site: site, verrou: 1 },
      type: "get",
      success: function(result){
	if(result[1]=="highlight"){
	  $("#icon-unlock").hide();
	  $("#icon-lock").show();
	  $(".pl-validation").html(result[2]);
	  $(".pl-validation").show();
	  // data-verrou : pour désactiver le menudiv
	  $("#planning-data").attr("data-verrou",1);
	  // data-validation : actualise la date de validation pour éviter un refresh_poste inutile
	  $("#planning-data").attr("data-validation",result[3]);
	  // refresh_poste : contrôle toute les 30 sec si le planning est validé depuis un autre poste
	  setTimeout("refresh_poste()",30000);
	}
	
	// Envoi des notifications
	planningNotifications(date);

	// Masque les lignes vides
	hideEmptyLines();

	CJInfo(result[0],result[1]);
      },
      error: function(result){
	CJInfo("Erreur lors de la validation du planning","error");
      }
    });
  });

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
	  var text2=text.val().replace(/\n/g,"#br#");
	  var text3=text.val().replace(/\n/g,"<br/>");
	  $.ajax({
	    dataType: "json",
	    url: "planning/poste/ajax.notes.php",
	    type: "post",
	    data: {date: $("#date").val(), site: $("#site").val(), text: encodeURIComponent(text2)},
	    success: function(result){
	      if(result.error){
		CJInfo(result.error,"error");
	      }
	      else{
		if(result.notes){
		  $("#pl-notes-button").val("Modifier le commentaire");
		  $("#pl-notes-div1").show();
		  var suppression="";
		}else{
		  $("#pl-notes-button").val("Ajouter un commentaire");
		  $("#pl-notes-div1").hide();
		  var suppression="Suppression du commentaire : ";
		}	
		// Met à jour le texte affiché en bas du planning
		$("#pl-notes-div1").html(result.notes);
		$("#pl-notes-div1-validation").html(suppression+result.validation);
		CJInfo("Le commentaire a été modifié avec succès","success");
		// Ferme le dialog
	      }
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
  
  
  // Formulaire Appel à disponibilité
  $( "#pl-appelDispo-form" ).dialog({
    autoOpen: false,
    height: 480,
    width: 650,
    modal: true,
    buttons: {
      "Envoyer": function() {
	allFields.removeClass( "ui-state-error" );
	var bValid = true;

	if ( bValid ) {
	  // Envoi le mail
	  var sujet=$( "#pl-appelDispo-sujet" ).val();
	  var message=$( "#pl-appelDispo-text" ).text();
	  sujet=sujet.trim();
	  message=message.trim();
	  message=message.replace(/\n/g,"<br/>");
	  
	  // L'objet appelDispoData contient les infos site, poste, date, debut, fin et agents
	  // Variable Globale définie lors du clic sur le lien "Appel à disponibilité", fonction appelDispo
	  // On ajoute le sujet et le message à cet objet et on l'envoi au script PHP pour l'envoi du mail
	  appelDispoData.sujet=sujet;
	  appelDispoData.message=message;

	  $( "#pl-appelDispo-form" ).dialog( "close" );
	  
	  $.ajax({
	    dataType: "json",
	    url: "planning/poste/ajax.appelDispoMail.php",
	    type: "post",
	    data: appelDispoData,
	    success: function(result){

	      if(result.error){
		CJInfo(result.error,"error");
	      }
	      else{
		CJInfo("L'appel à disponibilité a bien été envoyé","success");
	      }
	    },
	    error: function(){
	      updateTips("Une erreur est survenue lors de l'envoi de l'e-mail");
	    }
	  });
	}
      },

      Annuler: function() {
	$( this ).dialog( "close" );
      }
    },

    close: function() {
      updateTips("Envoyez un e-mail aux agents disponibles pour leur demander s&apos;ils sont volontaires pour occuper le poste choisi.");
      allFields.removeClass( "ui-state-error" );
    }
  });


  $(".cellDiv").contextmenu(function(){
    $(this).closest("td").attr("data-perso-id",$(this).attr("data-perso-id"));
    majPersoOrigine($(this).attr("data-perso-id"));
  });
  
  // Création du MenuDiv : menu affichant la liste des agents pour les placer dans les cellules
  $(".menuTrigger").contextmenu(function(e){
    // Si le planning est verrouillé ou pas admin, on quitte
    if($("#planning-data").attr("data-verrou")>0 || $("#planning-data").attr("data-autorisation")!=1){
      return false;
    }
    cellule=$(this).attr("data-cell");
    date=$("#date").val();
    debut=$(this).attr("data-start");
    fin=$(this).attr("data-end");
    poste=$(this).attr("data-situation");
    perso_id=$(this).attr("data-perso-id");
    site=$("#site").val();
    
    // On supprime l'ancien menu (s'il existe) pour eviter les problemes de remanence
    $("#menudiv1").remove();
    $("#menudiv2").remove();

    $.ajax({
      url: "planning/poste/ajax.menudiv.php",
      datatype: "json",
      data: {cellule: cellule, date: date, debut: debut, fin: fin, poste: poste, site: site, perso_nom: perso_nom_origine},
      type: "get",
      success: function(result){
	// si pas de result : on quitte (pas de droit admin)
	if(!result){
	  return false;
	}
	// result = tableau1 et tableau2
	result=JSON.parse(result);

	// Affichage des tableaux
	$("body").append("<div id='menudiv1'>"+result[0]+"</div>");
	$("body").append("<div id='menudiv2'>"+result[1]+"</div>");

	// Position horizontale du tableau 1
	if($(window).width()-e.clientX<$("#menudiv1").width()){
	  var left1=e.pageX-$("#menudiv1").width();
	}else{
	  var left1=e.pageX;
	}
	$("#menudiv1").css("left",left1);
	
	// Hauteur et position verticale du tableau 1
	var h_tab=$("#menudivtab1").height();
	var h_win=$(window).height();
	
	// Hauteur du tableau 1
	$("#menudiv1").css("max-height",h_win-20);
	$("#menudiv1").css("height",h_tab+5);

	// Si tableau plus grand que l'écran
	if(h_tab>h_win){
	  var top1=$(window).scrollTop()+10;
	}
	// Si click en bas de l'écran
	else if((e.pageY+(h_tab/2))>h_win+$(window).scrollTop()){
	  var top1=h_win+$(window).scrollTop()-h_tab-10;
	}
	// Si click en haut de l'écran
	else if((e.pageY-(h_tab/2))<$(window).scrollTop()){
	  var top1=$(window).scrollTop()+10;
	}
	// Sinon
	else{
	  var top1=e.pageY-(h_tab/2);
	}
	
	$("#menudiv1").css("top",top1);

	// Position horizontale du tableau 2
	if($(window).width()-e.clientX<($("#menudiv1").width()+$("#menudiv2").width())){
	  var left2=left1-$("#menudiv2").width();
	}else{
	  var left2=left1+$("#menudiv1").width();
	}
	$("#menudiv2").css("left",left2);
      },

      error: function(result){
	CJInfo("Impossible d'afficher le menu des agents.#BR#"+result.responseText,"error");
      }
    });
    return false ;
  });

  // Masque le menu lorsque l'on clique en dehors
  $(document).click(function(){
    $("#menudiv1").remove();
    $("#menudiv2").remove();
  });
  
  // Masque le menu lorsque l'on appuye sur échappe
  $(document).keydown(function(e) {
    // ESCAPE key pressed
    if (e.keyCode == 27) {
      $("#menudiv1").remove();
      $("#menudiv2").remove();
    }
  });
  
  $(".masqueTableau").click(function(){
    var id=$(this).attr("data-id");
    // Masque le tableau
    $(".tableau"+id).hide();
    
    // Affiche les liens pour réafficher les tableaux masqués
    afficheTableauxDiv();
  });

});


// Fonctions JavaScript

/**
 * Affiche les tableaux masqués de la page planning
 */
function afficheTableau(id){
  $(".tableau"+id).show();
  afficheTableauxDiv();
}

/**
 * Affiche les liens permettant d'afficher les tableaux masqués en bas du planning
 * Enregistre la liste des tableaux cachés dans la base de données
 */
function afficheTableauxDiv(){
  // Affichage des liens en bas du planning
  $("#afficheTableaux").remove();
  
  var tab=new Array();
  var hiddenTables=new Array();
  $(".tr_horaires .td_postes:hidden").each(function(){
    var tabId=$(this).attr("data-id");
    var tabTitle=$(this).attr("data-title");
    var exist = false;
    for(i in hiddenTables){
      if(hiddenTables[i] == tabId){
        exist = true;
      }
    }
    if(!exist){
      tab.push("<a href='JavaScript:afficheTableau("+tabId+");'>"+tabTitle+"</a>");
      hiddenTables.push(tabId);
    }
  });
  
  if(tab.length>0){
    $("#tabsemaine1").after("<div id='afficheTableaux' class='noprint'>Tableaux masqués : "+tab.join(" ; ")+"</div>");
  }
  
  // Enregistre la liste des tableaux cachés dans la base de données
  var tableId=$("#tableau").attr("data-tableId");
  hiddenTables=JSON.stringify(hiddenTables);
  $.ajax({
    url: "planning/poste/ajax.hiddenTables.php",
    type: "post",
    dataType: "json",
    data: {tableId: tableId, hiddenTables: hiddenTables},
    success: function(result){
    },
    error: function(result){
    }
  });
}


/**
 * appelDispo : Ouvre une fenêtre permettant d'envoyer un mail aux agents disponibles pour un poste et créneau horaire choisis
 * Appelée depuis le menu permettant de placer les agents dans le plannings (ajax.menudiv.php)
 */
function appelDispo(site,siteNom,poste,posteNom,date,debut,fin,agents){
  // Variable globale à utiliser lors de l'envoi du mail
  appelDispoData={site:site, poste:poste, date:date, debut:debut, fin:fin, agents:agents};
  
  // Récupération du message par défaut depuis la config.
  $.ajax({
    url: "planning/poste/ajax.appelDispoMsg.php",
    type: "post",
    dataType: "json",
    data: {},
    success: function(result){
      // Récupération des infos de la base de données, table config
      var sujet=result[0];
      var message=result[1];
      
      // Remplacement des valeurs [poste] [date] [debut] [fin]
      if(siteNom){
	posteNom+=" ("+siteNom+")";
      }

      sujet=sujet.replace("[poste]",posteNom);
      sujet=sujet.replace("[date]",dateFr(date));
      sujet=sujet.replace("[debut]",heureFr(debut));
      sujet=sujet.replace("[fin]",heureFr(fin));

      message=message.replace("[poste]",posteNom);
      message=message.replace("[date]",dateFr(date));
      message=message.replace("[debut]",heureFr(debut));
      message=message.replace("[fin]",heureFr(fin));

      // Mise à jour du formulaire
      $( "#pl-appelDispo-sujet" ).val(sujet);
      $( "#pl-appelDispo-text" ).text(message);
      $( "#pl-appelDispo-form" ).dialog( "open" );
    },
    error: function(result){
      CJInfo(result.responseText,"error");
    }
  });
}


/**
 * bataille_navale : menu contextuel : met à jour la base de données en arrière plan et affiche les modifs en JS dans le planning
 * Récupére en Ajax les id, noms, prénom, service, statut dans agents placés
 * Met à jour la base de données en arrière plan
 * Refait ensuite l'affichage complet de la cellule. Efface est remplit la cellule avec les infos récupérées du fichier ajax.updateCell.php
 * Les cellules sont identifiables, supprimables et modifiables indépendament des autres
 * Les infos service et statut sont utilisées pour la mise en forme des cellules : utilisation des classes service_ et statut_
 */
function bataille_navale(poste,date,debut,fin,perso_id,barrer,ajouter,site,tout){
  if(site==undefined || site==""){
    site=1;
  }

  if(tout==undefined){
    tout=0;
  }
  
  var sr_config_debut=$("#planning-data").attr("data-sr-debut");
  var sr_config_fin=$("#planning-data").attr("data-sr-fin");

  $.ajax({
    url: "planning/poste/ajax.updateCell.php",
    type: "post",
    dataType: "json",
    data: {poste: poste, date: date, debut: debut, fin: fin, perso_id: perso_id, perso_id_origine: perso_id_origine, barrer: barrer, ajouter: ajouter, site: site, tout: tout},
    success: function(result){
      $("#td"+cellule).html("");
      
      // Suppression du sans repas sur les cellules ainsi marquée
      if(fin > sr_config_debut && debut < sr_config_fin){
        $(".agent_"+perso_id_origine).each(function(){
          var sr_debut=$(this).closest("td").data("start");
          var sr_fin=$(this).closest("td").data("end");
          if(sr_fin > sr_config_debut && sr_debut < sr_config_fin){
            $(this).find(".sansRepas").remove();
          }
        });
      }
      
      // Si pas de résultat (rien n'est affiché dans la cellule modifiée), 
      // on réinitialise perso_id_origine pour ne pas avoir de rémanence pour la gestion de SR et suppression
      if(!result){
        majPersoOrigine(0);
      }

      for(i in result){
        // Exemple de cellule
        // <div id='cellule11_0' class='cellule statut_bibas service_permanent' >Christophe C.</div>

        var title = '';
        
        var agent=result[i]["nom"]+" "+result[i]["prenom"].substr(0,1)+".";
        var perso_id=result[i]["perso_id"];

        // classes : A définir en fonction du statut, du service et des absences
        var classes="cellDiv";
        // Absences, suppression
        // absent == 1 : Absence validée ou absence sans gestion des validations
        var absence_valide = false;
        if(result[i]["absent"]=="1" || result[i]["supprime"]=="1"){
          classes+=" red striped";
          absence_valide = true;
        // absent == 2 : Absence non validée
        }else if(result[i]['absent'] == '2'){
          classes+=" red";
          title = agent+=' : Absence non validée';
        }

        // congés == 1 : Congé validé
        if(result[i]["conges"] == '1'){
          classes+=" orange striped";
          absence_valide = true;
        // congés == 2 : Congé non validé. Mais on ne le marque pas si une absence validée est déjà marquée
        }else if(result[i]['conges'] == '2' && absence_valide==false){
          classes+=" orange";
          title = agent+=' : Congé non validé';
        }

        // Si une absence ou un congé est validé, on efface title pour ne pas afficher XXX non validé(e)
        if(absence_valide){
          title = null;
        }

        // Service et Statut
        classes+=" service_"+result[i]["service"].toLowerCase().replace(" ","_");
        classes+=" statut_"+result[i]["statut"].toLowerCase().replace(" ","_");
        
        // Qualifications (activités) de l'agent
        classes+=' '+result[i]['activites'];

        var agent=result[i]["nom"]+" "+result[i]["prenom"].substr(0,1)+".";
        var perso_id=result[i]["perso_id"];

        // Sans Repas
        if(result[i]["sr"]){
          // Ajout du sans repas sur la cellule modifiée
          agent+="<font class='sansRepas'> (SR)</font>";

          // Ajout du sans repas sur les autres cellules concernées
          $(".agent_"+perso_id).each(function(){
            var sr_debut=$(this).closest("td").data("start");
            var sr_fin=$(this).closest("td").data("end");
            if(sr_fin > sr_config_debut && sr_debut < sr_config_fin){
              if($(this).text().indexOf("(SR)")==-1){
                $(this).append("<font class='sansRepas'> (SR)</font>");
              }
            }
          });
        }

        // Création d'une balise span avec les classes cellSpan et agent_ de façon à les repérer et agir dessus 
        debut=debut.replace(":","");
        fin=fin.replace(":","");
        var span="<span class='cellSpan agent_"+perso_id+"' title='"+title+"'>"+agent+"</span>";
        var div="<div id='cellule"+cellule+"_"+i+"' class='"+classes+"' data-perso-id='"+perso_id+"' oncontextmenu='majPersoOrigine("+perso_id+");'>"+span+"</div>"
        // oncontextmenu='majPersoOrigine("+perso_id+");' : necessaire car l'événement JQuery contextmenu sur .cellDiv ne marche pas sur les cellules modifiées
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

      // cacher le menudiv
      $("#menudiv1").remove();
      $("#menudiv2").remove();

      },
      error: function(result){
        CJInfo("Une erreur est survenue lors de l'enregistrement du planning.","error");
      }
    });

  // Affiche un message en haut du planning si pas de catégorie A en fin de service 
  verif_categorieA();

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
}

//	groupe_tab : utiliser pour menudiv
function groupe_tab(id,tab,hide,me){			// améliorer les variables (tableaux) pour plus d'évolution
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
  
  // Hauteur et position verticale du tableau 2 (menudiv2)
  var h_tab=$("#menudivtab2").height();
  var h_win=$(window).height();
  var pos=me.offset();
  var y=pos.top+me.height()/2;

  // Hauteur
  $("#menudiv2").css("height",h_tab+5);    
  $("#menudiv2").css("max-height",h_win-20);

  // Position
  // Si tableau plus grand que l'écran
  if(h_tab>h_win){
    var top2=$(window).scrollTop()+10;
  }
  // Si click en bas de l'écran
  else if((y+(h_tab/2))>h_win+$(window).scrollTop()){
    var top2=h_win+$(window).scrollTop()-h_tab-10;
  }
  // Si click en haut de l'écran
  else if((y-(h_tab/2))<$(window).scrollTop()){
    var top2=$(window).scrollTop()+10;
  }
  // Sinon
  else{
    var top2=y-(h_tab/2);
  }
  $("#menudiv2").css("top",top2);

}

function groupe_tab_hide(){
  $(".tr_liste").each(function(){
    $(this).hide();
  });
}


// Masque les lignes vides
function hideEmptyLines(){
  if($("#planning-data").attr("data-lignesVides")=="0" &&  $("#planning-data").attr("data-verrou")=="1"){
    $(".pl-line").each(function(){
      var hide=true;
      $(this).find(".menuTrigger").each(function(){
	if($(this).text()){
	  hide=false;
	}
      });
      if(hide==true){
	$(this).hide();
      }
    });
  }
}


/* majPersoOrigine : 
  Fonction permettant de mettre à jour les variables globales perso_xx_origine lors de la mise à jour d'une cellule
  C'est variables permette d'informer ajax.menudiv.php sur l'agent cliqué (id pour maj de la base de données, nom pour affichage)
*/
function majPersoOrigine(perso_id){
  perso_id_origine=perso_id;
  perso_nom_origine=$(".agent_"+perso_id+":eq(0)").text();
}


/** @function planningNotifications
 *  @param srting date
 *  Envoie les notifications aux agents concernés par des plannings validés ou modifiés
 */
function planningNotifications(date){
  $.ajax({
    url: "planning/poste/ajax.notifications.php",
    dataType: "json",
    data: {date: date},
    type: "get",
    success: function(result){
    },
    error: function(result){
      CJInfo(result.responseText,"error");
    }
  });
}

// refresh_poste : Actualise le planning en cas de modification
function refresh_poste(){
  if($("#planning-data").attr("data-verrou")==0){
    return false;
  }
  var validation=$("#planning-data").attr("data-validation");
    $.ajax({
    url: "planning/poste/ajax.refresh.php",
    type: "post",
    dataType: "json",
    data: {"date": $("#date").val(), "site": $("#site").val()},
    success: function(result){
      if(result!=validation){
	window.location.reload(false);
      }else{
	setTimeout("refresh_poste()",30000);
      }
    },
    error: function(result){
      CJInfo(result.responseText,"error");
      setTimeout("refresh_poste()",30000);
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
