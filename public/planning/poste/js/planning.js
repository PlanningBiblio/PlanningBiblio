/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/js/planning.js
Création : 2 juin 2014
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

// La variable globale "cellules" est un tableau contenant les <div> ajoutés en JQuery avec la fonction bataille_navale
// Enregistrer ces éléments dans une variable globale permet t'intéragir avec eux lors de la modification du planning, notamment pour ajouter et supprimer la class pl-highlight (surbrillance au survol)
cellules = new Array();

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

  $('td.menuTrigger').hover(
    function() {
      if($("#planning-data").attr("data-verrou") >0 ||
        $("#planning-data").attr("data-autorisation") != 1){
        return false;
      }

      if ($(this).is(':last-child')) {
        return;
      }

      if ($(this).find('div').length == 0) {
        return;
      }

      if ($(this).text() == '') {
        return;
      }

      if ($(this).next().hasClass('cellule_grise')) {
        return;
      }

      if ($(this).next().find('div').length) {
        return;
      }

      $(this).find('a.arrow-right').show();
    },
    function() {
      $(this).find('a.arrow-right').hide();
    }
  );

  $('tr').on('click', '.arrow-right', function() {
    var cell = $(this).parent();
    var job = cell.data('situation');
    var cell_to = cell.next();
    var cellid = cell_to.data('cell');
    var cFrom = cell_to.data('start');
    var to = cell_to.data('end');
    var date = $('#date').val();
    var site = $('#site').val();

    i = 0;
    checkcopy_agents = [];

    cell.find('div').each(function() {
        var element = $(this).clone();
        var agent_id = element.data('perso-id');
        element.attr('id', cellid + '_' + i);
        checkcopy_agents.push(agent_id);
        i++;
    });

    checkcopy_agents = JSON.stringify(checkcopy_agents);

    $.ajax({
        url: url('ajax/planningjob/checkcopy'),
        type: "get",
        dataType: "json",
        data: {date: date, from: cFrom, to: to, agents: checkcopy_agents},
        success: function(result) {
            if (result.availables.length) {
                var agents = JSON.stringify(result.availables);
                bataille_navale(job, date, cFrom, to, agents, '', '', site, '', null, cellid);
            }
            if (result.unavailables) {
                var message = "Les agents suivants n'ont pas été placés car ils sont indisponibles de " + heureFr(cFrom) + " à " + heureFr(to) + " : " + result.unavailables;
                CJInfo(message, 'error');
            }
        },
        error: function(){
            CJInfo("Une erreur est survenue lors de la copie.", "error");
        }
    });
  });
});

