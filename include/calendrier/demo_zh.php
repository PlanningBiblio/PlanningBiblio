<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <link rel="stylesheet" href="calendar.css" type="text/css" />
 <title>Calendar demo</title>
</head>

<body>
<p>Note: URL preservation has been disabled. Dates are clickable.</p>

<?php require_once("calendar.php"); ?>

<h1>Calendar without session</h1>
<?php Calendar(array("LANGUAGE_CODE" => "zh", "PREFIX" => "cal1_", "PRESERVE_URL" => false, "DATE_URL" => "target/target.php")); ?>

<h1>Calendar with session</h1>
<?php Calendar(array("LANGUAGE_CODE" => "zh", "PREFIX" => "cal2_", "PRESERVE_URL" => false, "USE_SESSION" => true, "DATE_URL" => "target/target.php")); ?>

<h1>JavaScript calendar (without session)</h1>
<script type="text/javascript" src="calendar_js.php?LANGUAGE_CODE=zh&amp;PREFIX=cal3_&amp;PRESERVE_URL=false&amp;DATE_URL=target%2ftarget.php"></script>

<h1>Calendar as return value</h1>
<?php
$html_code = Calendar(array("LANGUAGE_CODE" => "zh", "PREFIX" => "cal4_", "PRESERVE_URL" => false, "OUTPUT_MODE" => "return", "DATE_URL" => "target/target.php"));
// Commenting out the next line makes the calendar not being rendered
echo $html_code;
?>

<h1>Calendar with a custom "date URL" function</h1>
<p>The custom function looks in the directory "target" to see if there are files
whose names is the day of the month (with leading 0). If so, the date is clickable
and points to this file.</p>
<?php
function date_url($date) {
	$filename = "target/".substr($date, 0, 2).".html";
	if (file_exists($filename)) {
		return $filename;
	} else {
		return "";
	}
}

Calendar(array("LANGUAGE_CODE" => "zh", "PREFIX" => "cal5_", "PRESERVE_URL" => false, "DATE_URL_FUNCTION" => "date_url"));
?>

<h1>Calendar with a JavaScript call</h1>
<script type="text/javascript">
<!--
function myfunction(date) {
	alert("You clicked date " + date);
}
// -->
</script>
<?php Calendar(array("LANGUAGE_CODE" => "zh", "PREFIX" => "cal6_", "PRESERVE_URL" => false, "USE_SESSION" => false, "DATE_URL" => "javascript:myfunction('__DATE__');")); ?>

</body>
</html>
