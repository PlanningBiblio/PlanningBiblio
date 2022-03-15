/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/js/script.planningHebdo.js
Création : 26 août 2013
Dernière modification : 4 mai 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions JavaScript utiles à la gestion des plannings de présence
*/

// Fonction permettant d'afficher les heures correspondantes à chaque tableau
// lors de la modification d'un select ou au chargement de la page modif
function plHebdoCalculHeures(object,num){
  // Num : si horaires prédéfinis, 2 tableaux, num = null ou 2
  // Numero : numéro du tableau, en fonction de la variable $config['nb_semaine'], on peut avoir jusqu'à 3 tableaux

  // Récupération du numéro du tableau
  // Si object est un objet, la fonction est appelée par l'événement "change" sur un select
  if(object instanceof jQuery){
    tableau= object.closest("table").attr("id");
    numero = tableau.substring(tableau.length-1,tableau.length);
  }
  // Sinon, object est un entier, la fonction est appelée par la fonction plHebdoCalculHeures2
  // lorsque le document modif.php est chargé
  else{
    numero=object;
  }
  debut=numero*7;
  fin=debut+7;
  heures=0;
  elements=document.forms["form1"].elements;
  
  for(i=debut;i<fin;i++){
    debut1 = 0;
    debut2 = 0;
    debut3 = 0;
    fin1 = 0;
    fin2 = 0;
    fin3 = 0;
    breaktime = 0;
    
    
    // Si modification possible (select)
    if(elements["temps"+num+"["+i+"][0]"]){
      debut1 = $("select[name*='temps"+num+"["+i+"][0]']").val();
      fin1 = $("select[name*='temps"+num+"["+i+"][1]']").val();
      debut2 = $("select[name*='temps"+num+"["+i+"][2]']").val();
      fin3 = $("select[name*='temps"+num+"["+i+"][3]']").val();              // Passe à fin3 pour pouvoir intércaler une 2nde pause

      // Si 2 pauses (PlanningHebdo-Pause2)
      if($("select[name*='temps"+num+"["+i+"][5]']")){
        fin2 = $("select[name*='temps"+num+"["+i+"][5]']").val();
        debut3 = $("select[name*='temps"+num+"["+i+"][6]']").val();
      }

      if ($("select[name*='breaktime["+i+"]']").val()){
        breaktime = $("select[name*='breaktime["+i+"]']").val();
      }
    
    // Si lecture seule
    }else{
      debut1 = $("#temps"+num+"_"+i+"_0").text().replace("h",":");
      fin1 = $("#temps"+num+"_"+i+"_1").text().replace("h",":");
      debut2 = $("#temps"+num+"_"+i+"_2").text().replace("h",":");
      fin3 = $("#temps"+num+"_"+i+"_3").text().replace("h",":");    // Passe à fin3 pour pouvoir intércaler une 2nde pause

      // Si 2 pauses (PlanningHebdo-Pause2)
      if($("#temps"+num+"_"+i+"_5")){
        fin2 = $("#temps"+num+"_"+i+"_5").text().replace("h",":");
        debut3 = $("#temps"+num+"_"+i+"_6").text().replace("h",":");
      }

      if ($("input[name='breaktime_" + i + "']").val()) {
        breaktime = $("input[name='breaktime_" + i + "']").val();
      }
    }

    diff=0;
    
    // Journée avec 2 pauses
    if(debut1 && fin1 && debut2 && fin2 && debut3 && fin3){
      diff=diffMinutes(debut1,fin1);
      diff+=diffMinutes(debut2,fin2);
      diff+=diffMinutes(debut3,fin3);
    }
    // Journée avec pause le midi (pause 1)
    else if(debut1 && fin1 && debut2 && fin3){
      diff=diffMinutes(debut1,fin1);
      diff+=diffMinutes(debut2,fin3);
    }
    // Journée avec pause le midi (pause 2)
    else if(debut1 && fin2 && debut3 && fin3){
      diff=diffMinutes(debut1,fin2);
      diff+=diffMinutes(debut3,fin3);
    }
    // Matin uniquement (pause 1)
    else if(debut1 && fin1){
      diff=diffMinutes(debut1,fin1);
    }
    // Matin uniquement (pause 2)
    else if(debut1 && fin2){
      diff=diffMinutes(debut1,fin2);
    }
    // Journée complète sans pause
    else if(debut1 && fin3){
      diff=diffMinutes(debut1,fin3);
    }
    // Journée commençant en fin de pause 1 avec pause 2
    else if(debut2 && fin2 && debut3 && fin3){
      diff=diffMinutes(debut2,fin2);
      diff+=diffMinutes(debut3,fin3);
    }
    // Après midi seulement (pause 1)
    else if(debut2 && fin3){
      diff=diffMinutes(debut2,fin3);
    }
    // Après midi seulement (pause 2)
    else if(debut3 && fin3){
      diff=diffMinutes(debut3,fin3);
    }
    diff = diff - (breaktime * 60);
    heures+=diff;
    
    // Affichage du nombre d'heure pour chaque ligne
    $("#heures"+num+"_"+numero+"_"+(i+1)).html(heure4(diff/60));

    // Affichage en rouge si valeur négative
    $("#heures"+num+"_"+numero+"_"+(i+1)).removeClass('red');
    if(diff<0){
      $("#heures"+num+"_"+numero+"_"+(i+1)).addClass('red');
    }

  }

  heures=heure4(heures/60);
  $("#heures"+num+"_"+numero).text(heures);
  
  // TODO : alerter si les heures ne sont pas cohérentes (ex: fin inférieure au début)
}

