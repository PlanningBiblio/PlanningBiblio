/*
Planning Biblio, Version 1.9.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : js/script.js
Création : mai 2011
Dernière modification : 28 mars 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

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
      debut1=elements["temps"+num+"["+i+"][0]"].value;
      fin1=elements["temps"+num+"["+i+"][1]"].value;
      debut2=elements["temps"+num+"["+i+"][2]"].value;
      fin2=elements["temps"+num+"["+i+"][3]"].value;
    }
    else{
      debut1=$("#temps"+num+"_"+i+"_0").text().replace("h",":");
      fin1=$("#temps"+num+"_"+i+"_1").text().replace("h",":");
      debut2=$("#temps"+num+"_"+i+"_2").text().replace("h",":");
      fin2=$("#temps"+num+"_"+i+"_3").text().replace("h",":");
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
  document.getElementById(tip).innerHTML=heures;
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

function heure4(heure){
  heure=heure.toString();
  if(heure.indexOf("h")>0){
    heure=heure.replace("h00",".00");
    heure=heure.replace("h15",".25");
    heure=heure.replace("h30",".50");
    heure=heure.replace("h45",".75");
  }
  else{
    heure=heure.replace(".00","h00");
    heure=heure.replace(".25","h15");
    heure=heure.replace(".50","h30");
    heure=heure.replace(".75","h45");
    heure=heure.replace(".00","h00");
    heure=heure.replace(".5","h30");
    if(heure.indexOf("h")<0){
      heure+="h00";
    }
  }
  return heure;
}

function heure5(heure){
  heure=heure.toString();
  if(heure.indexOf("h")>0){
    heure=heure.replace("h00",":00");
    heure=heure.replace("h15",":25");
    heure=heure.replace("h30",":50");
    heure=heure.replace("h45",":75");
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

// supprime(page,id)	Utilisée par postes et modeles
function supprime(page,id){
  if(confirm("Etes vous sûr de vouloir supprimer cet élément ?")){
    $.ajax({
      url: page+"/ajax.delete.php",
      type: "get",
      data: "id="+id,
      success: function(){
	window.location.reload(false);
      },
      error: function(){
	information("Une erreur est survenue lors de la suppression","error");
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
      erreurs=erreurs+"\n - "+objet;
    else if(type){
      if(type.substr(0,4)=="date"){
	// Converti les dates JJ/MM/AAAA en AAAA-MM-JJ
        valeur=valeur.replace(/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/g,"$3-$2-$1");
      }
      if(type.substr(0,4)=="date" && verif_date(valeur)==0)
	erreurs=erreurs+"\n - "+objet+" doit être au format AAAA-MM-JJ";
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
	erreurs=erreurs+"\n - "+objet+" doit être au format HH:MM:SS";
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
    alert("Les champs suivants sont obligatoires :"+erreurs);
    return false;
  }
  else{
    if(valeur1 && valeur2 && valeur2<valeur1){
      alert("Le champ "+objet2+" doit être supérieur au champ "+objet1);
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

function position_retour(){
  var height=$(window).height();
  var scroll=$(window).scrollTop();
  $("#a_retour").css("top",scroll+height-50);
}
//	--------------------------------	FIN Aide		---------------------------------	//
//	---------------------------		Personnel		---------------------------------------		//
function createlogin(){
  login=document.form.prenom.value+"."+document.form.nom.value;
  login=login.trim();
  login=login.toLowerCase();
  login=removeAccents(login);
  login=login.replace(new RegExp(" ", "g"),"");
  document.form.login.value=login;
}

//	Select multiples
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
//	Fin Select Multpiles
//	---------------------------		FIN Personnel 		--------------------------------	//
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
    // Onglets
    $(".ui-tabs").tabs({
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

  
  // DataTables
  /*
  Les tableaux ayant la classe CJDataTable seront transformés en DataTable
  Les paramètres suivant peuvent leur être transmis via les classes et les attributs data-
  
  Sur le tableau (balise <table>), les attributs suivants :
  - data-sort : tri par défaut, doit être une chaine JSON du type [[0,"asc"],[1,"asc"]]. Valeur par défaut [[0,"asc"]]
  - data-stateSave : garde en mémoire l'état du tableau (tris, recherches). Valeurs : 0, false, 1 ou true. Valeur par défaut = true
  - data-length : nombre d'éléments affichés. Par défaut : 25
  
  Sur les balises th de l'entête, les classes suivantes permettent de définir le type de données contenues dans les cellules 
  pour trier correctement les colonnes :
  - dataTableNoSort : La colonne ne sera pas triable
  - dataTableDateFR : La colonne contient des dates au format JJ-MM-AAAA [HH:mm:ss]. 
      Si seule l'heure est affichée, le tri considère que la date est celle du jour
  - dataTableDateFR-fin : La colonne des dates de fin
  - dataTableHeureFR : La colonne contient des heures au format HH:mm[:ss]
  */
  
  $(".CJDataTable").each(function(){

    // Tri des colonnes en fonction des classes des th
    var aoCol=[];
    
    // Variables tr2 utilisées si 2 lignes en entête. tr2 = 2eme ligne
    var tr2=null;
    if($(this).find("thead tr").length==2){
      tr2=$(this).find("thead tr:nth-child(2)");
      tr2th=tr2.find("th");
      tr2thNb=tr2th.length;
      tr2Index=1;
    }

    $(this).find("thead tr:first th").each(function(){
      
      var th=[$(this)];
      
      // Si colspan et 2 lignes en entête, on se base sur la 2ème ligne
      if($(this).attr("colspan") && $(this).attr("colspan")>1 && tr2){
	th=new Array();
	for(i=0;i<$(this).attr("colspan");i++){
	  th.push(tr2.find("th:nth-child("+tr2Index+")"));
	  tr2Index++;
	}
      }

      for(i in th){
	// Par défault, tri basic
	if(th[i].attr("class")==undefined){
	  aoCol.push({"bSortable":true});
	}
	// si date
	else if(th[i].hasClass("dataTableDate")){
	  aoCol.push({"sType": "date"});
	}
	// si date FR
	else if(th[i].hasClass("dataTableDateFR")){
	  aoCol.push({"sType": "date-fr"});
	}
	// si date FR Fin
	else if(th[i].hasClass("dataTableDateFR-fin")){
	  aoCol.push({"sType": "date-fr-fin"});
	}
	// si heures fr (00h00)
	else if(th[i].hasClass("dataTableHeureFR")){
	  aoCol.push({"sType": "heure-fr"});
	}
	// si pas de tri
	else if(th[i].hasClass("dataTableNoSort")){
	  aoCol.push({"bSortable":false});
	}
	// Par défaut (encore) : tri basic
	else{
	  aoCol.push({"bSortable":true});
	}
      }
    });

    // Tri au chargement du tableau
    // Par défaut : 1ère colonne
    var sort=[[0,"asc"]];
    
    // Si le tableau à l'attribut data-sort, on récupère sa valeur
    if($(this).attr("data-sort")){
      var sort=JSON.parse($(this).attr("data-sort"));
    }
    
    // Taille du tableau par défaut
    var tableLength=25;
    if($(this).attr("data-length")){
      tableLength=$(this).attr("data-length")
    }

    // save state ?
    var saveState=true;
    if($(this).attr("data-stateSave") && ($(this).attr("data-stateSave")=="false" || $(this).attr("data-stateSave")=="0")){
      var saveState=false;
    }

    // Colonnes fixes
    var scollX=$(this).attr("data-fixedColumns")?"100%":"";
    
    // On applique le DataTable
    var CJDataTable=$(this).DataTable({
      "bJQueryUI": true,
      "sPaginationType": "full_numbers",
      "bStateSave": saveState,
      "aLengthMenu" : [[10,25,50,75,100,-1],[10,25,50,75,100,"All"]],
      "iDisplayLength" : tableLength,
      "aaSorting" : sort,
      "aoColumns" : aoCol,
      "oLanguage" : {"sUrl" : "vendor/dataTables.french.lang"},
      "sScrollX": scollX,
      "sDom": '<"H"lfr>t<"F"ip>T',
      "oTableTools": {
	"sSwfPath" : "vendor/DataTables-1.10.4/extensions/TableTools/swf/copy_csv_xls.swf",
	"aButtons": [
	  {
	    "sExtends": "xls",
	    "sButtonText": "Excel",
	  },
	  {
	    "sExtends": "csv",
	    "sButtonText": "CSV",
	  },
	  {
	    "sExtends": "pdf",
	    "sButtonText": "PDF",
	  },
	  {
	    "sExtends": "print",
	    "sButtonText": "Imprimer",
	  },
	]
      }
    });
    
    // Colonnes fixes
    if($(this).attr("data-fixedColumns")){
      var nb=$(this).attr("data-fixedColumns");
      new $.fn.dataTable.FixedColumns(CJDataTable, init={"iLeftColumns" : nb});
    }
  });

   // Check all checkboxes 
   $(".CJCheckAll").click(function(){
    $(this).closest("table").find("td input[type=checkbox]:visible").each(function(){
      $(this).click();
    });
  });
  
});