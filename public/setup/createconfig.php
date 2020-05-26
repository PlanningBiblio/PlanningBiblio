<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/setup/createconfig.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de créer le fichier de configuration (include/config.php) lors de l'installation.
Récupère les informations saisies dans le formulaire de la page setup/index.php (identifiant administrateur MySQL,
nom de la base de données à créer, identifiant de l'utilisateur de la base de données à créer
*/

session_start();

$env_file = __DIR__ . '/../../.env';
$env_local_file = __DIR__ . '/../../.env.local';

if (file_exists(__DIR__ . '/../../.env.local')) {
    header('Location: index.php');
}

$dbhost = filter_input(INPUT_POST, 'dbhost', FILTER_SANITIZE_STRING);
$dbport = filter_input(INPUT_POST, 'dbport', FILTER_SANITIZE_NUMBER_INT);
$dbname = filter_input(INPUT_POST, 'dbname', FILTER_SANITIZE_STRING);
$adminuser = filter_input(INPUT_POST, 'adminuser', FILTER_SANITIZE_STRING);
$adminpass = filter_input(INPUT_POST, 'adminpass', FILTER_UNSAFE_RAW);
$dbuser = filter_input(INPUT_POST, 'dbuser', FILTER_SANITIZE_STRING);
$dbpass = filter_input(INPUT_POST, 'dbpass', FILTER_UNSAFE_RAW);
$dbprefix = filter_input(INPUT_POST, 'dbprefix', FILTER_SANITIZE_STRING);
$dropuser = filter_input(INPUT_POST, 'dropuser', FILTER_SANITIZE_STRING);
$dropdb = filter_input(INPUT_POST, 'dropdb', FILTER_SANITIZE_STRING);

$app_secret = isset($_SESSION['app_secret']) ? $_SESSION['app_secret'] : bin2hex(random_bytes(16));
$_SESSION['app_secret'] = $app_secret;

$env_local_data = array();

foreach (file($env_file) as $line) {
    if (substr($line, 0, 1) == '#') {
        continue;
    }
    /** Set APP_ENV to prod generate errors on symfony pages
    // TODO FIXIT
    if (substr($line, 0, 7) == 'APP_ENV') {
        $line = "APP_ENV=prod";
    }
    */
    if (substr($line, 0, 12) == 'DATABASE_URL') {
        $line = "DATABASE_URL=mysql://$dbuser:$dbpass@$dbhost:$dbport/$dbname";
    }
    elseif (substr($line, 0, 15) == 'DATABASE_PREFIX') {
        $line = "DATABASE_PREFIX=$dbprefix";
    }
    elseif (substr($line, 0, 10) == 'APP_SECRET') {
        $line = "APP_DEBUG=0\nAPP_SECRET=$app_secret";
    }

    $env_local_data[] = $line;
}

$_SESSION['env_local_data'] = $env_local_data;

$path = substr(__DIR__, 0, -12);

include "header.php";
echo "<form name='form' action='createdb.php' method='post'>\n";
echo "<input type='hidden' name='dbhost' value='$dbhost' />\n";
echo "<input type='hidden' name='dbport' value='$dbport' />\n";
echo "<input type='hidden' name='dbuser' value='$dbuser' />\n";
echo "<input type='hidden' name='dbpass' value='$dbpass' />\n";
echo "<input type='hidden' name='adminuser' value='$adminuser' />\n";
echo "<input type='hidden' name='adminpass' value='$adminpass' />\n";
echo "<input type='hidden' name='dbname' value='$dbname' />\n";
echo "<input type='hidden' name='dbprefix' value='$dbprefix' />\n";
echo "<input type='hidden' name='dropuser' value='$dropuser' />\n";
echo "<input type='hidden' name='dropdb' value='$dropdb' />\n";
echo "</form>\n";

if ($file = fopen($env_local_file, "w\n")) {

    foreach ($env_local_data as $line) {
        fputs($file, $line."\n");
    }
    fclose($file);

    echo "<p>Le fichier de configuration a bien été créé.</p>\n";
    echo "<p>Cliquez sur <a href='javascript:document.form.submit();'>continuer</a>.</p>\n";

} else {

    echo "<p style='color:red;'>Impossible de créer le fichier .env.local<br/><br/>\n";
    echo "Veuillez créer manuellement le fichier <b>.env.local</b> dans le dossier <b>$path</b> avec les informations suivantes.<br/><br/>\n";
    echo "<p style='text-align:left; color:black;'>\n";
    foreach ($env_local_data as $line) {
        $line = str_replace("\n", "<br/>", $line);
        echo $line . "<br/>\n";
    }
    echo "</p>\n";
    echo "Cliquez ensuite sur <a href='javascript:document.form.submit();' class='ui-button'>Continuer</a></p>\n";
}

include "footer.php";
