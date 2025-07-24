<?php
/**
Le script public/conges/cron.ctrlConges.php est déprécié.
Il sera supprimé à partir de la version 25.10
Utilisez bin/console app:holiday:reminder
*/

session_start();

/** $version=$argv[0]; permet d'interdire l'execution de ce script via un navigateur
 *  Le fichier config.php affichera une page "accès interdit si la $version n'existe pas
 *  $version prend la valeur de $argv[0] qui ne peut être fournie que en CLI ($argv[0] = chemin du script appelé en CLI)
 */

// $version=$argv[0]; = sécurité : autorise l'execution du script en CLI, l'interdit en HTTP
$version=$argv[0];

require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/function.php';

$CSRFToken = CSRFToken();

$message = 'Le script public/conges/cron.ctrlConges.php est déprécié. Utilisez bin/console app:holiday:reminder';
echo $message . "\n";
logs($message, 'Rappels-conges', $CSRFToken);
exec(__DIR__ . '/../../bin/console app:holiday:reminder');
