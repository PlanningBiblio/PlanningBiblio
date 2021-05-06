<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/ajax.menudiv.php
Création : mai 2011
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Christophe Le Guennec <Christophe.Leguennec@u-pem.fr>

Description :
Affiche le menu déroulant avec le nom des services et des agents dans la page planning/poste/index.php.
Permet de placer les agents dans les cellules du planning. Ecrit le nom des agents dans les cellules en JavaScript (innerHTML)
et met à jour la base de données en arrière plan avec la fonction JavaScript "bataille navale"

Cette page est appelée par la fonction ItemSelMenu(e) déclendhée lors d'un click-droit dans la page planning/poste/index.php
*/

session_start();

// TMP For test
$version = 'ajax';

ini_set("display_error", 0);

require_once "../../include/config.php";
require_once "../../include/function.php";
require_once "../../include/horaires.php";
require_once "../../absences/class.absences.php";
require_once "../../personnel/class.personnel.php";
require_once "fonctions.php";
require_once "class.planning.php";
require_once __DIR__."/../volants/class.volants.php";
require_once __DIR__."/../../init_ajax.php";

use App\Model\AbsenceReason;
use App\PlanningBiblio\WorkingHours;

//	Initilisation des variables
$site=filter_input(INPUT_GET, "site", FILTER_SANITIZE_NUMBER_INT);
$date=filter_input(INPUT_GET, "date", FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));
$debut=filter_input(INPUT_GET, "debut", FILTER_CALLBACK, array("options"=>"sanitize_time"));
$fin=filter_input(INPUT_GET, "fin", FILTER_CALLBACK, array("options"=>"sanitize_time"));
$perso_id=filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);
$perso_nom=filter_input(INPUT_GET, "perso_nom", FILTER_SANITIZE_STRING);
$poste=filter_input(INPUT_GET, "poste", FILTER_SANITIZE_NUMBER_INT);
$CSRFToken=trim(filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING));

$login_id=$_SESSION['login_id'];
$tab_exclus=array(0);
$absents=array(0);
$absences_non_validees = array(0);
$agents_qualif=array(0);
$tab_deja_place=array(0);
$journey = array();
$absences_journey = array();
$sr_init=null;
$exclusion = array();
$motifExclusion = array();
$tableaux=array();

$d=new datePl($date);
$j1=$d->dates[0];
$j7=$d->dates[6];
$semaine=$d->semaine;
$semaine3=$d->semaine3;

$break_countdown = ($config['PlanningHebdo'] && $config['PlanningHebdo-PauseLibre']) ? 1 : 0;
// PlanningHebdo et EDTSamedi étant incompatibles, EDTSamedi est désactivé si PlanningHebdo est activé
if ($config['PlanningHebdo']) {
    $config['EDTSamedi']=0;
}
  
