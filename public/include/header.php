<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/include/header.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affcihe l'entête HTML
Page notamment appelée par les fichiers index.php, admin/index.php
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once "accessDenied.php";
    exit;
}

$theme=$config['Affichage-theme']?$config['Affichage-theme']:"default";
$themeJQuery=$config['Affichage-theme']?$config['Affichage-theme']:"default";
if (!file_exists("themes/$theme/jquery-ui.min.css")) {
    $themeJQuery="default";
}
if (!file_exists("themes/$theme/$theme.css")) {
    $theme="default";
}

$favicon = null;
if (!file_exists("themes/$theme/favicon.png")) {
    $favicon = "<link rel='icon' type='image/svg' href='themes/$theme/images/favicon.ico' />\n";
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Planno</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
<script type='text/JavaScript' src='js/jquery-1.11.1.min.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/jquery.timepicker.min.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/plb/planno-timepicker.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/jquery-ui-1.11.2/jquery-ui.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/carhartl-jquery-cookie-3caf209/jquery.cookie.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/DataTables/datatables.min.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/CJScript.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/datePickerFr.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/dataTables.sort.js?version=<?php echo $version; ?>'></script>
<script type='text/JavaScript' src='js/script.js?version=<?php echo $version; ?>'></script>
<?php
getJSFiles($page, $version);
?>

<link rel='StyleSheet' href='js/DataTables/datatables.min.css?version=<?php echo $version; ?>' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/<?php echo $themeJQuery; ?>/jquery-ui.min.css?version=<?php echo $version; ?>' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/default/jquery.timepicker.min.css?version=<?php echo $version; ?>' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/default/default.css?version=<?php echo $version; ?>' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/default/print.css?version=<?php echo $version; ?>' type='text/css' media='print'/>
<?php
if ($theme!="default") {
    echo "<link rel='StyleSheet' href='themes/{$theme}/{$theme}.css?version=$version' type='text/css' media='all'/>\n";
}
echo $favicon;
?>
</head>

<body>

<?php
// Affichage des messages d'erreur ou de confirmation venant de la page précedente
$msg=filter_input(INPUT_GET, "msg", FILTER_SANITIZE_STRING);
$msgType=filter_input(INPUT_GET, "msgType", FILTER_SANITIZE_STRING);
if ($msg) {
    echo "<script type='text/JavaScript'>CJInfo('$msg','$msgType');</script>\n";
}

$msg2=filter_input(INPUT_GET, "msg2", FILTER_SANITIZE_STRING);
$msg2Type=filter_input(INPUT_GET, "msg2Type", FILTER_SANITIZE_STRING);
if ($msg2) {
    echo "<script type='text/JavaScript'>CJInfo('$msg2','$msg2Type',82,15000);</script>\n";
}
?>

<div id='opac' style='display:none'></div>
<div style='position:relative;top:30px;' class='noprint'>
</div>
