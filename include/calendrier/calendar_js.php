<?php
// Script qui permet d'integrer le calendrier en tant que JavaScript
// Script used to integrate the calendar via as a JavaScript
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
// Usage:
// ------
// This script must be placed along with the file calendar.php. If you rename
// calendar.php, you must also rename this file the same way.
// E.g.: inc_cal.phtml => inc_cal_js.phtml
// 
// In order to integrate the calendar as a JavaScript, just do as follows:
// 
// <script type="text/javascript" src="/calendar_js.php?..."></script>
// 
// Put in the URL the parameters that you would like to pass to the function
// Calendar(): ?PREFIX=...&CSS_PREFIX=&DATE_URL=...&URL_PARAMETER=...
// &USE_SESSION=...&PRESERVE_URL=...
// 
// Utilisation :
// -------------
// Ce script doit se trouver a cote du script calendar.php. Si vous avez
// renomme calendar.php, ce script doit s'appeler comme le nouveau nom, mais
// avec _js en plus. Exemple : inc_cal.phtml => inc_cal_js.phtml
// 
// Pour l'integration JavaScript, il suffit de faire :
// 
// <script type="text/javascript" src="/calendar_js.php?..."></script>
// 
// Passez dans l'URL les parametres que vous souhaitez passer a la fonction
// Calendar() : ?PREFIX=...&CSS_PREFIX=&DATE_URL=...&URL_PARAMETER=...
// &USE_SESSION=...&PRESERVE_URL=...
// 


// Content-Type
header("Content-Type: text/javascript");

// Shall we display the calendar or the JavaScript that retrieves the missing
// parameters?
if (isset($_GET["display_calendar"])) {
	// We display the calendar in the JavaScript format
	require_once($_SERVER["DOCUMENT_ROOT"].str_replace("_js.", ".", $_SERVER["PHP_SELF"]));
	// We get all the calendar parameters from the URL
	// We replace all "true"/"false" by real booleans
	foreach ($_GET as $key => $value) {
		if ($value == "true") {
			$params[$key] = true;
		} elseif ($value == "false") {
			$params[$key] = false;
		} else {
			$params[$key] = $value;
		}
	}
	$params["JS"] = true;
	calendar($params);
} else {
	$prefix = (isset($_GET["PREFIX"]))?$_GET["PREFIX"]:"calendar_";
	
	// If sessions are used, we do a session_start() in order to get the SID
	// if required
	if (isset($_GET["USE_SESSION"]) && $_GET["USE_SESSION"] = "true") {
		session_start();
		$SID = "&amp;".SID;
	} else {
		$SID = "";
	}
	
	// URL to call the calendar
	$calendar_URL = str_replace("&", "&amp;", $_SERVER["REQUEST_URI"]);
	$URL_items = parse_url($calendar_URL);
	if (isset($URL_items["query"])) {
		$calendar_URL .= "&amp;";
	} else {
		$calendar_URL .= "?";
	}
	// We add the paremeter to say that the calendar must be display as a
	// JavaScript
	$calendar_URL .= "display_calendar=true";
?>
// URL of the current page
var calendar_current_url = document.location;
// We retrieve the date to display
var calendar_month_date = "";
// We also retrieve the SID if it exists for the session
var SID = "";
var query_string = this.location.search.substring(1);
if (query_string.length > 0) {
	var params = query_string.split("&");
	for (var i = 0; i < params.length; i++) {
		var pos = params[i].indexOf("=");
		if (params[i].substring(0, pos) == "<?php echo $prefix; ?>date") {
			calendar_month_date = params[i].substring(pos + 1);
		}
		if (params[i].substring(0, pos) == "<?php echo session_name(); ?>") {
			SID = "<?php echo session_name(); ?>=" + params[i].substring(pos + 1);
		}
	}
}

// We display the calendar via a second JavaScript call
document.write('<script type="text/javascript" src="<?php echo $calendar_URL; ?>&amp;JS_URL=' + escape(calendar_current_url) + '&amp;<?php echo $prefix; ?>date=' + calendar_month_date + '&amp;' + SID + '"></script>');
<?php
}
?>
