<?php
/**
Planning Biblio, Version 2.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : ics/cron.ics.php
Création : 28 juin 2016
Dernière modification : 28 juin 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Intègre les absences et congés des fichiers ICS dans la table absences

@note : modifier la variable $path
*/

// TEST
ini_set("display_errors","on");
error_reporting(999);

$version="cron";
$path="/planning";
require_once "$path/include/config.php";
require_once "$path/ics/class.ics.php";
require_once "$path/personnel/class.personnel.php";


// Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est en cours d'execution
$tmp_dir=sys_get_temp_dir();
$lockFile=$tmp_dir."/planningBiblioIcs.lock";déjà 

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

// On recherche tout le personnel actif
$p= new personnel();
$p->supprime = array(0);
$p->fetch();
$agents = $p->elements;

// TEST
// Nettoyage
// $db=new db();
// $db->query("truncate ics");
// $db=new db();
// $db->delete2("absences", array("motif"=>"Import ICS"));
// $db->delete2("absences", array("motif"=>"Import ICS", "perso_id"=>10));
// exit;

// Parse le fichier ICS et enregistre les événements (nouveaux ou modifiés) dans la table ics
// foreach($tab as $elem){

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


foreach($agents as $agent){
  for($i=1; $i<3; $i++){
    if(!$servers[$i] or !$var[$i]){
      continue;
    }
    
    switch($var[$i]){
      case "login" : $url=str_replace("[{$var[$i]}]",$agent["login"],$servers[$i]); break;
      case "email" :
      case "mail" : $url=str_replace("[{$var[$i]}]",$agent["mail"],$servers[$i]); break;
      default : $url=false; break;
    }
  
    if(!$url){
      continue;
    }
    
    echo $url."\n";
    if(!file_exists($url)){
      continue;
    }

    
    $ics=new CJICS();
    $ics->src=$url;
    $ics->perso_id=$agent["id"];
    $ics->pattern=$config["ICS-Pattern$i"];
    $ics->table="absences";
    $ics->updateTable();
  }
}

// TODO : lock
unlink($lockFile);

?>