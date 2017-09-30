/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : js/script.js
Création : mai 2011
Dernière modification : 30 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Farid Goara <farid.goara@u-pem.fr>
@author Etienne Cavalié


Description :
Fichier contenant les principales fonctions JavaScript

Cette page est appelée par les fichiers include/header.php, setup/header.php et planning/poste/menudiv.php
*/

//	---------------------------		Fonctions communes		------------------------	//
function annuler(nb){
  if(opener){
    opener.window.location.reload(false);
    window.close();
  }
  else{
	  history.go(-nb);
  }
}
	
// Fonction permettant d'afficher les heures correspondantes à chaque tableau d'emploi du temps
// lors de la modification d'un select ou au chargement d'une page
function calculHeures(object,num,form,tip,numero){
  // Num : si horaires prédéfinis, 2 tableaux, num = null ou 2
  // Numero : numéro du tableau, en fonction de la variable $config['nb_semaine'], on peut avoir jusqu'à 3 tableaux
  // tip : Affichage qui sera mis à jour
  debut=numero*7;
  fin=debut+7;
  
  heures=0;
  elements=document.forms[form].elements;
  
  for(i=debut;i<fin;i++){
    if(elements["temps"+num+"["+i+"][0]"]){
      heure0=elements["temps"+num+"["+i+"][0]"].value;
      heure1=elements["temps"+num+"["+i+"][1]"].value;
      heure2=elements["temps"+num+"["+i+"][2]"].value;
      heure3=elements["temps"+num+"["+i+"][3]"].value;
      
      heure5 = null;
      heure6 = null;
      if(elements["temps"+num+"["+i+"][5]"] != undefined){
        heure5=elements["temps"+num+"["+i+"][5]"].value;
        heure6=elements["temps"+num+"["+i+"][6]"].value;
      }
    }
    else{
      heure0=$("#temps"+num+"_"+i+"_0").text().replace("h",":");
      heure1=$("#temps"+num+"_"+i+"_1").text().replace("h",":");
      heure2=$("#temps"+num+"_"+i+"_2").text().replace("h",":");
      heure5=$("#temps"+num+"_"+i+"_5").text().replace("h",":");
      heure6=$("#temps"+num+"_"+i+"_6").text().replace("h",":");
      heure3=$("#temps"+num+"_"+i+"_3").text().replace("h",":");
    }
    
    
  /**
   * Tableau affichant les différentes possibilités
   * NB : le paramètre heures[4] est utilisé pour l'affectation du site. Il n'est pas utile ici
   * NB : la 2ème pause n'est pas implémentée depuis le début, c'est pourquoi les paramètres heures[5] et heures[6] viennent s'intercaler avant $heure[3]
   *
   *    Heure 0     Heure 1     Heure 2     Heure 5     Heure 6     Heure 3
   * 1                           [ tableau vide]
   * 2    |-----------|           |-----------|           |-----------|   
   * 3    |-----------|           |-----------------------------------|   
   * 4    |-----------|                                   |-----------|
   * 5    |-----------|
   * 6    |-----------------------------------|           |-----------|   
   * 7    |-----------------------------------|
   * 8    |-----------------------------------------------------------|
   * 9                            |-----------|
   * 10                           |-----------------------------------|
   */

    // Constitution des groupes de plages horaires
    var diff=0;
    var tab = new Array();
    
    // 1er créneau : cas N° 2; 3; 4; 5
    if (heure0 && heure1) {
      tab.push(new Array(heure0, heure1));
    
    // 1er créneau fusionné avec le 2nd : cas N° 6 et 7
    } else if (heure0 && heure5) {
      tab.push(new Array(heure0, heure5));
    
    // Journée complète : cas N° 8
    } else if (heure0 && heure3) {
      tab.push(new Array(heure0, heure3));
    }
    
    // 2ème créneau : cas N° 1 et 9
    if (heure2 &&  heure5) {
      tab.push(new Array(heure2, heure5));
      
    // 2ème créneau fusionné au 3ème : cas N° 3 et 10
    } else if (heure2 && heure3) {
      tab.push(new Array(heure2, heure3));
    }
    
    // 3ème créneau : cas N° 2; 4; 6
    if (heure6 && heure3) {
      tab.push(new Array(heure6, heure3));
    }

    
    for(j in tab){
      diff += diffMinutes(tab[j][0],tab[j][1]);
    }
    
    heures+=diff;
    
    // Affichage du nombre d'heure pour chaque ligne
    if(diff){
      $("#heures"+num+"_"+numero+"_"+(i+1)).html(heure4(diff/60));
    }
      
  }
  heures=heure4(heures/60);
  $("#"+tip).text(heures);
  
}

