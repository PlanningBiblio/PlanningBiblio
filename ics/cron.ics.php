<?php
/**
Planning Biblio, Version 2.4.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : ics/cron.ics.php
Création : 28 juin 2016
Dernière modification : 19 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Intègre les absences et congés des fichiers ICS dans la table absences

@note : Modifiez le crontab de l'utilisateur Apache (ex: #crontab -eu www-data) en ajoutant les 2 lignes suivantes :
# Planning Biblio : Importation des fichiers ICS toutes les 5 minutes
* /5 * /usr/bin/php5 -f /var/www/html/planning/cron.ics.php
Pour la ligne précédente, ne mettez pas d'espace entre l'étoile et le /5
Remplacer si besoin le chemin d'accès au programme php et le chemin d'accès à ce fichier
@note : Modifiez la variable $path suivante en renseignant le chemin absolu vers votre dossier planningBiblio
*/

$path="/var/www/html/planning";

/** $version=$argv[0]; permet d'interdire l'execution de ce script via un navigateur
 *  Le fichier config.php affichera une page "accès interdit si la $version n'existe pas
 *  $version prend la valeur de $argv[0] qui ne peut être fournie que en CLI ($argv[0] = chemin du script appelé en CLI)
 */
$version=$argv[0];

// chdir($path) : important pour l'execution via le cron
chdir($path);

require_once "$path/include/config.php";
require_once "$path/ics/class.ics.php";
require_once "$path/personnel/class.personnel.php";

logs("Début d'importation des fichiers ICS","ICS");

// Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
$tmp_dir=sys_get_temp_dir();
$lockFile=$tmp_dir."/planningBiblioIcs.lock"; 

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

// Recherche les serveurs ICS et les variables openURL
$servers=array(1=>null, 2=>null);
$var=array(1=>null, 2=>null);

for($i=1; $i<3; $i++){
  if(trim($config["ICS-Server$i"])){
    $servers[$i]=trim($config["ICS-Server$i"]);
    if($servers[$i]){
      $pos1=strpos($servers[$i],"[");

      if($pos1){
		$var[$i] = substr($servers[$i],$pos1 +1);
		
		$pos2=strpos($var[$i],"]");
		
		if($pos2){
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
foreach($agents as $agent){

  // Pour les URL N°1 et N°2 
  for($i=1; $i<3; $i++){
    if(!$servers[$i] or !$var[$i]){
      continue;
    }
    
    // Selon le paramètre openURL (mail ou login)
    switch($var[$i]){
      case "login" : $url=str_replace("[{$var[$i]}]",$agent["login"],$servers[$i]); break;
      case "email" :
      case "mail" : $url=str_replace("[{$var[$i]}]",$agent["mail"],$servers[$i]); break;
      default : $url=false; break;
    }
  
    if(!$url){
	  logs("Impossible de constituer une URL valide pour l'agent #{$agent['id']}","ICS");
      continue;
    }
    
    logs("Importation du fichier $url pour l'agent #{$agent['id']}","ICS");

    if(!file_exists($url)){
	  logs("Fichier $url non trouvé pour l'agent #{$agent['id']}","ICS");
      continue;
    }

    $ics=new CJICS();
    $ics->src=$url;
    $ics->perso_id=$agent["id"];
    $ics->pattern=$config["ICS-Pattern$i"];
    $ics->table="absences";
    $ics->logs=true;
    $ics->updateTable();
  }
}

// Unlock
unlink($lockFile);

?>