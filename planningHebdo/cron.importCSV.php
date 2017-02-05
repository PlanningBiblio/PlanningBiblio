<?php
/**
Planning Biblio, Version 2.5.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : ics/cron.ics.php
Création : 1er juillet 2016
Dernière modification : 19 novembre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Import les heures de présences depuis un fichier CSV

@note : Modifiez le crontab de l'utilisateur Apache (ex: #crontab -eu www-data) en ajoutant les 2 lignes suivantes :
# Planning Biblio : Importation des heures de présence tous les jours à minuit
0 0 * * * /usr/bin/php5 -f /var/www/html/planning/cron.ics.php
Remplacer si besoin le chemin d'accès au programme php et le chemin d'accès à ce fichier
@note : Modifiez la variable $path suivante en renseignant le chemin absolu vers votre dossier planningBiblio
*/

$path="/planning";

/** $version=$argv[0]; permet d'interdire l'execution de ce script via un navigateur
 *  Le fichier config.php affichera une page "accès interdit si la $version n'existe pas
 *  $version prend la valeur de $argv[0] qui ne peut être fournie que en CLI ($argv[0] = chemin du script appelé en CLI)
 */
$version=$argv[0];

// chdir($path) : important pour l'execution via le cron
chdir($path);

require_once "$path/include/config.php";
require_once "$path/personnel/class.personnel.php";


// Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
$tmp_dir=sys_get_temp_dir();
$lockFile=$tmp_dir."/planningBiblioCSV.lock"; 

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

$agents = array();
if (!empty($p->elements)) {
  foreach($p->elements as $elem){
    $agents[$elem['login']] = $elem;
  }
}

// On ouvre le fichier CSV
$CSVFile = trim($config['PlanningHebdo-CSV']);
logs("Importation du fichier $CSVFile","PlanningHebdo");

if( !$CSVFile or !file_exists($CSVFile)){
  logs("Fichier $CSVFile non trouvé","PlanningHebdo");
  exit;
}