// Fonction permettant d'afficher les heures correspondantes à chaque tableau
// lors de l'affichage de la page modif.php. Appelle la fonction plHebdoCalculHeures.
function plHebdoCalculHeures2(){
  $("table[id^='tableau']").each(function(){
    id=$(this).attr("id");
    numero=id.substring(id.length-1,id.length);
    plHebdoCalculHeures(numero,"");
  });
}


// Lors de la modification des select du tableau 1, on met à jour les autres tableaux si la case "Même planning" est cochée
function plHebdoChangeHiddenSelect(){
  $(".memePlanning").each(function(){
    if($(this).prop("checked")){
      var id=$(this).attr("data-id");
      plHebdoCopySelect(id);
    }
  });
}

/* Copie des infos du tableau 1 vers les autres tableaux lorsque l'on coche la case "Même planning", 
ou lorsque l'on modifie les select du tableau 1 est que la case "Même Planning" est cochée
*/
function plHebdoCopySelect(id){
  // Copie les infos du tableau 1 vers le tableau sélectionné
  var i=0;
  $("#div"+id).find("select").each(function(){
    var val=$("#div0").find("select:eq("+i+")").val();
    $(this).val(val);
    i++;
  });

  // Calcul et affichage des heures par jour et par semaine
  plHebdoCalculHeures2();
}

/* Vérifie lors du chargement de la page, si les plannings semaine 2,3 ... sont les mêmes que le planning de la semaine 1
Si oui, coche la case "Même planning que la semaine 1" et masque les tableaux correspondants
*/
function plHebdoMemePlanning(){

  // Si modification autorisée (select affichés)
  if($(".tableau").find("select").length){
    var modif=true;
  }else{
    var modif=false;
  }

  var tab={};

  $(".tableau").each(function(){
    // On stock les infos des plannings dans des tableaux
    var i=$(this).attr("data-id");
    tab[i]=new Array();
    
    var empty=true;

    if(modif){
      $(this).find("select").each(function(){
	var value=$(this).val();
	tab[i].push(value);

	// Test si le tableau 1 est vide pour ne pas cocher les cases lors de la création de nouveaux plannings
	if(value){
	  empty=false;
	}
      });
    }else{
      $(this).find(".td_heures").each(function(){
	var value=$(this).text();
	tab[i].push(value);

	// Test si le tableau 1 est vide pour ne pas cocher les cases lors de la création de nouveaux plannings
	if(value){
	  empty=false;
	}
      });      
    }

    // On compare le tableau courant au premier tableau
    if(i>0 && empty==false){
      // Si les tableaux sont les mêmes
      if(JSON.stringify(tab[i]) == JSON.stringify(tab[0])){
	// On coche la case "Même planning ...", le tableau sera caché par l'évènement $(".memePlanning").click()
	if(modif){
	  $("#memePlanning"+i).click();
	}else{
	  $("#memePlanning"+i).show();
	  $("#div"+i).hide();
	}
      }
    }

  });
}