function ctrl_form(champs){
  erreur=false;
  tab=champs.split(",");
  champs=new Array();
  for(i=0;i<tab.length;i++){
    if(!document.getElementById(tab[i]).value){
      champs.push(tab[i]);
      erreur=true;
    }
  }
  if(erreur){
    champs.join(",");
    alert("Les champs \""+champs+"\" sont obligatoires.");
    return false;
  }
  else
    return true;
}

// checkDate1 : utilisée pour valider les formulaires Jquery-UI, la date (o) doit être supérieure ou égale à aujourd'hui
function checkDate1( o, n) {
  if(n==undefined){
    n=false;
  }
  var today=new Date();
  var d=new Date();
  tmp=o.val().split("/");
  d.setDate(parseInt(tmp[0]));
  d.setMonth(parseInt(tmp[1])-1);
  d.setFullYear(parseInt(tmp[2]));
  diff=dateDiff(today,d);
  if(diff.day<0){
    if(n){
      o.addClass( "ui-state-error" );
      updateTips( n );
    }
    return false;
  } else {
    return true;
  }
}

// checkDate2 : utilisée pour valider les formulaires Jquery-UI. date2 doit être supérieur à date1
function checkDate2( date1, date2, n ) {
  var d1=new Date();
  tmp=date1.val().split("/");
  d1.setDate(parseInt(tmp[0]));
  d1.setMonth(parseInt(tmp[1])-1);
  d1.setFullYear(parseInt(tmp[2]));

  var d2=new Date();
  tmp=date2.val().split("/");
  d2.setDate(parseInt(tmp[0]));
  d2.setMonth(parseInt(tmp[1])-1);
  d2.setFullYear(parseInt(tmp[2]));

  diff=dateDiff(d1,d2);
  if(diff.day<0){
    date2.addClass( "ui-state-error" );
    updateTips( n );
    return false;
  } else {
    return true;
  }
}

// checkDiff : utilisée pour valider les formulaires Jquery-U, o1 et o2 doivent avoir des valeurs différentes
function checkDiff( o1, o2, n ) {
  if (o1.val() == o2.val()){
    o2.addClass( "ui-state-error" );
    updateTips( n );
    return false;
  } else {
    return true;
  }
}

// checkRegexp : utilisée pour valider les formulaires Jquery-UI.
function checkRegexp( o, regexp, n ) {
  if ( !( regexp.test( o.val() ) ) ) {
    o.addClass( "ui-state-error" );
    updateTips( n );
    return false;
  } else {
    return true;
  }
}

function dateDiff(date1,date2){
  var diff={}
  var tmp=date2-date1;

  tmp=Math.floor(tmp/1000);
  diff.sec=tmp%60;
  
  tmp=Math.floor((tmp-diff.sec)/60);
  diff.min=tmp%60;
  
  tmp=Math.floor((tmp-diff.min)/60);
  diff.hour=tmp%24;

  tmp=Math.floor((tmp-diff.hour)/24);
  diff.day=tmp;
  
  return diff;
}
  
function dateFr(date){
  if(date.indexOf("-")>0){
    tab=date.split("-");
    date=tab[2]+"/"+tab[1]+"/"+tab[0];
  }else{
    tab=date.split("/");
    date=tab[2]+"-"+tab[1]+"-"+tab[0];
  }
  return date;
}

function decompte(dcpt){
  var affiche = '';
	dcpt=parseInt(dcpt);
     
  if(dcpt > 1){
    var affiche = 'Veuillez réessayer dans '+dcpt+' secondes';
  }else{
    var affiche = 'Veuillez réessayer dans '+dcpt+' seconde';
  }
	if(dcpt > 0){
  	$("#chrono").text(affiche);
		dcpt--;
		setTimeout('decompte('+dcpt+')', 1000);
	}else{
		$("#chrono").hide();
		$("#link").show();
	}
}
 

function diffMinutes(debut,fin){		// Calcul la différence en minutes entre 2 heures (formats H:i:s)
  var d=new Date("Mon, 26 Aug 2013 "+debut);
  d=d.getTime()/60000;				// Nombre de milisecondes, converti en minutes
  var f=new Date("Mon, 26 Aug 2013 "+fin);
  f=f.getTime()/60000;
  return f-d;
}