$lines = file($CSVFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// On place les éléments du fichiers dans le tableau $temps
$temps = array();

// Pour chaque ligne
foreach($lines as $line){
  $cells=explode(";", $line);
  // Pour chaque cellule
  for($i=0; $i<count($cells); $i++){
    $cells[$i] = trim($cells[$i]);
    
    // Mise en form de la date
    if($i==1){
      $cells[$i] = date("Y-m-d", strtotime($cells[$i]));
    }
    
    // Mise en forme des heures
    if($i>1){
      if(isset($cells[$i]) and $cells[$i]){
		// supprime les h et les : de façon à traiter tous les formats de de la même façon (formats acceptés : 0000, 00h00, 00:00, 000, 0h00, 0:00)
		$cells[$i] = str_replace(array("h",":"), null, $cells[$i]);
		$min = substr($cells[$i],-2);
		$hre = sprintf("%02s",substr($cells[$i],0,-2));
		$cells[$i] = $hre.":$min:00";
      }else{
		$cells[$i] = "00:00:00";
      }
    }
  }

  // Si les horaires de l'après midi ne sont pas renseignés, on initialise les variables pour éviter les erreurs PHP
  if(!isset($cells[4])){
    $cells[4]=null;
    $cells[5]=null;
  }
  
  // Récupération de l'ID de l'agent
  $perso_id = $agents[$cells[0]]['id'];
  
  // Récupération du site de l'agent
  // Récupération depuis la table personnel, donc ne fonctionne que si l'agent ne travaille que sur un site
  
  // Config. monosite : $site = 1
  if($config['Multisites-nombre'] == 1){
	$site = 1;
  // Config. Multisites
  }else{
	// tous les sites sur lesquels l'agent peut travailler
	$sites = $agents[$cells[0]]['sites']; 
	
	// Si au moins un site est renseigné, on affecte l'agent au premier site trouvé
	if( is_array($sites) ){
	  $site = $sites[0];
	// Sinon, on l'affecte au site N°1
	}else{
	  $site = 1;
	}
  }

  // Identification de la semaine, premier jour et dernier jour (regroupement pasr semaine)
  $lundi = date('N', strtotime($cells[1])) == 1 ? $cells[1] : date("Y-m-d", strtotime(date("Y-m-d",strtotime($cells[1]))." last Monday"));
  $dimanche = date('N', strtotime($cells[1])) == 7 ? $cells[1] : date("Y-m-d", strtotime(date("Y-m-d",strtotime($cells[1]))." next Sunday"));

  // Création d'un tableau par agent
  if(!array_key_exists($perso_id,$temps)){
	$temps[$perso_id]=array("perso_id"=> $perso_id);
  }
  
  // Chaque tableau "agent" contient un tableau par semaine
  // Création des tableaux "semaines" avec date de début (lundi), date de fin (dimanche) et emploi du temps
  if(!array_key_exists($lundi,$temps[$perso_id])){
	$temps[$perso_id][$lundi]['debut']=$lundi;
	$temps[$perso_id][$lundi]['fin']=$dimanche;
	$temps[$perso_id][$lundi]['temps']=array();
  }
  
  // Mise en forme du champ "temps"
  // Le champ "temps" contient un tableau contenant les emplois du temps de chaque jour : index ($jour) de 0 à 6 (du lundi au dimanche)
  $jour=date("N", strtotime($cells[1])) -1;
  $temps[$perso_id][$lundi]['temps'][$jour] = array($cells[2],$cells[3],$cells[4],$cells[5],$site);

  // Clé identifiant les infos de la ligne (pour comparaison avec la DB)
  // La clé est composée de l'id de l'agent et du md5 du tableau de sa semaine, tableau comprenant le debut, la fin et l'emploi du temps.
  $cle = $perso_id.'-'.md5(json_encode($temps[$perso_id][$lundi]));
  $temps[$perso_id][$lundi]['cle'] = $cle;

}

// $cles : tableau contenant les clés des éléments du fichiers pour comparaison avec la base de données
$cles = array();

// On reprend tous les éléments du tableau $temps finalisé et on prépare les données pour l'insertion dans la base de données (tableau $tab);
// $tab : tableau contenant les éléments à importer
$tab = array();

foreach($temps as $perso){
  foreach($perso as $semaine){
	if(is_array($semaine)){
	  $cles[] = $semaine['cle'];
	  $temps = json_encode($semaine['temps']);
	  $tab[] =  array(":perso_id"=>$perso['perso_id'], ":debut"=>$semaine['debut'], ":fin"=>$semaine['fin'], ":temps"=>$temps,":cle"=>$semaine['cle']);
	}
  }
}


// $cles_db : tableau contenant les clé des éléments de la base de données pour comparaison avec le fichier
$cles_db = array();

// Recherche des éléments déjà importés
$tab_db=array();
$db = new db();
$db->select2("planningHebdo",null,array('cle'=>'>0'));
if($db->result){
  foreach($db->result as $elem){
    $tab_db[$elem['cle']] = $elem;
    $cles_db[] = $elem['cle'];
  }
}


// Insertion des nouvelles valeurs ou valeurs modifiées
$insert = array();
foreach($tab as $elem){
  if(!in_array($elem[":cle"],$cles_db)){
	if($elem[':debut'] <= date('Y-m-d') and $elem[':fin'] >= date('Y-m-d')){
	  $elem[':actuel'] = "1";
	} else {
	  $elem[':actuel'] = "0";
	}
    $insert[]=$elem;
  }
}

// Nombre d'éléments à importer
$nb = count($insert);

if($nb > 0){
  $db=new dbh();
  $db->prepare("INSERT INTO `{$dbprefix}planningHebdo` (`perso_id`, `debut`, `fin`, `temps`, `saisie`, `valide`, `validation`, `actuel`, `cle`) VALUES (:perso_id, :debut, :fin, :temps, SYSDATE(), '99999', SYSDATE(), :actuel, :cle);");
  foreach($insert as $elem){
	$db->execute($elem);
  }

  if(!$db->error){
	logs("$nb éléments importés","PlanningHebdo");
  }else{
	logs("Une erreur est survenue pendant l'importation","PlanningHebdo");
  }
}else{
  logs("Rien à importer","PlanningHebdo");
}

// Suppression des valeurs supprimées ou modifiées
$delete = array();
foreach($cles_db as $elem){
  if(!in_array($elem,$cles)){
    $delete[]=array(":cle"=>$elem);
  }
}

// Nombre d'éléments à supprimer
$nb = count($delete);

if($nb >0){
  $db=new dbh();
  $db->prepare("DELETE FROM `{$dbprefix}planningHebdo` WHERE `cle`=:cle;");
  foreach($delete as $elem){
	$db->execute($elem);
  }
  
  if(!$db->error){
	logs("$nb éléments supprimés","PlanningHebdo");
  }else{
	logs("Une erreur est survenue lors de la suppression d'éléments","PlanningHebdo");
  }
}else{
  logs("Aucun élément à supprimer","PlanningHebdo");
}

// Unlock
unlink($lockFile);

?>