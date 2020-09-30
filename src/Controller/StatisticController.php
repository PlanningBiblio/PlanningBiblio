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
require_once __DIR__ . "/../../public/statistiques/class.statistiques.php";
include_once __DIR__ . '/../../public/absences/class.absences.php';
include_once __DIR__ . '/../../public/postes/class.postes.php';
include_once __DIR__ . '/../../public/personnel/class.personnel.php';

class StatisticController extends BaseController
{

    /**
     * @Route("/statistics/position", name="statistics.position", methods={"GET", "POST"})
     */
    public function position(Request $request, Session $session)
    {
        // Initialisation des variables :
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $tri = $request->get("tri");
        $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_NUMBER_INT);

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_postes = isset($post['postes']) ? $post['postes'] : null;
        $post_sites = isset($post['selectedSites']) ? $post['selectedSites'] : null;

        $joursParSemaine = $this->config('Dimanche') ? 7 : 6;

        $nbSites = $this->config('Multisites-nombre');

        if (!array_key_exists('stat_poste_postes', $_SESSION)) {
            $_SESSION['stat_poste_postes'] = null;
            $_SESSION['stat_poste_tri'] = null;
        }

        if (!$debut and array_key_exists("stat_debut", $_SESSION)) {
            $debut = $_SESSION['stat_debut'];
        }

        if (!$fin and array_key_exists("stat_fin", $_SESSION)) {
            $fin = $_SESSION['stat_fin'];
        }

        if (!$tri and array_key_exists("stat_poste_tri", $_SESSION)) {
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
        $postes = array();

        if ($post_postes) {
            foreach ($post_postes as $elem) {
                $postes[] = $elem;
            }
        } else {
            $postes = $_SESSION['stat_poste_postes'];
        }
        $_SESSION['stat_poste_postes'] = $postes;

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


        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }
        
        $multisites= [];
        if ($nbSites > 1){
            for ($i = 1; $i <= $nbSites; $i++){
                $multisites[] = $this->config("Multisites-site{$i}");
            }
        }

        $_SESSION['stat_poste_sites'] = $selectedSites;

        // Filtre les sites dans les requêtes SQL
        if ($nbSites > 1 and is_array($selectedSites)) {
            $sitesSQL = "0,".join(",", $selectedSites);
        } else {
            $sitesSQL = "0,1";
        }

        $tab = array();

        // Récupération des infos sur les agents
        $p = new \personnel();
        $p->fetch();
        $agents_infos = $p->elements;

        //-------------- Récupération de la liste des postes pour le menu déroulant ------------------------
        $db = new \db();
        $db->select2("postes", "*", array("statistiques"=>"1"), "ORDER BY `etage`,`nom`");
        $postes_list = $db->result;

