<?php
/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : statistiques/export.php
Création : mai 2011
Dernière modification : 20 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'exporter les différentes statistiques. Recherche les informations dans la bases de données voulues ($_GET['nom']),
les place dans les tableaux $cellules et $lignes, puis les écrit dans un fichier (data/stat_$_GET['nom'])

Page appelée par la fonction JavaScript "export_stat" lors du clique sur les liens "exporter" des pages de statistiques
*/

require_once(__DIR__ . '/../../init/init_ajax.php');
require_once(__DIR__ . '/../include/sanitize.php');
require_once(__DIR__ . '/../include/function.php');
require_once('class.statistiques.php');


// Initialisation des variables
$nom = $request->get('nom');
$type = $request->get('type');

$nom = filter_var($nom, FILTER_SANITIZE_URL);
$type = filter_var($type, FILTER_CALLBACK, array('options' => 'sanitize_file_extension'));

 // Compter les jours ouvrables (ou ouvrés) entre début et fin
$debut = isset($_SESSION['stat_debut']) ? $_SESSION['stat_debut'] : null;
$fin = isset($_SESSION['stat_fin']) ? $_SESSION['stat_fin'] : null;
$debutSQL=dateFr($debut);
$finSQL=dateFr($fin);

$db=new db();
$db->query("SELECT `date` FROM `{$dbprefix}pl_poste` WHERE `date` BETWEEN '$debutSQL' AND '$finSQL' GROUP BY `date`;");
$nbJours=$db->nb;

$Fnm = "stat_{$nom}_".date("YmdHis");

if ($type=="csv") {
    $separateur="';'";
    $Fnm.=".csv";
} else {
    $separateur="\t";
    $Fnm.=".xls";
}

echo $Fnm;	// Retour AJAX du nom de fichier
$Fnm = "../data/$Fnm";

$tab=$_SESSION['stat_tab'];

$debutAlpha = dateAlpha($debutSQL);
$finAlpha = dateAlpha($finSQL);

// Suppression des <sup></sup> dans les dates
$debutAlpha = preg_replace('/<.[^>]*>/', '', $debutAlpha);
$finAlpha = preg_replace('/<.[^>]*>/', '', $finAlpha);


$lignes=array();

