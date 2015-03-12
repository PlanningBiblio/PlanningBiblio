<?php
// Translation tables for the calendar
// Tableaux de traduction pour le calendrier
// 
// Copyright (c) 2005-2006 - Sylvain BAUDOIN
// Please, report all errors to webmaster@themanualpage.org
// Veuillez remonter toute erreur a webmaster@themanualpage.org
// 
// The PHP code of this page may be redistributed and/or modified according to
// the terms of the GNU General Public License, as it has been published by the
// Free Software Foundation (version 2 and above).
// This program is distributed in the hope that it will be useful but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
// details.
// You should have received a copy of the GNU General Public License along with
// this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// 
// Ce programme PHP est un logiciel libre ; vous pouvez le redistribuer et/ou le
// modifier au titre des clauses de la Licence Publique Generale GNU, telle que
// publiee par la Free Software Foundation ; soit la version 2 de la Licence, ou
// (a votre discretion) une version ulterieure quelconque.
// Ce programme est distribue dans l'espoir qu'il sera utile, mais SANS AUCUNE
// GARANTIE ; sans meme une garantie implicite de COMMERCIABILITE ou DE
// CONFORMITE A UNE UTILISATION PARTICULIERE. Voir la Licence Publique Generale
// GNU pour plus de details.
// Vous devriez avoir recu un exemplaire de la Licence Publique Generale GNU
// avec ce programme ; si ce n'est pas le cas, ecrivez a la Free Software
// Foundation Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
// 
// 
// This file contains the translations for the calendar:
//   - $MONTHS: list of month. From January to December.
//   - $WEEK_DAYS: short names for the week days. From Sunday (Sun) to Saturday
//     (Sat).
//   - $MONTH_HEADER: how the month and year must be display in the calendar
//     header. %m will be replaced by the month and %y by the year.
// 
// It is strongly recommended to use the standard ISO 639 language codes. You
// will find a list of all language ISO codes here:
// http://www.loc.gov/standards/iso639-2/langcodes.html
// 
// Ce fichier contient les traductions pour le calendrier :
//   - $MONTHS: liste des mois. De janvier a decembre.
//   - $WEEK_DAYS: noms courts pour les jours de la semaine. De dimanche (dim)
//     a samedi (sam).
//   - $MONTH_HEADER: indique comment le mois et l'annee doivent etre affiches
//     dans l'en-tete du calendrier. %m correspond au mois et %y a l'annee.
// 
// Il est vivement recommande d'utiliser les codes de langue de la norme ISO
// 639. Vous trouverez une liste des codes ISO ici :
// http://www.loc.gov/standards/iso639-2/langcodes.html
// 

// Symbol of the sun and the moon coming from:
// Symboles du soleil et de la lune venant de:
// http://www.fileformat.info/info/unicode/block/miscellaneous_symbols/utf8test.htm
$CalSol = "&#x2600;";
$CalLun = "&#x263E;";

// Months (from January to December)
// Mois (de janvier a decembre)
$MONTHS["fr"] = array("Janvier", "F&eacute;vrier", "Mars", "Avril", "Mai",
                "Juin", "Juillet", "Ao&ucirc;t", "Septembre", "Octobre",
                "Novembre", "D&eacute;cembre");
$MONTHS["en"] = array("January", "February", "March", "April", "May",
                "June", "Jully", "August", "September", "October", "November",
                "December");
$MONTHS["de"] = array("Januar", "Februar", "M&auml;rz", "April", "Mai",
                "Juni", "Juli", "August", "September", "Oktober", "November",
                "Dezember");
$MONTHS["es"] = array("enero", "febrero", "marzo", "abril", "mayo", "junio",
                "julio", "agosto", "septiembre", "octubre", "noviembre",
                "diciembre");
$MONTHS["it"] = array("gennaio", "febbraio", "marzo", "aprile", "maggio",
                "giugno", "luglio", "agosto", "settembre", "ottobre",
                "novembre", "dicembre");
$MONTHS["zh"] = array("&#23493;", "&#21359;", "&#36784;", "&#24051;",
                "&#21320;", "&#26410;", "&#30003;", "&#37193;",
                "&#25100;", "&#20133;", "&#23376;", "&#19985;");

// Week days (from Sunday to Saturday)
// Jours de la semaine (de dimanche a samedi)
$WEEK_DAYS["fr"] = array("dim", "lun", "mar", "mer", "jeu", "ven", "sam");
$WEEK_DAYS["en"] = array("Sun", "Mon", "Tue", "Web", "Thu", "Fri", "Sat");
$WEEK_DAYS["de"] = array("Son", "Mon", "Die", "Mit", "Don", "Fre", "Sam");
$WEEK_DAYS["es"] = array("dom", "lun", "mar", "mi&eacute;", "jue", "vie", "s&aacute;b");
$WEEK_DAYS["it"] = array("dom", "lun", "mart", "mer", "gio", "ven", "sab");
$WEEK_DAYS["zh"] = array("&#26143;&#26399;&#26085;", "&#26143;&#26399;&#19968;",
                   "&#26143;&#26399;&#20108;", "&#26143;&#26399;&#19977;",
                   "&#26143;&#26399;&#22235;", "&#26143;&#26399;&#20116;",
                   "&#26143;&#26399;&#20845;");

// Structure of the calendar header:
//   - %m = month
//   - %y = year
// Structure de l'en-tete du calendrier :
//   - %m = mois
//   - %y = annee
$MONTH_HEADER["fr"] = "%m %y";
$MONTH_HEADER["en"] = "%y, %m";
$MONTH_HEADER["de"] = "%m %y";
$MONTH_HEADER["es"] = "%m %y";
$MONTH_HEADER["it"] = "%m %y";
$MONTH_HEADER["zh"] = "&#x2600; %y, %m";

// Call back to the current date name value
// Valeur du nom de rappel vers la date courante
$CALLBACK["fr"] = "Aujourd'hui";
$CALLBACK["en"] = "Today";
$CALLBACK["de"] = "Heute";
$CALLBACK["es"] = "hoy";
$CALLBACK["it"] = "oggi";
$CALLBACK["zh"] = "&#20170;&#22825;";
?>
