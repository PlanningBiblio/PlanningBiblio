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
require_once __DIR__ . "/../../public/statistiques/class.statistiques.php";
include_once __DIR__ . '/../../public/absences/class.absences.php';
include_once __DIR__ . '/../../public/postes/class.postes.php';

class StatisticController extends BaseController
{

    /**
     * @Route("/statistics", name="statistics.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        return $this->output('statistics/index.html.twig');
    }


    /**
     * @Route("/statistics.time", name="statistics.time", methods={"GET"})
     */
    public function bytime(Request $request, Session $session)
    {
        //    Initialisation des variables
        $CSRFToken = trim($request->get("CSRFToken"));
        if (!$CSRFToken) {
            $CSRFToken = $GLOBALS['CSRFSession'];
        }

        $debut = $request->get("debut");
        if ($debut) {
            $fin = $request->get("fin");
            $selection_groupe = $request->get('selection_groupe');
            $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
            $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
            $selection_groupe = filter_var($selection_groupe, FILTER_CALLBACK, array("options"=>"sanitize_on"));

            $debut = dateSQL($debut);
            $fin = $fin ? dateFr($fin) : $debut;
        } elseif (array_key_exists("stat_temps_debut", $_SESSION['oups'])) {
            $debut = $_SESSION['oups']['stat_temps_debut'];
            $fin = $_SESSION['oups']['stat_temps_fin'];
            $selection_groupe = $_SESSION['oups']['stat_temps_selection_groupe'];
        } else {
           $date=$_SESSION['PLdate'];
           $d = new \datePl($date);
           $debut = $d->dates[0];
           $fin = $this->config('Dimanche') ? $d->dates[6] : $d->dates[5];
           $selection_groupe = false;
        }

        $_SESSION['oups']['stat_temps_debut'] = $debut;
        $_SESSION['oups']['stat_temps_fin'] = $fin;
        $_SESSION['oups']['stat_temps_selection_groupe'] = $selection_groupe;
        $current = $debut;

        while ($current<=$fin) {
            if (date("w", strtotime($current)) == 0 and !$this->config('Dimanche')) {

            } else {
                $dates[] = array($current,dateAlpha2($current));
            }

            $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
        }

        $debutFr = dateFr($debut);
        $finFr = dateFr($fin);
        $heures = array();  // Nombre total d'heures pour chaque jour
        $agents = array();  // Même chose avec le nombre d'agents
        $agents_id = array();   // Utilisé pour compter les agents présents chaque jour
        $nbAgents = array();  // Nombre d'agents pour chaque jour
        $tab = array();
        $nb = count($dates);  // Nombre de dates
        $nbSemaines = $nb/($this->config('Dimanche')? 7 : 6);   // Nombre de semaines
        $totalAgents = 0;        // Les totaux
        $totalHeures = 0;
        $siteHeures = array(0,0);   // Heures par site
        $siteAgents = array(0,0);   // Agents par site
        $multisites = [];

        // Affichage des statistiques par groupe de postes
        $groupes = array();
        $groupes_keys = array();
        $affichage_groupe = null;
        $totauxGroupesHeures = null;
        $totauxGroupesPerso = null;

        $p = new \postes();
        $p->fetch();
        // Rassemble les postes dans un tableau en fonction de leur groupe (ex: $groupe['pret'] = array(1,2,3))

        foreach ($p->elements as $poste) {
            $groupes[$poste['groupe']][] = $poste['id'];
        }

        $checked = null;
        if (!empty($groupes) and count($groupes)>1) {
            $checked = $selection_groupe ? "checked='checked'" : null;
            $affichage_groupe = "<span id='stat-temps-aff-grp'><input type='checkbox' value='on' id='selection_groupe' name='selection_groupe' $checked /><label for='selection_groupe'>Afficher les heures par groupe de postes</label></span>";
        }

        if ($affichage_groupe and $selection_groupe) {
            // $groupes_keys : nom des groupes
            $keys = array_keys($groupes);

            // Affichage des groupes selon l'ordre du menu déroulant
            $db = new \db();
            $db->select2('select_groupes', 'valeur', null, 'order by rang');
            if ($db->result) {
                foreach ($db->result as $elem) {
                    if (in_array($elem['valeur'], $keys)) {
                        $groupes_keys[] = $elem['valeur'];
                    }
                }
            }
            // Autres (les postes qui ne sont pas affectés à des groupes)
            if (in_array('', $keys)) {
                $groupes_keys[] = '';
            }

            // Initialisation des totaux (footer)
            foreach ($groupes_keys as $g) {
                $totauxGroupesHeures[$g] = 0;
                $totauxGroupesPerso[$g] = array();
            }
        }

        // Recherche des heures de SP à effectuer pour tous les agents pour toutes les semaines demandées
        $d = new \datePl($debut);
        $d1 = $d->dates[0];
        // Pour chaque semaine
        for ($d = $d1; $d <= $fin; $d = date("Y-m-d", strtotime($d."+1 week"))) {
            $heuresSP[$d] = calculHeuresSP($d, $CSRFToken);
            // déduction des absences
            if ($this->config('Planning-Absences-Heures-Hebdo')) {
                $a=new \absences();
                $a->CSRFToken = $CSRFToken;
                $heuresAbsences[$d] = $a->calculHeuresAbsences($d);
                foreach ($heuresAbsences[$d] as $key => $value) {
                    if (array_key_exists($key, $heuresSP[$d])) {
                        $heuresSP[$d][$key] = $heuresSP[$d][$key]-$value;
                        if ($heuresSP[$d][$key] < 0) {
                            $heuresSP[$d][$key] = 0;
                        }
                    }
                }
            }
        }
        // Calcul des totaux d'heures de SP à effectuer sur la période
        $totalSP = array();
        foreach ($heuresSP as $key => $value) {        // $key=date, $value=array
            foreach ($value as $key2 => $value2) {        // $key2=perso_id, $value2=heures
                if (!array_key_exists($key2, $totalSP)) {
                    $totalSP[$key2]=(float) $value2;
                } else {
                    $totalSP[$key2] += (float) $value2;
                }
            }
        }
        // Calcul des moyennes hebdomadaires de SP à effectuer
        $moyennesSP = array();
        foreach ($totalSP as $key => $value) {
            $moyennesSP[$key] = $value/(count($heuresSP));
        }


        // Recherche des absences dans la table absences
        $a = new \absences();
        $a->valide = true;
        $a->agents_supprimes = array(0,1,2);
        $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $debut." 00:00:00", $fin." 23:59:59");
        $absencesDB = $a->elements;