switch ($nom) {
    case "postes": 									// Postes
        $lignes=array("Statistiques par poste du $debutAlpha au $finAlpha",null);
        $lignes[]="Les agents";
        $lignes[]=implode($separateur, array("Poste","Etage","Obligatoire/renfort","Heures","Moyenne jour","Moyenne hebdo","Nom de l'agent","Prénom de l'agent","Heures par agent"));
        foreach ($tab as $elem) {
            $jour = ($nbJours > 0) ? $elem[2] / $nbJours : 0;
            $hebdo = statistiques::average($elem[2], $debut, $fin);
            foreach ($elem[1] as $agent) {
                $cellules=array();
                $cellules[]=$elem[0][1];			// nom du poste
                $cellules[]=$elem[0][2];			// Etage
                $cellules[]=$elem[0][3];			// Obligatoire
                $cellules[]=number_format($elem[2], 2, ',', ' ');	// Nombre d'heures
                $cellules[]=number_format($jour, 2, ',', ' ');	// moyenne jour
                $cellules[]=number_format($hebdo, 2, ',', ' ');	// moyenne hebdo
                $cellules[]=$agent[1];				// Nom de l'agent
                $cellules[]=$agent[2];				// Prénom
                $cellules[]=number_format($agent[3], 2, ',', ' ');	// Heures par agent
                $lignes[]=implode($separateur, $cellules);
            }
        }
        $lignes[]=null;
        $lignes[]="Les services";
        foreach ($tab as $elem) {
            $jour = ($nbJours > 0) ? $elem[2] / $nbJours : 0;
            $hebdo = statistiques::average($elem[2], $debut, $fin);
            foreach ($elem["services"] as $service) {
                $cellules=array();
                $cellules[]=$elem[0][1];				// nom du poste
                $cellules[]=$elem[0][2];				// Etage
                $cellules[]=$elem[0][3];				// Obligatoire
                $cellules[]=number_format($elem[2], 2, ',', ' ');		// Nombre d'heures
                $cellules[]=number_format($jour, 2, ',', ' ');		// moyenne jour
                $cellules[]=number_format($hebdo, 2, ',', ' ');		// moyenne hebdo
                $cellules[]=str_replace("ZZZ_", null, $service["nom"]);	// Nom du service
                $cellules[]=number_format($service["heures"], 2, ',', ' ');// Heures par agent
                $lignes[]=implode($separateur, $cellules);
            }
        }
        $lignes[]=null;
        $lignes[]="Les statuts";
        foreach ($tab as $elem) {
            $jour = ($nbJours > 0) ? $elem[2] / $nbJours : 0;
            $hebdo = statistiques::average($elem[2], $debut, $fin);
            foreach ($elem["statuts"] as $statut) {
                $cellules=array();
                $cellules[]=$elem[0][1];				// nom du poste
                $cellules[]=$elem[0][2];				// Etage
                $cellules[]=$elem[0][3];				// Obligatoire
                $cellules[]=number_format($elem[2], 2, ',', ' ');		// Nombre d'heures
                $cellules[]=number_format($jour, 2, ',', ' ');		// moyenne jour
                $cellules[]=number_format($hebdo, 2, ',', ' ');		// moyenne hebdo
                $cellules[]=str_replace("ZZZ_", null, $statut["nom"]);	// Nom du statut
                $cellules[]=number_format($statut["heures"], 2, ',', ' ');	// Heures par agent
                $lignes[]=implode($separateur, $cellules);
            }
        }
        break;
  
    case "postes_synthese": 							// Postes (synthèse)
        $lignes=array("Statistiques par poste (synthèse) du $debutAlpha au $finAlpha",null);
        $lignes[]=implode($separateur, array("Poste","Etage","Obligatoire/renfort","Heures","Moyenne jour","Moyenne hebdo"));
        foreach ($tab as $elem) {
            $jour = ($nbJours > 0) ? $elem[2] / $nbJours : 0;
            $hebdo = statistiques::average($elem[2], $debut, $fin);
            $total_heures+=$elem[2];
            $total_jour+=$jour;
            $total_hebdo+=$hebdo;
            $cellules=array();
            $cellules[]=$elem[0][1];									// nom du poste
            $cellules[]=$elem[0][2];									// Etage
            $cellules[]=$elem[0][3];									// Obligatoire
            $cellules[]=number_format($elem[2], 2, ',', ' ');				// Nombre d'heures
            $cellules[]=number_format(round($jour, 2), 2, ',', ' ');		// moyenne jour
            $cellules[]=number_format(round($hebdo, 2), 2, ',', ' ');		// moyenne hebdo
            $lignes[]=implode($separateur, $cellules);
        }
        $lignes[]=implode($separateur, array("Total","","",number_format($total_heures, 1, ',', ' '),number_format(round($total_jour, 2), 2, ',', ' '),number_format(round($total_hebdo, 2), 2, ',', ' ')));
        break;
  
    case "postes_renfort": 							// Postes de renfort
        $lignes=array("Poste de renfort du $debutAlpha au $finAlpha",null);
        $lignes[]=implode($separateur, array("Poste","Etage","Heures","Moyenne jour","Moyenne hebdo","Jours","Heures par jour","Début","Fin","Heures"));
        foreach ($tab as $elem) {
            $jour = ($nbJours > 0) ? $elem[2] / $nbJours : 0;
            $hebdo = statistiques::average($elem[2], $debut, $fin);
            foreach ($elem[1] as $date) {
                foreach ($date[1] as $horaires) {
                    $cellules=array();
                    $cellules[]=$elem[0][1];									// nom du poste
                    $cellules[]=$elem[0][2];									// Etage
                    $cellules[]=number_format($elem[2], 2, ',', ' ');				// Nombre d'heures
                    $cellules[]=number_format(round($jour, 2), 2, ',', ' ');		// moyenne jour
                    $cellules[]=number_format(round($hebdo, 2), 2, ',', ' ');		// moyenne hebdo
                    $cellules[]=dateFr($date[0]);								// date
                    $cellules[]=number_format($date[2], 2, ',', ' ');				// heures par jour
                    $cellules[]=$horaires[0];									// debut
                    $cellules[]=$horaires[1];									// fin
                    $cellules[]=number_format($horaires[2], 2, ',', ' ');			// heures
                    $lignes[]=implode($separateur, $cellules);
                }
            }
        }
        break;
  
    // Absences
    case "absences":
        $debut=date($_SESSION['oups']['stat_absences_debut']);
        $fin=date($_SESSION['oups']['stat_absences_fin']);
        $motifs=$_SESSION['oups']['stat_absences_motifs'];
        $totaux=$_SESSION['oups']['stat_absences_totaux'];
      
        // Première ligne et entête du tableau
        $lignes[]="Statistiques sur les absences du $debut au $fin";		// Affichage du nom des colonnes
        $tmp=array("Nom","Prénom","Total","Total d'heures");
        foreach ($motifs as $m) {
            $tmp[]=str_replace("<br/>", " ", $m)." Nombre";
            $tmp[]=str_replace("<br/>", " ", $m)." Heures";
        }
        $lignes[]=implode($separateur, $tmp);
      
        // Contenu
        foreach ($tab as $elem) {
            $cellules=array();
            $cellules[]=html_entity_decode($elem['nom'], ENT_QUOTES|ENT_IGNORE, "UTF-8");		// Nom et prénom
            $cellules[]=html_entity_decode($elem['prenom'], ENT_QUOTES|ENT_IGNORE, "UTF-8");
            $cellules[]=$elem['total'];								// Total
            $cellules[]=$elem['totalHeures'];							// Total d'heures
            foreach ($motifs as $m) {
                $cellules[]=$elem[$m]['total'];							// Nombre d'absences par motif
                $cellules[]=$elem[$m]['heures'];							// Heures d'absences par motif
            }
            $lignes[]=implode($separateur, $cellules);
        }
      
        // Totaux (dernière ligne)
        $cellules=array("Totaux","",$totaux['_general'],$totaux['_generalHeures']);
        foreach ($motifs as $m) {								// Nombre d'absences par motif
            $cellules[]=$totaux[$m]['frequence'];
            $cellules[]=$totaux[$m]['heures'];
        }
        $lignes[]=implode($separateur, $cellules);
        break;
  
    case "temps":									// Feuille de temps
        $debutFr=dateFr($_SESSION['oups']['stat_temps_debut']);
        $finFr=dateFr($_SESSION['oups']['stat_temps_fin']);
        $dates=$_SESSION['stat_dates'];
        $heures=$_SESSION['stat_heures'];
        $agents=$_SESSION['stat_agents'];
        $totauxGroupesHeures = $_SESSION['oups']['stat_groupesHeures'];
        $totauxGroupesPerso = $_SESSION['oups']['stat_groupesPerso'];
        $groupes_keys = $_SESSION['oups']['stat_groupes'];
      
        $lignes[]="Du $debutFr au $finFr";		// Affichage du nom des colonnes
        $tmp=array("Nom","Prénom","Statut");
      
        foreach ($dates as $d) {
            $tmp[]=str_replace("<br/>", " ", $d[1]);
        }
      
        // Totaux par groupe de postes
        if (!empty($groupes_keys)) {
            foreach ($groupes_keys as $g) {
                $tmp[] = $g['name'];
            }
        }
      
        $tmp[]="Total";
        $tmp[]="Max";
        $tmp[]="Moyenne Hebdo.";
        $tmp[]=" Max. Hebdo.";
        $lignes[]=implode($separateur, $tmp);
        foreach ($tab as $elem) {
            $cellules=array();
            $cellules[]=html_entity_decode($elem['nom'], ENT_QUOTES|ENT_IGNORE, "UTF-8");		// Nom et prénom
            $cellules[]=html_entity_decode($elem['prenom'], ENT_QUOTES|ENT_IGNORE, "UTF-8");
            $cellules[]=html_entity_decode($elem['statut'], ENT_QUOTES|ENT_IGNORE, "UTF-8");	// Statut
            foreach ($dates as $d) {								// Heures de chaque jour
                $cellules[] = is_numeric(heure4($elem[$d[0]]['total'])) ? number_format(heure4($elem[$d[0]]['total']), 2, ',', '') : null;
            }
        
            // Totaux par groupe de postes
            if (!empty($groupes_keys)) {
                foreach ($groupes_keys as $g) {
                    $cellules[] = number_format((float) heure4($elem['groupe'][$g['id']]), 2, ',', '');
                }
            }
        
            $cellules[] = is_numeric(heure4($elem['total'])) ? number_format(heure4($elem['total']), 2, ',', '') : null;               // Total d'heures sur la période
            $cellules[] = is_numeric(heure4($elem['max'])) ? number_format(heure4($elem['max']), 2, ',', '') : null;                   // Nombre d'heures maximum sur la période
            $cellules[] = is_numeric(heure4($elem['semaine'])) ? number_format(heure4($elem['semaine']), 2, ',', '') : null;           // Moyenne d'heures par semaine
            $cellules[] = is_numeric(heure4($elem['heuresHebdo'])) ? number_format(heure4($elem['heuresHebdo']), 2, ',', '') : null;   // Quota
            $lignes[]=implode($separateur, $cellules);
        }
        
        // ligne "Nombre d'heures"
        $cellules = array("Nombre d'heures","","");
        foreach ($dates as $d) {
            $cellules[] = is_numeric($heures[$d[0]]) ? number_format($heures[$d[0]], 2, ',', '') : null;
        }
      
        // Totaux par groupe de postes
        if (!empty($groupes_keys)) {
            foreach ($groupes_keys as $g) {
                $cellules[] = number_format(heure4($totauxGroupesHeures[$g['id']]), 2, ',', '');
            }
        }
      
        $cellules[] = is_numeric($_SESSION['oups']['stat_totalHeures']) ? number_format($_SESSION['oups']['stat_totalHeures'], 2, ',', '') : null;
        $lignes[]=implode($separateur, $cellules);
        $cellules=array("Nombre d'agents","","");						// Lignes "Nombres d'agents
        foreach ($dates as $d) {
            $cellules[] = is_numeric($_SESSION['oups']['stat_nbAgents'][$d[0]]) ? $_SESSION['oups']['stat_nbAgents'][$d[0]] : null;
        }
        $total=0;
        foreach ($_SESSION['oups']['stat_nbAgents'] as $elem) {
            $total += (float) $elem;
        }
        
        // Totaux par groupe de postes
        if (!empty($groupes_keys)) {
            foreach ($groupes_keys as $g) {
                $cellules[] = $totauxGroupesPerso[$g['id']];
            }
        }
      
        $cellules[]=$total; //$agents[7];
        $lignes[]=implode($separateur, $cellules);
        break;
  
    case "samedis": // Samedis
        $lignes=statistiquesSamedis($tab, $debutAlpha, $finAlpha, $separateur, $nbJours);
        break;
  
    default:
        $lignes = statistiques1($nom, $tab, $debutAlpha, $finAlpha, $separateur, $nbJours, $debut, $fin);
        break;
}

$inF = fopen($Fnm, "w\n");

$lignes=array_map("utf8_decode", $lignes);
$lignes=array_map("html_entity_decode_latin1", $lignes);

foreach ($lignes as $elem) {
    if ($type=="csv") {
        fputs($inF, "'$elem'\n");
    } else {
        fputs($inF, $elem."\n");
    }
}
fclose($inF);