//function to create error and alert dialogs
function errorHighlight(e, type, icon) {
    if (!icon) {
        if (type === 'highlight') {
            icon = 'ui-icon-info';
        } else {
            icon = 'ui-icon-alert';
        }
    }
    return e.each(function() {
        $(this).addClass('ui-widget');
        var alertHtml = '<div class="ui-state-' + type + ' ui-corner-all" style="padding:0 .7em;">';
        alertHtml += '<p style="text-align:center;">';
        alertHtml += '<span class="ui-icon ' + icon + '" style="float:left;margin-right: .3em;"></span>';
        alertHtml += $(this).html();
        alertHtml += '</p>';
        alertHtml += '</div>';

        $(this).html(alertHtml);
    });
}

function heureFr(heure){
  heure=heure.toString();
  heure=heure.replace(/([0-9]*):([0-9]*):([0-9]*)/,"$1h$2");
  return heure;
}

function heure4(heure){
  heure=heure.toString();
  if(heure.indexOf("h")>0){
    tmp = heure.split('h');
    centiemes = parseFloat(tmp[1]) / 60;
    heure = parseFloat(tmp[0]) + parseFloat(centiemes);
    heure = heure.toFixed(2);
  }
  else if(heure.indexOf('.')>0){
    tmp = heure.split('.');
    centieme = parseFloat(heure) - parseFloat(tmp[0]);
    minutes = centieme * 0.6;
    heure = parseFloat(tmp[0]) + parseFloat(minutes);
    heure = heure.toFixed(2);
    heure = heure.toString().replace('.','h');
  }
  else{
    heure += 'h00';
  }
  return heure;
}


function information(message,type,top,time){
  if(top==undefined){
    top=60;
  }
  
  if(time==undefined){
    time=5000;
  }

  if(typeof(timeoutJSInfo)!== "undefined"){
    window.clearTimeout(timeoutJSInfo);
  }
  $("#JSInformation").remove();
  $("body").append("<div id='JSInformation'>"+message+"</div>");
  errorHighlight($("#JSInformation"),type);
  position($("#JSInformation"),top,"center");
  timeoutJSInfo=window.setTimeout("$('#JSInformation').remove()",time);
}

function modif_mdp(){
  document.form.action.value="mdp";
  document.form.submit();
}

function popup(url,width,height){
  document.getElementById("popup").src="index.php?page="+url+"&menu=off";
  document.getElementById("popup").style.width=width+"px";
  document.getElementById("popup").style.height=height+"px";
  document.getElementById("popup").style.left=((screen.width - width)/2)+"px";
  document.getElementById("popup").style.top=((screen.height - height)/3)+"px";
  document.getElementById("popup").style.display="";
  document.getElementById("opac").style.display="";
}

function popup_closed(){
  parent.document.getElementById("popup").src="";
  parent.document.getElementById("popup").style.display="none";
  parent.document.getElementById("opac").style.display="none";
}

function removeAccents(strAccents){
  strAccents = strAccents.split('');
  strAccentsOut = new Array();
  strAccentsLen = strAccents.length;
  var accents = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñÿýŷỳŸỲŶ';
  var accentsOut = ['A','A','A','A','A','A','a','a','a','a','a','a','O','O','O','O','O','O','O','o','o','o','o','o','o','E','E','E','E','e','e','e','e','e','C','c','D','I','I','I','I','i','i','i','i','U','U','U','U','u','u','u','u','N','n','y','y','y','Y','Y','Y'];
  for (var y = 0; y < strAccentsLen; y++) {
    if (accents.indexOf(strAccents[y]) != -1) {
      strAccentsOut[y] = accentsOut[accents.indexOf(strAccents[y])];
    }
    else{
      strAccentsOut[y] = strAccents[y];
    }
  }
  strAccentsOut = strAccentsOut.join('');
  return strAccentsOut;
}


/**
 * Réninitialise l'URL du calendrier ICS de l'agent
 * @param int id : ID de l'agent
 * @param string nom : Prénom et Nom de l'agent (pour affichage de la confirmation
 */
