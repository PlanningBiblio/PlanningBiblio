<?php

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once(__DIR__.'/../include/accessDenied.php');
    exit;
}

$CSRFToken = CSRFToken();

$cli = isset($argv[0]);

echo "<div>Le site est en cours de maintenance</div>";
if (! $cli) {
    echo "<br/>\n";
}

if (!$cli) {
    include(__DIR__.'/../include/footer.php');
}

