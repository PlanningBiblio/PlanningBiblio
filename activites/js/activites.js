/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : activites/js/activites.js
Création : 30 avril 2014
Dernière modification : 30 avril 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier regroupant les scripts JS de la page activites/index.php
Initilisation du tableau (dataTable)

Page appelée par la page include/header.php
*/

$(document).ready(function(){
  $("#tableActivities").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": true,
    "aaSorting" : [[2,"asc"]],
    "aoColumns" : [{"bSortable":false},{"bSortable":true},{"bSortable":true},],
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Toutes"]],
    "iDisplayLength" : 25,
    "oLanguage" : {"sUrl" : "vendor/dataTables.french.lang"}
  });
});