function resetICSURL(id, CSRFToken, nom){
  if(nom == undefined){
    var res = confirm("Etes vous sûr(e) de vouloir réinitialiser l'URL de votre calendrier ICS ?");
  } else {
    var res = confirm("Etes vous sûr(e) de vouloir réinitialiser l'URL du calendrier de "+nom+" ?");
  }
  
  if(res){
    $.ajax({
      url: "ics/ajax.resetURL.php",
      type: "post",
      dataType: "json",
      data: {id: id, CSRFToken: CSRFToken},
      success: function(result){
        $("#url-ics").text(result.url);
        CJInfo("L'URL du calendrier a été réinitialisée avec succès","success");
      },
      error: function(result){
        CJInfo("Une erreur est survenue lors de la réinitialisation de l'URL<br/>"+result.responseText,"error");
      }
    });
  }
}

// Supprime les balises HTML
function sanitize_string(a){
  reg=new RegExp("<.[^<>]*>", "gi" );
  a=a.replace(reg,"").trim();
  return a;
}

/** pre-remplissage de l'heure de fin avec l'heure de début
* @author Farid Goara <farid.goara@u-pem.fr>
*/
function setEndHour(){
  if($("select[name=hre_debut]").val() != "" && $("select[name=hre_fin]").val() == ""){
    $("select[name=hre_fin]").prop("selectedIndex",$("select[name=hre_debut]").prop("selectedIndex"));
  }
}

// supprime(page,id, CSRFToken)	Utilisée par postes et modeles
function supprime(page, id, CSRFToken){
  if(confirm("Etes vous sûr de vouloir supprimer cet élément ?")){
    $.ajax({
      url: page+"/ajax.delete.php",
      type: "get",
      data: "id="+id+"&CSRFToken="+CSRFToken,
      success: function(){
	window.location.reload(false);
      },
      error: function(){
	CJInfo("Une erreur est survenue lors de la suppression","error");
      }
    });
  }
}

// Suppression des jours fériés
function supprime_jourFerie(id){
  if(document.getElementById("jour"+id).value){
    jour=document.getElementById("jour"+id).value;
    if(confirm("Etes vous sûr de vouloir supprimer le "+dateFr(jour)+" ?")){
      document.getElementById("tr"+id).style.display="none";
      document.getElementById("jour"+id).value="";
    }
  }
}

// updateAgentsList : actualise la liste des agents d'un select en fonction d'un paramètre deleted=yes/no
// Permet d'afficher ou non les agents supprimés dans un select (select_id) selon si une checkbox (me) est cochée ou non
// le fichier ajax.updateAgentsList.php retourne un tableau [[id=> ,nom=>, prenom=> ],[id=> ,nom=>, prenom=> ], ...] encodé en JSON
// Fonction utilisée dans les pages absences/voir.php et plugins/conges/voir.php

function updateAgentsList(me,select_id){
  var deleted=me.checked?"yes":"no";
  var index=$("#perso_id").val();
  var in_array=false;

  $.ajax({
    url: "personnel/ajax.updateAgentsList.php",
    type: "get",
    data: "deleted="+deleted,
    success: function(result){
      result=JSON.parse(result);
      $("#"+select_id).html("<option value='0'>Tous</option>");
      for(key in result){
	$("#"+select_id).append("<option value='"+result[key]["id"]+"'>"+result[key]["nom"]+" "+result[key]["prenom"]+"</option>");
	if(result[key]["id"]==index){
	  in_array=true;
	}
      }
      index=in_array?index:0;
      $("#"+select_id).val(index);

      $("#"+select_id).closest("span").effect("highlight",null,2000);
    },
    error: function(){
      information("Une erreur est survenue lors de la mise à jour de la liste des agents.","error");
    }
  });
}


// updateTips : utilisée pour valider les formulaires Jquery-UI
function updateTips( t ) {
  var tips=$( ".validateTips" );
  tips
    .text( t )
    .addClass( "ui-state-highlight" );
  setTimeout(function() {
    tips.removeClass( "ui-state-highlight", 1500 );
  }, 500 );
}

