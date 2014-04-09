/*
Planning Biblio, Version 1.7.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : js/script.js
Création : mai 2011
Dernière modification : 9 avril 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier contenant les principales fonctions JavaScript

Cette page est appelée par les fichiers include/header.php, setup/header.php et planning/poste/menudiv.php
*/

//	----------------------------		Variables			------------------------	//
//	----------------------------		Variables Menu contextuel	------------------------	//
var poste;
var output;
var perso_id;
var date;
var debut;
var fin;
var tableau;
var tab_menu;
var menudiv_display="none";
//	----------------------------		Position du pointeur		-----------------------		//
// Detection du navigateur
nc6=(typeof(window.controllers) !='undefined' && typeof(window.locationbar) != 'undefined')?true:false;
nc4=(document.layers)?true:false;
ie4=(document.all)? true:false;

// on lance la detection des mouvements du pointeur
// instructions pour netscape 4.x
if(nc4){
  document.captureEvents(Event.MOUSEMOVE);
}
// Instructions pour Netscape 6.x
if(nc6) {
//   document.addEventListener("mousemove",document.onmousemove,true);
  suivre_souris;
}
// Instructions pour IE
document.onmousemove=suivre_souris;
// fonction executée pour chaque mouvement de pointeur
//	----------------------------		FIN Position du pointeur	------------------------	//
//	----------------------------		FIN Variables			------------------------	//


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




