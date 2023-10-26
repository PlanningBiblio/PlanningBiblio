<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/setup/createdb.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Créer la base de données. Vérifie si la base et l'utilisateur MySQL existent. Les supprimes si demandé. Créé l'utilisateur et
la base.
Inclus le fichiers setup/db_structure.php et setup/db_data.php afin de créer les tables et les remplir.
Inclus ensuite le fichier setup/createconfig.php si la base a été créée correctement

Ce fichier valide le formulaire de la page setup/index.php
*/

require_once(__DIR__ . '/../../init/init_ajax.php');

//	Variables
$dbhost = $request->get('dbhost');
$dbport = $request->get('dbport');
$dbname = $request->get('dbname');
$dbAdminUser = $request->get('adminuser');
$dbAdminPass = $request->get('adminpass');
$dbuser = $request->get('dbuser');
$dbpass = $request->get('dbpass');
$dbprefix = $request->get('dbprefix');
$dropUser = $request->get('dropuser');
$dropDB = $request->get('dropdb');

$dbport = filter_var($dbport, FILTER_SANITIZE_NUMBER_INT);

$sql=array();
$erreur=false;
$message="<p style='color:red'>Il y a eu des erreurs pendant la création de la base de données.<br/></p>\n";

//	Entête
include "header.php";

// Initialisation de la connexion MySQL
$dblink=mysqli_init();

//	Vérifions si l'utilisateur existe
$user_exists=false;
$req="SELECT * FROM `mysql`.`user` WHERE `User`='$dbuser' AND `Host`='$dbhost';";
$dbconn=mysqli_real_connect($dblink, $dbhost, $dbAdminUser, $dbAdminPass, 'mysql');
$dblink->set_charset("utf8mb4");

$dbname=mysqli_real_escape_string($dblink, $dbname);
$dbuser=mysqli_real_escape_string($dblink, $dbuser);
$dbpass=mysqli_real_escape_string($dblink, $dbpass);

$query=mysqli_query($dblink, $req);
if (mysqli_fetch_array($query)) {
    $user_exists=true;
}

//	Suppression de l'utilisateur si demandé
if ($dropUser) {
    if ($user_exists) {
        $sql[]="DROP USER '$dbuser'@'$dbhost';";
        $user_exists=false;
    }
}
//	Suppression de la base si demandé
if ($dropDB) {
    $sql[]="DROP DATABASE IF EXISTS `$dbname` ;";
}

//	Création de l'utilisateur
if (!$user_exists) {
    $sql[]="CREATE USER '$dbuser'@'$dbhost' IDENTIFIED BY '$dbpass';";
}
$sql[]="GRANT USAGE ON `$dbname` . * TO '$dbuser'@'$dbhost' IDENTIFIED BY '$dbpass' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";

//	Création de la base
$sql[]="CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
$sql[]="GRANT ALL PRIVILEGES ON `$dbname` . * TO '$dbuser'@'$dbhost';";

$sql[]="USE $dbname;";

//	Création des tables
include "db_structure.php";

//	Insertion des données
include "db_data.php";


if ($dbconn) {
    foreach ($sql as $elem) {
        $message.=str_replace("\n", "<br/>", $elem)."<br/>";
        if (trim($elem)) {
            if (!mysqli_multi_query($dblink, $elem)) {
                $erreur=true;
                $message.="<p style='color:red'>ERROR : ";
                $message.=mysqli_error($dblink);
                $message.="</p>\n";
            }
        }
    }
    mysqli_close($dblink);
} else {
    $erreur=true;
    $message.="<p style='color:red'>ERROR : Impossible de se connecter au serveur MySQL</p>\n";
}

$message.="<p><a href='index.php'>Retour</a></p>\n";

$path = substr(__DIR__, 0, -12);

if ($erreur) {
    echo $message;
} else {
    echo "<p>La base de donnée a bien été créée.</p>\n";

    if (!file_exists(__DIR__ . '/../../.env.local')) {
        echo "<p style='color:red;'>Le fichier de configuration est manquant.<br/><br/>\n";
        echo "Veuillez créer le fichier <b>.env.local</b> dans le dossier <b>$path</b> avec les informations suivantes.<br/><br/>\n";
        echo "<p style='text-align:left;margin-bottom: 30px;'>\n";
        foreach ($_SESSION['env_local_data'] as $line) {
            $line = str_replace("\n", "<br/>", $line);
            echo $line . "<br/>\n";
        }
        echo "</p>\n";
    }


    include "config.php";
}

include "footer.php";
