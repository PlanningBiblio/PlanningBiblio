/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : js/datepickerFr.js
Création : 14 novembre 2013
Dernière modification : 27 décembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Initialise les variables du plugin JQuery UI DatePicker

Cette page est appelée par le fichier index.php
*/

/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood{at}iinet.com.au),
                         Stéphane Nahmani (sholby@sholby.net),
                         Stéphane Raimbault <stephane.raimbault@gmail.com> */
jQuery(function($){
        $.datepicker.regional['fr'] = {
                closeText: 'Fermer',
                prevText: 'Précédent',
                nextText: 'Suivant',
                currentText: 'Aujourd\'hui',
                monthNames: ['janvier', 'février', 'mars', 'avril', 'mai', 'juin',
                        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
                monthNamesShort: ['janv.', 'févr.', 'mars', 'avril', 'mai', 'juin',
                        'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'],
                dayNames: ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
                dayNamesShort: ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'],
                dayNamesMin: ['D','L','M','M','J','V','S'],
                weekHeader: 'Sem.',
                dateFormat: 'dd/mm/yy',
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['fr']);
});