        if (!empty($postes)) {
            //    Recherche du nombre de jours concernés
            $db = new \db();
            $db->select2("pl_poste", "date", array("date"=>"BETWEEN{$debutSQL}AND{$finSQL}", "site"=>"IN{$sitesSQL}"), "GROUP BY `date`;");
            $nbJours = $db->nb;

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            //    Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
            //    On stock le tout dans le tableau $resultat
            $postes_select = join(",", $postes);
            $db = new \db();

            $db->selectInnerJoin(
                array("pl_poste","perso_id"),
                array("personnel","id"),
                array("debut","fin","date","poste","site"),
                array("nom","prenom",array("name"=>"id", "as"=>"perso_id")),
                array("date"=>"BETWEEN{$debutSQL}AND{$finSQL}", "poste"=>"IN{$postes_select}","absent"=>"<>1","supprime"=>"<>1", "site"=>"IN{$sitesSQL}"),
                array(),
                "ORDER BY `poste`,`nom`,`prenom`"
            );

            $resultat = $db->result;


            //    Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel)
            //    pour chaques postes sélectionnés
            foreach ($postes as $poste) {
                if (array_key_exists($poste, $tab)) {
                    $heures = $tab[$poste][2];
                    $sites = $tab[$poste]["sites"];
                } else {
                    $heures = 0;
                    for ($i = 1; $i<= $nbSites; $i++) {
                        $sites[$i] = 0;
                    }
                }
                $agents = array();
                $services = array();
                $statuts = array();
                if (is_array($resultat)) {
                    foreach ($resultat as $elem) {
                        if ($poste == $elem['poste']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent : continue
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {
                                foreach ($absencesDB[$elem['perso_id']] as $a) {
                                    if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                        continue 2;
                                    }
                                }
                            }

                            // on créé un tableau par agent avec son nom, prénom et la somme des heures faites par poste
                            if (!array_key_exists($elem['perso_id'], $agents)) {
                                $agents[$elem['perso_id']] = array($elem['perso_id'],$elem['nom'],$elem['prenom'],0,"site" => $elem['site']);
                            }
                            $agents[$elem['perso_id']][3] += diff_heures($elem['debut'], $elem['fin'], "decimal");

                            // On compte les heures de chaque site
                            if ($nbSites > 1) {
                                $sites[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                            }

                            // On compte toutes les heures (globales)
                            $heures += diff_heures($elem['debut'], $elem['fin'], "decimal");

                            foreach ($postes_list as $elem2) {
                                if ($elem2['id'] == $poste) {    // on créé un tableau avec le nom et l'étage du poste.
                                    $poste_tab = array($poste,$elem2['nom'],$elem2['etage'],$elem2['obligatoire']);
                                    break;
                                }
                            }

                            // On créé un tableau par service
                            if (array_key_exists($elem['perso_id'], $agents_infos)) {
                                $service = $agents_infos[$elem['perso_id']]['service'];
                            }
                            $service = isset($service) ? $service : "ZZZ_Autre";
                            if (!array_key_exists($service, $services)) {
                                $services[$service] = array("nom"=>$service,"heures"=>0);
                            }
                            $services[$service]["heures"] += diff_heures($elem['debut'], $elem['fin'], "decimal");

                            // On créé un tableau par statut
                            if (array_key_exists($elem['perso_id'], $agents_infos)) {
                                $statut = $agents_infos[$elem['perso_id']]['statut'];
                            }
                            $statut = isset($statut) ? $statut : "ZZZ_Autre";
                             if (!array_key_exists($statut, $statuts)) {
                                $statuts[$statut] = array("nom" => $statut, "heures" => 0);
                            }
                            $statuts[$statut]["heures"] += diff_heures($elem['debut'], $elem['fin'], "decimal");

                            // On met dans tab tous les éléments (infos postes + agents + heures du poste)
                            $tab[$poste] = array($poste_tab, $agents, $heures, "services" => $services, "statuts" => $statuts, "sites" => $sites);
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

        //        -------------        Tri du tableau        ------------------------------
        usort($tab, $tri);

        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;

        if($tab){
            foreach($tab as $elem){
                $siteEtage=array();
                if ($nbSites >1) {
                    for ($i = 1; $i <= $nbSites; $i++) {
                        if ($elem["sites"][$i]==$elem[2]) {
                            $siteEtage[] = $this->config("Multisites-site{$i}");
                            continue;
                        }
                    }
                }
                if ($elem[0][2]) {
                    $siteEtage[]=$elem[0][2];
                }
                if (!empty($siteEtage)) {
                    $siteEtage="(".join(" ", $siteEtage).")";
                } else {
                    $siteEtage=null;
                }
                $jour = heure4($elem[2]/$nbJours);
                $hebdo = heure4($jour*$joursParSemaine);
                $av_jour = null;
                $av_hebdo = null;

                if ($nbSites>1) {
                    for ($i = 1 ; $i <= $nbSites; $i++) {
                        if ($elem["sites"][$i] and $elem["sites"][$i] != $elem[2]) {
                            $av_jour = $elem["sites"][$i]/$nbJours;
                            $av_hebdo = $jour*$joursParSemaine;
                        }
                    }
                }
            }
            foreach ($elem[1] as $agent) {
                $agent[3] = heure4($agent[3]);
            }

            $elem['services'] = sort($elem['services']);
            foreach ($elem['services'] as $service) {
                $service['nom'] = str_replace("ZZZ_", "", $service['nom']);
                $service['heures'] = heure4($service['heures']);
            }
            $elem['status'] = sort($elem['statuts']);
            foreach ($elem['statuts'] as $statut) {
                $statut['nom'] = str_replace("ZZZ_", "", $statut['nom']);
                $statut['heures']= heure4($statut['heures']);
            }
            $elem[]=array(
                "jour" => $jour,
                "av_jour" => $av_jour,
                "hebdo" => $hebdo,
                "av_hebdo" => $av_hebdo,
                "siteEtage" => $siteEtage
            );
        }

        return $this->output('statistics/position.html.twig');
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
