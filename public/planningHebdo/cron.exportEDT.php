<?php
/**
Planning Biblio, Version 2.7.15
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/cron.exportEDT.php
Création : 10 août 2018
Dernière modification : 27 septembre 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Export les heures de présences vers un fichier CSV

@note : Modifiez le crontab de l'utilisateur Apache (ex: #crontab -eu www-data) en ajoutant les 2 lignes suivantes :
# Planning Biblio : Exportation des heures de présence tous les jours à minuit
0 0 * * * /usr/bin/php5 -f /var/www/html/planning/planningHebdo/cron.exportEDT.php
Remplacer si besoin le chemin d'accès au programme php et le chemin d'accès à ce fichier
@note : Modifiez la variable $path suivante en renseignant le chemin absolu vers votre dossier planningBiblio
*/

/**
@note Copie du fichier exporté via la commande ftp système :

Créer un fichier paramFTP.txt avec les infos suivantes :
open ip_du_serveur_kelio
user username password
lcd /dossier/dans/lequel/se/trouve/le/fichier/a/exporter
put fichier_a_exporter.csv
bye

ajouter dans le crontab :
/usr/bin/php -f /var/www/html/planning/cron.exportEDT.php && /usr/bin/ftp -n < /chemin/vers/fichier/paramFTP.txt
*/

$path="/var/www/html/planning";
$CSVFile = "/tmp/export-bodet.csv";
$days_before = 15;
$days_after = 60;

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
require_once "$path/planningHebdo/class.planningHebdo.php";

$CSRFToken = CSRFToken();

// Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
$tmp_dir=sys_get_temp_dir();
$lockFile=$tmp_dir."/planningBiblioExportCSV.lock";

if (file_exists($lockFile)) {
    $fileTime = filemtime($lockFile);
    $time = time();
    // Si le fichier existe et date de plus de 10 minutes, on le supprime et on continue.
    if ($time - $fileTime > 600) {
        unlink($lockFile);
    // Si le fichier existe et date de moins de 10 minutes, on quitte
    } else {
        exit;
    }
}
// On créé le fichier .lock
$inF=fopen($lockFile, "w");

// On recherche tout le personnel actif
$p= new personnel();
$p->supprime = array(0);
$p->fetch();

$agents = array();
if (empty($p->elements)) {
    exit;
}

$agents = $p->elements;

// $list sera un tableau contenant pour chaque date et pour chaque agent, les heures de présence
// format array(array('id_agent', 'date', 'indicateur_SP', 'debut1', 'fin1', 'debut2', 'fin2', 'debut3', 'fin3'))
$list = array();

$current = date('Y-m-d', strtotime("-$days_before days"));
$end = date('Y-m-d', strtotime("+$days_after days"));

while ($current < $end) {

//   $list[$current] = array();

    // Recheche le jour de la semaine (lundi (0) à dimanche (6)) et l'offest (décalage si semaine paire/impaire ou toute autre rotation)
    $d=new datePl($current);

    // jour de la semaine lundi = 0 ,dimanche = 6
    $jour = $d->position-1;
    if ($jour==-1) {
        $jour=6;
    }

    // Si utilisation de 2 plannings hebdo (semaine paire et semaine impaire)
    // Si semaine paire, position +=7 : lundi A = 0 , lundi B = 7 , dimanche B = 13
    if (!$config['EDTSamedi'] or $config['PlanningHebdo']) {
        $jour += ($d->semaine3 - 1) * 7;
    }

    // Recherche les heures de présence valides ce jour pour tous les agents
    $p=new planningHebdo();
    $p->debut=$current;
    $p->fin=$current;
    $p->valide=true;
    $p->fetch();

    if (!empty($p->elements)) {
        foreach ($p->elements as $elem) {

      // Récupération de l'ID Harpege de l'agent
            // Si l'agent n'a pas d'ID Harpège, on ne l'importe pas (donc continue) = Demande de la société Bodet
            // TODO : Voir si nous devons rendre ceci paramètrable : utilisation du champ matricule, login, email ou id. Pour Lille, se sera le champ matricule
            if (empty($agents[$elem["perso_id"]]['matricule'])) {
                continue;
            }
            $agent_id = $agents[$elem["perso_id"]]['matricule'];

            // Mise en forme du tableau temps
            /** Le tableau $elem["temps"][$jour] est constitué comme suit :
             0 => début période 1,
             1 => fin période 1,
             2 => début période 2,
             5 => fin période 2 si pause2 activée, sinon null,
             6 => début période 3 si pause 2, sinon null,
             3 => fin de journée (peut être fin de période 1, 2 ou 3)
             */
            $temps = array();
      
            if (isset($elem["temps"][$jour])) {

        // Première période : matinée : index 0 (début) et 1 (fin)
                if (!empty($elem["temps"][$jour][0]) and !empty($elem["temps"][$jour][1])) {
                    $temps[] = substr($elem["temps"][$jour][0], 0, 5);
                    $temps[] = substr($elem["temps"][$jour][1], 0, 5);
                }
                // Deuxième période : après-midi : index 2 (début) et 3 (fin)
                // Seulement s'il n'y a pas de 3ème période (voir cas suivant)
                if (!empty($elem["temps"][$jour][2]) and !empty($elem["temps"][$jour][3]) and empty($elem["temps"][$jour][5])) {
                    $temps[] = substr($elem["temps"][$jour][2], 0, 5);
                    $temps[] = substr($elem["temps"][$jour][3], 0, 5);
                }
                // Si 2 pauses sont enregistrées, les index 5 et 6 viennent s'intercaler entre les index 2 et 3. Les périodes sont donc composées des index 2 (début1) et 5 (fin1) et 6 (début2) et 3 (fin2)
                if (!empty($elem["temps"][$jour][2]) and !empty($elem["temps"][$jour][5])) {
                    $temps[] = substr($elem["temps"][$jour][2], 0, 5);
                    $temps[] = substr($elem["temps"][$jour][5], 0, 5);
                    $temps[] = substr($elem["temps"][$jour][6], 0, 5);
                    $temps[] = substr($elem["temps"][$jour][3], 0, 5);
                }
                // Journée complète : heures enregistrées sans pause entre les index 0 et 3
                if (!empty($elem["temps"][$jour][0]) and empty($elem["temps"][$jour][2]) and empty($elem["temps"][$jour][5]) and !empty($elem["temps"][$jour][3])) {
                    $temps[] = substr($elem["temps"][$jour][0], 0, 5);
                    $temps[] = substr($elem["temps"][$jour][3], 0, 5);
                }
                // Journée complète : heures enregistrées sans pause entre les index 0 et 5
                if (!empty($elem["temps"][$jour][0]) and empty($elem["temps"][$jour][2]) and !empty($elem["temps"][$jour][5]) and empty($elem["temps"][$jour][3])) {
                    $temps[] = substr($elem["temps"][$jour][0], 0, 5);
                    $temps[] = substr($elem["temps"][$jour][5], 0, 5);
                }
            }

            $heures_supp = null ;

            $list[]=array_merge(array($current, $agent_id, $heures_supp), $temps);
        }
    }

    $current = date('Y-m-d', strtotime($current." + 1 day"));
}

// On ouvre le fichier CSV
logs("Exportation des données vers le fichier $CSVFile", "PlanningHebdo", $CSRFToken);

$fp = fopen($CSVFile, 'w');

foreach ($list as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

// Unlock
unlink($lockFile);
logs("Exportation terminée (fichier $CSVFile)", "PlanningHebdo", $CSRFToken);
