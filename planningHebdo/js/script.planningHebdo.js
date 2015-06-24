/*
Planning Biblio, Version 2.0
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : planningHebdo/js/script.planningHebdo.js
Création : 26 août 2013
Dernière modification : 24 juin 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

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
    if(elements["temps"+num+"["+i+"][0]"]){
      debut1=elements["temps"+num+"["+i+"][0]"].value;
      fin1=elements["temps"+num+"["+i+"][1]"].value;
      debut2=elements["temps"+num+"["+i+"][2]"].value;
      fin2=elements["temps"+num+"["+i+"][3]"].value;
    }
    else{
      debut1=heure5($("#temps"+num+"_"+i+"_0").text());
      fin1=heure5($("#temps"+num+"_"+i+"_1").text());
      debut2=heure5($("#temps"+num+"_"+i+"_2").text());
      fin2=heure5($("#temps"+num+"_"+i+"_3").text());
    }
    if(debut1){
      diff=0;
      // Journée avec pause le midi
      if(debut1 && fin1 && debut2 && fin2){
	diff=diffMinutes(debut1,fin1);
	diff+=diffMinutes(debut2,fin2);
      }
      // Matin uniquement
      else if(debut1 && fin1){
	diff=diffMinutes(debut1,fin1);
      }
      // Après midi seulement
      else if(debut2 && fin2){
	diff=diffMinutes(debut2,fin2);
      }
      // Journée complète sans pause
      else if(debut1 && fin2){
	diff=diffMinutes(debut1,fin2);
      }
      heures+=diff;
      
      // Affichage du nombre d'heure pour chaque ligne
      if(diff){
	$("#heures"+num+"_"+numero+"_"+(i+1)).html(heure4(diff/60));
      }
    }
  }
  heures=heure4(heures/60);
  $("#heures"+num+"_"+numero).text(heures);
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
    // Suppression du planning en arrière plan
    $.ajax({
      url: "planningHebdo/ajax.delete.php",
      data: "id="+id,
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

  id=id?"&id="+id:null;
  perso_id=perso_id?"&perso_id="+perso_id:null;
  
  if(!debut || !fin){
    alert("Les dates de début et de fin sont obligatoires");
    return false;
  }
  
  if(fin<debut){
    alert("La date de fin doit être supérieure à la date de début");
    return false;
  }

  var retour=false;
  $.ajax({
    url: "planningHebdo/ajax.verifPlannings.php",
    data: "debut="+debut+"&fin="+fin+id+perso_id,
    type: "get",
    async: false,
    success: function(result){
      result=JSON.parse(result);
      if(result["retour"]=="OK"){
	retour="true";
      }else{
        if(perso_id){
	  message="Un planning est enregistré pour cet agent pour la période du "+dateFr(result["debut"])+" au "+dateFr(result["fin"])
	  +"\nVeuillez modifier les dates de début et/ou de fin ou modifier le premier planning.";
	}else{
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
  if(retour){
    return retour=="true"?true:false;
  }
}

function plHebdoVerifFormPeriodesDefinies(){
  var result=true;
  $(".selectAnnee").each(function(){
    if(!$(this).val()){
      result=false;
    }
  });
  if(!result){
    alert("Vous devez choisir l'année universitaire.");
  }
  return result; 
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