function plHebdoSupprime(id){
  if(confirm("Etes vous sûr(e) de vouloir supprimer ce planning de présence ?")){
    
    var CSRFToken = $('#CSRFSession').val();
    // Suppression du planning en arrière plan
    
    $.ajax({
      url: "planningHebdo/ajax.delete.php",
      dataType: "json",
      data: {id: id, CSRFToken: CSRFToken},
      type: "get",
    
      success: function(){
	// On cache la ligne du planning supprimée dans le tableau
	CJDataTableHideRow("#tr_"+id);
	CJInfo("Le planning a été supprimé","success");
      },
      error: function(){
	CJInfo("Erreur lors de la suppression du planning de pr&eacute;sence","error");
      }
    });
  }
}

function plHebdoVerifForm(){
  debut=$("input[name=debut]").val().replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");
  fin=$("input[name=fin]").val().replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");
  id=$("input[name=id]").val();
  perso_id=$("#perso_id").val();
  is_exception = $('input[name="exception"]').val();

  if(!debut || !fin){
    alert("Les dates de début et de fin sont obligatoires");
    return false;
  }
  
  if(fin<debut){
    alert("La date de fin doit être supérieure à la date de début");
    return false;
  }

  data = {debut: debut, fin: fin, id: id, perso_id: perso_id};
  if (is_exception) {
    data['exception'] = is_exception;
  }

  var retour=false;
  $.ajax({
    url: "planningHebdo/ajax.verifPlannings.php",
    dataType: "json",
    data: data,
    type: "get",
    async: false,
    success: function(result){
      if(result["retour"]=="OK"){
        retour="true";
      }
      else {
        if(result["autre_agent"]) {
          if (is_exception) {
            message="Une exception est enregistrés pour l'agent "+result["autre_agent"]+" pour la période du "+dateFr(result["debut"])+" au "+dateFr(result["fin"])
          } else {
            message="Un planning est enregistré pour l'agent "+result["autre_agent"]+" pour la période du "+dateFr(result["debut"])+" au "+dateFr(result["fin"])
          }
          message += "\nVeuillez modifier les dates de début et/ou de fin ou modifier le premier planning.";
        } else if (result['out_of_range']) {
          message = "Les dates de l'exception sont en dehors de la période du planning d'origine";
          retour = 'false';
        } else {
          message="Vous avez déjà enregistré un planning pour la période du "+dateFr(result["debut"])+" au "+dateFr(result["fin"])
          +"\nVeuillez modifier les dates de début et/ou de fin ou modifier le premier planning.";
        }
        alert(message);
        retour="false";
      }
    },
    error: function(result){
      information(result.responseText,"error");
      retour="false";
    }
  });

  if(retour == "false"){
    return false;
  } else if (is_exception) {
    if (id) {
      return true;
    }

    if (confirm("Vous êtes sur le point d'enregistrer une exception, voulez-vous continuer ?")){
      return true;
    } else {
      return false;
    }
  }

  return true;
}

$(function(){
  // Action lors du click sur la case à cocher "Même planning qu'en semaine 1"
  $(".memePlanning").click(function(){
    var id=$(this).attr("data-id");
    if($(this).prop("checked")){

      // Masque le tableau
      $("#div"+id).hide();
      
      // Copie des informations du tableau 1 vers le tableau sélectionné
      plHebdoCopySelect(id);

    }else{
      // Affiche le tableau si on décoche la case
      $("#div"+id).show();
    }
  });
  
  $("#perso_id").change(function(){
    $.ajax({
      url: "planningHebdo/ajax.getSites.php",
      dataType: "json",
      type: "post",
      data: {id: $(this).val()},
      success: function(result){
	var options="<option value=''>&nbsp;</option>\n";
	for(i in result){
	  options+= "<option value='"+result[i][0]+"'>"+result[i][1]+"</option>\n";
	}
	$(".selectSite").html(options);
      },
      error: function(result){
	CJInfo("Imposssible de récupérer la liste des sites de l'agent","error");
	CJInfo(result.responseText,"error");
      }
      
    });
    
  });
});