// Evénements JQuery
$(function() {
  
  // Déverrouillage du planning
  $("#icon-lock").click(function(){
    var date=$('#date').val();
    var site=$('#site').val();
    var CSRFToken = $("#planning-data").attr("data-CSRFToken");

    $.ajax({
      url: "planning/poste/ajax.validation.php",
      dataType: "json",
      data: {date: date, site: site, verrou: 0, CSRFToken: CSRFToken },
      type: "get",
      success: function(result){
        if(result[1]=="highlight"){
          $("#icon-lock").hide();
          $(".pl-validation").hide();
          $('#planning-drop').show();
          $('#planning-import').show();
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
    var date=$('#date').val();
    var site=$('#site').val();
    var CSRFToken = $("#planning-data").attr("data-CSRFToken");

    $.ajax({
      url: "planning/poste/ajax.validation.php",
      dataType: "json",
      data: {date: date, site: site, verrou: 1, CSRFToken: CSRFToken },
      type: "get",
      success: function(result){
        if(result[1]=="highlight"){
          $("#icon-unlock").hide();
          $("#icon-lock").show();
          $(".pl-validation").html(result[2]);
          $(".pl-validation").show();
          $('#planning-drop').hide();
          $('#planning-import').hide();
          // data-verrou : pour désactiver le menudiv
          $("#planning-data").attr("data-verrou",1);
          // data-validation : actualise la date de validation pour éviter un refresh_poste inutile
          $("#planning-data").attr("data-validation",result[3]);
          // refresh_poste : contrôle toute les 30 sec si le planning est validé depuis un autre poste
          setTimeout("refresh_poste()",30000);
        }

        // Envoi des notifications
        planningNotifications(date, site, CSRFToken);

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
    $( '#pl-notes-tips' ).text("Vous pouvez écrire ici un commentaire qui sera affiché en bas du planning.");
    $( "#pl-notes-form" ).dialog( "open" );
    return false;
  });

  // Formulaire Notes
  $( "#pl-notes-form" ).dialog({
    autoOpen: false,
    height: 480,
    width: 650,
    modal: true,
    dialogClass: 'popup-background',
    buttons: {
      "Enregistrer": {
	click: function() {
          allFields.removeClass( "ui-state-error");
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
              data: {date: $("#date").val(), site: $("#site").val(), text: encodeURIComponent(text2), CSRFToken: $('#CSRFSession').val()},
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
                updateTips("Une erreur est survenue lors de l'enregistrement du commentaire", "error");
              }
            });
          }
        },
        text: 'Enregistrer'
      },

      Annuler: {
        click: function() {
          $( this ).dialog( "close" );
              },
        text: "Annuler",
        class: "ui-button-type2"
            },
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
	  appelDispoData.CSRFToken = $('#CSRFSession').val();

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
	      updateTips("Une erreur est survenue lors de l'envoi de l'e-mail", "error");
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
    majPersoOrigine($(this).attr("data-perso-id"));
  });
  
  // Création du MenuDiv : menu affichant la liste des agents pour les placer dans les cellules
  $(".menuTrigger").contextmenu(function(e){
    // Si le planning est verrouillé ou pas admin, on quitte
    if($("#planning-data").attr("data-verrou")>0 || $("#planning-data").attr("data-autorisation")!=1){
      return false;
    }
    cellule=$(this).attr("data-cell");
    CSRFToken=$("#CSRFSession").val();
    date=$("#date").val();
    debut=$(this).attr("data-start");
    fin=$(this).attr("data-end");
    poste=$(this).attr("data-situation");
    perso_id=$(this).attr("data-perso-id");
    site=$("#site").val();

    postesFrontOffice = $("#planning-data").attr("data-postesFrontOffice");

    // On supprime l'ancien menu (s'il existe) pour eviter les problemes de remanence
    emptyContextMenu();

    $.ajax({
      url: url('planningjob/contextmenu'),
      datatype: "json",
      data: {cellule: cellule, CSRFToken: CSRFToken, date: date, debut: debut, fin: fin, poste: poste, site: site, perso_nom: perso_nom_origine, perso_id:perso_id_origine, postesFrontOffice:postesFrontOffice},
      type: "get",
      success: function(result){
        // si pas de result : on quitte (pas de droit admin)
        if(!result){
          return false;
        }

        // Affichage des tableaux
        initContextMenu(result);

        
        // Largeur du tableau 1 (on s'adapte à la longueur des lignes)
        $("#menudiv1").css("width",250);
        var width = $('#menudiv1 > table').width() +20;
        $("#menudiv1").css("width",width);

	// Position horizontale du tableau 1
	if( $(window).width() +5 -e.clientX < $("#menudiv1").width() ){
	  var left1 = e.pageX -5 -$("#menudiv1").width();
	}else{
	  var left1 = e.pageX +5;
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

      },

      error: function(result){
	CJInfo("Impossible d'afficher le menu des agents.#BR#"+result.responseText,"error");
      }
    });
    return false ;
  });

  // Masque le menu lorsque l'on clique en dehors
  $(document).click(function(){
    emptyContextMenu();
  });
  
  // Masque le menu lorsque l'on appuye sur échappe
  $(document).keydown(function(e) {
    // ESCAPE key pressed
    if (e.keyCode == 27) {
      emptyContextMenu();
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

function initContextMenu(data) {
  $("body").append("<div id='menudiv1'></div>");
  $("body").append("<div id='menudiv2'></div>");
  fillContextMenuLevel1(data);
  fillContextMenuLevel2(data);
}

function fillContextMenuLevel2(data) {
  if (data.menu2.agents === undefined) {
    return;
  }

  // Table for level two.
  menu2 = $('<table>').attr({
    cellspacing: '0',
    cellpadding: '0',
    id: 'menudivtab2',
    rules: 'rows',
    border: '1'
  });

  $.each(data.menu2.agents, function(index, agent) {
    menu2.append(ContextMenu2agents(data, agent));
  });

  menu2.appendTo('#menudiv2');
}

function ContextMenu2agents(data, agent) {
  td = $('<td>').attr({
    onclick: 'bataille_navale("' + data.position_id + '","' + data.date + '","'
              + data.start + '","' + data.end + '",' + agent.id + ',0,0,"' + data.site + '");'
  });

  font = $('<font>').attr({
    style: 'color:' + agent.color
  });

  // Agent's name
  sp_name = $('<span>').attr({
    class: 'menudiv-nom',
    title: agent.name_title
  }).html(agent.name);
  font.append(sp_name);

  // No lunch
  if (agent.no_lunch) {
    font.append('&nbsp;');
    nolunch = $('<span>').attr({
      class: 'red bold',
      title: "Sans Repas, l'agent n'a aucun créneau horaire pour prendre son repas"
    }).html('(SR)');
    font.append(nolunch);
  }

  // Already placed
  if (agent.placed) {
    font.append('&nbsp;');
    placed = $('<span>').attr({
      class: 'red bold',
      title: "L'agent est déjà placé sur ce poste dans la journée"
    }).html('(DP)');
    font.append(placed);
  }

  // 2 public service
  if (agent.two_sr) {
    font.append('&nbsp;');
    two_sp = $('<span>').attr({
      class: 'red bold',
      title: "2 plages de service public consécutives"
    }).html('(2 SP)');
    font.append(two_sp);
  }

  // Journey time too short
  if (agent.journey) {
    font.append('&nbsp;');
    journey = $('<span>').attr({
      class: 'red bold',
      title: "Temps de trajet insuffisant pour rejoindre le poste"
    }).html('(T)');
    font.append(journey);
  }

  // Time between absence and position
  if (agent.time_limit) {
    font.append('&nbsp;');
    time_limit = $('<span>').attr({
      class: 'red bold',
      title: "Délai insuffisant entre une indisponibilité et une plage de service public"
    }).html('(A)');
    font.append(time_limit);
  }

  // Exclusion.
  if (agent.exclusion.length !== 0) {
    font.append('&nbsp;(');
    separator = '';
    $.each(agent.exclusion, function(index, e) {
      title_attr = '';
      content = separator + '';
      separator = ', ';

      if (e == 'times') {
        title_attr = "Les horaires de l'agent ne lui permettent pas d'occuper ce poste";
        content = 'Horaires';
      }

      if (e == 'break') {
        title_attr = "La pause de cet agent n'est pas respectée";
        content += 'Pause';
      }

      if (e == 'other_site') {
        title_attr = "L'agent est prévu sur un autre site";
        content += 'Autre site';
      }

      if (e == 'site') {
        title_attr = "L'agent n'est pas prévu sur ce site";
        content += 'Site';
      }

      if (e == 'skills') {
        title_attr = "L'agent n'a pas toutes les qualifications requises pour occuper ce poste";
        content += 'Activités';
      }

      if (e == 'no_cat') {
        title_attr = "L'agent n'appartient à aucune des catégories requises" + data.category + " pour occuper ce poste";
        content += 'Catégorie';
      }

      if (e == 'wrong_cat') {
        title_attr = "L'agent n'appartient pas à la catégorie requise" + data.category + " pour occuper ce poste";
        content += 'Catégorie';
      }

      exclusion = $('<span>').attr({
        title: title_attr
      }).html(content);
      font.append(exclusion);

    });
    font.append(')');
  }

  if (data.display_times) {
    div_times = $('<div>').attr({
      class: 'menudiv-heures'
    });

    times_day = $('<font>').attr({
      title: 'Heures du jour'
    }).html(agent.times.day);
    div_times.append(times_day);
    div_times.append(' / ');

    times_week = $('<font>').attr({
      title: 'Heures de la semaine'
    }).html(agent.times.week);
    div_times.append(times_week);
    div_times.append(' / ');

    times_quota = $('<font>').attr({
      title: agent.times.quota_title
    }).html(agent.times.quota);
    div_times.append(times_quota);

    if (data.last_four_weeks) {
      div_times.append(' / ');
      times_four = $('<font>').attr({
        title: 'Heures des 4 dernières semaines'
      }).html(agent.times.times_four_weeks);
      div_times.append(times_four);
    }

    font.append(div_times);
  }

  td.append(font);

  td2 = $('<td>').attr({
    style: 'text-align:right;width:20px'
  });

  tr = $('<tr>').attr({
    id: 'tr' + agent.id,
    style: 'height:21px;' + agent.display,
    onmouseover: agent.group_hide + ' plMouseOver(' + agent.id + ');',
    onmouseout: 'plMouseOut(' + agent.id + ');',
    class: agent.class + ' ' + agent.class_tr_list + ' menudiv-tr'
  });

  tr.append(td);

  if (data.nb_agents > 0 && data.nb_agents < data.max_agents) {
    add = $('<a>').attr({
      href: 'javascript:bataille_navale("' + data.position_id + '","'
            + data.date + '","' + data.start + '","' + data.end + '",'
            + agent.id + ',0,1,"' + data.site + '");'
    }).html('+');

    replace = $('<a>').attr({
      style: 'color:red',
      href: 'javascript:bataille_navale("' + data.position_id + '","'
            + data.date + '","' + data.start + '","' + data.end + '",'
            + agent.id + ',1,1,"' + data.site + '");'
    }).html(' x&nbsp;');

    td2.append(add);
    td2.append(replace);
  }

  tr.append(td2);

  return tr;
}

function fillContextMenuLevel1(data) {
  // Table for level one
  menu1 = $('<table>').attr({
    frame: 'box',
    cellspacing: '0',
    cellpadding: '0',
    id: 'menudivtab1',
    rules: 'rows',
    border: '1'
  });

  // Add menu title
  menu1.append(contextMenuTitle(data));

  // Service (ClasseParService enabled)
  if (data.services !== undefined) {
    $.each(data.services, function(index, service) {
      menu1.append(contextMenuServices(service));
    });
  }

  // Agents (ClasseParService disabled)
  if (data.menu1.agents !== undefined) {
    $.each(data.menu1.agents, function(index, agent) {
      menu1.append(ContextMenu2agents(data, agent));
    });
  }

  // Unavailables agents main menu (agentsIndispo enabled)
  if (data.unavailables_agents !== undefined) {
    menu1.append(contextMenuUnavailable(data));
  }

  // Everybody (toutlemonde enabled)
  if (data.everybody && data.cell_enabled) {
    menu1.append(contextMenuEverybody(data));
  }

  if (data.nb_agents > 0 && data.cell_enabled) {
    // Remove agent.
    menu1.append(contextMenuRemove(data));

    // Score off agent.
    menu1.append(contextMenuScoreOff(data));
  }

  if (data.nb_agents > 1 && data.cell_enabled) {
    // Remove all.
    menu1.append(contextMenuRemoveAll(data));

    // Score off all.
    menu1.append(contextMenuScoreOffAll(data));
  }

  // Call for available agents.
  if (data.call_for_help && data.cell_enabled) {
    menu1.append(contextMenuCallForhelp(data));
  }

  // Disable ans enable cell.
  if (data.can_disable_cell) {
    menu1.append(contextMenuDisableCell(data));
  }

  menu1.appendTo('#menudiv1');

}

function contextMenuDisableCell(data) {
  on = 'bataille_navale("' + data.position_id + '","' + data.date + '","'
            + data.start + '","' + data.end + '",0,0,0,"' + data.site + '",1,-1);';
  title = 'Dégriser la cellule';

  if (data.cell_enabled) {
    on = 'bataille_navale("' + data.position_id + '","' + data.date + '","'
              + data.start + '","' + data.end + '",0,0,0,"' + data.site + '",1,1);';
    title = 'Griser la cellule';
  }

  td = $('<td>').attr({
    colspan: '2',
    onclick: on
  }).html(title);

  tr = $('<tr>').attr({
    class: 'menudiv-tr',
    onmouseover: 'groupe_tab_hide();'
  }).append(td);

  return tr;
}

function contextMenuCallForhelp(data) {
  td = $('<td>').attr({
    colspan: '2',
    id: 'td-appelDispo',
    onclick: 'appelDispo("' + data.site + '","' + data.site_name + '","'
              + data.position_id + '","' + data.position_name + '","'
              + data.date + '","' + data.start + '","' + data.end + '","'
              + data.call_for_help_agents + '");'
  }).html('Appel à disponibilité');

  if (data.call_for_help_nb) {
    nb_call = $('<strong>').html(data.call_for_help_nb);

    info = $('<span>').attr({
      title: data.call_for_help_info,
      style: 'position:absolute; right:5px;'
    }).append(nb_call);

    td.append(info);
  }

  tr = $('<tr>').attr({
    onmouseover: 'groupe_tab_hide()',
    class: 'menudiv-tr',
  }).append(td);

  return tr;
}

function contextMenuScoreOffAll(data) {
  td = $('<td>').attr({
    colspan: '2',
    class: 'red',
    onclick: 'bataille_navale("' + data.position_id + '","' + data.date + '","'
              + data.start + '","' + data.end + '",0,1,0,"' + data.site + '",1);'
  }).html('Tout barrer');

  tr = $('<tr>').attr({
    class: 'menudiv-tr'
  });

  attr = '';
  if (data.group_tab_hide) {
    attr = 'groupe_tab("vide","' + data.tab_agent + '",1,$(this));';
  }
  attr += ' groupe_tab_hide();';
  tr.attr('onmouseover', attr);

  tr.append(td);
  return tr;
}

function contextMenuRemoveAll(data) {
  td = $('<td>').attr({
    colspan: '2',
    onclick: 'bataille_navale("' + data.position_id + '","' + data.date + '","'
              + data.start + '","' + data.end + '",0,0,0,"' + data.site + '",1);'
  }).html('Tout supprimer');

  tr = $('<tr>').attr({
    class: 'menudiv-tr'
  });

  attr = '';
  if (data.group_tab_hide) {
    attr = 'groupe_tab("vide","' + data.tab_agent + '",1,$(this));';
  }
  attr += ' groupe_tab_hide();';
  tr.attr('onmouseover', attr);

  tr.append(td);
  return tr;
}

function contextMenuScoreOff(data) {
  td = $('<td>').attr({
    class: 'red',
    onclick: 'bataille_navale("' + data.position_id + '","' + data.date + '","'
              + data.start + '","' + data.end + '",0,1,0,"' + data.site + '");',
    onmouseover: 'plMouseOver(' + data.agent_id + ');',
    onmouseout: 'plMouseOut(' + data.agent_id + ');',
  }).html('Barrer ' + data.agent_name);

  tr = $('<tr>').attr({
    class: 'menudiv-tr'
  });

  attr = '';
  if (data.group_tab_hide) {
    attr = 'groupe_tab("vide","' + data.tab_agent + '",1,$(this));';
  }
  attr += ' groupe_tab_hide();';
  tr.attr('onmouseover', attr);

  tr.append(td);
  return tr;
}

function contextMenuRemove(data) {
  td = $('<td>').attr({
    colspan: '2',
    onclick: 'bataille_navale("' + data.position_id + '","' + data.date + '","'
              + data.start + '","' + data.end + '",0,0,0,"' + data.site + '");',
    onmouseover: 'plMouseOver(' + data.agent_id + ');',
    onmouseout: 'plMouseOut(' + data.agent_id + ');'
  }).html('Supprimer ' + data.agent_name);

  tr = $('<tr>').attr({
    class: 'menudiv-tr'
  });

  attr = '';
  if (data.group_tab_hide) {
    attr = 'groupe_tab("vide","' + data.tab_agent + '",1,$(this));';
  }
  attr += ' groupe_tab_hide();';
  tr.attr('onmouseover', attr);

  tr.append(td);
  return tr;
}

function contextMenuEverybody(data) {
  td = $('<td>').attr({
    colspan: '2',
    style: 'color:black;',
    onclick: 'bataille_navale("' + data.position_id + '","' + data.date + '","'
              + data.start + '","' + data.end + '",2,0,0,"' + data.site + '");',
  }).html('Tout le monde');

  tr = $('<tr>').attr({
    onmouseover: 'groupe_tab_hide();',
    class: 'menudiv-tr',
  }).append(td);

  return tr;
}

function contextMenuUnavailable(data) {
  unavailables = data.unavailables_agents;
  td = $('<td>').attr({
    colspan: '2',
    onmouseover: 'groupe_tab(' + unavailables.id
      + ',"' + data.tab_agent + '",'
      + data.group_tab_hide + ',$(this));'
  }).html('Agents indisponibles');

  tr = $('<tr>').attr({ class: 'menudiv-tr'}).append(td);

  return tr;
}

function contextMenuServices(data) {
  td = $('<td>').attr({
    colspan: '2',
    onmouseover: 'groupe_tab(' + data.id + ',"' + data.tab_agent + '",1,$(this));'
  }).html(data.service);

  tr = $('<tr>').attr({
    class: data.class + ' menudiv-tr',
  }).append(td);

  return tr;
}

function contextMenuTitle(data) {
  position = $('<div>').html(data.position_name);
  times = $('<div>').html(data.start_hr + ' - ' + data.end_hr);

  td = $('<td>').attr({ colspan: '2'}).append(position).append(times);

  tr = $('<tr>').attr({ class: 'menudiv-titre'}).append(td);

  return tr;
}

function emptyContextMenu() {
  $("#menudiv1").remove();
  $("#menudiv2").remove();
}

/**
 * Affiche les tableaux masqués de la page planning
 */
function afficheTableau(id){
  $(".tableau"+id).each(function(){
    if($(this).hasClass('empty-line')){}
    else{
      $(this).show();
    }
  });
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
    data: {tableId: tableId, hiddenTables: hiddenTables, CSRFToken: $('#CSRFSession').val()},
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

  var weekday = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
  var d = new Date(date);
  var jour = weekday[d.getDay()];

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
      sujet=sujet.replace("[jour]",jour);
      sujet=sujet.replace("[date]",dateFr(date));
      sujet=sujet.replace("[debut]",heureFr(debut));
      sujet=sujet.replace("[fin]",heureFr(fin));

      message=message.replace("[poste]",posteNom);
      message=message.replace("[jour]",jour);
      message=message.replace("[date]",dateFr(date));
      message=message.replace("[debut]",heureFr(debut));
      message=message.replace("[fin]",heureFr(fin));

      // Mise à jour du formulaire
      $( "#pl-appelDispo-sujet" ).val(sujet);
      $( "#pl-appelDispo-text" ).text(message);
      $( '#pl-appelDispo-tips' ).text("Envoyez un e-mail aux agents disponibles pour leur demander s'ils sont volontaires pour occuper le poste choisi.");
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
 * 
 * @param int perso_id : Si 0 = griser la cellule, si 2 = Tout le monde
 */
function bataille_navale(poste,date,debut,fin,perso_id,barrer,ajouter,site,tout,griser,cellid){
  if(griser==undefined){
    griser=0;
  }
  
  if(site==undefined || site==""){
    site=1;
  }

  if(tout==undefined){
    tout=0;
  }

  if (typeof cellid != 'undefined') {
    cellule = cellid;
  }

  var sr_config_debut=$("#planning-data").attr("data-sr-debut");
  var sr_config_fin=$("#planning-data").attr("data-sr-fin");
  var CSRFToken = $("#planning-data").attr("data-CSRFToken");

  $.ajax({
    url: "planning/poste/ajax.updateCell.php",
    type: "post",
    dataType: "json",
    data: {poste: poste, CSRFToken: CSRFToken, date: date, debut: debut, fin: fin, perso_id: perso_id, perso_id_origine: perso_id_origine, barrer: barrer, ajouter: ajouter, site: site, tout: tout, griser: griser},
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

      // Cellule grisée depuis le menudiv
      if(result != 'grise'){
        $("#td"+cellule).removeClass('cellule_grise');
      }

      if(result == 'grise'){
        $("#td"+cellule).addClass('cellule_grise');
        result = new Array();
      }
      
      for(i in result){
        // Exemple de cellule
        // <div id='cellule11_0' class='cellule statut_bibas service_permanent' >Christophe C.</div>

        var title = result[i]["nom"] + ' ' + result[i]["prenom"];
        
        var agent=result[i]["nom"]+" "+result[i]["prenom"].substr(0,1)+".";
        var perso_id=result[i]["perso_id"];

        // classes : A définir en fonction du statut, du service et des absences
        var classes="cellDiv pl-highlight pl-cellule-perso-"+perso_id;
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
          title = '';
        }

        // Service et Statut
        classes+=" service_"+result[i]["service"].toLowerCase().replace(/ /g,"_");
        classes+=" statut_"+result[i]["statut"].toLowerCase().replace(/ /g,"_");
        
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
        var span="<span class='cellSpan pl-highlight agent_"+perso_id+"' title='"+title+"'>"+agent+"</span>";
        var div="<div id='cellule"+cellule+"_"+i+"' class='"+classes+"' data-perso-id='"+perso_id+"' oncontextmenu='majPersoOrigine("+perso_id+");'>"+span+"</div>"
        // oncontextmenu='majPersoOrigine("+perso_id+");' : necessaire car l'événement JQuery contextmenu sur .cellDiv ne marche pas sur les cellules modifiées
        $("#td"+cellule).append(div);

        // Complète le tableau cellules initialisé au chargement de la page et contenant toutes les cellules ajoutées par la fonction bataille_navale
        cellules.push($('#cellule'+cellule+'_'+i));
      }

      // Ajout du widget pour copier les agents dans
      // la cellule immédiatement à droite.
      $("#td"+cellule).append('<a class="pl-icon arrow-right" href="#"></a>');

      // Suppresion de la surbrillance sur toutes les cellules une fois l'agent posté ou supprimé
      $('.pl-highlight').removeClass('pl-highlight', {duration:2500});

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
      emptyContextMenu();

      },
      error: function(result){
        CJInfo("Une erreur est survenue lors de l'enregistrement du planning.","error");
      }
    });

  // Affiche un message en haut du planning si pas de catégorie A en fin de service 
  verif_categorieA();

  updatePlanningAlert();

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

function updatePlanningAlert() {
    var link = $('a.non_places');
    if (!link) return;
    var date = $('#date').val();
    var site = $('#site').val();
    $('.tableau0.tr_horaires > .td_horaires').each(function() {
        var horaires = $(this).attr('id');
        var debut = horaires.substr(0, 8);
        var fin = horaires.substr(8);
        $.ajax({
            url: "planning/poste/ajax.getPlanningAlert.php",
            dataType: "json",
            data: {date: date, site: site, debut: debut, fin: fin},
            type: "get",
            success: function(result){
                if (result['amount'] == 0) {
                    tooltip = 'Aucun';
                } else {
                    tooltip = result['names'].join(', ');
                }
                if ($(this).find("a").length > 0) {
                    $(this).find("a").replaceWith("<a href='#' title='" + tooltip + "'> (" + result['amount'] + ")</a>");
                }
            },
        });
    });
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

  // Largeur du tableau 2 (on s'adapte à la longueur des lignes)
  $("#menudiv2").css("width",250);
  var width = $('#menudiv2 > table').width() +20;
  $("#menudiv2").css("width",width);

  // Position horizontale du tableau 2
  var left1 = $("#menudiv1").position();
  left1 = left1.left;
  if((left1 + $('#menudiv1').width() + width) > $(window).width()){
    var left2=left1-$("#menudiv2").width();
  }else{
    var left2=left1+$("#menudiv1").width();
  }
  $("#menudiv2").css("left",left2);

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

/**
 * @function plMouseOut
 * @param int id
 * Actions executées lorsque les lignes du menudiv ne sont plus survolées
 * Retire la surbrillance des agents dans le planning
 */
function plMouseOut(id){
  $('.pl-highlight').removeClass('pl-highlight');
}

/**
 * @function plMouseOver
 * @param int id
 * Actions executées lorsque les lignes du menudiv sont survolées
 * Met en surbrillance l'agent survolé dans le planning
 */
function plMouseOver(id){

  // Ajoute la classe pl-highlight aux éléments existants au chargment de la page
  $('.pl-cellule-perso-'+id).addClass('pl-highlight');

  // Ajoute la classe pl-highlight aux éléments ajoutés en Jquery (append, fonction bataille_navale)
  // cellules est un tableau initialisé au chargment de la page (début de ce script), et complété par la fonction bataille_navale
  for(i in cellules){
    if(cellules[i].hasClass('pl-cellule-perso-'+id)){
      cellules[i].addClass('pl-highlight');
    }
  }
}

/** @function planningNotifications
 *  @param srting date
 *  Envoie les notifications aux agents concernés par des plannings validés ou modifiés
 */
function planningNotifications(date, site, CSRFToken){
  $.ajax({
    url: "planning/poste/ajax.notifications.php",
    dataType: "json",
    data: {date: date, site: site, CSRFToken: CSRFToken},
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
