<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <link rel="stylesheet" href="calendar.css" type="text/css" />
 <title>D&eacute;monstration du calendrier</title>
</head>

<body>
<p>Note&nbsp;: la conservation des URL a &eacute;t&eacute; d&eacute;sactiv&eacute;e. Les dates sont cliquables.</p>

<?php require_once("calendar.php"); ?>

<h1>Calendrier sans session</h1>
<?php Calendar(array("PREFIX" => "cal1_", "PRESERVE_URL" => false, "DATE_URL" => "/target/target.php")); ?>

<h1>Calendrier avec session</h1>
<?php Calendar(array("PREFIX" => "cal2_", "PRESERVE_URL" => false, "USE_SESSION" => true, "DATE_URL" => "target/target.php")); ?>

<h1>Calendrier en JavaScript (sans session)</h1>
<script type="text/javascript" src="calendar_js.php?PREFIX=cal3_&amp;PRESERVE_URL=false&amp;DATE_URL=target%2ftarget.php"></script>

<h1>Calendrier en tant que valeur de retour</h1>
<?php
$html_code = Calendar(array("PREFIX" => "cal4_", "PRESERVE_URL" => false, "OUTPUT_MODE" => "return", "DATE_URL" => "target/target.php"));
// Commenter la ligne suivante fait "disparaitre" le calendrier
echo $html_code;
?>

<h1>Calendrier avec fonction personnelle d'"URL de date"</h1>
<p>La fonction personnelle regarde dans le r&eacute;pertoire "target" s'il existe un
fichier dont le nom est le jour du mois (avec 0 frontal). Si un tel fichier existe,
la date est cliquable et le lien pointe vers ce fichier.</p>
<?php
function date_url($date) {
	$filename = "target/".substr($date, 0, 2).".html";
	if (file_exists($filename)) {
		return $filename;
	} else {
		return "";
	}
}

Calendar(array("LANGUAGE_CODE" => "fr", "PREFIX" => "cal5_", "PRESERVE_URL" => false, "DATE_URL_FUNCTION" => "date_url"));
?>

<h1>Calendrier avec appel &agrave; une fonction JavaScript</h1>
<script type="text/javascript">
<!--
function mafonction(date) {
	alert("Vous avez clique sur la date " + date);
}
// -->
</script>
<?php Calendar(array("PREFIX" => "cal6_", "PRESERVE_URL" => false, "USE_SESSION" => false, "DATE_URL" => "javascript:mafonction('__DATE__');")); ?>

</body>
</html>
