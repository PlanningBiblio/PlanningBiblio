<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2020 Jérôme Combes

@file hamac/cron.hamac.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Importe les absences depuis Hamac

@note : Modifiez le crontab de l'utilisateur Apache (ex: #crontab -eu www-data) en ajoutant les 2 lignes suivantes :
# Planning Biblio : Importation des absences Hamac toutes les 15 minutes
* /15 * /usr/bin/php5 -f /var/www/html/planning/ics/cron.ics.php
Pour la ligne précédente, ne mettez pas d'espace entre l'étoile et le /15
Remplacer si besoin le chemin d'accès au programme php et le chemin d'accès à ce fichier
@note : Modifiez la variable $path suivante en renseignant le chemin absolu vers votre dossier planningBiblio
*/

$path="/var/www/html/planning";

/**
 * @note Default configuration for Hamac
 */
$status_extra = array();
$status_waiting = array(3);
$status_validated = array(2,5);

session_start();

/** $version=$argv[0]; permet d'interdire l'execution de ce script via un navigateur
 *  Le fichier config.php affichera une page "accès interdit si la $version n'existe pas
 *  $version prend la valeur de $argv[0] qui ne peut être fournie que en CLI ($argv[0] = chemin du script appelé en CLI)
 */
$version=$argv[0];

// chdir($path) : important pour l'execution via le cron
chdir($path);

require_once "$path/include/config.php";
require_once "$path/personnel/class.personnel.php";

$CSRFToken = CSRFToken();

logs("Début d'importation Hamac", "Hamac", $CSRFToken);

// Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
$tmp_dir=sys_get_temp_dir();
$lockFile=$tmp_dir."/planningBiblioHamac.lock";

if (file_exists($lockFile)) {
    logs("Fichier " . $lockFile . " existe", "Hamac", $CSRFToken);
    $fileTime = filemtime($lockFile);
    $time = time();
    // Si le fichier existe et date de plus de 10 minutes, on le supprime et on continue.
    if ($time - $fileTime > 600) {
        logs("Fichier " . $lockFile . " date de plus de 10 minutes, on le supprime et on continue", "Hamac", $CSRFToken);
        unlink($lockFile);
    // Si le fichier existe et date de moins de 10 minutes, on quitte
    } else {
        logs("Fichier " . $lockFile . " date de moins de 10 minutes, on quitte, arrêt du traitement", "Hamac", $CSRFToken);
        exit;
    }
} else {
    logs("Fichier " . $lockFile . " n'existe pas", "Hamac", $CSRFToken);
}
// On créé le fichier .lock
$inF=fopen($lockFile, "w");
fclose($inF);
logs("Fichier " . $lockFile . " créé", "Hamac", $CSRFToken);

// On recherche tout le personnel actif
$p= new personnel();
logs("On recherche tout le personnel actif", "Hamac", $CSRFToken);
$p->supprime = array(0);
$p->fetch();
$agents = $p->elements;

// Les logins des agents qui acceptent la synchronisation depuis Hamac
$logins = array();
$key = $config['Hamac-id'];
logs("\$key = \$config['Hamac-id'] = " . $config['Hamac-id'], "Hamac", $CSRFToken);


foreach ($agents as $elem) {
    logs("mail = " . $elem['mail'] . " - login = " . $elem[$key], "Hamac", $CSRFToken);
    if ($elem['check_hamac']) {
        logs("\$elem['check_hamac'] = true", "Hamac", $CSRFToken);
        $logins[] = $elem[$key];
        $perso_ids[$elem[$key]] = $elem['id'];
        logs("\$elem['id'] = " . $elem['id'] . " - \$perso_ids[\$elem[\$key]] = " . $perso_ids[$elem[$key]], "Hamac", $CSRFToken);
    } else {
        logs("\$elem['check_hamac'] = false", "Hamac", $CSRFToken);
    }
}

$ids_list = implode(',', $perso_ids);

logs("\$ids_list = " . $ids_list, "Hamac", $CSRFToken);

