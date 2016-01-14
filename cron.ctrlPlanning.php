<?php
$path="/planning";
$version="cron";

require_once "$path/include/config.php";
require_once "$path/include/function.php";
require_once "$path/planning/postes_cfg/class.tableaux.php";
require_once "$path/postes/class.postes.php";


// TODO : mettre ce qui suit dans la BDD/config (maj.php et db_data.php)
$config['Planning-Rappel-Jours']=5;
$config['Planning-Rappel-Renfort']=0;

// Gestion des sites
$sites=array();
for($i=1;$i<=$config['Multisites-nombre'];$i++){
  $sites[]=array($i,$config["Multisites-site".$i]);
}

// Dates à controler
$jours=$config['Planning-Rappel-Jours'];

// TEST
$jours=3;
// TEST

// Recherche la date de jour et les $jours suivants
$dates=array();
for($i=0;$i<=$jours;$i++){
  $time=strtotime("+ $i days");
  $dates[]=date("Y-m-d",$time);
  $jour_semaine=date("w", $time);

  // Si le jour courant est un samedi, nous recherchons 2 jours supplémentaires pour avoir le bon nombre de jours ouvrés.
  // Nous controlons également le samedi et le dimanche
  if($jour_semaine==6){
    $jours=$jours+2;
  }
}

// Listes des postes
$p=new postes();
$p->fetch();
$postes=$p->elements;

// Création du message qui sera envoyé par e-mail
$data=array();

// Prépare la requête permettant de vérifier si les postes sont occupés
// On utilide PDO pour de meilleurs performances car la même requête sera executée de nombreuses fois avec des valeurs différentes
$dbh=new dbh();
$dbh->prepare("SELECT `id`,`perso_id`,`absent` FROM `{$dbprefix}pl_poste` 
  WHERE `date`=:date AND `site`=:site AND `poste`=:poste AND `debut`=:debut AND `fin`=:fin AND `absent`='0' AND `supprime`='0';");

$lastId=null;

// Pour chaque date et pour chaque site
foreach($dates as $date){
  foreach($sites as $site){
    
    // on créé un tableau pour stocker les éléments par dates et sites
    $data[$date][$site[0]]=array();

    // On recherche les plannings qui ne sont pas créés (aucune structure affectée)
    $db=new db();
    $db->select2("pl_poste_tab_affect",null,array("date"=>$date, "site"=>$site[0]));
    if(!$db->result){
      $data[$date][$site[0]]["message"]="Le planning {$site[1]} du ".dateFr($date)." n'est pas cr&eacute;&eacute;<br/>\n";
      continue;
    }

    else{
      // Si le planning est créé, on récupère le numéro du tableau pour ensuite 
      // comparer la structure au planning complété afin de trouver les cellules vides
      $tableauId=$db->result[0]['tableau'];

      // On recherche les plannings qui ne sont pas validés
      $db=new db();
      $db->select2("pl_poste_verrou",null,array("date"=>$date, "site"=>$site[0], "verrou2"=>1));
      if(!$db->result){
	$data[$date][$site[0]]["message"]="Le planning {$site[1]} du ".dateFr($date)." n'est pas valid&eacute;<br/>\n";
      }
    }

    // On recherche les plannings qui ne sont pas complets (cellules vides)
    // Recherche des tableaux (structures)
    $t=new tableau();
    $t->id=$tableauId;
    $t->get();
    $tableau=$t->elements;
    
    foreach($tableau as $elem){
    
      // On stock dans notre tableau data les éléments date, site, tableau
      $data[$date][$site[0]]['tableau'][$elem['nom']]["dateFr"]=dateFr($date);
      $data[$date][$site[0]]['tableau'][$elem['nom']]["site"]=$site[0];
      $data[$date][$site[0]]['tableau'][$elem['nom']]["siteNom"]=$site[1];
      $data[$date][$site[0]]['tableau'][$elem['nom']]["tableau"]=$elem['nom'];
      $data[$date][$site[0]]['tableau'][$elem['nom']]["tableauNom"]=$elem['titre'];

      // $tab = liste des postes/plages horaires non occupés, cellules grisées excluses, poste non obligatoires exclus selon config
      $tab=array();
      $i=-1;
      
      // Pour chaque ligne du tableau (structure)
      foreach($elem['lignes'] as $l){
	// Ne regarde que les lignes "postes"
	if($l['type']=="poste"){
	  // Pour chaque créneau horaire du tableau (structure)
	  foreach($elem['horaires'] as $key => $h){
	    // Si cellule grisées, on l'exclus (donc continue)
	    if(in_array($l['ligne']."_".($key+1),$elem['cellules_grises'])){
	      continue;
	    }
	    // Si on ne veut pas des postes de renfort et si le poste n'est pas obligatoire, on l'exclus
	    if(!$config['Planning-Rappel-Renfort'] and $postes[$l['poste']]['obligatoire']!="Obligatoire"){
	      continue;
	    }

	    // On contrôle si le poste est occupé
	    // Pour ceci, on execute la requête préparée plus haut avec PDO
	    $sql=array(":date"=>$date, ":site"=>$site[0], ":poste"=>$l['poste'], ":debut"=>$h['debut'], ":fin"=>$h['fin']);
	    $dbh->execute($sql);
	    $result=$dbh->result[count($dbh->result)-1];

	    // TODO Compléter ce qui suit en contrôlant qu'il n'y ait pas d'absent / congés 
	    // TODO Contrôler les tables absences / conges (le champ absent est déjà contrôlé)
	    // TODO : Faire une méthode absences::check(perso_id,debut,fin,valide=true) return true/false;
	    // TODO : Faire une méthode conges::check(perso_id,debut,fin,valide=true) return true/false;
	    
	    // Si la dernière execution de la requête ne donne pas de résultat ($lastId=$result['id'])
	    if($lastId==$result['id']){
	      // On enregistre dans le table les informations de la cellule

	      // On regroupe les horaires qui se suivent sur un même poste
	      if(!empty($tab) and $tab[$i]['fin']==$h['debut'] and $tab[$i]['poste']==$l['poste']){
		$tab[$i]["fin"]=$h['fin'];
	      }
	      else{
		$i++;
		$tab[$i]=array("ligne"=>$l['ligne'], "poste"=>$l['poste'], "posteNom"=>$postes[$l['poste']]['nom'], 
		  "debut"=>$h['debut'], "fin"=>$h['fin']);
	      }
	    }
	    
	    $lastId=$result['id'];
	  }
	}
      }
      $data[$date][$site[0]]['tableau'][$elem['nom']]["data"]=$tab;
    }
  }
}

// TEST
print_r($data);

?>