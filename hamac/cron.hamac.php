<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ics/cron.hamac.php
Création : 7 février 2018
Dernière modification : 7 février 2018
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

if(file_exists($lockFile)){
  $fileTime = filemtime($lockFile);
  $time = time();
  // Si le fichier existe et date de plus de 10 minutes, on le supprime et on continue.
  if ($time - $fileTime > 600){
    unlink($lockFile);
  // Si le fichier existe et date de moins de 10 minutes, on quitte
  } else{
    exit;
  }
}
// On créé le fichier .lock
$inF=fopen($lockFile,"w");
fclose($inF);

// On recherche tout le personnel actif
$p= new personnel();
$p->supprime = array(0);
$p->fetch();
$agents = $p->elements;

// Les logins des agents qui acceptent la synchronisation depuis Hamac
$logins = array();
$key = $config['Hamac-id'];

foreach($agents as $elem){
  if($elem['check_hamac']){
    $logins[] = $elem[$key];
    $perso_ids[$elem[$key]] = $elem['id'];
  }
}

$ids_list = implode(',', $perso_ids);

// Recherche de toutes les absences déjà importées depuis Hamac
$absences = array();
$db = new db();
$db->select2('absences', null, array('cal_name' => 'hamac', 'perso_id' => "IN$ids_list"));
if($db->result){
  foreach($db->result as $elem){
    // On indexe le tableau avec le champ UID qui n'est autre que l'id Hamac
    $absences[$elem['uid']] = $elem;
  }
}

// On récupère les clés (IDs Hamac) pour vérifier si les absences du fichier Hamac sont dans la base de données
$uids = array_keys($absences);


// On lit le fichier CSV
$filename = trim($config['Hamac-csv']);

// Si le fichier n'existe pas, on quitte
if(!file_exists($filename)){
  logs("Le fichier $filename n'existe pas", "Hamac", $CSRFToken);
  // Unlock
  unlink($lockFile);

  exit;
}

// Status à importer
$status = explode(',', $config['Hamac-status']);


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
$inF = fopen($filename, 'a+');

while($tab = fgetcsv($inF, 1024, ';')){

  $uid = $tab[0];

  // Si les logins du fichier Hamac ne sont pas dans le tableau $logins, on passe.
  // Le tableau $logins ne contient que les agents actifs qui acceptent la synchronisation Hamac
  if(!in_array($tab[4], $logins)){
    continue;
  }

  // Si l'absence a été supprimée, on la supprime de la base (status 9)
  // Important : Faire la suppression avant le contrôle des status car le status 9 sera ignoré à la prochaine étape
  if($tab[6] == 9 and in_array($uid, $uids)){
    $delete = array(':id' => $absences[$uid]['id']);

    $dbd->execute($delete);

    continue;
  }

  // Si le status de l'absence Hamac n'est pas dans la liste des status à importer, on passe.
  if(!in_array($tab[6], $status)){
    continue;
  }

  // Préparation des données
  $perso_id = $perso_ids[$tab[4]];
  $demande = date('Y-m-d H:i:s');
  $debut = preg_replace('/(\d+)\/(\d+)\/(\d+) (\d+:\d+:\d+)/', "$3-$2-$1 $4", $tab[2]);
  $fin = preg_replace('/(\d+)\/(\d+)\/(\d+) (\d+:\d+:\d+)/', "$3-$2-$1 $4", $tab[3]);
  $motif = !empty(trim($config['Hamac-motif'])) ? trim($config['Hamac-motif']) : 'Hamac';
  $commentaires = $tab[1];

  // Validations
  // Si le status de l'absence Hamac est 2, l'absence est validée
  if($tab[6] == 2){
    $valide_n1 = 99999;
    $validation_n1 = date('Y-m-d H:i:s');
    $valide_n2 = 99999;
    $validation_n2 = date('Y-m-d H:i:s');
  } else {
    $valide_n1 = 0;
    $validation_n1 = '0000-00-00 00:00:00';
    $valide_n2 = 0;
    $validation_n2 = '0000-00-00 00:00:00';
  }


  // Si l'absence n'est pas dans la base de données, on l'importe.
  if(!in_array($uid, $uids)){
    $insert = array(':perso_id' => $perso_id, ':debut' => $debut, ':fin' => $fin, ':motif' => $motif, ':commentaires' => $commentaires, ':demande' => $demande, ':valide' => $valide_n2, ':validation' => $validation_n2, ':valide_n1' => $valide_n1, ':validation_n1' => $validation_n1, ':cal_name' => 'hamac', ':ical_key' => $uid, ':uid' => $uid);

    $dbi->execute($insert);
    
    continue;
  }

  // Si l'absence existe, on vérifie si elle a changé.
  $absence = $absences[$uid];

  if($absence['perso_id'] != $perso_id
    or $absence['debut'] != $debut
    or $absence['fin'] != $fin
    or $absence['commentaires'] != $commentaires
    or $absence['valide'] != $valide_n2)
  {

    // Si l'absence a changé, on met à jour la base de données
    $update = array(':perso_id' => $perso_id, ':debut' => $debut, ':fin' => $fin, ':commentaires' => $commentaires, ':valide' => $valide_n2, ':validation' => $validation_n2, ':valide_n1' => $valide_n1, ':validation_n1' => $validation_n1, ':id' => $absence['id']);

    $dbu->execute($update);

    continue;
  }
}
fclose($inF);

// Unlock
unlink($lockFile);

?>