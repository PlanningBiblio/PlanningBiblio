<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\PlanningBiblio\PresentSet;

$version = 'symfony';

include_once __DIR__ . "/../../public/conges/class.conges.php";
include_once __DIR__ . "/../../public/include/function.php";
require_once __DIR__ . "/../../public/include/db.php";
require_once __DIR__ . "/../../public/include/horaires.php";
include_once __DIR__ . '/../../public/statistiques/class.statistiques.php';
include_once __DIR__ . '/../../public/absences/class.absences.php';

class StatisticController extends BaseController
{
    /**
     * @Route("/statistics/positionsummary", name="statistics.positionsummary", methods={"GET", "POST"})
     */
    public function positionsummary(Request $request, Session $session)
    {
        //	Variables :
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $tri = $request->get("tri");
        $post = $request->request->all();
        $nbSites = $this->config('Multisites-nombre');
        $dbprefix = $GLOBALS['dbprefix'];

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_postes = isset($post['postes'])?$post['postes']:null;
        $post_sites = isset($post['selectedSites'])?$post['selectedSites']:null;

        $joursParSemaine = $this->config('Dimanche') ? 7 : 6;

        if (!$debut and array_key_exists('stat_debut', $_SESSION)) {
            $debut = $_SESSION['stat_debut'];
        }
        if (!$fin and array_key_exists('stat_fin', $_SESSION)) {
            $fin = $_SESSION['stat_fin'];
        }
        if (!$tri and array_key_exists('stat_poste_tri', $_SESSION)) {
            $tri = $_SESSION['stat_poste_tri'];
        }

        if (!$debut) {
            $debut = "01/01/".date("Y");
        }
        if (!$fin) {
            $fin = date("d/m/Y");
        }
        if (!$tri) {
            $tri = "cmp_01";
        }

        $_SESSION['stat_debut'] = $debut;
        $_SESSION['stat_fin'] = $fin;
        $_SESSION['stat_poste_tri'] = $tri;

        $debutSQL = dateFr($debut);
        $finSQL = dateFr($fin);

        // Postes
        if (!array_key_exists('stat_poste_postes', $_SESSION)) {
            $_SESSION['stat_poste_postes'] = null;
        }

        $postes=array();
        if ($post_postes) {
            foreach ($post_postes as $elem) {
                $postes[] = $elem;
            }
        } else {
            $postes = $_SESSION['stat_poste_postes'];
        }
        $_SESSION['stat_poste_postes']=$postes;

        // Filtre les sites
        if (!array_key_exists('stat_poste_sites', $_SESSION)) {
            $_SESSION['stat_poste_sites'] = array();
        }

        if ($post_sites) {
            $selectedSites = array();
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION['stat_poste_sites'];
        }

        $_SESSION['stat_poste_postes'] = $postes;

        // Filtre les sites
        if (!array_key_exists('stat_poste_sites', $_SESSION)) {
            $_SESSION['stat_poste_sites'] = null;
        }

        if ($nbSites>1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }
        $_SESSION['stat_poste_sites'] = $selectedSites;
        
        // Filtre les sites dans les requêtes SQL
        if ($nbSites>1 and is_array($selectedSites)) {
            $sitesSQL =" 0,".join(",", $selectedSites);
        } else {
            $sitesSQL = "0,1";
        }

        $tab = array();

        $total_heures = 0;
        $total_jour = 0;
        $total_hebdo = 0;
        $selected = null;

        //		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
        $db = new \db();
        $db->query("SELECT * FROM `{$dbprefix}postes` WHERE `statistiques`='1' ORDER BY `etage`,`nom`;");
        $postes_list = $db->result;

        if (!empty($postes)) {
            //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
            //	On stock le tout dans le tableau $resultat
            $postes_select = join(",", $postes);
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);
            $postesREQ = $db->escapeString($postes_select);

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB=$a->elements;

            $req = "SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
            `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
            `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
            `{$dbprefix}personnel`.`id` as `perso_id`, `{$dbprefix}pl_poste`.site as `site` 
            FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` 
            ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
            WHERE `{$dbprefix}pl_poste`.`date`>='$debutREQ' AND `{$dbprefix}pl_poste`.`date`<='$finREQ' 
            AND `{$dbprefix}pl_poste`.`poste` IN ($postesREQ) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
            AND `{$dbprefix}pl_poste`.`supprime`<>'1'  AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ) 
            ORDER BY `poste`,`nom`,`prenom`;";
            $db->query($req);
            $resultat = $db->result;

            //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel) pour chaque poste sélectionné
            foreach ($postes as $poste) {
                if (array_key_exists($poste, $tab)) {
                    $heures = $tab[$poste][2];
                    $sites = $tab[$poste]["sites"];
                } else {
                    $heures = 0;
                    for ($i = 1; $i <= $nbSites; $i++) {
                        $sites[$i] = 0;
                    }
                }
                $agents = array();
                if (is_array($resultat)) {
                    foreach ($resultat as $elem) {
                        if ($poste == $elem['poste']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent : continue
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {
                                foreach ($absencesDB[$elem['perso_id']] as $a) {
                                    if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                        continue 2;
                                    }
                                }
                            }
                            //	On créé un tableau par agent avec son nom, prénom et la somme des heures faites par poste
                            if (!array_key_exists($elem['perso_id'], $agents)) {
                                $agents[$elem['perso_id']] = array($elem['perso_id'], $elem['nom'], $elem['prenom'], 0, "site"=>$elem['site']);
                            }
                            $agents[$elem['perso_id']][3] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                            // On compte les heures de chaque site
                            if ($nbSites>1) {
                                $sites[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                            }
                            // On compte toutes les heures (globales)
                            $heures += diff_heures($elem['debut'], $elem['fin'], "decimal");
                
                            foreach ($postes_list as $elem2) {
                                if ($elem2['id'] == $poste) {	// on créé un tableau avec le nom et l'étage du poste.
                                    $poste_tab=  array($poste, $elem2['nom'], $elem2['etage'], $elem2['obligatoire']);
                                    break;
                                }
                            }
                            //	On met dans tab tous les éléments (infos postes + agents + heures du poste)
                            $tab[$poste] = array($poste_tab,$agents,$heures,"sites"=>$sites);
                        }
                    }
                }
            }
        }

        // Heures et jours d'ouverture au public
        $s = new \statistiques();
        $s->debut = $debutSQL;
        $s->fin = $finSQL;
        $s->joursParSemaine = $joursParSemaine;
        $s->selectedSites = $selectedSites;
        $s->ouverture();
        $ouverture = $s->ouvertureTexte;

        //		-------------		Tri du tableau		------------------------------
        usort($tab, $tri);

        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;
        $multisites = array();
        if ($nbSites > 1) {
            for ($i=1;$i<=$nbSites;$i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }
        
        if ($tab) {
            //	Recherche du nombre de jours concernés
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);
        
            $db->select("pl_poste", "`date`", "`date` BETWEEN '$debutREQ' AND '$finREQ' AND `site` IN ($sitesREQ)", "GROUP BY `date`;");
            $nbJours = $db->nb;
        
            foreach ($tab as &$elem) {
                $jour=$elem[2]/$nbJours;
                $hebdo=$jour*$joursParSemaine;
                $total_heures+=$elem[2];
                $total_jour+=$jour;
                $total_hebdo+=$hebdo;
        
                // Sites
                $siteEtage=array();
                if ($nbSites > 1) {
                    for ($i = 1; $i <= $nbSites; $i++) {
                        if ($elem["sites"][$i]==$elem[2]) {
                            $siteEtage[]=$multisites[$i];
                            continue;
                        }
                    }
                }
                // Etages
                if ($elem[0][2]) {
                    $siteEtage[]=$elem[0][2];
                }
                if (!empty($siteEtage)) {
                    $siteEtage="(".join(" ", $siteEtage).")";
                } else {
                    $siteEtage=null;
                }

                $elem["siteEtage"] = $siteEtage;
                $elem[2] = heure4($elem[2]);
                $elem["jour"] = heure4($jour);
                $elem["hebdo"] = heure4($hebdo);

            }
        
            $total_heures = heure4($total_heures);
            $total_jour = heure4($total_jour);
            $total_hebdo = heure4($total_hebdo);
        }
        
        $this->templateParams(
            array(
                "debut" => $debut,
                "fin" => $fin,
                "multisites" => $multisites,
                "nbSites" => $nbSites,
                "ouverture" => $ouverture,
                "postes" => $postes,
                "postes_list" => $postes_list,
                "selectedSites" => $selectedSites,
                "tab" => $tab,
                "total_hebdo" => $total_hebdo,
                "total_heures" => $total_heures,
                "total_jour"  => $total_jour,
                "tri" => $tri
            )
        );
        return $this->output('statistics/positionsummary.html.twig');
    }
    /**
     * @Route("/statistics/attendeesmissing", name="statistics.attendeesmissing", methods={"GET", "POST"})
     */
    public function attendeesmissing( Request $request, Session $session )
    {
        $params = $request->request->all();
        if ($request->get('reset')) {
            $params['reset'] = 1;
        }

        if (empty($params) and !empty($_SESSION['present_absent_from'])) {
            $params['from'] = $_SESSION['present_absent_from'];
            $params['to'] = $_SESSION['present_absent_to'];
        }

        if (empty($params) || isset($params['reset'])) {
            $params['from'] = date('d/m/Y');
            $params['to'] = date('d/m/Y');
        }

        $_SESSION['present_absent_from'] = $params['from'];
        $_SESSION['present_absent_to'] = $params['to'];

        $startTime = strtotime(dateSQL($params['from']));
        $endTime = strtotime(dateSQL($params['to']));

        $by_date = array();
        for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) {
            $date = date('Y-m-d', $i);

            $conges = array();
            if ($this->config('Conges-Enable')) {
                $c = new \conges();
                $conges = $c->all($date.' 00:00:00', $date.' 23:59:59');
            }

            $absences = new \absences();
            $absences->valide = false;
            $absent_ids = array(2);
            $absences->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date, array(1));
            $absents = $absences->elements;

            foreach ($conges as $elem) {
                $elem['motif']="Congé payé";
                $absents[] = $elem;
            }

            foreach ($absents as $key => $absent) {
                $absents[$key]['motif'] = html_entity_decode($absent['motif'], ENT_QUOTES|ENT_HTML5);
                preg_match('/00:00:00$/', $absent['debut'], $matche_start, PREG_OFFSET_CAPTURE);
                preg_match('/23:59:59$/', $absent['fin'], $matche_end, PREG_OFFSET_CAPTURE);
                if ($matche_start && $matche_end) {
                    $absents[$key]['all_the_day'] = 1;
                } else {
                    $absents[$key]['from'] = substr($absent['debutAff'], -5);
                    $absents[$key]['to'] = substr($absent['finAff'], -5);
                    $absents[$key]['all_the_day'] = 0;
                }

                if ($absent['debut'] <= $date . " 00:00:00"
                    and $absent['fin'] >= $date . " 23:59:59"
                    and $absent['valide'] > 0) {
                    $absent_ids[] = $absent['perso_id'];
                }
            }

            $d = new \datePL($date);
            $presentset = new PresentSet($date, $d, $absent_ids, new \db());
            $presents = $presentset->all();
            foreach ($presents as $key => $present) {
                $presents[$key]['heures'] = html_entity_decode($present['heures'], ENT_QUOTES|ENT_HTML5);
            }

            // Gather attendance and absences in a single table
            $tab = array();
            foreach ($presents as $present) {
                $tab[$present['id']] = array('nom' => $present['nom'], 'prenom' => null, 'presence' => $present);
            }

            foreach ($absents as $absent) {
                if ($absent['valide'] < 0 ) {
                    continue;
                }
                if (empty($tab[$absent['perso_id']])) {
                    $tab[$absent['perso_id']] = array('nom' => $absent['nom'], 'prenom' => $absent['prenom']);
                }
                $tab[$absent['perso_id']]['absences'][] = $absent;
            }

            $by_date[] = array(
                'date' => date('d/m/Y', $i),
                'tab' => $tab,
            );
        }

        $this->templateParams(array(
            'by_date'   => $by_date,
            'from'      => $params['from'],
            'to'        => $params['to']
        ));

        return $this->output('statistics/attendeesmissing.html.twig');
    }
}