// Recherche de toutes les absences déjà importées depuis Hamac
$absences = array();
logs("Recherche de toutes les absences déjà importées depuis Hamac", "Hamac", $CSRFToken);
$db = new db();
$db->select2('absences', null, array('cal_name' => 'hamac', 'perso_id' => "IN$ids_list"));
if ($db->result) {
    foreach ($db->result as $elem) {
        // On indexe le tableau avec le champ UID qui n'est autre que l'id Hamac
        $absences[$elem['uid']] = $elem;
        logs("\$elem['uid'] = " . $elem['uid'] . " - \$absences[\$elem['uid']] = " . json_encode($absences[$elem['uid']]), "Hamac", $CSRFToken);
    }
}

// On récupère les clés (IDs Hamac) pour vérifier si les absences du fichier Hamac sont dans la base de données
$uids = array_keys($absences);


// On lit le fichier CSV
$filename = trim($config['Hamac-csv']);

// Si le fichier n'existe pas, on quitte
if (!file_exists($filename)) {
    logs("Le fichier $filename n'existe pas, on quitte, arret du traitement", "Hamac", $CSRFToken);
    // Unlock
    unlink($lockFile);

    exit;
}

// Status à importer
$status = explode(',', $config['Hamac-status']);
$status = array_merge($status, $status_extra);

logs("Status à importer : \$config['Hamac-status'] " . $config['Hamac-status'], "Hamac", $CSRFToken);


