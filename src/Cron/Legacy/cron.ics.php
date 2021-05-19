<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file src/Cron/Legacy/cron.ics.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Intègre les absences et congés des fichiers ICS dans la table absences

@note : Modifiez le crontab de l'utilisateur Apache (ex: #crontab -eu www-data) en ajoutant les 2 lignes suivantes :
# Planning Biblio : Importation des fichiers ICS toutes les 5 minutes
* /5 * /usr/bin/php5 -f /var/www/html/planning/ics/cron.ics.php
Pour la ligne précédente, ne mettez pas d'espace entre l'étoile et le /5
Remplacer si besoin le chemin d'accès au programme php et le chemin d'accès à ce fichier
*/

session_start();

/** $version=$argv[0]; permet d'interdire l'execution de ce script via un navigateur
 *  Le fichier config.php affichera une page "accès interdit si la $version n'existe pas
 *  $version prend la valeur de $argv[0] qui ne peut être fournie que en CLI ($argv[0] = chemin du script appelé en CLI)
 */
$version=$argv[0];

// chdir(__DIR__ . '/../../../public/') : important pour l'execution via le cron
chdir(__DIR__ . '/../../../public/');

require_once __DIR__ . '/../../../public/include/config.php';
require_once __DIR__ . '/../../../public/ics/class.ics.php';
require_once __DIR__ . '/../../../public/personnel/class.personnel.php';

$CSRFToken = CSRFToken();

logs("Début d'importation des fichiers ICS", "ICS", $CSRFToken);

// Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
$tmp_dir=sys_get_temp_dir();
$lockFile=$tmp_dir."/planningBiblioIcs.lock";

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
fclose($inF);

// Recherche les serveurs ICS et les variables openURL
// Index des tableaux $servers et $var :
// 1 : Fichiers ICS provenant d'une source externe, renseignés dans la config. : ICS / ICS-Servers1
// 2 : Fichiers ICS provenant d'une source externe, renseignés dans la config. : ICS / ICS-Servers2
// 3 : Fichiers ICS provenant d'une source externe, renseignés dans la fiche des agents (url_ics)

$servers=array(1=>null, 2=>null);
$var=array(1=>null, 2=>null);

for ($i=1; $i<3; $i++) {
    if (trim($config["ICS-Server$i"])) {
        $servers[$i]=trim($config["ICS-Server$i"]);
        if ($servers[$i]) {
            $pos1=strpos($servers[$i], "[");

            if ($pos1) {
                $var[$i] = substr($servers[$i], $pos1 +1);
        
                $pos2=strpos($var[$i], "]");
        
                if ($pos2) {
                    $var[$i] = substr($var[$i], 0, $pos2);
                }
            }
        }
    }
}


// On recherche tout le personnel actif
$p= new personnel();
$p->supprime = array(0);
$p->fetch();
$agents = $p->elements;

// Pour chaque agent, on créé les URL des fichiers ICS et on importe les événements
foreach ($agents as $agent) {

  // Pour les URL N°1, N°2 et url de la fiche agent (N°3)
    // Si le paramètre ICS-Server3 est activé, on recherche également une URL personnalisée dans la fiche des agents (champ url_ics).
  
    $fin = $config['ICS-Server3'] ? 3 : 2;
    for ($i=1; $i <= $fin; $i++) {
        if ($i<3) {
            if (!$servers[$i] or !$var[$i]) {
                continue;
            }
      
            $url=false;

            // Selon le paramètre openURL (mail ou login)
            switch ($var[$i]) {
        case "login":
          if (!empty($agent["login"])) {
              $url=str_replace("[{$var[$i]}]", $agent["login"], $servers[$i]);
          }
          break;
        case "email":
        case "mail":
          if (!empty($agent["mail"])) {
              $url=str_replace("[{$var[$i]}]", $agent["mail"], $servers[$i]);
          }
          break;
        case "matricule":
          if (!empty($agent["matricule"])) {
              $url=str_replace("[{$var[$i]}]", $agent["matricule"], $servers[$i]);
          }
          break;
        case "perso_id":
          if (!empty($agent["id"])) {
              $url=str_replace("[{$var[$i]}]", $agent["id"], $servers[$i]);
          }
          break;
        default: $url=false; break;
      }
        }
    
        if ($i == 3) {
            $url = $agent['url_ics'];
        }
  
        if (!$url) {
            logs("Agent #{$agent['id']} : Impossible de constituer une URL valide", "ICS", $CSRFToken);
            continue;
        }
    
        // Test si le fichier existe
        if (substr($url, 0, 1) == '/' and !file_exists($url)) {
            logs("Agent #{$agent['id']} : Le fichier $url n'existe pas", "ICS", $CSRFToken);
            continue;
        }
    
        // Test si l'URL existe
        if (substr($url, 0, 4) == 'http') {
            $test = get_headers($url, 1);
            if (strstr($test[0], '404')) {
                logs("Agent #{$agent['id']} : $url 404 Not Found", "ICS", $CSRFToken);
                continue;
            }
        }
    

        // Si la case importation correspondant à ce calendrier est décochée, on ne l'importe pas et on purge les éventuels événements déjà importés.
        if (!$agent["ics_$i"]) {
            $ics=new CJICS();
            $ics->src=$url;
            $ics->perso_id=$agent["id"];
            $ics->table="absences";
            $ics->logs=true;
            $ics->CSRFToken = $CSRFToken;
            $ics->purge();
            continue;
        }
    
        logs("Agent #{$agent['id']} : Importation du fichier $url", "ICS", $CSRFToken);

        $ics=new CJICS();
        $ics->src=$url;
        $ics->perso_id=$agent["id"];
        $ics->pattern = $config["ICS-Pattern$i"];
        $ics->status = $config["ICS-Status$i"];
        $ics->table="absences";
        $ics->logs=true;
        $ics->CSRFToken = $CSRFToken;
        $ics->updateTable();
    }
}

// Unlock
unlink($lockFile);
