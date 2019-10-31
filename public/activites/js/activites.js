/**
Planning Biblio, Version 2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/activites/js/activites.js
Création : 21 mars 2016
Dernière modification : 21 mars 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les scripts JS nécessaires aux pages activites/* (affichage et modification des activités)
Fichier intégré par le fichier include/header.php avec la fonction getJSFiles.
*/

$(document).ready(function(){
  errorHighlight($(".important"),"highlight");
});