function verif_date(d){
  // Cette fonction vérifie le format AAAA-MM-JJ saisi et la validité de la date.
  // Le séparateur est défini dans la variable separateur
  var amin=1999; // année mini
  var amax=2080; // année maxi
  var separateur="-"; // separateur entre jour/mois/annee
  var j=(d.substring(8));
  var m=(d.substring(5,7));
  var a=(d.substring(0,4));
  var ok=1;
  if ( ((isNaN(j))||(j<1)||(j>31)) && (ok==1) ) {
  //       alert("Le jour n'est pas correct."); 
    ok=0;
  }
  if ( ((isNaN(m))||(m<1)||(m>12)) && (ok==1) ) {
  //       alert("Le mois n'est pas correct."); 
    ok=0;
  }
  if ( ((isNaN(a))||(a<amin)||(a>amax)) && (ok==1) ) {
  //     alert("L'année n'est pas correcte."); 
    ok=0;
  }
  if ( ((d.substring(4,5)!=separateur)||(d.substring(7,8)!=separateur)) && (ok==1) ) {
  //   alert("Les séparateurs doivent être des "+separateur); 
    ok=0;
  }
  if (ok==1){
    var d2=new Date(a,m-1,j);
    j2=d2.getDate();
    m2=d2.getMonth()+1;
    a2=d2.getFullYear();
    if (a2<=100) {a2=1900+a2}
    if ( (j!=j2)||(m!=m2)||(a!=a2) ){
    //           alert("La date "+d+" n'existe pas !");
      ok=0;
    }
  }
  return ok;
}
 
function verif_form(champs,form){
  if(form==undefined){
    form="form";
  }
  erreurs="";
  valeur1="";
  valeur2="";
  champ=champs.split(";");
  for(i=0;i<champ.length;i++){
    tab=champ[i].split("=");
    objet=tab[0];
    type=tab[1];
    valeur=document.forms[form].elements[objet].value;

    if(type=="date2" && !valeur)
      valeur=valeur1;

    if(valeur=="")
      erreurs=erreurs+"<li>"+objet+"</li>";
    else if(type){
      if(type.substr(0,4)=="date"){
	// Converti les dates JJ/MM/AAAA en AAAA-MM-JJ
        valeur=valeur.replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");
      }
      if(type.substr(0,4)=="date" && verif_date(valeur)==0)
	erreurs=erreurs+"<li>"+objet+" doit être au format JJ/MM/AAAA</li>";
      if(type=="date1"){
	objet1=objet;
	valeur1=valeur;
      }
      else if(type=="date2"){
	objet2=objet;
	valeur2=valeur;
      }
      else if(type=="date2Obligatoire"){
	objet2=objet;
	valeur2=valeur;
      }
      if(type.substr(0,5)=="heure" && verif_heure(valeur)==0)
	erreurs=erreurs+"<li>"+objet+" doit être au format HH:MM:SS</li>";
      if(type=="heure1"){
	objet1=objet;
	valeur1=valeur;
      }
      else if(type=="heure2"){
	objet2=objet;
	valeur2=valeur;
      }
    }
  }
  
  if(erreurs){
    CJInfo("Les champs suivants sont obligatoires :<ul>"+erreurs+"</ul>","error");
    return false;
  }
  else{
    if(valeur1 && valeur2 && valeur2<valeur1){
      CJInfo("Le champ "+objet2+" doit être supérieur au champ "+objet1,"error");
      return false;
    }
    else{
      return true;
    }
  }
}

function verif_heure(heure){
  var separateur=":";
  var h=(heure.substring(0,2));
  var m=(heure.substring(3,5));
  var s=(heure.substring(6));

  var ok=0;
  if(h>-1 && h<24 && m>-1 && m<60 &&s>-1 && s<60)
    ok=1;
  return ok;
}
	
function verif_mail(mail){
  p=mail.indexOf('@');
  if (p<1 || p==(mail.length-1))
    return false;
  tmp=mail.split("@");
  p=tmp[1].indexOf('.');
  if (p<1 || p==(tmp[1].length-1))
    return false;
  return true;
}
//	---------------------------		FIN Fonctions communes		------------------------	//
//	--------------------------------	Absences		---------------------------------	//
function all_day(){
  if(!document.form.allday.checked){
    document.getElementById("hre_debut").style.display="";
    document.getElementById("hre_fin").style.display="";
  }
  else{
    document.getElementById("hre_debut").style.display="none";
    document.getElementById("hre_fin").style.display="none";
    document.form.hre_debut.value="";
    document.form.hre_fin.value="";
  }
}
//	--------------------------------	FIN Absences		---------------------------------	//
//	--------------------------------	Aide			---------------------------------	//
function position(object,top,left){
  object.css("position","absolute");
  object.css("top",top);
  object.css("z-index",10);
  if(left=="center"){
    left=($("body").width()-object.width())/2;
    object.css("left",left);
  }
}
//	--------------------------------	FIN Aide		---------------------------------	//
//	--------------------------------	Statistiques		---------------------------------	//
function export_stat(nom,type){
  $.ajax({
    url: "statistiques/export.php",
    type: "get",
    data: "nom="+nom+"&type="+type,
    success: function(result){
      window.open("data/"+result);
    },
    error: function(){
      information("Une erreur est survenue lors de l'exportation.","error");
    }
  });
}

