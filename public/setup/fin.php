<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/setup/fin.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Dernière page d'installation. Reçoit les informations du formualire de la page setup/config.php (informations sur le
responsable du planning). Insère l'utilisateur dans la base de données (table personnel et config pour le nom et l'email
du responsable.
Affiche le message "configuration terminée" et invite l'utilisateur à se connecter au planning
*/

session_start();

$version="setup";
$path = substr(__DIR__, 0, -12);

include "header.php";

if (!file_exists(__DIR__ . '/../../.env.local')) {
    echo "<p style='color:red;'>Le fichier de configuration est manquant.<br/><br/>\n";
    echo "Veuillez créer le fichier <b>.env.local</b> dans le dossier <b>$path</b> avec les informations suivantes.<br/><br/>\n";
    echo "<p style='text-align:left;margin-bottom: 30px;'>\n";
    foreach ($_SESSION['env_local_data'] as $line) {
        $line = str_replace("\n", "<br/>", $line);
        echo $line . "<br/>\n";
    }
    echo "</p>\n";
    echo "<p>Cliquez ensuite sur <a href='javascript:location.reload();' class='ui-button'>Recharger</a>\n";
    include "footer.php";
    exit;
}

include "../include/config.php";

$CSRFToken = filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
$nom=filter_input(INPUT_POST, "nom", FILTER_SANITIZE_STRING);
$prenom=filter_input(INPUT_POST, "prenom", FILTER_SANITIZE_STRING);
$password=filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
$password2=filter_input(INPUT_POST, "password2", FILTER_UNSAFE_RAW);
$email=filter_input(INPUT_POST, "email", FILTER_UNSAFE_RAW);
$erreur=false;

if (strlen($password)<6) {
    echo "<p style='color:red'>Le mot de passe doit comporter au moins 6 caractères.<br/>\n";
    echo "<a href='javascript:history.back();'>Retour</a></p>\n";
    include "footer.php";
    exit;
}

if ($password!=$password2) {
    echo "<p style='color:red'>Les mots de passe ne correspondent pas.<br/>\n";
    echo "<a href='javascript:history.back();'>Retour</a></p>\n";
    include "footer.php";
    exit;
}

$password =  password_hash($password, PASSWORD_BCRYPT);

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->update("personnel", array("nom"=>$nom, "prenom"=>$prenom, "password"=>$password, "mail"=>$email), array("id"=>"1"));
if ($db->error) {
    $erreur=true;
}

if ($erreur) {
    echo "<p style='color:red'>Il y a eu des erreurs.</p>\n";
    echo "<a href='javascript:history.back();'>Retour</a>\n";
} else {
    echo "<h3>L'installation est terminée.</h3>\n";
    echo "Veuillez verifier l'installation.<br/>Si tout fonctionne, supprimez le dossier \"setup\".<br/>\n";
    // FIXME At this point, $config['URL'] is not set properly.
    echo "<p><a href='{$config['URL']}/login?newlogin=admin' class='ui-button'>Se connecter au planning</a><br/><br/></p>\n";
}
include "footer.php";
