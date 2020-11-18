<?php


// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once(__DIR__.'/../include/accessDenied.php');
    exit;
}

$CSRFToken = CSRFToken();

$cli = isset($argv[0]);

echo "Le site est actuellement en maintenance.";
if (! $cli) {
    echo "<br/>\n";
}

if (version_compare($config['Version'], "2.0") === -1) {
    echo "<br/>Vous devez d'abord installer la version 2.0<br/>\n";
    exit;
}

if (!$cli) {
    echo "<br/><br/><a href='index.php'>Continuer</a>\n";
    include(__DIR__.'/../include/footer.php');
}
