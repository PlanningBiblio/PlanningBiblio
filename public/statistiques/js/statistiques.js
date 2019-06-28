/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : statistiques/js/statistiques.js
Création : 20 septembre 2017
Dernière modification : 20 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les scripts JS nécessaires auxpages statistiques/* (affichage des statistiques)
Fichier intégré par le fichier include/header.php avec la fonction getJSFiles.
*/


$(function(){
  $('#statistiques_heures_defaut_lien').click(function(){
    $('#statistiques_heures_defaut_hidden').val('1');
    $('#form').submit();
  });
});