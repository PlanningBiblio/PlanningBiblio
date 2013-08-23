<?php
// Displays a calendar as a table
// Affiche un petit calendrier sous forme de tableau
// 
// Version: v2.4
// Copyright (c) 2005-2008 - Sylvain BAUDOIN
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
// You integrate the calendar in a PHP page as follows:
// 
// ...
// require_once("calendar.php");
// ...
// $parameters = array("param1" => value1, "param2" => value2, ...);
// calendar($parameters);
// ...
// 
// Utilisation :
// -------------
// Le calendrier s'integre dans une page PHP en faisant :
// 
// ...
// require_once("calendar.php");
// ...
// $parametres = array("param1" => value1, "param2" => value2, ...);
// calendar($parametres);
// ...
// 
// Parameters:
// -----------
// "PREFIX":
//         prefix of the URL and session parameters of the calendar. Define a
//         different value for each different calendar to display along in the
//         same page. Do not start this prefix by a digit.
//         Default value: "calendar_".
// 
// "CSS_PREFIX":
//         prefix of the CSS classes used for styling the calendar. To be used
//         to render the calendars for different styles.
//         Default value: "calendar_".
// 
// "DATE_URL":
//         if set, indicates a URL to use for making the days clickable.
//         Starting from version 2.4, DATE_URL is not systematically completed
//         with the URL parameter indicated by the calendar parameter
//         "URL_PARAMETER". Instead, if the string "__DATE__" is found in
//         DATE_URL, the URL is not completed but the token is replaced by
//         the formatted date. This new working allows you to build more types
//         of URL, including JavaScript calls, without the need of a date URL
//         function (DATE_URL_FUNCTION). Example: if DATE_URL is
//         "javascript:myfunction(param1, '__DATE__', param3);", then the
//         following link will be constructed for the date 03/12/2008:
//         "javascript:myfunction(param1, '03122008', param3);".
//         Else, if DATE_URL does not contain the string "__DATE__", the
//         provided URL is completed as before with the URL parameter indicated
//         by the calendar parameter "URL_PARAMETER".
//         If DATE_URL_FUNCTION is defined as well as this parameter, this
//         parameter is ignored and only DATE_URL_FUNCTION is taken into
//         account (except for the calendar title).
//         Default value: "".
// 
// "URL_PARAMETER":
//         if the previous parameter ("DATE_URL") is set, indicates the name of
//         the URL parameter used to complete the URL "DATE_URL" and pass the
//         clicked date. The date is formated according to the value of the
//         parameter URL_DAY_DATE_FORMAT for the days and URL_MONTH_DATE_FORMAT
//         for the month and year (title links).
//         Default value: "date".
// 
// "USE_SESSION":
//         set TRUE to store the calendar rendering data in session. This allows
//         this script to remember the date to be displayed while browsing among
//         various pages.
//         Default value: FALSE.
//         WARNING: if you want to use sessions, you must create the session
//         first at the very beginning of the page, because this script will not
//         do it.
// 
// "PRESERVE_URL":
//         when building the links for the "previous month" and "next month"
//         links, tells if current URL must be preserved (TRUE) and the date
//         appended (?xx=yyy&...&date=...) or if the query string of the current
//         URL must be discarded (FALSE) and just add the date parameter
//         (?date=...).
//         Default value: TRUE.
// 
// "JS":
//         tells if the calendar is integrated as a JavaScript (TRUE) or not.
//         Default value: FALSE.
// 
// "JS_URL":
//         if the calendar is integrated as a JavaScript, this parameter gives
//         the URL of the page that integrates the calendar.
//         Default value: "".
// 
// "FIRST_WEEK_DAY":
//         first day of the week: 1 for Monday, 2 for Tuesday, etc..., 7 or
//         0 for Sunday.
//         Default value: 1 (Monday).
// 
// "LANGUAGE_CODE":
//         2-letter ISO code of the language to use for rendering the calendar.
//         Default value: "fr" (French).
// 
// "CLICKABLE_TITLE":
//         when DATE_URL is set, tells if the calendar title (i.e. the month +
//         year at the top of the calendar) is also clickable. In this case, the
//         date passed in the URL parameter has the format indicated by the
//         parameter URL_MONTH_DATE_FORMAT.
//         Default value: TRUE.
// 
// "OUTPUT_MODE":
//         if set to "return", will make the function Calendar return the HTML
//         code of the calendar. If set to "echo", the HTML code of the calendar
//         is directly echoed into the response to the web browser. Use "return"
//         if you want to get the HTML code of the calendar into a PHP variable
//         and make some processing on it.
//         Default value: "echo".
// 
// "URL_DAY_DATE_FORMAT":
//         when DATE_URL is defined, tells the format of the calendar day dates
//         passed in the URL. This format must comply with the format supported
//         by the PHP function date. Has no effect if DATE_URL is not defined.
//         Default value: "dmY" (ddmmyyyy).
// 
// "URL_MONTH_DATE_FORMAT":
//         when DATE_URL is defined, tells the format of the month date passed
//         in the URL for the calendar's title. This format must comply with
//         the format supported by the PHP function date. Has no effect if
//         DATE_URL is not defined.
//         Default value: "mY" (mmyyyy).
// 
// "PRE_DATE_URL_FUNCTION":
//         this parameter indicates the name of a user function to be called
//         before the calendar is generated and the function indicated by
//         DATE_URL_FUNCTION is called. This function is called with two
//         parameters:
//           - The first visible date of the calendar (format: ddmmyyyy)
//           - The last visible date of the calendar (format: ddmmyyyy)
//         This function is useful to define the range of dates that are going
//         to be displayed in the calendar, that is the range of dates for which
//         the function DATE_URL_FUNCTION will be called. This could be used to
//         optimize the execution of SQL queries used in DATE_URL_FUNCTION.
//         Default value: "" (no custom pre date URL function used).
// 
// "DATE_URL_FUNCTION":
//         indicates the name of a user function to be called to return the
//         target URL of the date passed as a parameter (format: ddmmyyyy): if
//         this parameter is defined, everytime a date is going to be rendered,
//         this function is called with the date as a parameter. If you want
//         this date to be clickable, simply returns the target URL; if you do
//         not want this date to be clickable, simply return NULL, FALSE or even
//         "".
//         If DATE_URL is defined as well as this parameter, only this
//         DATE_URL_FUNCTION parameter is taken into account.
//         Default value: "" (no custom date URL function used).
// 
// Parametres :
// ------------
// "PREFIX" :
//         prefixe des parametres d'URL et de session du calendrier. Definissez
//         une valeur differente pour chaque calendrier a afficher sur la meme
//         page. Ne pas commencer le prefixe par un chiffre.
//         Valeur par defaut : "calendar_".
// 
// "CSS_PREFIX" :
//         prefixe des classes CSS utilisees pour le style du calendrier. A
//         utiliser pour afficher des calendriers dans differents styles.
//         Valeur par defaut : "calendar_".
// 
// "DATE_URL" :
//         si defini, indique une URL a utiliser pour rendre les jours du
//         calendrier cliquables.
//         A partir de la version 2.4, DATE_URL n'est plus systematiquement
//         complete avec le parametre "URL_PARAMETER". A la place, si la chaine
//         "__DATE__" est trouvee dans DATE_URL, l'URL n'est pas completee mais
//         "__DATE__" est remplace par la date formatee. Ceci permet desormais
//         de construire davantage d'URL, y compris des appels JavaScript, sans
//         avoir a passer par une fonction de date (voir DATE_URL_FUNCTION).
//         Par exemple, si DATE_URL vaut "javascript:mafonction(param1, '__DATE__', param3);"
//         alors le lien suivant sera calcule pour la date du 03/12/2008 :
//         "javascript:mafonction(param1, '03122008', param3);"
//         Sinon, si DATE_URL ne contient pas "__DATE__", cette URL est
//         completee par le parametre d'URL indique par le parametre
//         "URL_PARAMETER" du calendrier.
//         Si le parametre DATE_URL_FUNCTION est egalement defini, ce parametre
//         est ignore et seul DATE_URL_FUNCTION sera pris en compte (sauf pour
//         le titre du calendrier).
//         Valeur par defaut : "".
// 
// "URL_PARAMETER" :
//         si le parametre precedent ("DATE_URL") est defini, indique le nom du
//         parametre d'URL a utiliser pour completer l'URL "DATE_URL" avec la
//         date cliquee. La date est passée au format indiqué par le parametre
//         URL_DAY_DATE_FORMAT pour les jours et URL_MONTH_DATE_FORMAT pour le
//         mois et l'annee (lien du titre du calendrier).
//         Valeur par defaut : "date".
// 
// "USE_SESSION" :
//         mettre a TRUE pour stocker les donnees d'affichage du calendrier en
//         session. Cela permet de memoriser l'affichage lorsqu'on navigue
//         entre plusieurs pages.
//         Valeur par defaut : FALSE (faux).
//         ATTENTION : si vous utilisez les sessions, n'oubliez pas de creer la
//         session au tout debut de votre script, ce script ne le fera pas.
// 
// "PRESERVE_URL" :
//         indique, au moment de constuire les URL des liens "mois precedent"
//         et "mois suivant", s'il faut conserver (TRUE) l'URL actuelle de la
//         page et ajouter la date (?xx=yyy&...&date=...) ou s'il faut supprimer
//         la query string et ne mettre que le parametre de date (?date=...).
//         Valeur par defaut : TRUE (vrai).
// 
// "JS" :
//         indique si le calendrier est integre en JavaScript (TRUE) ou non.
//         Valeur par defaut : FALSE (faux).
// 
// "JS_URL" :
//         si l'integration JavaScript est utilisee, doit indiquer l'URL de la
//         page integrant le calendrier.
//         Valeur par defaut : "".
// 
// "FIRST_WEEK_DAY" :
//         premier jour de la semaine : 1 pour lundi, 2 pour mardi, etc..., 7 ou
//         0 pour dimanche.
//         Valeur par defaut : 1 (lundi).
// 
// "LANGUAGE_CODE" :
//         code ISO a 2 lettres de la langue d'affichage du calendrier.
//         Valeur par defaut : "fr" (francais).
// 
// "CLICKABLE_TITLE" :
//         lorsque DATE_URL est defini, dit si le titre du calendrier (i.e. le
//         mois + annee en haut du calendrier) est cliquable. Dans ce cas, la
//         date passee dans le parametre d'URL est au format indique par le
//         parametre URL_MONTH_DATE_FORMAT.
//         Valeur par defaut : TRUE (vrai).
// 
// "OUTPUT_MODE" :
//         si defini a "return", le code HTML du calendrier sera renvoye en tant
//         que valeur de retour de la fonction Calendar. Si defini a "echo", le
//         code HTML du calendrier sera directement renvoye dans la reponse au
//         navigateur. Utilisez "return" si vous voulez recuperer le code HTML
//         du calendrier dans une variable PHP et eventuellement faire des
//         traitements dessus.
//         Valeur par defaut : "echo".
// 
// "URL_DAY_DATE_FORMAT" :
//         lorsque DATE_URL est defini, indique le format de la date des jours
//         du calendrier passee dans l'URL. Ce format doit etre donne selon le
//         format supporte par la fonction PHP date. Sans effet si DATE_URL
//         n'est pas defini.
//         Valeur par defaut : "dmY" (jjmmaaaa).
// 
// "URL_MONTH_DATE_FORMAT" :
//         lorsque DATE_URL est defini, indique le format de la date du mois
//         passee dans l'URL (lien du titre du calendrier). Ce format doit etre
//         donne selon le format supporte par la fonction PHP date. Sans effet
//         si DATE_URL n'est pas defini.
//         Valeur par defaut : "mY" (mmaaaa).
// 
// "PRE_DATE_URL_FUNCTION":
//         ce parametre indique le nom d'une fonction utilisateur a appeler
//         avant que le calendrier ne soit genere et avant que la fonction
//         DATE_URL_FUNCTION soit appelee. La fonction est alors appelee avec
//         deux parametres :
//           - La premiere date visible du calendrier (format : jjmmaaaa)
//           - La derniere date visible du calendrier (format : jjmmaaaa)
//         Cette fonction peut etre utile pour definir la plage de dates qui
//         vont etre affichees dans le calendrier, c'est-a-dire la plage de
//         dates pour lesquelles la fonction DATE_URL_FUNCTION sera appelee.
//         Cela peut servir a optimiser l'execution de requetes SQL utilisees
//         dans DATE_URL_FUNCTION.
//         Valeur par defaut : "" (aucune fonction personnelle "pre URL de date"
//         sera utilisee).
// 
// "DATE_URL_FUNCTION":
//         indique le nom d'une fonction utilisateur a appeler pour recuperer
//         l'URL cible de la date passee en parametre (format : jjmmaaaa) : si ce
//         parametre est defini, chaque fois qu'une date sera affichee, la
//         fonction indiquee sera appelee avec la date du jour passee en
//         parametre. Si vous voulez que la date passee en parametre soit
//         cliquable, renvoyez simplement cette URL ; si vous ne voulez pas que
//         la date soit cliquable, renvoyez simplement NULL, FALSE ou "".
//         Si DATE_RUL est egalement defini, seul le parametre DATE_URL_FUNCTION
//         sera pris en compte.
//         Valeur par defaut : "" (aucune fonction personnelle "URL de date"
//         sera appelee).
// 
function Calendar($params) {
	// 
	// VARIABLES
	// 
	
	// Global variables 
	global $_SESSION;
	global $_SERVER;
	global $_GET;
	
	// Calendar parameters with default values
	$PREFIX                = "calendar_";
	$CSS_PREFIX            = "calendar_";
	$DATE_URL              = "";
	$URL_PARAMETER         = "date";
	$USE_SESSION           = FALSE;
	$PRESERVE_URL          = TRUE;
	$JS                    = FALSE;
	$JS_URL                = "";
	$FIRST_WEEK_DAY        = 1;
	$LANGUAGE_CODE         = "fr";
	$CLICKABLE_TITLE       = TRUE;
	$OUTPUT_MODE           = "echo";
	$URL_DAY_DATE_FORMAT   = "dmY";
	$URL_MONTH_DATE_FORMAT = "mY";
	$PRE_DATE_URL_FUNCTION = "";
	$DATE_URL_FUNCTION     = "";
	
	// Will contains the complete HTML code of the calendar in the case the
	// output mode is set to "return"
	$CALENDAR_RESPONSE = "";
	
	// Overwrite parameters with custom values
	extract($params);
	
	// Translations for month and day
	include("calendar_locales.php");
	// Month names
	if (isset($MONTHS[$LANGUAGE_CODE])) {
		$month_name = $MONTHS[$LANGUAGE_CODE];
	} else {
		$month_name = $MONTHS["fr"];
	}
	// Short names of days
	if (isset($WEEK_DAYS[$LANGUAGE_CODE])) {
		$day_name = $WEEK_DAYS[$LANGUAGE_CODE];
	} else {
		$day_name = $WEEK_DAYS["fr"];
	}
	// Current month's name
	if (isset($MONTH_HEADER[$LANGUAGE_CODE])) {
		$month_header = $MONTH_HEADER[$LANGUAGE_CODE];
	} else {
		$month_header = $MONTH_HEADER["fr"];
	}
	
	
	// 
	// FUNCTIONS
	// 
	
	// This function displays HTML code: if $JS = TRUE, we do not display line
	// breaks
	if (! function_exists("calendar_display")) {
		function calendar_display($text, $JS, &$CALENDAR_RESPONSE) {
			if ($JS) {
				// We escape all ' of the text
				$CALENDAR_RESPONSE .= "document.writeln('".str_replace("'", "\\'", $text)."');\n";
			} else {
				$CALENDAR_RESPONSE .= $text."\n";
			}
		}
	}
	
	// This function sets the calendar URL parameter $URL_PARAMETER to $date in
	// the given URL $URL. Used for the previous and next arrows of the calendar
	// title and the calendar dates when set as clickable with the parameter
	// DATE_URL.
	if (! function_exists("calendar_calculate_URL")) {
		function calendar_calculate_URL($URL, $URL_PARAMETER, $date, $PRESERVE_URL, $USE_SESSION) {
			if (strpos($URL, "__DATE__") !== FALSE) {
				return str_replace("__DATE__", $date, $URL);
			} else {
				$URL_components = parse_url($URL);
				$new_URL        = $URL_components["path"]."?";
				$add_SID        = $USE_SESSION;
				// Maybe $URL is an absolute URL so we must add the beginning of the URL
				if (isset($URL_components["scheme"])) {
					$new_URL = substr($URL, 0, strpos($URL, $URL_components["path"])).$new_URL;
				}
				// We retrieve and preserve the current URL parameters if required
				if ($PRESERVE_URL && isset($URL_components["query"])) {
					parse_str($URL_components["query"], $query_string);
					// We build the query string
					foreach ($query_string as $param => $value) {
						if ($param != $URL_PARAMETER) {
							$new_URL .= $param."=".urlencode($value)."&amp;";
						}
						// If the SID is already there, we do not add it again
						if ($USE_SESSION && $param == session_name()) {
							$add_SID = FALSE;
						}
					}
				}
				
				// We add the date
				$new_URL .= $URL_PARAMETER."=".$date;
				
				// We also add the session ID (SID) if necessary
				if ($add_SID && SID != "") {
					$new_URL .= "&amp;".SID;
				}
				
				return $new_URL;
			}
		}
	}
	
	// This function calculates the date of the previous month with the mmyyyy
	// format
	if (! function_exists("calendar_previous_month")) {
		function calendar_previous_month($month, $year) {
			if ($month == 1) {
				$new_month = "12";
				$new_year  = $year - 1;
			} else {
				$new_month = (($month > 10)?"":"0").($month - 1);
				$new_year  = $year;
			}
			
			return $new_month.$new_year;
		}
	}
	
	// This function calculates the date of the next month with the mmyyyy format
	if (! function_exists("calendar_next_month")) {
		function calendar_next_month($month, $year) {
			if ($month == 12) {
				$new_month = "01";
				$new_year  = $year + 1;
			} else {
				$new_month = (($month < 9)?"0":"").($month + 1);
				$new_year  = $year;
			}
			
			return $new_month.$new_year;
		}
	}
	
	// 
	// MAIN LOOP
	// 
	
	// In the case of JavaScript integration with session, we create the session.
	// We are allowed to do that because in JavaScript integration this PHP script
	// is not included in any custom page.
	if ($JS && $USE_SESSION) {
		session_start();
	}
	
	// Today's date
	$today = date("dmY");
	
	// Month and year to display (gotten from URL)
	if (isset($_GET[$PREFIX."date"])) {
		if ($_GET[$PREFIX."date"] != "") {
			$month = (int)substr($_GET[$PREFIX."date"], 0, 2);
			$year  = substr($_GET[$PREFIX."date"], 2);
		}
	}
	
	// Default month to show (if not found in the URL)
	if (!isset($month)) {
		$month = date("n");
		// In the case of session, we must get the session date
		if ($USE_SESSION && isset($_SESSION[$PREFIX."month"])) {
			$month = $_SESSION[$PREFIX."month"];
		}
	}
	// We put the month in the session if required
	if ($USE_SESSION) {
		$_SESSION[$PREFIX."month"] = $month;
	}
	
	// Default year to show (if not found in the URL)
	if (!isset($year)) {
		$year = date("Y");
		// In the case of session, we must get the session date
		if ($USE_SESSION && isset($_SESSION[$PREFIX."year"])) {
			$year = $_SESSION[$PREFIX."year"];
		}
	}
	// We put the year in the session if required
	if ($USE_SESSION) {
		$_SESSION[$PREFIX."year"] = $year;
	}
	
	// We calculate the first day of the month to show
	$first_month_day = gmmktime(0, 0, 0, $month, 1, $year);
	
	// We calculate the week day of this first day so that we can determine how
	// many days we are far from the first week day
	$offset = (7 - ($FIRST_WEEK_DAY % 7 - gmdate("w", $first_month_day))) % 7;
	
	// First day of the calendar
	$current_day = $first_month_day - 3600 * 24 * $offset;
	
	// How many rows in the calendar?
	$row_number = ceil((gmdate("t", $first_month_day) + $offset) / 7);
	
	// We call the pre date url function if any
	if (function_exists($PRE_DATE_URL_FUNCTION)) {
		call_user_func($PRE_DATE_URL_FUNCTION, gmdate("dmY", $current_day), gmdate("dmY", $current_day + (7 * $row_number - 1) * 3600 * 24));
	}
	
	// We display the top of the calendar
	if ($JS) {
		$URL_page = $JS_URL;
	} else {
		$URL_page = $_SERVER["REQUEST_URI"];
	}
	calendar_display("<table class=\"".$CSS_PREFIX."main\" summary=\"\">", $JS, $CALENDAR_RESPONSE);
	calendar_display("	<tr class=\"".$CSS_PREFIX."title\">", $JS, $CALENDAR_RESPONSE);
	calendar_display("		<td class=\"".$CSS_PREFIX."cell_title_left_arrow_clickable\"><a href=\""
	                 .calendar_calculate_URL($URL_page, $PREFIX."date", calendar_previous_month($month, $year), $PRESERVE_URL, $USE_SESSION)
					 ."\" class=\"".$CSS_PREFIX."title_left_arrow_clickable\">&lt;&lt;</a></td>", $JS, $CALENDAR_RESPONSE);
	if ($DATE_URL != "" && $CLICKABLE_TITLE) {
		calendar_display("		<td class=\"".$CSS_PREFIX."cell_title_month_clickable\"><a href=\""
		                 .calendar_calculate_URL($DATE_URL, $URL_PARAMETER, date($URL_MONTH_DATE_FORMAT, mktime(0, 0, 0, $month, 1, $year)), TRUE, $USE_SESSION)
						 ."\" class=\"".$CSS_PREFIX."title_month_clickable\">"
						 .str_replace("%y", $year, str_replace("%m", $month_name[$month - 1], $month_header))
						 ."</a></td>", $JS, $CALENDAR_RESPONSE);
	} else {
		calendar_display("		<td class=\"".$CSS_PREFIX."cell_title_month\">"
		                 .str_replace("%y", $year, str_replace("%m", $month_name[$month - 1], $month_header))
						 ."</td>", $JS, $CALENDAR_RESPONSE);
	}
	calendar_display("		<td class=\"".$CSS_PREFIX."cell_title_right_arrow_clickable\"><a href=\""
	                 .calendar_calculate_URL($URL_page, $PREFIX."date", calendar_next_month($month, $year), $PRESERVE_URL, $USE_SESSION)
					 ."\" class=\"".$CSS_PREFIX."title_right_arrow_clickable\">&gt;&gt;</a></td>", $JS, $CALENDAR_RESPONSE);
	calendar_display("	</tr>", $JS, $CALENDAR_RESPONSE);
	calendar_display("	<tr>", $JS, $CALENDAR_RESPONSE);
	calendar_display("		<td colspan=\"3\">", $JS, $CALENDAR_RESPONSE);
	calendar_display("			<table class=\"".$CSS_PREFIX."table\" summary=\"\">", $JS, $CALENDAR_RESPONSE);
	calendar_display("				<tr>", $JS, $CALENDAR_RESPONSE);
	for ($counter = 0; $counter < 7; $counter++) {
		calendar_display("					<th>".$day_name[($FIRST_WEEK_DAY + $counter) % 7]."</th>", $JS, $CALENDAR_RESPONSE);
	}
	calendar_display("				</tr>", $JS, $CALENDAR_RESPONSE);
	
	// We are going to display a table => 2 nested loops
	for ($row = 1; $row <= $row_number; $row++) {
		// The first loop displays the rows
		calendar_display("				<tr>", $JS, $CALENDAR_RESPONSE);
		
		// The second loop displays the days (as columns)
		for ($column = 1; $column <= 7; $column++) {
			// Day currently displayed
			$day = gmdate("j", $current_day);
			
			// Calculate day URL
			if (function_exists($DATE_URL_FUNCTION)) {
				$this_date_url = call_user_func($DATE_URL_FUNCTION, gmdate("dmY", $current_day));
				if ($this_date_url == NULL || $this_date_url == FALSE) {
					$this_date_url = "";
				}
			} elseif ($DATE_URL != "") {
				$this_date_url = calendar_calculate_URL($DATE_URL, $URL_PARAMETER, gmdate($URL_DAY_DATE_FORMAT, $current_day), TRUE, $USE_SESSION);
			} else {
				$this_date_url = "";
			}

			// If it is saturday or sunday, we use the "weekend" style
			$cell_class = array();
			$day_class  = array();
			if (gmdate("w", $current_day) == 6 || gmdate("w", $current_day) == 0) {
				$cell_class[] = "weekend";
				$day_class[]  = "weekend";
			}
			if (gmdate("dmY", $current_day) == $today) {
				$cell_class[] = "today";
				$day_class[]  = "today";
			} else {
				// Days not in the current month with CSS class "other_month"
				if (gmdate("n", $current_day) != $month) {
					$cell_class[] = "other_month";
					$day_class[]  = "other_month";
				} else {
					$cell_class[] = "day";
					$day_class[]  = "day";
				}
			}
			if ($this_date_url != "") {
				$cell_class[] = "clickable";
				$day_class[]  = "clickable";
			}

			// Final content
			if (count($cell_class) > 0) {
				$table_cell = "					<td class=\"".$CSS_PREFIX."cell_".implode("_", $cell_class)."\">";
			} else {
				$table_cell = "					<td>";
			}
			if ($this_date_url != "") {
				if (count($day_class) > 0) {
					$table_cell .= "<a href=\"".$this_date_url."\" class=\"".$CSS_PREFIX.implode("_", $day_class)."\">".$day."</a>";
				} else {
					$table_cell .= "<a href=\"".$this_date_url."\">".$day."</a>";
				}
			} else {
				if (count($day_class) > 0) {
					$table_cell .= "<span class=\"".$CSS_PREFIX.implode("_", $day_class)."\">".$day."</span>";
				} else {
					$table_cell .= $day;
				}
			}
			calendar_display($table_cell."</td>", $JS, $CALENDAR_RESPONSE);
			
			// Next day
			$current_day += 3600 * 24 + 1;
		}
		
		// End of rows
		calendar_display("				</tr>", $JS, $CALENDAR_RESPONSE);
	}
	
	calendar_display("			</table>", $JS, $CALENDAR_RESPONSE);
	calendar_display("		</td>", $JS, $CALENDAR_RESPONSE);
	calendar_display("	</tr>", $JS, $CALENDAR_RESPONSE);
	
	// Display a link to the current date at the bottom of the calendar
	calendar_display("	<tr class=\"".$CSS_PREFIX."footer\">", $JS, $CALENDAR_RESPONSE);
	// We change the CSS class according to the month being displayed
	if ($month.$year == date("nY")) {
		calendar_display("		<td colspan=\"3\" class=\"".$CSS_PREFIX."cell_footer_current_month_clickable\"><a href=\""
		                 .calendar_calculate_URL($URL_page, $PREFIX."date", date("mY"), $PRESERVE_URL, $USE_SESSION)
						 ."\" class=\"".$CSS_PREFIX."footer_current_month_clickable\">".$CALLBACK[$LANGUAGE_CODE]
						 ."</a></td>", $JS, $CALENDAR_RESPONSE);
	} else {
		calendar_display("		<td colspan=\"3\" class=\"".$CSS_PREFIX."cell_footer_other_month_clickable\"><a href=\""
		                 .calendar_calculate_URL($URL_page, $PREFIX."date", date("mY"), $PRESERVE_URL, $USE_SESSION)
						 ."\" class=\"".$CSS_PREFIX."footer_other_month_clickable\">".$CALLBACK[$LANGUAGE_CODE]
						 ."</a></td>", $JS, $CALENDAR_RESPONSE);
	}
	calendar_display("	</tr>", $JS, $CALENDAR_RESPONSE);
	
	calendar_display("</table>", $JS, $CALENDAR_RESPONSE);
	
	// Return the HTML code?
	if ($OUTPUT_MODE == "return") {
		return $CALENDAR_RESPONSE;
	} else {
		echo $CALENDAR_RESPONSE;
	}
}
?>