        $db = new \db();
        $debutREQ = $db->escapeString($debut);
        $finREQ = $db->escapeString($fin);
        $dbprefix = $GLOBALS['dbprefix'];

        $req = "SELECT `{$dbprefix}pl_poste`.`date` AS `date`, `{$dbprefix}pl_poste`.`debut` AS `debut`, ";
        $req.="`{$dbprefix}pl_poste`.`fin` AS `fin`, `{$dbprefix}personnel`.`id` AS `perso_id`, ";
        $req.="`{$dbprefix}pl_poste`.`site` AS `site`, `{$dbprefix}pl_poste`.`poste` AS `poste`, ";
        $req.="`{$dbprefix}personnel`.`nom` AS `nom`,`{$dbprefix}personnel`.`prenom` AS `prenom`, ";
        $req.="`{$dbprefix}personnel`.`statut` AS `statut` ";
        $req.="FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` ";
        $req.="INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}postes`.`id`=`{$dbprefix}pl_poste`.`poste` ";
        $req.="WHERE `date`>='$debutREQ' AND `date`<='$finREQ' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`statistiques`='1' ";
        $req.="ORDER BY `nom`,`prenom`;";

        $db->query($req);
        if ($db->result) {
            foreach ($db->result as $elem) {
                // Vérifie à partir de la table absences si l'agent est absent
                // S'il est absent, on met à 1 la variable $elem['absent']
                foreach ($absencesDB as $a) {
                    if ($elem['perso_id'] == $a['perso_id'] and $a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                        continue 2;
                    }
                }
                if (!array_key_exists($elem['perso_id'], $tab)) {        // création d'un tableau de données par agent (id, nom, heures de chaque jour ...)
                    $tab[$elem['perso_id']] = array(
                        "perso_id" => $elem['perso_id'],
                        "nom"      => $elem['nom'],
                        "prenom"   => $elem['prenom'],
                        "statut"   => $elem['statut'],
                        "site1"    => 0,
                        "site2"    => 0,
                        "total"    => 0,
                        "semaine"  => 0
                    );
                    foreach ($dates as $d) {
                        $tab[$elem['perso_id']][$d[0]] = array('total'=>0);
                        if (!empty($groupes_keys)) {
                            foreach ($groupes_keys as $g) {
                                $tab[$elem['perso_id']][$d[0]]["group_$g"] = 0;
                            }
                        }
                    }

                    // Totaux par groupe de postes
                    foreach ($groupes_keys as $g){
                        $tab[$elem['perso_id']]['group_'.$g] = 0;
                    }
                }

                $d = new \datePl($elem['date']);
                $position = $d->position != 0 ? $d->position-1 : 6;
                $tab[$elem['perso_id']][$elem['date']]['total']+=diff_heures($elem['debut'], $elem['fin'], "decimal");    // ajout des heures par jour
                $tab[$elem['perso_id']]['total']+=diff_heures($elem['debut'], $elem['fin'], "decimal");    // ajout des heures sur toutes la période
                if ($elem["site"]) {
                    if (!array_key_exists("site{$elem['site']}", $tab[$elem['perso_id']])) {
                        $tab[$elem['perso_id']]["site{$elem['site']}"]=0;
                    }
                    $tab[$elem['perso_id']]["site{$elem['site']}"]+=diff_heures($elem['debut'], $elem['fin'], "decimal");    // ajout des heures sur toutes la période par site
                }

                $totalHeures+=diff_heures($elem['debut'], $elem['fin'], "decimal");        // compte la somme des heures sur la période

                if (!array_key_exists($elem['site'], $siteHeures)) {
                    $siteHeures[$elem['site']] = 0;
                }
                $siteHeures[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                // Totaux par groupe de postes
                foreach ($groupes_keys as $g) {
                    if (in_array($elem['poste'], $groupes[$g])) {
                        $tab[$elem['perso_id']]['group_'.$g] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        $tab[$elem['perso_id']][$elem['date']]['group_'.$g] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        $totauxGroupesHeures[$g] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        if (!in_array($elem['perso_id'], $totauxGroupesPerso[$g])) {
                            $totauxGroupesPerso[$g][] = $elem['perso_id'];
                        }
                    }
                }
            }
        }

        $nbSites = $this->config('Multisites-nombre');
        if ($nbSites >1){
            for($i = 1; $i <= $nbSites; $i++){
                $multisites[] = $this->config("Multisites-sites$i");
            }
        }

        // Totaux par groupe de postes
        foreach ($groupes_keys as $g) {
            $totauxGroupesPerso[$g] = count($totauxGroupesPerso[$g]);
        }

        // pour chaque jour, on compte les heures et les agents
        foreach ($dates as $d) {
            $agents_id = array();
            if (is_array($tab)) {
                foreach ($tab as $elem) {
                    // on compte les heures de chaque agent
                    if (!array_key_exists($d[0], $agents)) {
                        $agents[$d[0]] = 0;
                    }
                    if (array_key_exists($d[0], $elem)) {
                        $agents[$d[0]]++;
                    }

                    // on compte le total d'heures par jour
                    if (!array_key_exists($d[0], $heures)) {
                        $heures[$d[0]] = 0;
                    }
                    if (array_key_exists($d[0], $elem)) {
                        $heures[$d[0]] += $elem[$d[0]]['total'];
                    }

                    // on compte les agents par jour + le total sur la période
                    if (!in_array($elem['perso_id'], $agents_id) and $elem[$d[0]]['total']){
                        $agents_id[] = $elem['perso_id'];
                        $totalAgents++;

                        for ($i = 1; $i <= $nbSites; $i++) {
                            if (array_key_exists("site$i", $elem)) {
                                if (!array_key_exists($i, $siteAgents)) {
                                    $siteAgents[$i] = 0;
                                }
                                $siteAgents[$i]++;
                            }
                        }
                    }
                }
            }
            // on compte les agents par jour (2ème partie)
            $nbAgents[$d[0]] = count($agents_id);
        }

        // Formatage des données pour affichage
        $keys = array_keys($tab);
        foreach ($keys as $key) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $tab[$key]["site{$i}Semaine"] = array_key_exists("site{$i}", $tab[$key])?number_format($tab[$key]["site{$i}"] / $nbSemaines, 2, '.', ' ') : "-";
                $tab[$key]["site{$i}"] = array_key_exists("site{$i}", $tab[$key]) ? number_format($tab[$key]["site{$i}"], 2, '.', ' ') : "-";
            }

