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
include_once __DIR__ . '/../../public/statistiques/class.statistiques.php';
include_once __DIR__ . '/../../public/absences/class.absences.php';

class StatisticController extends BaseController
{
     /**
     * @Route("/statistics/absence", name="statistics.absence", methods={"GET", "POST"})
     */
    public function absence( Request $request, Session $session )
    {
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $site = $request->get('site');
                
        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        
        $afficheHeures = $this->config('PlanningHebdo') ? true : false;

        if ($debut) {
            $fin = $fin ? $fin : $debut;
            $site = $site ? $site : 0;
        } elseif (array_key_exists("stat_absences_debut", $_SESSION['oups'])) {
            $debut = $_SESSION['oups']['stat_absences_debut'];
            $fin = $_SESSION['oups']['stat_absences_fin'];
            $site = isset($_SESSION['oups']['stat_absences_site']) ? $_SESSION['oups']['stat_absences_site'] : 0;
        } else {
            $date = $_SESSION['PLdate'];
            $d = new \datePl($date);
            $debut = dateFr($d->dates[0]);
            $fin = $config['Dimanche'] ? dateFr($d->dates[6]) : dateFr($d->dates[5]);
            $site = 0;
        }
        $_SESSION['oups']['stat_absences_debut'] = $debut;
        $_SESSION['oups']['stat_absences_fin'] = $fin;
        
        $debutSQL = dateSQL($debut);
        $finSQL = dateSQL($fin);
        
        $sites = null;
        $nbSites = $this->config('Multisites-nombre');;
        if ($nbSites > 1) {
            $sites = array();
            if ($site == 0) {
                for ($i = 1;$i <= $nbSites; $i++) {
                    $sites[] = $i;
                }
            } else {
                $sites = array($site);
            }
        }
        
        // Recherche des absences
        $a = new \absences();
        $a->valide = true;
        $a->fetch("`debut`,`fin`,`nom`,`prenom`", null, $debutSQL, $finSQL, $sites);
        $absences = $a->elements;
        
        // Recherche des motifs d'absences
        $motifs = array();
        if (is_array($absences) and !empty($absences)) {
            foreach ($absences as $elem) {
                if (!in_array($elem['motif'], $motifs)) {
                    $motifs[] = $elem['motif'];
                }
            }
        }
        sort($motifs);
        
        // Regroupe les absences par agent et par motif
        // Et ajoute les heures correspondantes
        $tab = array();
        $totaux = array("_general"=>0,"_generalHeures"=>0);
        foreach ($absences as $elem) {
            if (!array_key_exists($elem['perso_id'], $tab)) {
                $tab[$elem['perso_id']] = array(
                    "nom"         => $elem['nom'],
                    "prenom"      => $elem['prenom'],
                    "total"       => 0,
                    "totalHeures" => 0
                );
            }
            if (!array_key_exists($elem['motif'], $tab[$elem['perso_id']])) {
                $tab[$elem['perso_id']][$elem['motif']] = array(
                    "total"  => 0,
                    "heures" => 0
                );
            }
            if (!array_key_exists($elem['motif'], $totaux)) {
                $totaux[$elem['motif']] = array(
                    "frequence" => 0,
                    "heures"    => 0
                );
            }
          
            // Total agent
            $tab[$elem['perso_id']]['total']++;
            // Totaux généraux
            $totaux['_general']++;
            // Total agent pour le motif courant
            $tab[$elem['perso_id']][$elem['motif']]['total']++;
            // Total pour ce motif
            $totaux[$elem['motif']]['frequence']++;
        
            // Ajout des heures d'absences
            if ($afficheHeures) {
                $a = new \absences();
                $a->calculTemps($elem['debut'], $elem['fin'], $elem['perso_id']);
                $heures = $a->heures;
        
                // heures agent pour le motif courant
                if ($a->error) {
                    $tab[$elem['perso_id']][$elem['motif']]['heures'] = "Erreur";
                } elseif (is_numeric($tab[$elem['perso_id']][$elem['motif']]['heures'])) {
                    $tab[$elem['perso_id']][$elem['motif']]['heures'] += $heures;
                }
                // Total heures pour ce motif
                if ($a->error) {
                    $totaux[$elem['motif']]['heures'] = "Erreur";
                } elseif (is_numeric($totaux[$elem['motif']]['heures'])) {
                    $totaux[$elem['motif']]['heures'] += $heures;
                }
        
                if ($a->error) {
                    // Total heures agent
                    $tab[$elem['perso_id']]['totalHeures'] = "Erreur";
                    // Totaux heures généraux
                    $totaux['_generalHeures'] = "Erreur";
                } else {
                    // Total heures agent
                    if (is_numeric($tab[$elem['perso_id']]['totalHeures'])) {
                        $tab[$elem['perso_id']]['totalHeures'] += $heures;
                    }
                    // Totaux heures généraux
                    if (is_numeric($totaux['_generalHeures'])) {
                        $totaux['_generalHeures'] += $heures;
                    }
                }
            }
        }
        
        // Pour les exports
        $_SESSION['oups']['stat_absences_motifs'] = $motifs;
        $_SESSION['oups']['stat_absences_totaux'] = $totaux;
        $_SESSION['stat_tab']=$tab;
        
        // Affichage du tableau
        $multisites = array();
        $selectedSites = array();
        if ($nbSites >1) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i == $site ?? $i;
                $multisites[] = $this->config("Multisites-site{$i}");
            }
        }
      
        foreach ($tab as &$elem) {
            foreach ($motifs as $motif) {
                if (in_array($motif, $elem)){
                    $elem[$motif]['heures'] = is_numeric($elem[$motif]['heures']) ? heure4($elem[$motif]['heures']) : "Erreur";
                }
            }
        }
        
        $totaux['_generalHeures'] = is_numeric($totaux['_generalHeures']) ? heure4($totaux['_generalHeures']) : "Erreur";
            
        foreach ($motifs as $motif) {
            $totaux[$motif]['heures'] = is_numeric($totaux[$motif]['heures']) ? heure4($totaux[$motif]['heures']) : "Erreur";
        }

        $this->templateParams(array(
          "afficheHeures"   => $afficheHeures,
          "debut"           => $debut,
          "fin"             => $fin,
          "motifs"          => $motifs,
          "multisites"      => $multisites,
          "nbSites"         => $nbSites,
          "selectedSites"   => $selectedSites,
          "tab"             => $tab,
          "totaux"          => $totaux
        ));
        return $this->output('statistics/absence.html.twig');
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
