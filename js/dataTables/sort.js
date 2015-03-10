/*
Planning Biblio, Version 1.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : js/dataTables/sort.js
Création : 9 décembre 2013
Dernière modification : 13 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les fonctions de tris personnalisés pour les dataTables
*/

// Tri des dates avec (ou sans) heure (DD/MM/YYYY HHhii)
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
  "date-fr-pre": function ( a ) {
    if(!a){
      return " ";
    }
    // Séparation date et heure
    a=a.replace("&nbsp;"," ");
    var tmp = a.split(' ');
    
    // Tri Année Mois Jour Heure
    var frDatea = tmp[0].split('/');
    if(!tmp[1]){
      tmp[1]=" ";
    }
    return (frDatea[2] + frDatea[1] + frDatea[0] + tmp[1]);
  },

  "date-fr-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
  },

  "date-fr-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
  }
});


// Tri des dates de fin avec (ou sans) heure (DD/MM/YYYY HHhii)
// une date sans heure est supérieure à une date avec heure, si pas d'heure, heure=23h59
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
  "date-fr-fin-pre": function ( a ) {
    var tmp = a.split(' ');
    var frDatea = tmp[0].split('/');
    return (frDatea[2] + frDatea[1] + frDatea[0] + tmp[1]);
  },

  "date-fr-fin-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
  },

  "date-fr-fin-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
  }
});

// Tri des heures au format 00h00 et 0h00
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
  "heure-fr-pre": function ( a ) {
    if(!a){
      return " ";
    }

    if(a.search("N/A")>0){
      a="0000";
    }

    var prefix="";
    switch(a.length){
      case 4 : prefix="000"; break;
      case 5 : prefix="00"; break;
      case 6 : prefix="0"; break;
    }
    
    a=prefix+a;
    return (a);
  },

  "heure-fr-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
  },

  "heure-fr-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
  }
});