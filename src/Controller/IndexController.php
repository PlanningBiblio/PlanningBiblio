<?php

namespace App\Controller;

use App\Model\AbsenceReason;
use App\Model\SelectFloor;
use App\Model\Agent;
use App\Model\Model;
use App\PlanningBiblio\PresentSet;
use App\PlanningBiblio\Framework;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/planning/poste/class.planning.php');
require_once(__DIR__ . '/../../public/planning/volants/class.volants.php');
require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/activites/class.activites.php');
require_once(__DIR__ . '/../../public/personnel/class.personnel.php');
require_once(__DIR__ . '/../../public/planning/poste/fonctions.php');
require_once(__DIR__ . '/../../public/planningHebdo/class.planningHebdo.php');
require_once(__DIR__ . '/../../public/include/function.php');

class IndexController extends BaseController
{

    private $CSRFToken;

    private $dbprefix;

    /**
     * @Route("/index", name="index", methods={"GET"})
     */
    public function index(Request $request)
    {
        // Initialisation des variables
        $CSRFToken=filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
        $this->CSRFToken = $CSRFToken;
        $groupe=filter_input(INPUT_GET, "groupe", FILTER_SANITIZE_NUMBER_INT);
        $site=filter_input(INPUT_GET, "site", FILTER_SANITIZE_NUMBER_INT);
        $tableau=filter_input(INPUT_GET, "tableau", FILTER_SANITIZE_NUMBER_INT);
        $date=filter_input(INPUT_GET, "date", FILTER_SANITIZE_STRING);

        $this->dbprefix = $GLOBALS['dbprefix'];

        // Contrôle sanitize en 2 temps pour éviter les erreurs CheckMarx
        $date = filter_var($date, FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));

        list($date, $dateFr) = $this->setDate($date);

        list($d, $semaine, $semaine3, $jour, $dates, $datesSemaine, $dateAlpha)
            = $this->getDatesPlanning($date);

        $_SESSION['oups']['week'] = false;

        $groupes = $this->getFrameworksGroup();
        $site = $this->setSite($site);
        $pasDeDonneesSemaine = $this->noWeekDataFor($datesSemaine, $site);
        global $idCellule;
        $idCellule=0;
        list($autorisationN1, $autorisationN2, $autorisationNotes) = $this->getPermissionsFor($site);
        list($jour3, $periode2) = $this->getSelectedDay($jour);
        list($verrou, $perso2, $date_validation2, $heure_validation2, $validation2)
            = $this->getLockingData($date, $site);
        $messages_infos = $this->getInfoMessages($date);
        $affSem = $this->getWeekData($site, $semaine, $semaine3);

        // Positions, skills...
        $activites = $this->getSkills();
        $categories = $this->getCategories();
        global $postes;
        $postes = $this->getPositions($activites, $categories);
        $currentFramework = $this->currentFramework($date, $site);
        $show_framework_select = 0;
        if(!$currentFramework and !$tableau and !$groupe and $autorisationN2) {
            $show_framework_select = 1;
        }


        // Planning's comments
        $comments = $this->getComments($date, $site);

        $this->templateParams(array(
            'content_planning' => true,
            'date' => $date, 'dates' => $dates, 'site' => $site,
            'start' => $d->dates[0],
            'startHr' => dateFr($d->dates[0]),
            'end' => $d->dates[6],
            'endHr' => dateFr($d->dates[6]),
            'dateFr' => $dateFr,
            'affSem' => $affSem, 'day' => $jour,
            'public_holiday' => jour_ferie($date),
            'messages_infos' => $messages_infos,
            'locked' => $verrou, 'perso2' => $perso2,
            'date_validation2' => $date_validation2,
            'heure_validation2' => $heure_validation2,
            'validation2' => $validation2,
            'autorisationN1' => $autorisationN1,
            'autorisationN2' => $autorisationN2,
            'autorisationNotes' => $autorisationNotes,
            'CSRFSession' => $GLOBALS['CSRFSession'],
            'week_view' => false,
            'show_framework_select' => $show_framework_select,
            'base_url' => $this->config('URL'),
            'comments' => $comments,
            'CSRFToken' => $GLOBALS['CSRFSession'],
        ));


        // Framework choice.
        $tab = 0;
        if ($show_framework_select) {
            $db = new \db();
            $db->select2("pl_poste_tab", "*", array("supprime"=>null), "order by `nom` DESC");
            $frameworks = $db->result;

            $this->templateParams(array(
                    'frameworks' => $frameworks,
                    'no_week_planning' => $pasDeDonneesSemaine,
                    'groups' => $groupes,
                    'week' => $semaine,
            ));

            return $this->output('planning/poste/index.html.twig');

        } elseif ($groupe and $autorisationN2) {
            $tab = $this->resetWeekFrameworkAffect($date, $dates, $site, $groupe);
        } elseif ($tableau and $autorisationN2) {	//	Si tableau en argument
            $tab = $tableau;
            $this->resetFrameworkAffect($date, $tab, $site);
        } else {
            $tab = $currentFramework;
        }

        if (!$tab) {
            $this->templateParams(array('no_tab' => 1));

            return $this->output('planning/poste/index.html.twig');
        }

