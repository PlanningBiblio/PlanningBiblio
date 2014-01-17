/*
Planning Biblio, Version 1.6.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : js/calendrier.js
Création : 04 janvier 2013
Dernière modification : 4 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier contenant les fonctions JavaScript necessaires à l'utilisation des calendriers

Cette page est appelée par le fichier calendrier/index.php
*/

function setDate(date,champ,form){
  if(form==undefined){
    form="form";
  }
  date=date.substr(0,4)+"-"+date.substr(4,2)+"-"+date.substr(6,2);
  parent.document.forms[form].elements[champ].value=date;
  parent.document.getElementById("calendrier").style.display="none";
}

function setPlDate(date){
  date=date.substr(0,4)+"-"+date.substr(4,2)+"-"+date.substr(6,2);
  parent.document.location.href="../index.php?page=planning/poste/index.php&date="+date;
}