// Préparation des requêtes d'insertion, de mise à jour et de suppression
// $dbi : DB Insert
$dbi = new dbh();
$dbi->CSRFToken = $CSRFToken;
$dbi->prepare("INSERT INTO `{$dbprefix}absences` (`perso_id`, `debut`, `fin`, `motif`, `commentaires`, `demande`, `valide`, `validation`, `valide_n1`, `validation_n1`, `cal_name`, `ical_key`, `uid`) 
  VALUES (:perso_id, :debut, :fin, :motif, :commentaires, :demande, :valide, :validation, :valide_n1, :validation_n1, :cal_name, :ical_key, :uid);");

// $dbu : DB Update
$dbu = new dbh();
$dbu->CSRFToken = $CSRFToken;
$dbu->prepare("UPDATE `{$dbprefix}absences` SET `perso_id` = :perso_id, `debut` = :debut, `fin` = :fin, `commentaires` = :commentaires, `valide` = :valide, `validation` = :validation, `valide_n1` = :valide_n1, `validation_n1` = :validation_n1 WHERE `id` = :id;");

// $dbd : DB Delete
$dbd = new dbh();
$dbd->CSRFToken = $CSRFToken;
$dbd->prepare("DELETE FROM `{$dbprefix}absences` WHERE `id` = :id;");


// On lit le fichier CSV
$inF = fopen($filename, 'r');

logs("On lit le fichier CSV " . $filename, "Hamac", $CSRFToken);

while ($tab = fgetcsv($inF, 1024, ';')) {
    $uid = $tab[0];

    logs("uid = " . $uid, "Hamac", $CSRFToken);

    // Si les logins du fichier Hamac ne sont pas dans le tableau $logins, on passe.
    // Le tableau $logins ne contient que les agents actifs qui acceptent la synchronisation Hamac
    if (!in_array($tab[4], $logins)) {
        logs("\$tab[4] = " . $tab[4] . " ne fait pas partie des logins des agents actifs qui acceptent la synchronisation Hamac, on passe à la ligne suivante dans le CSV", "Hamac", $CSRFToken);
        continue;
    }

    // Si l'absence a été supprimée, on la supprime de la base (status 9)
    // Important : Faire la suppression avant le contrôle des status car le status 9 sera ignoré à la prochaine étape
    if ($tab[6] == 9 and in_array($uid, $uids)) {
        logs("Status = 9, absence supprimée, on passe à la ligne suivante dans le CSV", "Hamac", $CSRFToken);
        $delete = array(':id' => $absences[$uid]['id']);

        $dbd->execute($delete);

        continue;
    }

    // Si le status de l'absence Hamac n'est pas dans la liste des status à importer, on passe.
    if (!in_array($tab[6], $status)) {
        logs("\$status = " . $tab[6] . " n'est pas dans la liste des status à importer (" . $config['Hamac-status'] . "), on passe à la ligne suivante dans le CSV", "Hamac", $CSRFToken);
        continue;
    }

    // Préparation des données
    logs("Préparation des données", "Hamac", $CSRFToken);
    $perso_id = $perso_ids[$tab[4]];
    logs("perso_id = " . $perso_id, "Hamac", $CSRFToken);
    $demande = date('Y-m-d H:i:s');
    logs("demande = " . $demande, "Hamac", $CSRFToken);
    $debut = preg_replace('/(\d+)\/(\d+)\/(\d+) (\d+:\d+:\d+)/', "$3-$2-$1 $4", $tab[2]);
    logs("debut = " . $debut, "Hamac", $CSRFToken);
    $fin = preg_replace('/(\d+)\/(\d+)\/(\d+) (\d+:\d+:\d+)/', "$3-$2-$1 $4", $tab[3]);
    logs("fin = " . $fin, "Hamac", $CSRFToken);
    $motif = !empty(trim($config['Hamac-motif'])) ? trim($config['Hamac-motif']) : 'Hamac';
    logs("motif = " . $motif, "Hamac", $CSRFToken);
    $commentaires = $tab[1];
    logs("commentaires = " . $commentaires, "Hamac", $CSRFToken);

    // Validations
    // Si le status de l'absence Hamac est 2, l'absence est validée
    if ( in_array($tab[6], $status_validated)) {
        logs("Si le status de l'absence Hamac est 2, l'absence est validée au niveau 2", "Hamac", $CSRFToken);
        $valide_n1 = 99999;
        $validation_n1 = date('Y-m-d H:i:s');
        $valide_n2 = 99999;
        $validation_n2 = date('Y-m-d H:i:s');
    } elseif ( in_array($tab[6], $status_waiting)) {
        logs("Si le status de l'absence Hamac est 1, l'absence est validée au niveau 1", "Hamac", $CSRFToken);
        $valide_n1 = 99999;
        $validation_n1 = date('Y-m-d H:i:s');
        $valide_n2 = 0;
        $validation_n2 = '0000-00-00 00:00:00';
    } else {
        logs("L'absence n'est pas validée", "Hamac", $CSRFToken);
        $valide_n1 = 0;
        $validation_n1 = '0000-00-00 00:00:00';
        $valide_n2 = 0;
        $validation_n2 = '0000-00-00 00:00:00';
    }


    // Si l'absence n'est pas dans la base de données, on l'importe.
    if (!in_array($uid, $uids)) {
        logs("Si l'absence n'est pas dans la base de données, on l'importe", "Hamac", $CSRFToken);
        $insert = array(':perso_id' => $perso_id, ':debut' => $debut, ':fin' => $fin, ':motif' => $motif, ':commentaires' => $commentaires, ':demande' => $demande, ':valide' => $valide_n2, ':validation' => $validation_n2, ':valide_n1' => $valide_n1, ':validation_n1' => $validation_n1, ':cal_name' => 'hamac', ':ical_key' => $uid, ':uid' => $uid);

        $dbi->execute($insert);
    
        logs("Absence importée, on passe à la ligne suivante dans le CSV", "Hamac", $CSRFToken);
        continue;
    }

    // Si l'absence existe, on vérifie si elle a changé.
    logs("Si l'absence existe, on vérifie si elle a changé", "Hamac", $CSRFToken);
    $absence = $absences[$uid];

    if ($absence['perso_id'] != $perso_id
    or $absence['debut'] != $debut
    or $absence['fin'] != $fin
    or $absence['commentaires'] != $commentaires
    or $absence['valide_n1'] != $valide_n1
    or $absence['valide'] != $valide_n2) {
        // Si l'absence a changé, on met à jour la base de données
        logs("Si l'absence a changé, on met à jour la base de données", "Hamac", $CSRFToken);
        $update = array(':perso_id' => $perso_id, ':debut' => $debut, ':fin' => $fin, ':commentaires' => $commentaires, ':valide' => $valide_n2, ':validation' => $validation_n2, ':valide_n1' => $valide_n1, ':validation_n1' => $validation_n1, ':id' => $absence['id']);

        $dbu->execute($update);

        logs("Absence changé dans la base de donnée, on passe à la ligne suivante dans le CSV", "Hamac", $CSRFToken);

        continue;
    }
}
fclose($inF);

// Unlock
unlink($lockFile);