//			----------------		Vérification des droits d'accès		-----------------------------//
$url=explode("?", $_SERVER['REQUEST_URI']);
$url=$url[0];
if (!$_SESSION['login_id']) {
    exit;
} else {
    $autorisation=false;
    $db_admin=new db();			// Vérifions si l'utilisateur à les droits de modifier les plannings
    $db_admin->select2("personnel", "droits", array("id"=>$login_id));
    $droits=json_decode(html_entity_decode($db_admin->result[0]['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
    if (!in_array((300+$site), $droits) and !in_array((1000+$site), $droits)) {
        exit;
    }
}
//			----------------		FIN Vérification des droits d'accès		-----------------------------//


// nom et activités du poste
$db=new db;
$db->select2("postes", null, array("id"=>$poste));
$posteNom=$db->result[0]['nom'];
$activites = json_decode(html_entity_decode($db->result[0]['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
$stat=$db->result[0]['statistiques'];
$teleworking = $db->result[0]['teleworking'];
$bloquant=$db->result[0]['bloquant'];
$categories = $db->result[0]['categories'] ? json_decode(html_entity_decode($db->result[0]['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();

// Nom du site
$siteNom=null;
if ($config['Multisites-nombre']>1) {
    $siteNom=$config["Multisites-site$site"];
}

// Liste des statuts correspondant aux catégories nécessaires pour être placé sur le poste
$categorie = null;
$categories_nb = 0;
$statuts=array();

if (!empty($categories)) {
    $categories=join(",", $categories);
    $db=new db();
    $categories=$db->escapeString($categories);
    $db->select("select_statuts", null, "categorie IN ($categories)");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $statuts[]=$elem['valeur'];
        }
    }
    $db=new db();
    $db->select2('select_categories', 'valeur', array('id' => "IN$categories"));

    $tmp = array();
    if ($db->result) {
        foreach ($db->result as $elem) {
            $tmp[] = str_replace('Cat&eacute;gorie ', null, $elem['valeur']);
        }
        $categorie = ' ('.implode(', ', $tmp).')';

        $categories_nb = $db->nb;
    }
}

//	Recherche des services
$db=new db();
$db->query("SELECT `{$dbprefix}personnel`.`service` AS `service`, `{$dbprefix}select_services`.`couleur` AS `couleur` FROM `{$dbprefix}personnel` INNER JOIN `{$dbprefix}select_services`
	ON `{$dbprefix}personnel`.`service`=`{$dbprefix}select_services`.`valeur` WHERE `{$dbprefix}personnel`.`service`<>'' GROUP BY `service`;");
$services=$db->result;
$services[]=array("service"=>"Sans service");

// Recherche des agents volants
if ($config['Planning-agents-volants']) {
    $v = new volants($date);
    $v->fetch($date);
    $agents_volants = $v->selected;
}

// Recherche des agents déjà postés à l'horaire choisi
// Ne pas regarder les postes non-bloquant et ne pas regarder si le poste est non-bloquant
if ($bloquant=='1') {
    $db=new db();
    $dateSQL=$db->escapeString($date);
    $debutSQL=$db->escapeString($debut);
    $finSQL=$db->escapeString($fin);

    $req="SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` FROM `{$dbprefix}pl_poste` "
    ."INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
    ."WHERE `{$dbprefix}pl_poste`.`debut`<'$finSQL' AND `{$dbprefix}pl_poste`.`fin`>'$debutSQL' "
        ."AND `{$dbprefix}pl_poste`.`date`='$dateSQL' AND `{$dbprefix}postes`.`bloquant`='1'";

    $db->query($req);
    if ($db->result) {
        foreach ($db->result as $elem) {
            $tab_exclus[]=$elem['perso_id'];
            $tab_deja_place[]=$elem['perso_id'];
        }
    }

    // Search for remote job (add journey time)
    if ($config['Journey-time-between-sites'] > 0) {
        $j_time = $config['Journey-time-between-sites'];
        $start_with_journey = date('H:i:s', strtotime("-$j_time minutes", strtotime($debutSQL)));
        $end_with_journey = date('H:i:s', strtotime("+$j_time minutes", strtotime($finSQL)));

        if ($config['Multisites-nombre'] > 1) {
            $req="SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` "
                . "FROM `{$dbprefix}pl_poste` "
                . "INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
                . "WHERE `{$dbprefix}pl_poste`.`debut`<'$end_with_journey' AND `{$dbprefix}pl_poste`.`fin`>'$start_with_journey' "
                . "AND `{$dbprefix}pl_poste`.`site` != $site "
                . "AND `{$dbprefix}pl_poste`.`date`='$dateSQL' AND `{$dbprefix}postes`.`bloquant`='1'";

            $db=new db();
            $db->query($req);
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $journey[] = $elem['perso_id'];
                }
            }
        }
    }

    if ($config['Journey-time-between-areas'] > 0) {
        $j_time = $config['Journey-time-between-areas'];
        $start_with_journey = date('H:i:s', strtotime("-$j_time minutes", strtotime($debutSQL)));
        $end_with_journey = date('H:i:s', strtotime("+$j_time minutes", strtotime($finSQL)));

        $req = "SELECT `tableau` FROM `{$dbprefix}pl_poste_tab_affect` WHERE `date` = '$dateSQL' AND `site` = $site";
        $db = new db();
        $db->query($req);
        $table_id = $db->result[0]['tableau'];

        $req = "SELECT `tableau` FROM `{$dbprefix}pl_poste_lignes` WHERE `numero` = $table_id AND `poste` = $poste AND `type` = 'poste'";
        $db = new db();
        $db->query($req);
        $sub_table_id = $db->result[0]['tableau'];

        $req = "SELECT `poste` FROM `{$dbprefix}pl_poste_lignes` WHERE `numero` = $table_id AND `tableau` != $sub_table_id AND `type` = 'poste'";
        $db = new db();
        $db->query($req);
        $autres_postes = array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $autres_postes[] = $elem['poste'];
            }
        }

        $req="SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` "
            . "FROM `{$dbprefix}pl_poste` "
            . "INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
            . "WHERE `{$dbprefix}pl_poste`.`debut`<'$end_with_journey' AND `{$dbprefix}pl_poste`.`fin`>'$start_with_journey' "
            . "AND `{$dbprefix}pl_poste`.`poste` IN (" . join(",", $autres_postes) . ") "
            . "AND `{$dbprefix}pl_poste`.`site` = $site "
            . "AND `{$dbprefix}pl_poste`.`date`='$dateSQL' AND `{$dbprefix}postes`.`bloquant`='1'";
        $db = new db();
        $db->query($req);
        if ($db->result) {
            foreach ($db->result as $elem) {
                $journey[] = $elem['perso_id'];
            }
        }
    }
}

if ($config['Journey-time-for-absences'] > 0) {
    $j_time = $config['Journey-time-for-absences'];
    $start_with_journey = date('Y-m-d H:i:s', strtotime("-$j_time minutes", strtotime($debutSQL)));
    $end_with_journey = date('Y-m-d H:i:s', strtotime("+$j_time minutes", strtotime($finSQL)));

    $a=new absences();
    $a->valide = true;
    $a->fetch(null, null, $start_with_journey, $end_with_journey, null);
    $absences=$a->elements;

    foreach ($absences as $absence) {
        $absences_journey[] = $absence['perso_id'];
    }
}

// Count day hours for all agent.
$day_hours = array();
if ($break_countdown) {
    $db=new db();
    $dateSQL=$db->escapeString($date);

    $db->query("SELECT perso_id, debut, fin FROM `{$dbprefix}pl_poste` WHERE date = '$dateSQL' AND supprime = '0';");
    if ($db->result) {
        foreach ($db->result as $elem) {
            // Get day duration as timestamp
            // for an easier comparison.
            $elem_duration = strtotime($elem['fin']) - strtotime($elem['debut']);

            if (!isset($day_hours[$elem['perso_id']])) {
                $day_hours[$elem['perso_id']] = 0;
            }

            $day_hours[$elem['perso_id']] += $elem_duration;
        }
    }
}

// recherche des personnes à exclure (absents)
$db=new db();
$dateSQL=$db->escapeString($date);
$debutSQL=$db->escapeString($debut);
$finSQL=$db->escapeString($fin);

$teleworking_exception = null;

if ($teleworking) {
    $teleworking_reasons = array();
    $absence_reasons = $entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
    foreach ($absence_reasons as $reason) {
        $teleworking_reasons[] = $reason->valeur();
    }
    $teleworking_exception = (!empty($teleworking_reasons) and is_array($teleworking_reasons)) ? "AND `motif` NOT IN ('" . implode("','", $teleworking_reasons) . "')" : null;
}

$db->select('absences', 'perso_id,valide', "`debut`<'$dateSQL $finSQL' AND `fin` >'$dateSQL $debutSQL' $teleworking_exception");

if ($db->result) {
    foreach ($db->result as $elem) {
        if ($elem['valide'] > 0 or $config['Absences-validation'] == '0') {
            $tab_exclus[]=$elem['perso_id'];
            $absents[]=$elem['perso_id'];
        } elseif ($config['Absences-non-validees']) {
            $absences_non_validees[] = $elem['perso_id'];
        }
    }
}

// recherche des personnes à exclure (congés)
if ($config['Conges-Enable']) {
    include "../../conges/menudiv.php";
}

// recherche des personnes à exclure (ne travaillant pas à cette heure)
$db=new db();
$dateSQL=$db->escapeString($date);

$db->query("SELECT * FROM `{$dbprefix}personnel` WHERE `actif` LIKE 'Actif' AND (`depart` >= '$dateSQL' OR `depart` = '0000-00-00');");

$verif=true;	// verification des heures des agents
if (!$config['ctrlHresAgents'] and ($d->position==6 or $d->position==0)) {
    $verif=false; // on ne verifie pas les heures des agents le samedi et le dimanche (Si ctrlHresAgents est desactivé)
}

// Si module PlanningHebdo : recherche des plannings correspondant à la date actuelle
if ($config['PlanningHebdo']) {
    require_once "../../planningHebdo/class.planningHebdo.php";
    $p=new planningHebdo();
    $p->debut=$date;
    $p->fin=$date;
    $p->valide=true;
    $p->fetch();

    $tempsPlanningHebdo=array();
    $breakTimes = array();

    if (!empty($p->elements)) {
        foreach ($p->elements as $elem) {
            $tempsPlanningHebdo[$elem["perso_id"]]=$elem["temps"];
            $breaktimes[$elem["perso_id"]] = $elem["breaktime"];
        }
    }
}

if ($db->result and $verif) {
    foreach ($db->result as $elem) {

  // Récupération du planning de présence
        $temps=array();

        // Si module PlanningHebdo : emploi du temps récupéré à partir de planningHebdo
        if ($config['PlanningHebdo']) {
            if (array_key_exists($elem['id'], $tempsPlanningHebdo)) {
                $temps=$tempsPlanningHebdo[$elem['id']];
            }
        } else {
            // Emploi du temps récupéré à partir de la table personnel
            $temps=json_decode(html_entity_decode($elem['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        }

        $jour = $d->planning_day_index_for($elem['id']);

        // Gestion des exclusions
        $exclusion[$elem['id']] = array();

        // Contrôle des heures de présence. Si l'agent n'est pas présent sur toute la plage horaire, type d'exclusion = horaires
        if (!calculSiPresent($debut, $fin, $temps, $jour)) {
            $exclusion[$elem['id']][]="horaires";
        }

        if ($break_countdown) {
            $day_hour = isset($day_hours[$elem['id']]) ? $day_hours[$elem['id']] : 0;
            $requested_hours = strtotime($fin) - strtotime($debut);

            $wh = new WorkingHours($temps);
            $tab = $wh->hoursOf($jour);

            $hours_limit = 0;
            foreach ($tab as $t) {
                $hours_limit += strtotime($t[1]) - strtotime($t[0]);
            }

            $breaktime = isset($breaktimes[$elem['id']][$jour]) ? $breaktimes[$elem['id']][$jour] * 3600 : 0;
            $hours_limit = $hours_limit - $breaktime;

            if ($day_hour + $requested_hours > $hours_limit) {
                $exclusion[$elem['id']][]="break";
            }

        }

        // Multisites : Contrôle si l'agent est prévu sur ce site.
        // Ce filtre concerne tous les agents, qu'ils soient amenés ou non à travailler sur ce site.
        // Un autre filtre éliminera complétement les agents pour lesquels le courant site n'est pas cochés dans leur onglet "infos générales".
        if ($config['Multisites-nombre']>1) {

            // Le champs correspondant à l'affectation du site est heures[4]
            $site_agent = !empty($temps[$jour][4]) ? $temps[$jour][4] : null;

            // Si le champ "site_agent" est vide et que l'agent n'a pas déjà été exclus pour un problème d'horaires, type d'exclusion = sites
            if (empty($site_agent) and !in_array('horaires', $exclusion[$elem['id']])) {
                $exclusion[$elem['id']][]="sites";
            }

            // Si le champs "site_agent" est renseigné mais qu'il ne correspond pas au site choisi, type d'exclusion = autre_site
            if (!empty($site_agent) and $site_agent != -1 and $site_agent != $site) {
                $exclusion[$elem['id']][]="autre_site";
            }
        }
    }
}

// Contrôle du personnel déjà placé dans la ligne
$deja=deja_place($date, $poste);

// Contrôle du personnel placé juste avant ou juste après la plage choisie
$deuxSP=deuxSP($date, $debut, $fin);

// Récupère le nombre d'agents déjà placés dans la cellule
$db=new db();
$dateSQL=$db->escapeString($date);
$debutSQL=$db->escapeString($debut);
$finSQL=$db->escapeString($fin);
$posteSQL=$db->escapeString($poste);
$siteSQL=$db->escapeString($site);

// Cellule grisée par depuis le menudiv
$cellule_grise = false;

$db->select("pl_poste", null, "`poste`='$posteSQL' AND `debut`='$debutSQL' AND `fin`='$finSQL' AND `date`='$dateSQL' AND `site`='$siteSQL'");

$nbAgents=0;
if ($db->result) {
    // On exclus les agents qui sont déjà dans cette cellule (important s'il s'agit d'un poste non-bloquant)
    foreach ($db->result as $elem) {
        if ($elem['perso_id'] > 0) {
            $tab_exclus[] = $elem['perso_id'];
            $nbAgents ++;
        }
        $cellule_grise = $elem['grise'] == 1 ? true : $cellule_grise;
    }
}
$exclus=join(',', $tab_exclus);

//--------------		Liste du personnel disponible			---------------//


// Recherche des agents disponibles
$agents_dispo=array();

$db=new db();
$dateSQL=$db->escapeString($date);

$req="SELECT * FROM `{$dbprefix}personnel` "
  ."WHERE `actif` LIKE 'Actif' AND `arrivee` <= '$dateSQL' AND (`depart` >= '$dateSQL' OR `depart` = '0000-00-00') "
  ."AND `id` NOT IN ($exclus) ORDER BY `nom`,`prenom`;";

$db->query($req);
$agents_tmp=$db->result;

if ($agents_tmp) {
    foreach ($agents_tmp as $elem) {

  // Elimine les agents non qualifiés
        if (is_array($activites)) {
            $postes = json_decode(html_entity_decode($elem['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            if (!is_array($postes)) {
                $exclusion[$elem['id']][] = 'activites';
            } else {
                foreach ($activites as $a) {
                    if (!in_array($a, $postes)) {
                        $exclusion[$elem['id']][] = 'activites';
                        break;
                    }
                }
            }
        }
    
        // Elimine les agents qui ne sont pas dans la catégorie requise
        if (!empty($statuts)) {
            if (!in_array($elem['statut'], $statuts)) {
                $exclusion[$elem['id']][] = 'categories';
            }
        }

        // Elimine les agents qui ne travaille pas sur ce site (en multi-sites)
        // Contrôle du champ "sites" de l'onglet "infos générales"
        // NOTE : Ce contrôle est réalisé plus bas pour une exclusion complète des agents mais nous devons le garder ici afin de les éliminer des agents disponibles
        if ($config['Multisites-nombre']>1) {
            $sites = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            if (!is_array($sites) or !in_array($site, $sites)) {
                $exclusion[$elem['id']][] = 'sites';
            }
        }

        // Distinction des agents volants pour $agents_dispo (et agents_tous)
        if ($config['Planning-agents-volants'] and in_array($elem['id'], $agents_volants)) {
            $elem['statut'] = 'volants';
        }

        // Si aucune exclusion n'est enregistrée, on met l'agent dans la liste des agents disponibles
        if (empty($exclusion[$elem['id']])) {
            $agents_dispo[] = $elem;
        }

        // Si au moins une exclusion est enregistrée, l'agent ne sera pas placé dans les agents disponibles.
        // On renseigne les motifs d'exclusions
        else {
            if (in_array('horaires', $exclusion[$elem['id']])) {
                $motifExclusion[$elem['id']][]="<span title='Les horaires de l&apos;agent ne lui permettent pas d&apos;occuper ce poste'>Horaires</span>";
            } elseif (in_array('break', $exclusion[$elem['id']])) {
                $motifExclusion[$elem['id']][]="<span title='La pause de cet agent n&apos;est pas respectée'>Pause</span>";
            }
            if (in_array('autre_site', $exclusion[$elem['id']])) {
                $motifExclusion[$elem['id']][]="<span title='L&apos;agent est pr&eacute;vu sur un autre site'>Autre site</span>";
            }
            if (in_array('sites', $exclusion[$elem['id']])) {
                $motifExclusion[$elem['id']][]="<span title='L&apos;agent n&apos;est pas pr&eacute;vu sur ce site'>Site</span>";
            }
            if (in_array('activites', $exclusion[$elem['id']])) {
                $motifExclusion[$elem['id']][]="<span title='L&apos;agent n&apos;a pas toutes les qualifications requises pour occuper ce poste'>Activit&eacute;s</span>";
            }
            if (in_array('categories', $exclusion[$elem['id']])) {
                if ($categories_nb > 1) {
                    $title = "L&apos;agent n&apos;appartient &agrave; aucune des cat&eacute;gories requises{$categorie} pour occuper ce poste";
                } else {
                    $title = "L&apos;agent n&apos;appartient pas &agrave; la cat&eacute;gorie requise{$categorie} pour occuper ce poste";
                }

                $motifExclusion[$elem['id']][]="<span title='$title'>Cat&eacute;gorie</span>";
            }
        }
    }
}

// Tableau agents tous = agents dispo. Sera complété ensuite
$agents_tous=$agents_dispo;


// Recherche des agents indisponibles
foreach ($agents_dispo as $elem) {
    $agents_qualif[]=$elem['id'];
}
$agents_qualif=join(',', $agents_qualif);
$absents=join(',', $absents);
$tab_deja_place=join(',', $tab_deja_place);

$db=new db();
$dateSQL=$db->escapeString($date);

$req="SELECT * FROM `{$dbprefix}personnel` "
  ."WHERE `actif` LIKE 'Actif' AND `arrivee` <= '$dateSQL' AND (`depart` >= '$dateSQL' OR `depart` = '0000-00-00') AND `id` NOT IN ($agents_qualif) "
  ."AND `id` NOT IN ($tab_deja_place) AND `id` NOT IN ($absents)  ORDER BY `nom`,`prenom`;";


$db->query($req);
$autres_agents_tmp = $db->result;

$autres_agents=array();
if ($autres_agents_tmp) {
    foreach ($autres_agents_tmp as $elem) {
        // Elimine complétement les agents qui ne travaillent pas sur ce site (en multi-sites) (ne seront pas dans les indisponibles)
        // Contrôle du champ "sites" de l'onglet "infos générales"
        // NOTE Le même contrôle est réalisé plus haut afin d'éliminer les agents de la liste des agents disponibles. Ce 2ème contrôle élimine complétement les agents (pas dans les indisponibles)
        if ($config['Multisites-nombre']>1) {
            $sites = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            if (!is_array($sites) or !in_array($site, $sites)) {
                continue;
            }
        }

        // Distinction des agents volants pour $autres_agents (et agents_tous)
        if ($config['Planning-agents-volants'] and in_array($elem['id'], $agents_volants)) {
            $elem['statut'] = 'volants';
        }

        // Complète la liste des indisponibles et la liste de tous les agents
        $autres_agents[] = $elem;
        $agents_tous[]=$elem;
    }
}

// Creation des différentes listes (par service + liste des absents + liste des non qualifiés)
// Affichage par service
$newtab=array();
if ($agents_dispo) {
    foreach ($agents_dispo as $elem) {
        if ($elem['id']!=2) {
            if (!trim($elem['service'])) {
                $newtab["Sans service"][]=$elem['id'];
            } else {
                $newtab[$elem['service']][]=$elem['id'];
            }
        }
    }
}

if ($autres_agents) {
    foreach ($autres_agents as $elem) {
        if ($elem['id']!=2) {
            $newtab["Autres"][]=$elem['id'];
        }  		// Affichage des agents hors horaires, non qualifiés
    }
}

$listparservices=array();
if (is_array($services)) {
    foreach ($services as $elem) {
        if (array_key_exists($elem['service'], $newtab)) {
            $listparservices[]=join(',', $newtab[$elem['service']]);
        } else {
            $listparservices[]=null;
        }
    }
}

if (array_key_exists("Autres", $newtab)) {
    $listparservices[]=join(',', $newtab['Autres']);
} else {
    $listparservices[]=null;
}
$tab_agent=join(';', $listparservices);
    
// début d'affichage
$tableaux['position_name'] = $posteNom;
$tableaux['position_id'] = $poste;
$tableaux['date'] = $date;
$tableaux['start'] = $debut;
$tableaux['start_hr'] = heure2($debut);
$tableaux['end'] = $fin;
$tableaux['end_hr'] = heure2($fin);
$tableaux['site'] = $site;
$tableaux['site_name'] = $siteNom ? $siteNom : '';
$tableaux['tab_agent'] = $tab_agent;
$tableaux['group_tab_hide'] = $config['ClasseParService'] ? 1 : 0;
$tableaux['everybody'] = $config['toutlemonde'] ? 1 : 0;
$tableaux['cell_enabled'] = $cellule_grise ? 0 : 1;
$tableaux['nb_agents'] = $nbAgents;
$tableaux['agent_id'] = $perso_id;
$tableaux['agent_name'] = $perso_nom;
$tableaux['call_for_help'] = $config['Planning-AppelDispo'] ? 1 : 0;
$tableaux['can_disable_cell'] = in_array( 900 + $site, $droits) ? 1 : 0;

$tableaux['menu1'] = array();

//		-----------		Affichage de la liste des services		----------//
if ($services and $config['ClasseParService']) {
    $tableaux['services'] = array();
    $i=0;
    foreach ($services as $elem) {
        if (array_key_exists($elem['service'], $newtab) and !$cellule_grise) {
            $elem['class'] = "service_".strtolower(removeAccents(str_replace(" ", "_", $elem['service'])));
            $elem['tab_agent'] = $tab_agent;
            $elem['id'] = $i;
            $tableaux['services'][] = $elem;
        }
        $i++;
    }
}

//		-----------		Affichage de la liste des agents s'ils ne sont pas classés par services		----------//
if (!$config['ClasseParService'] and !$cellule_grise) {
    $hide=false;
    $p=new planning();
    $p->site=$site;
    $p->CSRFToken = $CSRFToken;
    $p->menudivAfficheAgents($poste, $agents_dispo, $date, $debut, $fin, $deja, $stat, $nbAgents, $sr_init, $hide, $deuxSP, $motifExclusion, $absences_non_validees, $journey, $absences_journey);
    $tableaux['menu1']['agents']=$p->menudiv;
}

if (array_key_exists("Autres", $newtab) and $config['agentsIndispo'] and !$cellule_grise) {
    $tableaux['unavailables_agents'] = array('id' => count($services));
}

//		-----------		Affichage de l'utilisateur "tout le monde"		----------//
if ($config['toutlemonde'] and !$cellule_grise) {
}

//~ -----				Affiche de la "Case vide"  (suppression)	--------------------------//
if ($nbAgents>0 and !$cellule_grise) {
}

// Ajout du lien pour les appels à disponibilité
if ($config['Planning-AppelDispo'] and !$cellule_grise) {
    // Consulte la base de données pour savoir si un mail a déjà été envoyé
    $db=new db();
    $db->select2("appel_dispo", null, array("site"=>$site,"poste"=>$poste,"date"=>$date,"debut"=>$debut,"fin"=>$fin), "ORDER BY `timestamp` desc");
    $nbEnvoi=$db->nb;
    $nbEnvoiInfo = '';
    if ($db->result) {
        $dateEnvoi=dateFr($db->result[0]['timestamp']);
        $heureEnvoi=heure2(substr($db->result[0]['timestamp'], 11, 5));
        $destinataires=count(explode(";", $db->result[0]['destinataires']));
        $s=$destinataires>1?"s":null;

        $nbEnvoiInfo="L&apos;appel &agrave; disponibilit&eacute; a d&eacute;j&agrave; &eacute;t&eacute; envoy&eacute; $nbEnvoi fois&#013;";
        $nbEnvoiInfo.="Dernier envoi le $dateEnvoi &agrave; $heureEnvoi&#013;";
        $nbEnvoiInfo.="$destinataires personne{$s} contact&eacute;e{$s}";
    }

    $agents_appel_dispo = array();
    foreach ($agents_dispo as $a) {
        $agents_appel_dispo[]=array('id'=> $a['id'], 'nom'=> $a['nom'], 'prenom'=> $a['prenom'], 'mail' => $a['mail']);
    }
    $agents_appel_dispo=json_encode($agents_appel_dispo);
    $agents_appel_dispo=htmlentities($agents_appel_dispo, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);

    $tableaux['call_for_help_info'] = $nbEnvoiInfo;
    $tableaux['call_for_help_nb'] = $nbEnvoi;
    $tableaux['call_for_help_agents'] = $agents_appel_dispo;
}


//$tableaux[0].="</table>\n";

//	--------------		Affichage des agents			----------------//
//$tableaux[1]="<table cellspacing='0' cellpadding='0' id='menudivtab2' rules='rows' border='1'>\n";

//		-----------		Affichage de la liste des agents s'ils sont classés par services		----------//
$tableaux['menu2'] = array();
if ($agents_tous and $config['ClasseParService']) {
    $hide=true;
    $p=new planning();
    $p->site=$site;
    $p->CSRFToken = $CSRFToken;
    $p->menudivAfficheAgents($poste, $agents_tous, $date, $debut, $fin, $deja, $stat, $nbAgents, $sr_init, $hide, $deuxSP, $motifExclusion, $absences_non_validees, $journey, $absences_journey);
//$foo = $p->menudiv;
//var_dump($foo);
    $tableaux['menu2']=$p->menudiv;
}

//		-----------		Affichage de la liste des agents indisponibles 'ils ne sont pas classés par services	----------//
if ($autres_agents and !$config['ClasseParService'] and $config['agentsIndispo']) {
    $hide=true;
    $p=new planning();
    $p->site=$site;
    $p->CSRFToken = $CSRFToken;
    $p->menudivAfficheAgents($poste, $autres_agents, $date, $debut, $fin, $deja, $stat, $nbAgents, $sr_init, $hide, $deuxSP, $motifExclusion, $absences_non_validees, $journey, $absences_journey);
    $tableaux['menu2']=$p->menudiv;
}

//--------------		FIN Liste du personnel disponible			---------------//
echo json_encode($tableaux);