function verif_select(nom){
  if(document.form.elements[nom+'[]'].value=="Tous"){
    for(i=document.form.elements[nom+'[]'].length-1;i>0;i--){
      document.form.elements[nom+'[]'][i].selected=true;
    }
    document.form.elements[nom+'[]'][0].selected=false;
  }
}
//	--------------------------------	FIN Statistiques	---------------------------------	//

// Initialisations JQuery-UI
$(function(){
  $(document).ready(function() {
    $(".ui-accordion").accordion({
      heightStyle: "content"
    });

    $(".ui-button").button();
    $(".datepicker").datepicker();
    $(".datepicker").addClass("center ui-widget-content ui-corner-all");

    /**
    * Initialiser le calendrier avec la date choisie
    * @author Farid Goara
    */
    if ($("#date").length > 0){
      if ($("#date").attr("data-set-calendar") != 'undefined' && $("#date").attr("data-set-calendar")!= false  ){
	var strSelectedDate=$("#date").attr("data-set-calendar");
	if(strSelectedDate){
	  var arrSelectedDate=strSelectedDate.split("-");
	  var numYear = arrSelectedDate[0];
	  var numMonth = parseInt(arrSelectedDate[1]) - 1;
	  var numDay = arrSelectedDate[2];
	  var objSelectedDate = new Date(numYear,numMonth,numDay);
	  $(".datepicker").datepicker("setDate",objSelectedDate);
	}
      }
    }

    /**
    * Initialiser le defaultDate du calendrier de fin avec eventuelle date choisie dans le calendrier debut
    * @author Farid Goara
    */
    $(".datepicker").focusin(function(){
      if($(this).attr("name") == "fin"){
	var objDateDefaultFin = "";
	var objDateCurrentDeb = "";
	if($('input[name="debut"]').datepicker("getDate")){
	  if(!$(this).datepicker("option","defaultDate" )){
	    $(this).datepicker("option","defaultDate",$('input[name="debut"]').datepicker("getDate"));
	  }
	  else{
	    objDateDefaultFin = new Date($(this).datepicker("option","defaultDate"));
	    objDateCurrentDeb = new Date($('input[name="debut"]').datepicker("getDate"));
	    if(objDateDefaultFin.getDate() != objDateCurrentDeb.getDate() || objDateDefaultFin.getMonth() != objDateCurrentDeb.getMonth() || objDateDefaultFin.getYear() != objDateCurrentDeb.getYear()){
	      $(this).datepicker("option","defaultDate",$('input[name="debut"]').datepicker("getDate"));
	    }
	  }
	}
      }
    });

    // Onglets
    $(".ui-tabs").tabs({
      active: $(".ui-tabs").attr("data-active"),
      // Fonctions personnalisées pour les tabs .ui-tab-cancel et .ui-tab-submit dans personnel/modif.php
      beforeActivate: function(event,ui){
	if($(ui.newTab).hasClass("ui-tab-cancel")){
 	  window.location.href=$(".ui-tab-cancel > a").attr("href");
	  return false;
	}
	if($(ui.newTab).hasClass("ui-tab-submit")){
 	  var command=$(".ui-tab-submit > a").attr("href");
	  if(command.substring(0,11)=="javascript:"){
	    command=command.substring(11,command.length);
	    eval(command);
	  }
	  return false;
	}
      }
    });
    $(".ui-tab-submit").css("position","absolute");
    $(".ui-tab-submit").css("right",5);
    $(".ui-tab-submit").css("top",7);
    var right=$(".ui-tab-submit").length>0?$(".ui-tab-submit").width()+10:5;
    $(".ui-tab-cancel").css("position","absolute");
    $(".ui-tab-cancel").css("right",right);
    $(".ui-tab-cancel").css("top",7);
  });
  
  // Infobulles
  $(document).tooltip();
});