            $tab[$key]['total'] = number_format($tab[$key]['total'], 2, '.', ' ');
            $tab[$key]['semaine'] = number_format($tab[$key]['total'] / $nbSemaines, 2, '.', ' ');      // ajout la moyenne par semaine

            if (!array_key_exists($key, $moyennesSP) or !is_numeric($moyennesSP[$key])) {
                $tab[$key]['heuresHebdo'] = "Erreur";
            } elseif ($moyennesSP[$key] > 0) {
                $tab[$key]['heuresHebdo'] = number_format($moyennesSP[$key], 2, '.', ' ');
            } else {
                $tab[$key]['heuresHebdo'] = 0;
            }

            if (!array_key_exists($key, $totalSP) or !is_numeric($totalSP[$key])) {
                $tab[$key]['max'] = "Erreur";
            } elseif ($totalSP[$key] > 0) {
                $tab[$key]['max'] = number_format($totalSP[$key], 2, '.', ' ');
            } else {
                $tab[$key]['max'] = 0;
            }

            foreach ($dates as $d) {
                $tab[$key][$d[0]]['total'] = $tab[$key][$d[0]]['total'] != 0 ? number_format($tab[$key][$d[0]]['total'], 2, '.', ' ') : '-';
            }
        }

        foreach ($dates as $d) {
            if (array_key_exists($d[0], $heures)) {
                $heures[$d[0]] = $heures[$d[0]] != 0 ? number_format($heures[$d[0]], 2, '.', ' ') : "-";
            } else {
                $heures[$d[0]] = "-";
            }

            if (array_key_exists($d[0], $nbAgents)) {
                $nbAgents[$d[0]] = $nbAgents[$d[0]] != 0 ? $nbAgents[$d[0]] : "-";
            } else {
                $nbAgents[$d[0]] = "-";
            }
        }
        $totalHeures = $totalHeures != 0 ? number_format($totalHeures, 2, '.', ' ') : "-";

        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (array_key_exists($i, $siteHeures) and $siteHeures[$i] != 0) {
                $siteHeures[$i] = number_format($siteHeures[$i], 2, '.', ' ');
            } else {
                $siteHeures[$i] = "-";
            }
            if (array_key_exists($i, $siteAgents) and $siteAgents[$i]!=0) {
                $siteAgents[$i] = $siteAgents[$i];
            } else {
                $siteAgents[$i] = "-";
            } 
        }
        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;
        $_SESSION['stat_heures'] = $heures;
        $_SESSION['stat_agents'] = $agents;
        $_SESSION['stat_dates'] = $dates;
        $_SESSION['oups']['stat_totalHeures'] = $totalHeures;
        $_SESSION['oups']['stat_nbAgents'] = $nbAgents;
        $_SESSION['oups']['stat_groupes'] = $groupes_keys;
        $_SESSION['oups']['stat_groupesHeures'] = $totauxGroupesHeures;
        $_SESSION['oups']['stat_groupesPerso'] = $totauxGroupesPerso;

        $this->templateParams(array(
            'debutFr'             => $debutFr,
            'finFr'               => $finFr,
            'CSRFToken'           => $CSRFToken,
            'dates'               => $dates,
            'heures'              => $heures,
            'groupes'             => $groupes,
            'groupes_keys'        => $groupes_keys,
            'nbGroupes'           => count($groupes),
            'checked'             => $checked,
            'nbAgents'            => $nbAgents,
            'nbSites'             => $nbSites,
            'multisites'          => $multisites,
            'totauxGroupesHeures' => $totauxGroupesHeures,
            'totauxGroupesPerso'  => $totauxGroupesPerso,
            'emptystr'            => '',
            'nbSemaines'          => $nbSemaines,
            'tab'                 => $tab,
            'siteAgents'          => $siteAgents,
            'sitesHeures'         => $siteHeures,
            'totalAgents'         => $totalAgents,
            'totalHeures'         => $totalHeures
        ));
        return $this->output('statistics/time.html.twig');
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