function calendrier(champ,form){
  if(form==undefined){
    form="form";
  }
  url="include/calendrier.php?champ="+champ+"&form="+form;
  
  X=document.body.clientWidth;
  Y=document.body.clientHeight;
  x=document.position.x.value;
  y=document.position.y.value;
  if(x>X-210)
    x=X-210;
  if(y>Y-180)
    y=Y-180;
  document.getElementById('calendrier').style.left=x+"px";
  document.getElementById('calendrier').style.top=y+"px";
  document.getElementById('calendrier').style.display="block";
  document.getElementById('calendrier').src=url;
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
	
function checkall(form,me){
  elems=document.forms[form].elements;
  for(i=0;i<elems.length;i++){
    if(elems[i].type=="checkbox" && elems[i]!=me){
      elems[i].click();
    }
  }
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

  
function file(fichier){
  if(fichier.indexOf("php?")>0)				// l'ajout du parametre unique ms (nombre de millisecondes depuis le 1er Janvier 1970)
    fichier=fichier+"&ms="+new Date().getTime();	// permet d'eviter les problème de cache (le navigateur pense ouvrir une nouvelle page)	
  else if(fichier.indexOf("php")>0)
    fichier=fichier+"?ms="+new Date().getTime();
    
  if(window.XMLHttpRequest) // FIREFOX
    xhr_object = new XMLHttpRequest();
  else if(window.ActiveXObject) // IE
    xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
  else
    return(false);

  xhr_object.open("GET", fichier, false);
  xhr_object.send(null);	
  if(xhr_object.readyState == 4) return(xhr_object.responseText);
  else return(false);
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

function information(message,type,top){
  if(top==undefined){
    top=60;
  }
  $("body").append("<div id='JSInformation'>"+message+"</div>");
  errorHighlight($("#JSInformation"),type);
  position($("#JSInformation"),top,"center");
  setTimeout("$('#JSInformation').remove()",5000);
}

function initform(objet){
  tab=objet.split(";");
  for(i=0;i<tab.length;i++){
    tab2=tab[i].split("=");
    champ=tab2[0];
    valeur=tab2[1];
    document.form.elements[champ].value=valeur;
  }
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

function popup_ctrl(url,autorisation,width,height){
  if(autorisation){
    popup(url,width,height);
  }
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

function retour(page){
  if(opener){
    opener.window.location.reload(false);
    window.close();
  }
  else{
    rep=confirm("Etes vous sûr(e) de vouloir quitter cette page sans l'avoir validée ?");
    if(rep)
      document.location.href="index.php?page="+page;
  }
}

function show(id,tab,li){
  tab=tab.split(",");		// tab contient le nom des autres onglets
  for(i=1;i<tab.length+2;i++){	// met l'onglet current = au li manquant (désactive l'onglet)
    if(!document.getElementById("li"+i)){
      document.getElementById("current").id="li"+i;
    }
  }
  
  document.getElementById(li).id="current";	// active l'onglet choisi
  document.getElementById(id).style.display="";	// affiche le div choisi
  for(i=0;i<tab.length;i++){			// cache le contenu des autres div
    document.getElementById(tab[i]).style.display="none";
  }
}

//	suivre_souris : determine la position de la souris pour l'affichage des calendriers
function suivre_souris(e){
  // Instruction pour Netscape 4 et supérieur
  if(nc4 || nc6){
    // On affete à x et y les positions X et Y du pointeur lors de l'évenement move
    var x=e.pageX;
    var y=e.pageY;
  }
  // Instructions équivalentes pour Internet Explorer
  if(ie4){
    var x = event.x;
    var y = event.y;
  }
  // On affecte les données obtenues au champs du formulaire
  document.position.x.value=x;
  document.position.y.value=y;
}

// supprime(page,id)	Utilisée par postes et modeles
function supprime(page,id){
  if(confirm("Etes vous sûr de vouloir supprimer cet élément ?")){
    file("index.php?page="+page+"/valid.php&id="+id+"&action=supprime");
    window.location.reload(false);
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

function tableauxNombre(){
  $.ajax({
    url: "planning/postes_cfg/ajax.tableaux.php",
    type: "get",
    data: "id="+$("#id").val()+"&nombre="+$("#nombre").val(),
    success: function(){
      location.href="index.php?page=planning/postes_cfg/modif.php&numero="+$("#id").val();
    },
    error: function(){
      information("Une erreur est survenue lors de la modification du nombre de tableaux.","error");
    }
  });
}

function tabSiteUpdate(){
  site=$("#selectSite").val();
  numero=$("#numero").val();
  $.ajax({
    url: "planning/postes_cfg/ajax.siteUpdate.php",
    type: "get",
    data: "numero="+numero+"&site="+site,
    success: function(){
      $("#TableauxTips").html("Le site a &eacute;t&eacute; modifi&eacute; avec succ&egrave;s");
      errorHighlight($("#TableauxTips"),"highlight");
      $("#TableauxTips").css("top",$("#submitSite").offset().top-15);
      $("#TableauxTips").css("left",$("#submitSite").offset().left+100);
      $("#TableauxTips").show();
      var timeout=setTimeout(function(){$("#TableauxTips").hide();},5000);
      location.reload(false);	// on rafraichi pour mettre à jour le tableau des lignes
    },
    error: function(){
      $("#TableauxTips").html("Une erreur est survenue pendant la modification du site.");
      errorHighlight($("#TableauxTips"),"error");
      $("#TableauxTips").css("top",$("#submitSite").offset().top-15);
      $("#TableauxTips").css("left",$("#submitSite").offset().left+100);
      $("#TableauxTips").show();
      var timeout=setTimeout(function(){$("#TableauxTips").hide();},5000);
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

function verif_categorieA(){
  $.ajax({
    url: "planning/poste/ajax.categorieA.php",
    type: "get",
    data: "date="+date+"&site="+site,
    success: function(retour){
      if(retour == "true"){
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
function getScrollingPosition(){
  var position = [0, 0];
  if (typeof window.pageYOffset != 'undefined'){
    position = [window.pageXOffset,window.pageYOffset];
  }
  else if (typeof document.documentElement.scrollTop!= 'undefined' && document.documentElement.scrollTop > 0){
    position = [document.documentElement.scrollLeft,document.documentElement.scrollTop];
  }
  else if (typeof document.body.scrollTop != 'undefined'){
    position = [document.body.scrollLeft,document.body.scrollTop];
  }
  return position;
}
	
function getWindowHeight(){
  var windowHeight=0;
  if(typeof(window.innerHeight)=='number') {
    windowHeight=window.innerHeight;
  }
  else{
    if(document.documentElement&& document.documentElement.clientHeight){
      windowHeight = document.documentElement.clientHeight;
    }
    else{
      if(document.body&&document.body.clientHeight){
	  windowHeight=document.body.clientHeight;
      }
    }
  }
  return windowHeight;
}

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
  var height=getWindowHeight();
  var scroll=getScrollingPosition();
  if(document.getElementById("a_retour"))
    document.getElementById("a_retour").style.top=scroll[1]+height-50+"px";
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
//	--------------------------------	Planning/postes		---------------------------------	//
function affiche_activites(div,action){
  if(action=="affiche"){
    document.getElementById("act"+div+"b").style.display="";
    document.getElementById("act"+div).style.display="none";
  }
  else{
    document.getElementById("act"+div).style.display="";
    document.getElementById("act"+div+"b").style.display="none";
  }
}

// 	bataille_navale : menu contextuel : met à jour la base de données en arrière plan et affiche les modifs en JS dans le planning
function bataille_navale(perso_id,nom,barrer,ajouter,classe){
  db=file("index.php?page=planning/poste/majdb.php&poste="+poste+"&debut="+debut+"&fin="+fin+"&perso_id="+perso_id+"&date="+date+"&barrer="+barrer+"&ajouter="+ajouter);
  
  if(!perso_id && !barrer){			// Supprimer tout
    $("#cellule"+cellule).html("&nbsp;");
    $("#cellule"+cellule).css("textDecoration","");
    $("#cellule"+cellule+"b").hide();
    $("#cellule"+cellule).removeClass();
    $("#td"+cellule).removeClass();
  }
  else if(!perso_id && barrer){			// Barrer l'(es) agent(s) placé(s)
    $("#cellule"+cellule).css("color","red");
    $("#cellule"+cellule).css("textDecoration","line-through");
    $("#cellule"+cellule+"b").css("color","red");
    $("#cellule"+cellule+"b").css("textDecoration","line-through");
  }
  else if(perso_id && !barrer && !ajouter){	// Remplacer l'agent placé par un autre
    $("#cellule"+cellule).text(nom);
    $("#cellule"+cellule).css("color","black");
    $("#cellule"+cellule).css("textDecoration","");
    $("#td"+cellule).attr("class",classe);
    $("#cellule"+cellule).attr("class","cellule "+classe);
    $("#cellule"+cellule+"b").hide();
  }
  else if(perso_id && barrer){			// barrer et ajoute un autre
    $("#td"+cellule).removeClass();
    $("#cellule"+cellule).css("textDecoration","line-through");
    $("#cellule"+cellule).css("color","red");
    $("#cellule"+cellule+"b").text(nom);
    $("#cellule"+cellule+"b").attr("class","cellule "+classe);
    $("#cellule"+cellule+"b").show();
  }
  else if(perso_id && ajouter){			// ajouter un agent
    if($("#cellule"+cellule).text()<nom){
      var nom1=$("#cellule"+cellule).text();
      var nom2=nom;
      var classe1=$("#cellule"+cellule).attr("class");
      var classe2=classe;
    }
    else{
      var nom1=nom;
      var nom2=$("#cellule"+cellule).text();
      var classe1=classe;
      var classe2=$("#cellule"+cellule).attr("class");
    }
    $("#td"+cellule).removeClass();
    $("#cellule"+cellule).text(nom1);
    $("#cellule"+cellule+"b").text(nom2);
    $("#cellule"+cellule).attr("class",classe1);
    $("#cellule"+cellule+"b").attr("class",classe2);
    $("#cellule"+cellule+"b").css("color","black");
    $("#cellule"+cellule+"b").css("textDecoration","");
    $("#cellule"+cellule+"b").show();
  }
  $("#menudiv").hide();				// cacher le menudiv

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

//	ItemSelMenu : Menu contextuel
function  ItemSelMenu(e){
  if(cellule=="")
    return false;

  document.getElementById("menudiv").scrollTop=0;
  text=file("index.php?page=planning/poste/menudiv.php&debut="+debut+"&fin="+fin+"&poste="+poste+"&date="+date+"&menu=off&positionOff=");
  hauteur=146;
  document.getElementById("menudiv").innerHTML=text;

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
  return false ;
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
//	--------------------------------	FIN Planning/postes	---------------------------------	//
//	--------------------------------	Tableaux		-------------------------	//
//	--------------------------------	Tableaux - Horaires	-------------------------	//
function add_horaires(tableau){
  for(i=0;i<50;i++){
    if(document.getElementById("tr_"+tableau+"_"+i).style.display=="none"){
      document.getElementById("tr_"+tableau+"_"+i).style.display="";
      return;
    }
  }
}

function change_horaires(elem){
  tmp=elem.name.split("_");
  tmp[2]++;
  elem2="debut_"+tmp[1]+"_"+tmp[2];
  for(i=0;i<document.form2.elements.length;i++){
    if(document.form2.elements[i].name==elem2){
      document.form2.elements[i].selectedIndex=elem.selectedIndex;
      break;
    }
  }
}
//	--------------------------------	FIN Tableaux - Horaires	-------------------------	//
//	--------------------------------	Tableaux - Lignes	-------------------------	//
function ajout(nom,id){
  id++;
  for(i=id;i<100;i++){
    if(document.getElementById("tr_"+nom+i).style.display=="none"){
      document.getElementById("tr_"+nom+i).style.display="";
      fin=i;
      break;
    }
  }
  for(i=fin;i>id;i--){
    j=i-1;
    document.form4.elements[nom+i].selectedIndex=document.form4.elements[nom+j].selectedIndex;
    document.form4.elements[nom+i].className=document.form4.elements[nom+j].className;
    document.getElementById("td_"+nom+i+"_0").className=document.getElementById("td_"+nom+j+"_0").className;
  }
  document.form4.elements[nom+id].selectedIndex=0;
  document.form4.elements[nom+id].className=null;
  document.getElementById("td_"+nom+i+"_0").className=null;
}

function couleur(nom,id){
  background=document.form4.elements[nom+id].options[document.form4.elements[nom+id].selectedIndex].style.background;
  color=document.form4.elements[nom+id].options[document.form4.elements[nom+id].selectedIndex].style.color;
  if(isNaN(document.form4.elements[nom+id].value)){		// Si Grande Ligne
    document.getElementById("tr_"+nom+id).style.background=background;
    i=1;
    while(document.getElementById("td_"+nom+id+"_"+i)){		// Affiche la cellule colspan
      document.getElementById("td_"+nom+id+"_"+i).style.display="none";
      i++;
    }
    document.getElementById("td_"+nom+id).style.display="";
  }
  else{													// Si poste
    document.getElementById("tr_"+nom+id).style.background="#FFFFFF";
    document.form4.elements[nom+id].style.background=background;
    document.form4.elements[nom+id].style.color=color;
    i=1;
    while(document.getElementById("td_"+nom+id+"_"+i)){		// Affiche les différentes cellules
      document.getElementById("td_"+nom+id+"_"+i).style.display="";
      i++;
    }
  document.getElementById("td_"+nom+id).style.display="none";
  }
  document.getElementById("td_"+nom+id+"_0").style.background=background;
  document.getElementById("tr_"+nom+id).style.color=color;
  document.getElementById("ajout_"+nom+id).style.color=color;
  document.getElementById("supprime_"+nom+id).style.color=color;
}

function couleur2(elem,td){
  if(elem.checked)
    document.getElementById(td).className="cellule_grise";
  else
    document.getElementById(td).className="";
}

function supprime_tab(nom,id){
  document.form4.elements["select_"+nom+id].value="";
  document.getElementById("tr_select_"+nom+id).style.display="none";
  i=1;
}
//	--------------------------------	FIN Tableaux - Lignes	-------------------------	//

function ctrl_nom(me){
  exist=false;
  valeur=me.value.toLowerCase();
  valeur=valeur.trim();
  for(i=0;i<grp_nom.length;i++){
    if(valeur==grp_nom[i]){
      exist=true;
    }
  }
  document.getElementById("submit").disabled=false;
  document.getElementById("nom_utilise").style.display="none";
  me.style.border=null;
  me.style.background="#FFFFFF";
    
  if(exist){
    me.style.border="solid 3px red";
    me.style.background="#FFCCCC";
    document.getElementById("submit").disabled=true;
    document.getElementById("nom_utilise").style.display="";
  }
}

function supprime_groupe(id,nom){
  if(confirm("Etes-vous sûr de vouloir supprimer le groupe \""+nom+"\" ?")){
    location.href="planning/postes_cfg/groupes_supp.php?id="+id;
  }
}

function supprime_ligne(id,nom){
  if(confirm("Etes-vous sûr de vouloir supprimer la ligne \""+nom+"\" ?")){
    file("planning/postes_cfg/supp_lignes.php?id="+id);
    location.href="index.php?page=planning/postes_cfg/index.php";
  }
}
//	Suppression des élements sélectionnés (page de suppression, exception (séparés par virgules))
function supprime_select(page,except){
  except=except.split(",");
  ids=new Array();
  i=0;
  while(document.form.elements["chk"+i]){
    exception=false;
    elem=document.form.elements["chk"+i];
    if(elem.checked){
      for(j=0;j<except.length;j++){
	if(except[j]==elem.value)
	  exception=true;
      }
      if(exception==false){
	ids.push(elem.value);
      }
    }
    i++;
  }
  if(!ids[0]){
    alert("Les éléments sélectionnés ne peuvent être supprimés.");
  }
  else if(confirm("Etes-vous sûr(e) de vouloir supprimer les éléments sélectionnés ?")){
    file("index.php?page="+page+"&ids="+ids);
  }
  window.location.reload(false);
}
//	--------------------------------	FIN Tableaux		-------------------------	//
//	--------------------------------	Statistiques		---------------------------------	//
function export_stat(nom,type){
  file("index.php?page=statistiques/export.php&nom="+nom+"&type="+type+"&menu=off");
  if(type=="csv")
    window.open("data/stat_"+nom+".csv");
  else
    window.open("data/stat_"+nom+".xls");
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
    $(".ui-button").button();
    $(".datepicker").datepicker();
  });
});