        // Div planning-data : permet de transmettre les valeurs $verrou et $autorisationN1 à la fonction affichant le menudiv
        // data-validation pour les fonctions refresh_poste et verrouillage du planning
        // Lignes vides pour l'affichage ou non des lignes vides au chargement de la page et après validation (selon la config)
        $this->templateParams(array(
            'lignesVides'   => $this->config('Planning-lignesVides'),
            'tab'           => $tab,
        ));

        if (!$verrou and !$autorisationN1) {
            return $this->output('planning/poste/index.html.twig');
        } else {
            // Looking for info cells.
            // All this infos will be stored in array
            // and used function cellules_postes().

            // $cellules will be used in the cellule_poste function.
            global $cellules;
            $cellules = $this->getCells($date, $site, $activites);

            // $absence_reasons will be used in the cellule_poste function.
            global $absence_reasons;
            $absence_reasons = $this->entityManager->getRepository(AbsenceReason::class);

            // looking for absences.
            global $absences;
            $absences = $this->getAbsences($date);

            // Show absences for curret site at bottom of the planning
            $absences_planning = $this->getAbsencesPlanning($date, $site);

            // $conges will be used in the cellule_poste function.
            global $conges;
            $conges = $this->getHolidays($date);

            // ------------ Planning display --------------------//
            // Separation lines
            $lignes_sep = $this->getSepLines();

            // Get framework structure, start and end hours.
            list($tabs, $debut, $fin) = $this->getFrameworkStructure($tab);

            $hiddenTables = $this->getHiddenTables($tab);


            $sn=1;

            $j=0;
            foreach ($tabs as $index => $tab) {
                $hiddenTable = in_array($j, $hiddenTables) ? 'hidden-table' : null;
                $tabs[$index]['hiddenTable'] = $hiddenTable;
                $tabs[$index]['j'] = $j;

                $cellules_grises = array();
                $tmp = array();

                $k=0;
                if ($tab['horaires'][0]['debut']>$debut) {
                    $tmp[]=array("debut"=>$debut, "fin"=>$tab['horaires'][0]['debut']);
                    $cellules_grises[]=$k++;
                }

                foreach ($tab['horaires'] as $key => $value) {
                    if ($key==0 or $value["debut"]==$tab['horaires'][$key-1]["fin"]) {
                        $tmp[]=$value;
                    } elseif ($value["debut"]>$tab['horaires'][$key-1]["fin"]) {
                        $tmp[]=array("debut"=>$tab['horaires'][$key-1]["fin"], "fin"=>$value["debut"]);
                        $tmp[]=$value;
                        $cellules_grises[]=$k++;
                    }
                    $k++;
                }

                $nb=count($tab['horaires'])-1;
                if ($tab['horaires'][$nb]['fin']<$fin) {
                    $tmp[]=array("debut"=>$tab['horaires'][$nb]['fin'], "fin"=>$fin);
                    $cellules_grises[]=$k;
                }

                $tab['horaires'] = $tmp;

                $tabs[$index]['titre2'] = $tab['titre'];
                if (!$tab['titre']) {
                    $tabs[$index]['titre2'] = "Sans nom $sn";
                    $sn++;
                }

                // Masquer les tableaux
                $masqueTableaux=null;
                if ($this->config('Planning-TableauxMasques')) {
                    // FIXME HTML
                    $masqueTableaux="<span title='Masquer' class='pl-icon pl-icon-hide masqueTableau pointer noprint' data-id='$j' ></span>";
                }
                $tabs[$index]['masqueTableaux'] = $masqueTableaux;

                // Lignes horaires
                $colspan=0;
                foreach ($tab['horaires'] as $key => $horaires) {

                    $tabs[$index]['horaires'][$key]['start_nb30'] = nb30($horaires['debut'], $horaires['fin']);
                    $tabs[$index]['horaires'][$key]['start_h3'] = heure3($horaires['debut']) ;
                    $tabs[$index]['horaires'][$key]['end_h3'] = heure3($horaires['fin']) ;

                    $colspan += nb30($horaires['debut'], $horaires['fin']);
                }
                $tabs[$index]['colspan'] = $colspan;

                foreach ($tab['lignes'] as $key => $ligne) {
                    // Check if the line is empty.
                    // Don't show empty lines if Planning-vides is disabled.
                    $emptyLine=null;
                    if (!$this->config('Planning-lignesVides') and $verrou and isAnEmptyLine($ligne['poste'])) {
                        $emptyLine="empty-line";
                    }

                    $tabs[$index]['lignes'][$key]['emptyLine'] = $emptyLine;
                    $tabs[$index]['lignes'][$key]['is_position'] = '';
                    $tabs[$index]['lignes'][$key]['separation'] = '';

                    // Position lines
                    if ($ligne['type']=="poste" and $ligne['poste']) {
                        $tabs[$index]['lignes'][$key]['is_position'] = 1;
                        // FIXME $classTD and $classTR are always the same.
                        // Cell class depends if the position
                        // is mandatory or not.
                        $classTD = $postes[$ligne['poste']]['obligatoire'] == "Obligatoire" ? "td_obligatoire" : "td_renfort";
                        // Line class depends if the position
                        // is mandatory or not.
                        $classTR = $postes[$ligne['poste']]['obligatoire'] == "Obligatoire" ? "tr_obligatoire" : "tr_renfort";

                        // Line class depends on skills and categories.
                        $classTR .= ' ' . $postes[$ligne['poste']]['classes'];

                        $tabs[$index]['lignes'][$key]['class_td'] = $classTD;
                        $tabs[$index]['lignes'][$key]['class_tr'] = $classTR;
                        $tabs[$index]['lignes'][$key]['position_name'] = $postes[$ligne['poste']]['nom'];

                        // floors
                        $tabs[$index]['lignes'][$key]['floor'] = '';
                        if ($this->config('Affichage-etages') and $postes[$ligne['poste']]['etage']) {
                            $tabs[$index]['lignes'][$key]['floor'] = $postes[$ligne['poste']]['etage'];
                        }

                        $i=1;
                        $k=1;
                        $tabs[$index]['lignes'][$key]['horaires'] = array();
                        foreach ($tab['horaires'] as $horaires) {
                            $ligne_horaire = array(
                                'cell_off' => 0,
                            );

                            // Cell disabled.
                            if (in_array("{$ligne['ligne']}_{$k}", $tab['cellules_grises']) or in_array($i-1, $cellules_grises)) {
                                $ligne_horaire['cell_off'] = 1;
                                $ligne_horaire['colspan'] = nb30($horaires['debut'], $horaires['fin']);
                                // If column added, that shift disabled cells.
                                if (in_array($i-1, $cellules_grises)) {
                                    $k--;
                                }
                            }
                            // function cellule_poste(date,debut,fin,colspan,affichage,poste,site)
                            else {
                                $ligne_horaire['cell_html'] = cellule_poste($date, $horaires["debut"], $horaires["fin"], nb30($horaires['debut'], $horaires['fin']), "noms", $ligne['poste'], $site);
                            }
                            $i++;
                            $k++;
                            $tabs[$index]['lignes'][$key]['horaires'][] = $ligne_horaire;
                        }
                    }

                    // Separation lines
                    if ($ligne['type']=="ligne") {
                        $tabs[$index]['lignes'][$key]['separation'] = $lignes_sep[$ligne['poste']];
                    }
                }
                $j++;
            }

            $this->templateParams(array('tabs' => $tabs));

            // Affichage des absences
            if ($this->config('Absences-planning')) {

                // Add holidays
                foreach ($conges as $elem) {
                    $elem['motif'] = 'Congé payé';
                    $absences_planning[] = $elem;
                    $absences_id[] = $elem['perso_id'];
                }

                usort($absences_planning, 'cmp_nom_prenom_debut_fin');

                switch ($this->config('Absences-planning')) {
                    case "1":
                        if (!empty($absences_planning)) {
                            $class="tr1";
                            foreach ($absences_planning as $index => $elem) {
                                $absences_planning[$index]['valide'] = 1;
                                if ($elem['valide'] <= 0 and $this->config('Absences-non-validees') == 0) {
                                    $absences_planning[$index]['valide'] = 0;
                                    continue;
                                }

                                $heures=null;
                                $debut=null;
                                $fin=null;
                                if ($elem['debut']>"$date 00:00:00") {
                                    $debut=substr($elem['debut'], -8);
                                }
                                if ($elem['fin']<"$date 23:59:59") {
                                    $fin=substr($elem['fin'], -8);
                                }
                                if ($debut and $fin) {
                                    $heures=" de ".heure2($debut)." à ".heure2($fin);
                                } elseif ($debut) {
                                    $heures=" à partir de ".heure2($debut);
                                } elseif ($fin) {
                                    $heures=" jusqu'à ".heure2($fin);
                                }
                
                                $bold = null;
                                $nonValidee = null;
                                if ($this->config('Absences-non-validees') == 1) {
                                    if ($elem['valide'] > 0) {
                                        $bold = 'bold';
                                    } else {
                                        $nonValidee = " (non validée)";
                                    }
                                }

                                $class=$class=="tr1"?"tr2":"tr1";
                                $absences_planning[$index]['class'] = $class;
                                $absences_planning[$index]['bold'] = $bold;
                                $absences_planning[$index]['heures'] = $heures;
                                $absences_planning[$index]['nonValidee'] = $nonValidee;
                            }
                        }
                        break;

                    case "2":
                        if (!empty($absences_planning)) {
                            foreach ($absences_planning as $index => $elem) {
                                $absences_planning[$index]['valide'] = 1;
                                if ($elem['valide'] <= 0 and $this->config('Absences-non-validees') == 0) {
                                    $absences_planning[$index]['valide'] = 0;
                                    continue;
                                }

                                $bold = null;
                                $nonValidee = null;
                                if ($this->config('Absences-non-validees') == 1) {
                                    if ($elem['valide'] > 0) {
                                        $bold = 'bold';
                                    } else {
                                        $nonValidee = " (non validée)";
                                    }
                                }

                                $absences_planning[$index]['bold'] = $bold;
                                $absences_planning[$index]['nonValidee'] = $nonValidee;
                            }
                        }
                        break;

                    case "3":
                        $heures=null;
                        $presents=array();
                        $absents=array(2); // 2 = Remove "Everybody" user

                        // Excludes those who are absent
                        // all the day
                        if (!empty($absences_planning)) {
                            foreach ($absences_planning as $elem) {
                                if ($elem['debut'] <= $date . ' 00:00:00'
                                    and $elem['fin'] >= $date . ' 23:59:59'
                                    and $elem['valide'] > 0) {
                                    $absents[]=$elem['perso_id'];
                                }
                            }
                        }

                        // Looking for agents to exclude
                        // because they don't work this day
                        $db = new \db();
                        $dateSQL=$db->escapeString($date);

                        $presentset = new PresentSet($dateSQL, $d, $absents, $db);
                        $presents = $presentset->all();

                        // Presents list
                        $class="tr1";
                        foreach ($presents as $index => $elem) {
                            $class=$class=="tr1"?"tr2":"tr1";
                            $presents[$index]['class'] = $class;
                            $presents[$index]['heures'] = html_entity_decode($elem['heures']);
                        }
                        $this->templateParams(array('presents' => $presents));

                        // Absents list
                        $class="tr1";
                        foreach ($absences_planning as $index => $elem) {
                            $absences_planning[$index]['valide'] = 1;
                            if ($elem['valide'] <= 0 and $this->config('Absences-non-validees') == 0) {
                                $absences_planning[$index]['valide'] = 0;
                                continue;
                            }

                            $heures=null;
                            $debut=null;
                            $fin=null;
                            if ($elem['debut']>"$date 00:00:00") {
                                $debut=substr($elem['debut'], -8);
                            }
                            if ($elem['fin']<"$date 23:59:59") {
                                $fin=substr($elem['fin'], -8);
                            }
                            if ($debut and $fin) {
                                $heures=", ".heure2($debut)." - ".heure2($fin);
                            } elseif ($debut) {
                                $heures=" à partir de ".heure2($debut);
                            } elseif ($fin) {
                                $heures=" jusqu'à ".heure2($fin);
                            }

                            $class=$class=="tr1"?"tr2":"tr1";

                            $bold = null;
                            $nonValidee = null;

                            if ($this->config('Absences-non-validees') == 1) {
                                if ($elem['valide'] > 0) {
                                    $bold = 'bold';
                                } else {
                                    $nonValidee = " (non validée)";
                                }
                            }

                            $absences_planning[$index]['bold'] = '';
                            $absences_planning[$index]['class'] = $class;
                            $absences_planning[$index]['heures'] = $heures;
                            $absences_planning[$index]['nonValidee'] = $nonValidee;
                        }
                        break;
                }
            }

            $this->templateParams(array('absences_planning' => $absences_planning));
        }

