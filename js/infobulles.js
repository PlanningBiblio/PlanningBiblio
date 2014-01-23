/*
Planning Biblio, Version 1.6.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : js/calendrier.js
Création : 4 septembre 2013
Dernière modification : 4 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fichier contenant les fonctions JavaScript à l'affichage des infobulles

Cette page est appelée par le fichier index.php
*/

$(function(){
  //	Affichage de l'infobulle
  $("a").mouseover(function(){
    if($(this).attr("title")==undefined) return false;
    $("body").append("<span class='infobulle'></span>");
    var bulle=$(".infobulle:last");
    bulle.append($(this).attr("title"));
    $(this).attr("title","");
    var offset=$(this).children("img").length?30:20;
    var posTop=$(this).offset().top-offset;
    var posLeft=$(this).offset().left+$(this).width()/2-bulle.width()/2;
    if(posLeft+bulle.width()>$("body").width()){
      posLeft=$("body").width()-bulle.width();
    }
    bulle.css({
      left:posLeft,
      top:posTop-10,
      opacity:0
    });
    bulle.animate({
      opacity:0.99
    });
  });

  
  //	Suppression de l'infobulle
  $("a").mouseout(function(){
    if($(this).attr("title")==undefined) return false;
    var bulle=$(".infobulle:last");
    var title=bulle.text()?bulle.text():undefined;
    $(this).attr("title",title);
    bulle.animate({
      opacity:0
    },700,"linear",function(){bulle.remove();}
    );
  });
});