<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/feries.php
Création : février 2012
Dernière modification : 8 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Contient la fonction jour_ferie permettant de déterminer rapidement si un jour est férié (fêtes...)
Fonction jour_ferie de Olravet (http://olravet.fr) du 05 Mai 2008 modifiée pour prendre en paramètre la date au format
YYYY-MM-DD et pour retourner le nom du jour ferié
Code source de Olravet commenté en page de cette page
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
$version = $GLOBALS['version'] ?? null;
if (!isset($version)) {
    include_once "accessDenied.php";
    exit;
}

function jour_ferie($date)
{
    $tmp=explode("-", $date);
    $jour = $tmp[2];
    $mois = $tmp[1];
    $annee = $tmp[0];

    // dates fériées fixes
    if ($jour == 1 && $mois == 1) {
        return "Jour de l'an";
    }
    if ($jour == 1 && $mois == 5) {
        return "Fête du travail";
    }
    if ($jour == 8 && $mois == 5) {
        return "8 mai 1945";
    }
    if ($jour == 14 && $mois == 7) {
        return "Fête nationale";
    }
    if ($jour == 15 && $mois == 8) {
        return "Assomption";
    }
    if ($jour == 1 && $mois == 11) {
        return "La Toussaint";
    }
    if ($jour == 11 && $mois == 11) {
        return "Armistice";
    }
    if ($jour == 25 && $mois == 12) {
        return "Noël";
    }

    // fetes religieuses mobiles
    $pak = easter_date($annee);
    $jp = date("d", $pak);
    $mp = date("m", $pak);
    if ($jp == $jour && $mp == $mois) {
        return "Pâques";
    }

    $lpk = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) +1, date("Y", $pak));
    $jp = date("d", $lpk);
    $mp = date("m", $lpk);
    if ($jp == $jour && $mp == $mois) {
        return "Lundi de Pâques";
    }

    $asc = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 39, date("Y", $pak));
    $jp = date("d", $asc);
    $mp = date("m", $asc);
    if ($jp == $jour && $mp == $mois) {
        return "Jeudi de l'Ascension";
    }

    $pe = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 49, date("Y", $pak));
    $jp = date("d", $pe);
    $mp = date("m", $pe);
    if ($jp == $jour && $mp == $mois) {
        return "Pentecôte";
    }

    $lp = mktime(date("H", $asc), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 50, date("Y", $pak));
    $jp = date("d", $lp);
    $mp = date("m", $lp);
    if ($jp == $jour && $mp == $mois) {
        return "Lundi Pentecôte";
    }

    return null;
}


/*		CODE SOURCE						      */

/******************************************************************************/
/*                                                                            */
/*                       __        ____                                       */
/*                 ___  / /  ___  / __/__  __ _____________ ___               */
/*                / _ \/ _ \/ _ \_\ \/ _ \/ // / __/ __/ -_|_-<               */
/*               / .__/_//_/ .__/___/\___/\_,_/_/  \__/\__/___/               */
/*              /_/       /_/                                                 */
/*                                                                            */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Titre          : Déterminer rapidement si un jour est férié (fêtes...      */
/*                                                                            */
/* URL            : http://www.phpsources.org/scripts382-PHP.htm              */
/* Auteur         : Olravet                                                   */
/* Date édition   : 05 Mai 2008                                               */
/* Website auteur : http://olravet.fr/                                        */
/*                                                                            */
/******************************************************************************/



/*
//	code source d'origine
function jour_ferie($timestamp)
{
$jour = date("d", $timestamp);
$mois = date("m", $timestamp);
$annee = date("Y", $timestamp);
$EstFerie = ;
// dates fériées fixes
if($jour == 1 && $mois == 1) $EstFerie = 1; // 1er janvier
if($jour == 1 && $mois == 5) $EstFerie = 1; // 1er mai
if($jour == 8 && $mois == 5) $EstFerie = 1; // 8 mai
if($jour == 14 && $mois == 7) $EstFerie = 1; // 14 juillet
if($jour == 15 && $mois == 8) $EstFerie = 1; // 15 aout
if($jour == 1 && $mois == 11) $EstFerie = 1; // 1 novembre
if($jour == 11 && $mois == 11) $EstFerie = 1; // 11 novembre
if($jour == 25 && $mois == 12) $EstFerie = 1; // 25 décembre
// fetes religieuses mobiles
$pak = easter_date($annee);
$jp = date("d", $pak);
$mp = date("m", $pak);
if($jp == $jour && $mp == $mois){ $EstFerie = 1;} // Pâques
$lpk = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak)
, date("d", $pak) +1, date("Y", $pak) );
$jp = date("d", $lpk);
$mp = date("m", $lpk);
if($jp == $jour && $mp == $mois){ $EstFerie = 1; }// Lundi de Pâques
$asc = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak)
, date("d", $pak) + 39, date("Y", $pak) );
$jp = date("d", $asc);
$mp = date("m", $asc);
if($jp == $jour && $mp == $mois){ $EstFerie = 1;}//ascension
$pe = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak),
 date("d", $pak) + 49, date("Y", $pak) );
$jp = date("d", $pe);
$mp = date("m", $pe);
if($jp == $jour && $mp == $mois) {$EstFerie = 1;}// Pentecôte
$lp = mktime(date("H", $asc), date("i", $pak), date("s", $pak), date("m", $pak),
 date("d", $pak) + 50, date("Y", $pak) );
$jp = date("d", $lp);
$mp = date("m", $lp);
if($jp == $jour && $mp == $mois) {$EstFerie = 1;}// lundi Pentecôte
// Samedis et dimanches
$jour_sem = jddayofweek(unixtojd($timestamp), );
if($jour_sem ==  || $jour_sem == 6) $EstFerie = 1;
// ces deux lignes au dessus sont à retirer si vous ne désirez pas faire
// apparaitre les
// samedis et dimanches comme fériés.
return $EstFerie;
}

echo jour_ferie(mktime(,,,12,25,2008));

//sortira 1 car la date indiquée est fériée (25/12/2008).
//les jours ouvrables donneront 0.
*/