        return $this->output('planning/poste/index.html.twig');
    }

    /**
     * @Route("/deleteplanning", name="planning.delete", methods={"POST"})
     */
    public function delete_planning(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $week = $request->get('week');
        $site = $request->get('site');
        $date = $request->get('date');
        $start = $request->get('start');
        $end = $request->get('end');

        $droits = $GLOBALS['droits'];
        if (!in_array((300 + $site), $droits)) {
            $session->getFlashBag()->add('error', "Vous n'avez pas les droits suffisants pour supprimer le(s) planning(s)");
            return $this->redirectToRoute('index');
        }

        if ($week) {
            // Table pl_poste (agents assignment)
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete('pl_poste', array(
                'site' => $site,
                'date' => "BETWEEN{$start}AND{$end}")
            );

            // Table pl_poste_tab_affect (frameworks assignment)
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete('pl_poste_tab_affect', array(
                'site' => $site,
                'date'=>"BETWEEN{$start}AND{$end}")
            );

            return $this->redirectToRoute('index');
        }

        // Table pl_poste (agents assignment)
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete('pl_poste', array(
            'site' => $site,
            'date' => $date)
        );

        // Table pl_poste_tab_affect (frameworks assignment)
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete('pl_poste_tab_affect', array(
            'site' => $site,
            'date' => $date)
        );

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/modelimport", name="model.import", methods={"POST"})
     */
    public function model_import(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $date = $request->get('date');;
        $site = $request->get('site');
        $get_absents = $request->get('absents');
        $model_id = $request->get('model');
        $droits = $GLOBALS['droits'];
        $dbprefix = $GLOBALS['dbprefix'];

        if (!in_array((300+$site), $droits)) {
            return $this->output('access-denied.html.twig');
        }

        $model = $this->entityManager
            ->getRepository(Model::Class)
            ->findOneBy(array('model_id' => $model_id));

        $dates = array();
        $d = new \datePl($date);

        if ($model->isWeek()) {
            // Search for all current dates
            // of the week.
            foreach ($d->dates as $elem) {
                $dates[] = $elem;
            }
        } else {
            // If it is not a week model,
            // insert only current date.
            $dates[0] = $date;
        }

        // Search for agents on other sites.
        $autres_sites = array();
        if ($this->config('Multisites-nombre') > 1) {
            $db = new \db();
            $db->select2('pl_poste', array('perso_id','date','debut','fin'), array('date' => "BETWEEN {$dates[0]} AND ".end($dates), 'site' => "<>$site"));
            if ($db->result) {
                foreach ($db->result as $as) {
                    $autres_sites[$as['perso_id'].'_'.$as['date']][] = array('debut' => $as['debut'], 'fin' => $as['fin']);
                }
            }
        }

        // Find all agents that are not deleted
        $agents = $this->entityManager
            ->getRepository('App\Model\Agent')
            ->findBy(array('supprime' =>'0'));

        if (!empty($agents)) {
            foreach ($agents as $agent) {
                $agent_list[] = $agent->id();
            }
        }

        // if module PlanningHebdo: search related plannings.
        $tempsPlanningHebdo = array();
        if ($this->config('PlanningHebdo')) {
            $p = new \planningHebdo();
            $p->debut = $date;
            $p->fin = $date;
            $p->valide = true;
            $p->fetch();

            if (!empty($p->elements)) {
                foreach ($p->elements as $elem) {
                    $tempsPlanningHebdo[$elem["perso_id"]]=$elem;
                }
            }
        }

        $i=0;
        foreach ($dates as $elem) {
            $i++; // Key of the day (1=Monday, 2=Tuesday ...) start with 1.
            $sql = null;
            $values = array();
            $absents = array();

            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete('pl_poste_tab_affect', array('date' => $elem, 'site' => $site));

            // Import frameworf
            // if it's a week model.
            if ($model->isWeek()) {
                $db = new \db();
                $db->select2('pl_poste_modeles_tab', '*', array('model_id'=>$model_id, 'site'=>$site, 'jour'=>$i));
            // Model for one day.
            } else {
                $db = new \db();
                $db->select2('pl_poste_modeles_tab', '*', array('model_id'=>$model_id, 'site'=>$site));
            }

            if ($db->result) {
                $tableau=$db->result[0]['tableau'];
                $db=new \db();
                $db->CSRFToken = $CSRFToken;
                $db->insert("pl_poste_tab_affect", array("date"=>$elem ,"tableau"=>$tableau ,"site"=>$site ));

                // N'importe pas les agents placés sur des postes supprimés (si tableau modifié)
                $postes = array();
                $db = new \db();
                $db->select2('pl_poste_lignes', 'poste', array('type'=>'poste', 'numero'=>$tableau));
                if ($db->result) {
                    foreach ($db->result as $elem2) {
                        $postes[] = $elem2['poste'];
                    }
                }

                // Do not import agents that are
                // on deleted time renges.
                $horaires = array();
                $db = new \db();
                $db->select2('pl_poste_horaires', array('debut','fin'), array('numero'=>$tableau));
                if ($db->result) {
                    foreach ($db->result as $elem2) {
                        $horaires[] = array('debut'=>$elem2['debut'], 'fin'=>$elem2['fin']);
                    }
                }
            }

            // Import agents
            // Week model.
            if ($model->isWeek()) {
                $db = new \db();
                $db->select2('pl_poste_modeles', '*', array('model_id' => $model_id, 'site'=>$site, 'jour'=>$i));
            // Day model.
            } else {
                $db = new \db();
                $db->select2('pl_poste_modeles', '*', array('model_id' => $model_id, 'site'=>$site));
		}

            if ($db->result) {
                foreach ($db->result as $elem2) {

                    // Don't import deleted agents
                    if ($elem2['perso_id'] > 0 and !in_array($elem2['perso_id'], $agent_list)) {
                        continue;
                    }

                    $value = array();

                    // Do not import agents placed on other site
                    if (isset($autres_sites[$elem2['perso_id'].'_'.$elem])) {
                        foreach ($autres_sites[$elem2['perso_id'].'_'.$elem] as $as) {
                            if ($as['debut'] < $elem2['fin'] and $as['fin'] > $elem2['debut']) {
                                continue 2;
                            }
                        }
                    }

                    $grise = $elem2['perso_id'] == 0 ? 1 : 0;

                    $value = array(
                        ':date' => $elem,
                        ':perso_id' => $elem2['perso_id'],
                        ':poste' => $elem2['poste'],
                        ':debut' => $elem2['debut'],
                        ':fin' => $elem2['fin'],
                        ':site' => $site,
                        ':absent' => 0,
                        ':grise' => $grise
                    );


                    $debut=$elem." ".$elem2['debut'];
                    $fin=$elem." ".$elem2['fin'];

                    // Look for absences

                    if (!$get_absents) {
                        $filter = $this->config('Absences-validation') ? 'AND `valide`>0' : null;

                        // Exclude absence with remote working reason.
                        $teleworking_reasons = $this->entityManager->getRepository(AbsenceReason::class)
                                                               ->getRemoteWorkingDescriptions();
                        $teleworking_exception = (!empty($teleworking_reasons) and is_array($teleworking_reasons)) ? "AND `motif` NOT IN ('" . implode("','", $teleworking_reasons) . "')" : null;
                        $filter .= " $teleworking_exception";

                        $db2 = new \db();
                        $db2->select('absences', '*', "`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' $filter ");
                        $absent = $db2->result ? true : false;

                        // Look for hollidays
                        $db2 = new \db();
                        $db2->select("conges", "*", "`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' AND `valide`>0");
                        $absent = $db2->result ? true : $absent;

                        // Don't import if absent and get_absents not checked
                        if ($absent) {
                            continue;
                        }
                    }

                    // Check if the agent is out of his schedule (schedule has been changed).
                    $week_number = 0;

                    if ($this->config('PlanningHebdo')) {
                        $temps = !empty($tempsPlanningHebdo[$elem2['perso_id']]['temps']) ? $tempsPlanningHebdo[$elem2['perso_id']]['temps'] : array();
                        $week_number = !empty($tempsPlanningHebdo[$elem2['perso_id']]['nb_semaine']) ? $tempsPlanningHebdo[$elem2['perso_id']]['nb_semaine'] : 0 ;
                    } else {
                        $agent = $this->entityManager->find(Agent::class, $elem2['perso_id']);
                        if (!empty($agent)) {
                            $temps = json_decode(html_entity_decode($agent->temps(), ENT_QUOTES, 'UTF-8'), true);
                        } else {
                            $temps = array();
                        }
                    }

                    $d = new \datePl($elem);
                    $day_index = $d->planning_day_index_for($elem2['perso_id'], $week_number);
                    if (!calculSiPresent($elem2['debut'], $elem2['fin'], $temps, $day_index)) {
                        $value[':absent'] = 2;
                    }

                    if (isset($value[':absent'])) {
                        $values[] = $value;
                    }
                }

                // insertion des données dans le planning du jour
                if (!empty($values)) {
                    // Suppression des anciennes données
                    $db=new \db();
                    $db->CSRFToken = $CSRFToken;
                    $db->delete("pl_poste", array("date"=>$elem, "site"=>$site));

                    // Insertion des nouvelles données
                    $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`perso_id`,`poste`,`debut`,`fin`,`absent`,`site`,`grise`) ";
                    $req.="VALUES (:date, :perso_id, :poste, :debut, :fin, :absent, :site, :grise);";
                    $dbh=new \dbh();
                    $dbh->CSRFToken = $CSRFToken;
                    $dbh->prepare($req);
                    foreach ($values as $value) {
                        $dbh->execute($value);
                    }
                }
            }
        }
        return $this->redirectToRoute('index', array('date' => $date));
    }

    /**
     * @Route("/modelform", name="model.form", methods={"GET"})
     */
    public function model_form(Request $request)
    {
        $CSRFToken = $request->get('CSRFToken');
        $date = $request->get('date');;
        $site = $request->get('site');
        $droits = $GLOBALS['droits'];

        if (!in_array((300+$site), $droits)) {
            return $this->output('access-denied.html.twig');
        }

        $semaine = " ";

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $models = $queryBuilder->select(array('m'))
        ->from(Model::class, 'm')
        ->where('m.site = :site')
        ->setParameter('site', $site)
        ->groupBy('m.nom')
        ->getQuery()
        ->getResult();

        $this->templateParams(array(
            'models'    => $models,
            'CSRFToken' => $CSRFToken,
            'date'      => $date,
            'site'      => $site,
        ));

        return $this->output('planning/poste/model_form.html.twig');
    }

    private function setDate($date)
    {
        if (!$date and array_key_exists('PLdate', $_SESSION)) {
            $date = $_SESSION['PLdate'];
        } elseif (!$date and !array_key_exists('PLdate', $_SESSION)) {
            $date = date("Y-m-d");
        }

        $_SESSION['PLdate'] = $date;

        $dateFr = dateFr($date);

        return array($date, $dateFr);
    }

    private function getDatesPlanning($date)
    {
        $d = new \datePl($date);
        $semaine=$d->semaine;
        $semaine3=$d->semaine3;
        $jour=$d->jour;
        $dates=$d->dates;
        $datesSemaine=implode(",", $dates);
        $dateAlpha=dateAlpha($date);

        return array($d, $d->semaine, $d->semaine3,
            $d->jour, $d->dates,
            implode(",", $d->dates),
            dateAlpha($date)
        );
    }

    private function getFrameworksGroup()
    {

        $t = new Framework();
        $t->fetchAllGroups();

        return $t->elements;
    }

    private function setSite($site)
    {
        // Multisites: default site is 1.
        // Site is $_GET['site'] if it is set, else we take
        // SESSION ['site'] or agent's site.
        if (!$site and array_key_exists("site", $_SESSION['oups'])) {
            $site = $_SESSION['oups']['site'];
        }
        if (!$site) {
            $p = new \personnel();
            $p->fetchById($_SESSION['login_id']);
            $site = isset($p->elements[0]['sites'][0]) ? $p->elements[0]['sites'][0] : null;
        }

        $site = $site ? $site : 1;

        $_SESSION['oups']['site']=$site;

        return $site;
    }

    private function noWeekDataFor($datesSemaine, $site)
    {
        $db = new \db();
        $db->select2('pl_poste', '*', array('date' => "IN$datesSemaine", 'site' => $site));

        if ($db->result) {
            return false;
        }

        return true;
    }

    private function getPermissionsFor($site)
    {
        $autorisationN1 = (in_array((300 + $site), $this->permissions)
            or in_array((1000 + $site), $this->permissions));

        $autorisationN2 = in_array((300 + $site), $this->permissions);

        $autorisationNotes = (in_array((300 + $site), $this->permissions)
            or in_array((800 + $site), $this->permissions)
            or in_array(1000 + $site, $this->permissions));

        return array($autorisationN1, $autorisationN2, $autorisationNotes);
    }

    private function getSelectedDay($jour)
    {
        $jour3 = '';
        $periode2 = '';

        switch ($jour) {
          case "lun":   $jour3="Lundi";     $periode2='semaine';    break;
          case "mar":   $jour3="Mardi";     $periode2='semaine';    break;
          case "mer":   $jour3="Mercredi";  $periode2='semaine';    break;
          case "jeu":   $jour3="Jeudi";     $periode2='semaine';    break;
          case "ven":   $jour3="Vendredi";  $periode2='semaine';    break;
          case "sam":   $jour3="Samedi";    $periode2='samedi';     break;
          case "dim":   $jour3="Dimanche";  $periode2='samedi';     break;
        }

        return array($jour3, $periode2);
    }

    private function getLockingData($date, $site)
    {
        $db = new \db();
        $db->select2("pl_poste_verrou", "*", array("date"=>$date, "site"=>$site));

        $verrou = false;
        $perso2 = null;
        $date_validation2 = null;
        $heure_validation2 = null;
        $validation2 = null;

        if ($db->result) {
            $verrou = $db->result[0]['verrou2'];
            $perso2 = nom($db->result[0]['perso2']);
            $date_validation2 = dateFr(substr($db->result[0]['validation2'], 0, 10));
            $heure_validation2 = substr($db->result[0]['validation2'], 11, 5);
            $validation2 = $db->result[0]['validation2'];
        }

        return array($verrou, $perso2, $date_validation2, $heure_validation2, $validation2);
    }

    private function getInfoMessages($date)
    {
        $db = new \db();
        $db->sanitize_string = false;
        $db->select2('infos', '*', array('debut'=>"<={$date}", 'fin'=>">={$date}"), "ORDER BY `debut`,`fin`");

        $messages_infos = null;
        if ($db->result) {
            foreach ($db->result as $elem) {
                $messages_infos[] = $elem['texte'];
            }
            $messages_infos = implode(' - ', $messages_infos);
        }

        return $messages_infos;
    }

    private function getWeekData($site, $semaine, $semaine3)
    {
        $nb_semaine = $this->config('nb_semaine');

        switch ($nb_semaine) {
            case 2:
                $type_sem = $semaine % 2 ?"Impaire":"Paire";
                $affSem = "$type_sem ($semaine)";
                break;
            case 3: 
                $type_sem = $semaine3;
                $affSem = "$type_sem ($semaine)";
                break;
            default:
                $affSem = $semaine;
                break;
        }

        return $affSem;
    }

    private function getSkills()
    {
        $a = new \activites();
        $a->deleted = true;
        $a->fetch();

        return $a->elements;
    }

    private function getCategories()
    {
        $categories = array();

        $db = new \db();
        $db->select2('select_categories');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $categories[$elem['id']] = $elem['valeur'];
            }
        }

        return $categories;
    }

    private function getPositions($activites, $categories)
    {
        $postes=array();

        $db = new \db();
        $db->select2('postes', '*', '1', 'ORDER BY `id`');
        $floors =  $this->entityManager->getRepository(SelectFloor::class);

        if ($db->result) {
            foreach ($db->result as $elem) {
                // Position CSS class
                $classesPoste=array();

                // Add classes according to skills
                $activitesPoste = $elem['activites'] ? json_decode(html_entity_decode($elem['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();

                foreach ($activitesPoste as $a) {
                    if (isset($activites[$a]['nom'])) {
                        $classesPoste[] = 'tr_activite_'.strtolower(removeAccents(str_replace(array(' ','/'), '_', $activites[$a]['nom'])));
                    }
                }

                // Add classes according to required categories.
                $categoriesPoste = $elem['categories'] ? json_decode(html_entity_decode($elem['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();
                foreach ($categoriesPoste as $cat) {
                    if (array_key_exists($cat, $categories)) {
                        $classesPoste[]="tr_".str_replace(" ", "", removeAccents(html_entity_decode($categories[$cat], ENT_QUOTES|ENT_IGNORE, "UTF-8")));
                    }
                }

                $postes[$elem['id']] = array(
                    'nom'           => $elem['nom'],
                    'etage'         => $floors->find($elem['etage']) ? $floors->find($elem['etage'])->valeur() : null,
                    'obligatoire'   => $elem['obligatoire'],
                    'teleworking'   => $elem['teleworking'],
                    'classes'       => implode(" ", $classesPoste)
                );
            }
        }

        return $postes;
    }

    private function currentFramework($date, $site)
    {
        $db = new \db();
        $db->select2('pl_poste_tab_affect', 'tableau', array('date'=>$date, 'site'=>$site));

        $currentFramework = '';
        if (isset($db->result[0]['tableau'])) {
            $currentFramework = $db->result[0]['tableau'] ?? '';
        }

        return $currentFramework;
    }

    private function getComments($date, $site)
    {
        $p = new \planning();
        $p->date = $date;
        $p->site = $site;
        $p->getNotes();
        $notes = $p->notes;
        $notesTextarea = $p->notesTextarea;
        $notesValidation = $p->validation;
        $notesDisplay = trim($notes) ? null : 'style=display:none;';
        $notesSuppression = ($notesValidation and !trim($notes))
            ? 'Suppression du commentaire : ' : null;

        return array(
            'notes' => $notes,
            'notesTextarea' => $notesTextarea,
            'notesValidation' => $notesValidation,
            'notesDisplay' => $notesDisplay,
            'notesSuppression' => $notesSuppression,
        );
    }

    private function resetWeekFrameworkAffect($date, $dates, $site, $groupe)
    {
        $t = new Framework();
        $t->fetchGroup($groupe);
        $groupeTab = $t->elements;

        $tmp = array();
        $tmp[$dates[0]]=array($dates[0],$groupeTab['lundi']);
        $tmp[$dates[1]]=array($dates[1],$groupeTab['mardi']);
        $tmp[$dates[2]]=array($dates[2],$groupeTab['mercredi']);
        $tmp[$dates[3]]=array($dates[3],$groupeTab['jeudi']);
        $tmp[$dates[4]]=array($dates[4],$groupeTab['vendredi']);
        $tmp[$dates[5]]=array($dates[5],$groupeTab['samedi']);
        if (array_key_exists("dimanche", $groupeTab)) {
            $tmp[$dates[6]]=array($dates[6],$groupeTab['dimanche']);
        }

        foreach ($tmp as $elem) {
            $db = new \db();
            $db->CSRFToken = $this->CSRFToken;
            $db->delete('pl_poste_tab_affect', array('date' => $elem[0], 'site' => $site));

            $db = new \db();
            $db->CSRFToken = $this->CSRFToken;
            $db->insert('pl_poste_tab_affect', array('date' => $elem[0], 'tableau' => $elem[1], 'site' => $site));
        }
        return $tmp[$date][1];
    }

    private function resetFrameworkAffect($date, $tab, $site)
    {
        $db = new \db();
        $db->CSRFToken = $this->CSRFToken;
        $db->delete('pl_poste_tab_affect', array('date' => $date, 'site' => $site));

        $db = new \db();
        $db->CSRFToken = $this->CSRFToken;
        $db->insert('pl_poste_tab_affect', array('date' => $date, 'tableau' => $tab, 'site' => $site));
    }

    private function getCells($date, $site, $activites)
    {
        $db = new \db();
        $db->selectLeftJoin(
            array("pl_poste","perso_id"),
            array("personnel","id"),
            array("perso_id","debut","fin","poste","absent","supprime","grise"),
            array("nom","prenom","statut","service","postes", 'depart'),
            array("date"=>$date, "site"=>$site),
            array(),
            "ORDER BY `{$this->dbprefix}personnel`.`nom`, `{$this->dbprefix}personnel`.`prenom`"
        );

        $cellules = $db->result ? $db->result : array();
        usort($cellules, "cmp_nom_prenom");

        // Recherche des agents volants
        if ($this->config('Planning-agents-volants')) {
            $v = new \volants($date);
            $v->fetch($date);
            $agents_volants = $v->selected;

            // Modification du statut pour les agents volants afin de personnaliser l'affichage
            foreach ($cellules as $k => $v) {
                if (in_array($v['perso_id'], $agents_volants)) {
                    $cellules[$k]['statut'] = 'volants';
                }
            }
        }

        // Ajoute les qualifications de chaque agent (activités) dans le tableaux $cellules pour personnaliser l'affichage des cellules en fonction des qualifications
        foreach ($cellules as $k => $v) {
            if ($v['postes']) {
                $p = json_decode(html_entity_decode($v['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                $cellules[$k]['activites'] = array();
                foreach ($activites as $elem) {
                    if (in_array($elem['id'], $p)) {
                        $cellules[$k]['activites'][] = $elem['nom'];
                    }
                }
            }
        }

        return $cellules;
    }

    private function getAbsences($date)
    {
        $a = new \absences();
        $a->valide = false;
        $a->documents = false;
        $a->rejected = false;
        $a->agents_supprimes = array(0,1,2);    // required for history
        $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date);
        $absences = $a->elements;

        usort($absences, "cmp_nom_prenom_debut_fin");

        return $absences;
    }

    private function getAbsencesPlanning($date, $site)
    {
        $a = new \absences();
        $a->valide = false;
        $a->documents = false;
        $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date, array($site));
        return $a->elements;
    }

    private function getHolidays($date)
    {
        $conges = array();

        if ($this->config('Conges-Enable')) {
            $c = new \conges();
            $conges = $c->all($date.' 00:00:00', $date.' 23:59:59');
        }

        return $conges;
    }

    private function getSepLines()
    {
        $lignes_sep = array();

        $db = new \db();
        $db->select2('lignes');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $lignes_sep[$elem['id']]=$elem['nom'];
            }
        }

        return $lignes_sep;
    }

    private function getFrameworkStructure($tab)
    {
        $t = new Framework();
        $t->id = $tab;
        $t->get();
        $tabs = $t->elements;

        $debut = '23:59';
        $fin = null;
        foreach ($tabs as $elem) {
            $debut = $elem["horaires"][0]["debut"] < $debut
                ? $elem["horaires"][0]["debut"]
                : $debut;

            $nb = count($elem["horaires"]) - 1;
            $fin = $elem["horaires"][$nb]["fin"] > $fin
                ? $elem["horaires"][$nb]["fin"]
                : $fin;
        }
        return array($tabs, $debut, $fin);
    }

    private function getHiddenTables($tab)
    {
        $hiddenTables = array();
        $db = new \db();
        $db->select2('hidden_tables', '*', array(
            'perso_id' => $_SESSION['login_id'],
            'tableau' => $tab
        ));

        if ($db->result) {
            $hiddenTables = json_decode(html_entity_decode($db->result[0]['hidden_tables'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        }

        return $hiddenTables;
    }
}
