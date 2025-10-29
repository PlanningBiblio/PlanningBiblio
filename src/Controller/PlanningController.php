<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\AbsenceReason;
use App\Entity\Agent;
use App\Entity\Model;
use App\Entity\PlanningPositionHistory;
use App\Entity\PlanningPositionLock;
use App\Entity\SelectFloor;
use App\Entity\SeparationLine;
use App\PlanningBiblio\Helper\PlanningPositionHistoryHelper;
use App\PlanningBiblio\PresentSet;
use App\PlanningBiblio\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../legacy/Class/class.absences.php');
require_once(__DIR__ . '/../../legacy/Class/class.activites.php');
require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../public/include/function.php');
require_once(__DIR__ . '/../../legacy/Class/class.planning.php');
require_once(__DIR__ . '/../../legacy/Class/class.planningFunctions.php');
require_once(__DIR__ . '/../../legacy/Class/class.volants.php');
require_once(__DIR__ . '/../../legacy/Class/class.planningHebdo.php');

// TODO FIXME : hidden tables do not work correctly on week view

class PlanningController extends BaseController
{
    private $admin1 = false;
    private $admin2 = false;
    private $adminNotes = false;
    private $absenceReasons;
    private $absences = [];
    private $cellId = 0;
    private $cells = [];
    private $date = null;
    private $dates = [];
    private $locked = 0;
    private $lockDate = null;
    private $lockPerson = null;
    private $holidays = [];
    private $positions = [];
    private $schedules = [];
    private $separations = [];
    private $site = 1;


    #[Route(path: '/{date?}', name: 'home', methods: ['GET'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    #[Route(path: '/{site}/{date?}', name: 'homeWithSite', methods: ['GET'], requirements: ['site' => '\d+', 'date' => '\d{4}-\d{2}-\d{2}'])]
    public function index(Request $request)
    {
        // Show all week plannings.
        if (!$request->get('date') and !empty($_SESSION['week'])) {
          $this->setSite($request);
          return $this->redirectToRoute('planning.week', ['site' => $this->site]);
        }

        /*
         * TODO : remettre de l'ordre dans les contrôles de la page index : 
         *  1./ Vérifier s'il y a des données pour le site et le jour choisi, si oui : appeler une fonction dédiée qui utilisera $this->createPlanning
         *  2./ S'il n'y a pas de données pour le site et le jour choisi, appeler une fonction dédiée au formulaire choix d'un tableau / choix d'un groupe
         *  3./ Contrôler les droits en lecture pour les 2 configurations
         *  4./ Penser aux commentaires qui peuvent être affichés/modifiés dans toutes ces configutations
         * TODO : si possible : simplifier les fonctions initPlanning, etc. 
         *   Voir si initPlanning peut trouver sa place dans le constructeur (attention : gestion de $view et penser au futur affichage pour plusieurs sites)
         */
       
        $view = 'default';

        list($site, $date, $d, $semaine, $dates, $autorisationN1, $autorisationN2, $autorisationNotes, $comments) = $this->initPlanning($request, $view);

        // Index page only
        $currentFramework = $this->getCurrentFramework($date, $site);
        $show_framework_select = 0;
        if(!$currentFramework and $autorisationN2) {
            $show_framework_select = 1;
        }

        $not_ready = 0;
        if(!$currentFramework and !$autorisationN2) {
            $not_ready = 1;
        }

        // Index page only
        // Check if an action is undoable or redoable.
        $undoables = $this->entityManager
            ->getRepository(PlanningPositionHistory::class)
            ->undoable($date, $site);
        $redoables = $this->entityManager
            ->getRepository(PlanningPositionHistory::class)
            ->redoable($date, $site);

        $undoable = 1;
        if (empty($undoables)) {
            $undoable = 0;
        }

        $redoable = 1;
        if (empty($redoables)) {
            $redoable = 0;
        }

        // Index page only
        $this->templateParams(array(
            'start' => $this->dates[0]->dates[0],
            'end' => $this->dates[0]->dates[6],
            'not_ready' => $not_ready,
            'locked' => null,
            'lockDate' => null,
            'lockPerson' => null,
            'autorisationN2' => $this->admin2,
            'autorisationNotes' => $this->adminNotes,
            'undoable' => $undoable,
            'redoable' => $redoable,
            'show_framework_select' => $show_framework_select,
            'comments' => $comments[$date][$site],
        ));


        // Index page only
        // Framework choice.
        $groupes = $this->getFrameworksGroup();
        $pasDeDonneesSemaine = $this->noWeekDataFor($this->dates[4], $site);

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
                    'tab' => $tab,
            ));

            return $this->output('planning/poste/index.html.twig');

        } else {
            $tab = $currentFramework;
        }

        $this->templateParams(array(
            'tab'           => $tab,
        ));

        if (!$tab) {
            return $this->output('planning/poste/index.html.twig');
        }

        if (!$this->locked and !$this->admin1) {
            $this->templateParams(array(
                'absences_planning'   => [],
                'presents'            => 0,
                'tabs'                => 0,
            ));

            return $this->output('planning/poste/index.html.twig');

        } else {

            // ------------ Planning display --------------------//
            $this->createPlannings($request, $view);

            // Show absences at bottom of the planning
            $absences_planning = $this->getAbsencesPlanning($date, $site);

            if (in_array($this->config('Absences-planning'), [1,2])) {
                $this->templateParams(array('absences_planning' => $absences_planning));
            }

            // Affichage des présences et absences
            if (in_array($this->config('Absences-planning'), [3,4])) {

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

                // Filter by site if required
                $siteFilter = $this->config('Absences-planning') == 4 ? $site : 0;

                $presentset = new PresentSet($dateSQL, $d, $absents, $db, $siteFilter);
                $presents = $presentset->all();

                // Merge presences and absences
                $presentIds = array();

                // Add absences to people who are in present list
                foreach ($presents as &$elem) {
                    $presentIds[] = $elem['id'];
                    $elem['absences'] = array();
                    foreach ($absences_planning as &$abs) {
                        if ($abs['perso_id'] == $elem['id']) {
                            $elem['absences'][] = $abs;
                            $abs['done'] = true;
                        }
                    }
                }

                // Add absences to people who are not in present list
                foreach ($absences_planning as &$abs) {
                    if (!isset($abs['done'])) {
                        if (in_array($abs['perso_id'], $presentIds)) {
                            foreach ($presents as &$elem) {
                                if ($abs['perso_id'] == $elem['id']) {
                                    $elem['absences'][] = $abs;
                                    break;
                                }
                            }
                        } else {
                            $presents[] = array(
                                'id' => $abs['perso_id'],
                                'nom' => $abs['nom'] . ' ' . $abs['prenom'],
                                'prenom' => $abs['prenom'],
                                'heures' => null,
                                'site' => null,
                                'absences' => array($abs),
                            );
                            $presentIds[] = $abs['perso_id'];
                        }
                    }
                }

                $this->templateParams(array('presents' => $presents));
            }
        }

        return $this->output('planning/poste/index.html.twig');
    }

    #[Route(path: '/week/{date?}', name: 'planning.week', methods: ['GET'], requirements: ['date' => '\d{4}-\d{2}-\d{2}'])]
    public function week(Request $request)
    {
        $this->createPlannings($request, 'week');
        return $this->output('planning/poste/week.html.twig');
    }

    #[Route(path: '/deleteplanning', name: 'planning.delete', methods: ['POST'])]
    public function delete_planning(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $week = $request->get('week');
        $site = $request->get('site');
        $date = $request->get('date');
        $start = $request->get('start');
        $end = $request->get('end');

        if (!in_array((300 + $site), $this->permissions)) {
            $session->getFlashBag()->add('error', "Vous n'avez pas les droits suffisants pour supprimer le(s) planning(s)");
            return $this->redirectToRoute('home');
        }

        if ($week) {
            $history = new PlanningPositionHistoryHelper();
            $history->delete_plannings($session, $start, $end, $site);

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

            // Table pl_poste_verrou (Locked Plannings)
            $this->entityManager
                ->getRepository(PlanningPositionLock::class)
                ->delete($start, $end, $site);

            return $this->redirectToRoute('home');
        }

        $history = new PlanningPositionHistoryHelper();
        $history->delete_plannings($session, $date, $date, $site);

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

        return $this->redirectToRoute('home');
    }

    #[Route(path: '/modelimport', name: 'model.import', methods: ['POST'])]
    public function model_import(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $date = $request->get('date');;
        $site = $request->get('site');
        $get_absents = $request->get('absents');
        $model_id = $request->get('model');
        $dbprefix = $this->config('dbprefix');

        if (!in_array((300+$site), $this->permissions)) {
            return $this->output('access-denied.html.twig');
        }

        $model = $this->entityManager
            ->getRepository(Model::class)
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

        // Remove locks from associated plannings
        $start = $dates[0];
        $end = end($dates);

        $this->entityManager
            ->getRepository(PlanningPositionLock::class)
            ->delete($start, $end, $site);

        // Search for agents on other sites.
        $autres_sites = array();
        if ($this->config('Multisites-nombre') > 1) {
            $db = new \db();
            $db->select2('pl_poste', array('perso_id','date','debut','fin','poste'), array('date' => "BETWEEN {$dates[0]} AND ".end($dates), 'site' => "<>$site"));
            if ($db->result) {
                foreach ($db->result as $as) {
                    $autres_sites[$as['perso_id'].'_'.$as['date']][] = array('debut' => $as['debut'], 'fin' => $as['fin'], 'poste' => $as['poste']);
                }
            }
        }

        // Find all agents that are not deleted
        $agents = $this->entityManager
            ->getRepository('App\Entity\Agent')
            ->findBy(array('supprime' =>'0'));

        if (!empty($agents)) {
            foreach ($agents as $agent) {
                $agent_list[] = $agent->getId();
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

        // Get all possitions.
        $all_positions = array();

        // Get blocking positions
        $blockingPositions = array();

        $db = new \db();
        $db->select('postes', '*');
        if ($db->result) {
            foreach ($db->result as $position) {
                $all_positions[$position['id']] = $position;
                if ($position['bloquant']) {
                    $blockingPositions[] = $position['id'];
                }
            }
        }

        if (!$get_absents) {
            // Get teleworking reasons
            $teleworking_reasons = $this->entityManager->getRepository(AbsenceReason::class)
                ->getRemoteWorkingDescriptions();
        }

        $i=0;
        foreach ($dates as $elem) {
            $i++; // Key of the day (1=Monday, 2=Tuesday ...) start with 1.
            $sql = null;
            $values = array();
            $absents = array();

            $history = new PlanningPositionHistoryHelper();
            $history->delete_plannings($session, $elem, $elem, $site, 'import-model');

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

            $positions = array();
            $horaires = array();
            if ($db->result) {
                $tableau=$db->result[0]['tableau'];
                $db=new \db();
                $db->CSRFToken = $CSRFToken;
                $db->insert("pl_poste_tab_affect", array("date"=>$elem ,"tableau"=>$tableau ,"site"=>$site ));

                // N'importe pas les agents placés sur des postes supprimés (si tableau modifié)
                $db = new \db();
                $db->select2('pl_poste_lignes', 'poste', array('type'=>'poste', 'numero'=>$tableau));
                if ($db->result) {
                    foreach ($db->result as $elem2) {
                        $positions[] = $elem2['poste'];
                    }
                }

                // Do not import agents that are
                // on deleted time renges.
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

                    // Do not import agents if the cell does not exist
                    if (!$this->positionExists($elem2, $positions, $horaires)) {
                        continue;
                    }

                    // Do not import agents placed on other site
                    if (isset($autres_sites[$elem2['perso_id'].'_'.$elem])) {
                        foreach ($autres_sites[$elem2['perso_id'].'_'.$elem] as $as) {
                            if (in_array($as['poste'], $blockingPositions)
                                and in_array($elem2['poste'], $blockingPositions) ) {
                                if ($as['debut'] < $elem2['fin'] and $as['fin'] > $elem2['debut']) {
                                    continue 2;
                                }
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

                        // Exclude absence with remote working reason only for teleworking compliants positions.
                        $position = isset($all_positions[$elem2['poste']]) ? $all_positions[$elem2['poste']] : null;
                        if ($position && $position['teleworking'] == 1) {
                            $teleworking_exception = (!empty($teleworking_reasons) and is_array($teleworking_reasons))
                                ? "AND `motif` NOT IN ('" . implode("','", $teleworking_reasons) . "')" : null;
                            $filter .= " $teleworking_exception";
                        }

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
                            $temps = json_decode(html_entity_decode($agent->getWorkingHours(), ENT_QUOTES, 'UTF-8'), true);
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
        return $this->redirectToRoute('home');
    }

    #[Route(path: '/modelform', name: 'model.form', methods: ['GET'])]
    public function model_form(Request $request)
    {
        $CSRFToken = $request->get('CSRFToken');
        $date = $request->get('date');;
        $site = $request->get('site');

        if (!in_array((300+$site), $this->permissions)) {
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

    #[Route(path: '/setFramework', name: 'planning.setFramework', methods: ['POST'])]
    public function setFramework(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $session = $request->getSession();
        $date = $session->get('date');
        $site = $session->get('site');

        if (!in_array((300 + $site), $this->permissions)) {
            return $this->redirectToRoute('access-denied');
        }

        $CSRFToken = $request->get('CSRFToken');
        $tab = $request->get('tab');

        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete('pl_poste_tab_affect', array('date' => $date, 'site' => $site));

        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->insert('pl_poste_tab_affect', array('date' => $date, 'tableau' => $tab, 'site' => $site));

        return $this->redirectToRoute('home');
    }

    #[Route(path: '/setFrameworkGroup', name: 'planning.setFrameworkGroup', methods: ['POST'])]
    public function setFrameworkGroup(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $session = $request->getSession();
        $date = $session->get('date');
        $site = $session->get('site');

        if (!in_array((300 + $site), $this->permissions)) {
            return $this->redirectToRoute('access-denied');
        }

        $CSRFToken = $request->get('CSRFToken');
        $group = $request->get('group');

        $d = new \datePl($date);
        $dates = $d->dates;

        $t = new Framework();
        $t->fetchGroup($group);
        $groupTab = $t->elements;

        $tmp = array();
        $tmp[$dates[0]] = array($dates[0], $groupTab['lundi']);
        $tmp[$dates[1]] = array($dates[1], $groupTab['mardi']);
        $tmp[$dates[2]] = array($dates[2], $groupTab['mercredi']);
        $tmp[$dates[3]] = array($dates[3], $groupTab['jeudi']);
        $tmp[$dates[4]] = array($dates[4], $groupTab['vendredi']);
        $tmp[$dates[5]] = array($dates[5], $groupTab['samedi']);

        if (array_key_exists('dimanche', $groupTab)) {
            $tmp[$dates[6]] = array($dates[6], $groupTab['dimanche']);
        }

        foreach ($tmp as $elem) {
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete('pl_poste_tab_affect', array('date' => $elem[0], 'site' => $site));

            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->insert('pl_poste_tab_affect', array('date' => $elem[0], 'tableau' => $elem[1], 'site' => $site));
        }

        return $this->redirectToRoute('home');
    }

    /*
     * Envoi un mail aux agents disponibles pour l'occupation d'un poste vacant.
     * Lors de la validation du formulaire "Appel à disponibilité"
     * Script appelé par $( "#pl-appelDispo-form" ).dialog({ Envoyer ]), public/js/planning.js
     */
    #[Route(path: 'planning.call-for-help/send-mail', name: 'planning.call_for_help/send_mail', methods: ['POST'])]
    public function callForHelpSendMail(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $CSRFToken = $request->get('CSRFToken');
        $site = $request->get('site');
        $poste = $request->get('poste');
        $date = $request->get('date');
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $agents = $request->get('agents');
        $sujet = $request->get('sujet');
        $message = $request->get('message');

        $agents=html_entity_decode($agents, ENT_QUOTES|ENT_IGNORE, "UTF-8");
        $agents=json_decode($agents, true);

        $message=str_replace(array("\n","\r"), "<br/>", $message);

        if (!is_array($agents)) {
            $return = ["Aucun agent trouvé."];
            return new Response(json_encode($return));
        }

        // Récupération des destinataires
        $destinataires=array();
        foreach ($agents as $elem) {
            $destinataires[]=$elem['mail'];
        }

        // Envoi du mail
        $m=new \CJMail();
        $m->subject=$sujet;
        $m->message=$message;
        $m->to=$destinataires;
        $isSent=$m->send();

        // Enregistrement dans la base de données pour signaler que l'envoi a eu lieu
        if ($isSent) {
            $successAddresses=implode(";", $m->successAddresses);
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("appel_dispo", array( "site"=>$site, "poste"=>$poste, "date"=>$date, "debut"=>$debut, "fin"=>$fin,
                                              "destinataires"=>$successAddresses, "sujet"=>$sujet, "message"=>$message));
        }

        // retour vers la fonction JS
        if ($m->error) {
            $return = array("error"=>$m->error);
            return new Response(json_encode($return));
        } elseif (!$isSent) {
            $return = array("error"=>"Une erreur est survenue lors de l&apos;envoi du mail");
            return new Response(json_encode($return));
        } else {
            $return = ["ok"];
            return new Response(json_encode($return));
        }
    }

    /*
     * Récupère le message par défaut pour l'appel à disponibilité
     * Script appelé depuis la function JS appelDispo (public/js/planning.js)
     * lors du clic sur le lien "Appel à disponibilité" dans le menu permettant de placer les agents
     */
    #[Route(path: 'planning.call-for-help/get-message', name: 'planning.call_for_help/get_message', methods: ['POST'])]
    public function callForHelpGetMessage(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $tab=array(null,null);

        $db=new \db();
        $db->select2("config", "valeur", array("nom"=>"Planning-AppelDispoSujet"));
        if ($db->result) {
            $tab[0]=html_entity_decode($db->result[0]["valeur"], ENT_QUOTES|ENT_IGNORE, "utf-8");
        }
        $db=new \db();
        $db->select2("config", "valeur", array("nom"=>"Planning-AppelDispoMessage"));
        if ($db->result) {
            $tab[1]=html_entity_decode($db->result[0]["valeur"], ENT_QUOTES|ENT_IGNORE, "utf-8");
        }

        return new Response(json_encode($tab));
    }

    /*
     * Contrôle en arrière plan si un agent de catégorie A est placé en fin de service.
     * Permet d'afficher ou de masquer l'alerte "pas d'agent de catégorie A en fin de service" en haut du planning
     * Page appellée par la fonction JavaScript verif_categorieA lors du chargement de la page /index et lors de la modification
     * d'une cellule (fonction JS bataille_navale)
     * Affiche "true" ou "false"
     */
    #[Route(path: '/planning/end-of-service/check', name: 'planning.end_of_service.check', methods: ['GET'])]
    public function EndOfServiceCheck(Request $request)
    {
        $p=new \planning();
        $p->date = $request->get('date');
        $p->site = $request->get('site');
        $p->finDeService();

        if ($p->categorieA) {
            $return = ["true"];
        } else {
            $return = ["false"];
        }

        return new Response(json_encode($return));
    }

    /*
     * Permet de récupérer les préférences sur les tableaux cachés
     */
    #[Route(path: '/planning/hidden-tables', name: 'planning.hidden_tables.get', methods: ['GET'])]
    public function ajaxGetHiddenTables(Request $request, Session $session)//There is a private function with the same name as getHiddenTables
    {
        $perso_id = $session->get('loginId');
        $tableId = $request->get('tableId');
        $tableId = filter_var($tableId, FILTER_SANITIZE_NUMBER_INT);

        $db=new \db();
        $db->select2("hidden_tables", "*", array("perso_id"=>$perso_id,"tableau"=>$tableId));
        if ($db->result) {
            $return = html_entity_decode($db->result[0]["hidden_tables"], ENT_QUOTES|ENT_IGNORE, "utf-8");
        } else {
            $return = [""];
        }

        return new Response(json_encode($return));
    }

    /*
     * Permet l'enregistrement des préférences sur les tableaux cachés
     */
    #[Route(path: '/planning/hidden-tables', name: 'planning.hidden_tables.set', methods: ['POST'])]
    public function setHiddenTables(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $perso_id = $session->get('loginId');
        $CSRFToken = $request->get('CSRFToken');
        $hiddenTables = $request->get('hiddenTables');
        $tableId = $request->get('tableId');

        $tableId = filter_var($tableId, FILTER_SANITIZE_NUMBER_INT);

        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("hidden_tables", array("perso_id"=>$perso_id,"tableau"=>$tableId));

        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        $db->insert("hidden_tables", array("perso_id"=>$perso_id,"tableau"=>$tableId,"hidden_tables"=>$hiddenTables));
        $return = [""];
        return new Response(json_encode($return));
    }

    private function createCell($date, $debut, $fin, $colspan, $output, $poste, $site)
    {
        $resultats=array();
        $classe=array();
        $i=0;

        if ($this->cells) {

            // Recherche des sans repas en dehors de la boucle pour optimiser les performances (juillet 2016)
            $p = new \planning();
            $sansRepas = $p->sansRepas($date, $debut, $fin, $poste);

            foreach ($this->cells as $elem) {
                $title=null;

                if ($elem['poste']==$poste and $elem['debut']==$debut and $elem['fin']==$fin) {
                    //		Affichage du nom et du prénom
                    $nom_affiche=$elem['nom'];
                    $title = $elem['nom'];
                    if ($elem['prenom']) {
                        $nom_affiche.=" ".mb_substr($elem['prenom'], 0, 1).".";
                        $title .= ' ' . $elem['prenom'];
                    }

                    $resultat = $nom_affiche;

                    //		Affichage des sans repas
                    if ($elem['nom'] and ($sansRepas === true or in_array($elem['perso_id'], $sansRepas))) {
                        $resultat.="<font class='sansRepas'>&nbsp;(SR)</font>";
                    }

                    $class_tmp=array();

                    // Cellule grisée depuis le menudiv
                    if (isset($elem['grise']) and $elem['grise'] == 1) {
                        $class_tmp[]= 'cellule_grise';
                    }

                    //		On barre les absents (agents barrés directement dans le plannings, table pl_poste)
                    if ($elem['absent'] == 1 or $elem['supprime']) {
                        $class_tmp[]="red";
                        $class_tmp[]="striped";
                    }

                    if (isset($elem['depart'])
                        && $elem['depart'] > '0000-00-00'
                        && $elem['depart'] < $date)
                    {
                        $class_tmp[]="red";
                        $class_tmp[]="striped";
                        $title = 'Date de départ dépassée';
                    }

                    if ($elem['absent'] == 2) {
                        $class_tmp[] = "out-of-work-time";
                        $title = 'En dehors de ses heures de présence';
                    }

                    // On marque les absents (absences enregistrées dans la table absences)
                    $absence_valide = false;

                    foreach ($this->absences as $absence) {

                        // Skip teleworking absences if the position is compatible with
                        if ($this->positions[$poste]['teleworking']) {
                            $reason = $this->absenceReasons->findOneBy(array('valeur' => $absence['motif']));
                            if (!empty($reason) and $reason->getTeleworking() == 1) {
                                continue;
                            }
                        }

                        if (($this->config('Absences-Exclusion') == 1 and $absence['valide'] == 99999)
                            or $this->config('Absences-Exclusion') == 2) {
                            continue;
                        }

                        if ($absence['perso_id'] == $elem['perso_id'] and $absence['debut'] < $date . ' ' . $fin and $absence['fin'] > $date . ' ' . $debut) {
                            // Absence validée : rouge barré
                            if ($absence['valide'] > 0 or $this->config('Absences-validation') == 0) {
                                $class_tmp[] = 'red';
                                $class_tmp[] = 'striped';
                                $absence_valide = true;
                                break;  // Garder le break à cet endroit pour que les absences validées prennent le dessus sur les non-validées
                            }
                            // Absence non-validée : rouge
                            elseif ($this->config('Absences-non-validees')) {
                                $class_tmp[] = 'red';
                                $title = $nom_affiche . ' : Absence non-validée';
                            }
                        }
                    }

                    // Il peut y avoir des absences validées et non validées. Si ce cas ce produit, la cellule sera barrée et on n'affichera pas "Absence non-validée"
                    if ($absence_valide) {
                        $title=null;
                    }

                    //		On barre les congés
                    if ($this->config('Conges-Enable')) {
                        $conge_valide = false;

                        // On marque les congés
                        foreach ($this->holidays as $conge) {
                            if ($conge['perso_id'] == $elem['perso_id'] and $conge['debut'] < "$date {$elem['fin']}" and $conge['fin'] > "$date {$elem['debut']}") {
                                // Congé validé : orange barré
                                if ($conge['valide'] > 0) {
                                    $class_tmp[] = 'orange';
                                    $class_tmp[] = 'striped';
                                    $conge_valide = true;
                                    break;  // Garder le break à cet endroit pour que les congés validées prennent le dessus sur les non-validés
                                }
                                // congé non-validée : orange, sauf si une absence validée existe
                                elseif ($this->config('Absences-non-validees') and !$absence_valide) {
                                    $class_tmp[] = 'orange';
                                    $title = $nom_affiche . ' : Congé non-validé';
                                }
                            }
                        }

                        // Il peut y avoir des absences  et des congés validés et non validés. Si une absence ou un congé est validé, la cellule sera barrée et on n'affichera pas "Congé non-validé"
                        if ($conge_valide or $absence_valide) {
                            $title = null;
                        }
                    }

                    // Classe en fonction du statut et du service
                    if ($elem['statut']) {
                        $class_tmp[]="statut_".strtolower(removeAccents(str_replace(" ", "_", $elem['statut'])));
                    }
                    if ($elem['service']) {
                        $class_tmp[]="service_".strtolower(removeAccents(str_replace(" ", "_", $elem['service'])));
                    }
                    if (isset($elem['activites']) and is_array($elem['activites'])) {
                        foreach ($elem['activites'] as $a) {
                            $class_tmp[]='activite_'.strtolower(removeAccents(str_replace(array('/',' ',), '_', $a)));
                        }
                    }

                    // Color the logged in agent.
                    if (!empty($this->config('Affichage-Agent')) and $elem['perso_id'] == $_SESSION['login_id']) {
                        $class_tmp[] = 'current-user-cell';
                    }

                    $classe[$i] = implode(' ', $class_tmp);

                    // Création d'une balise span avec les classes cellSpan, et agent_ de façon à les repérer et agir dessus à partir de la fonction JS bataille_navale.
                    $span="<span class='cellSpan agent_{$elem['perso_id']}' title='$title' >$resultat</span>";
                    $resultats[$i]=array("text"=>$span, "perso_id"=>$elem['perso_id']);
                    $i++;
                }
            }
        }

        $this->cellId++;

        $cellule = "<td id='td{$this->cellId}' colspan='$colspan' style='text-align:center;' class='menuTrigger' oncontextmenu='cellule={$this->cellId}'
            data-start='$debut' data-end='$fin' data-situation='$poste' data-cell='{$this->cellId}' data-perso-id='0'>";

        for ($i=0;$i<count($resultats);$i++) {
            $cellule .= "<div id='cellule{$this->cellId}_$i' class='cellDiv {$classe[$i]} pl-cellule-perso-{$resultats[$i]['perso_id']}'
                data-perso-id='{$resultats[$i]['perso_id']}'>{$resultats[$i]['text']}</div>";
        }

        $cellule .= '<a class="pl-icon arrow-right" role="button"></a>';
        $cellule .= "</td>\n";

        return $cellule;
    }

    private function createPlannings($request, $view)
    {
        list($site, $date, $d, $semaine, $dates, $autorisationN1, $autorisationN2, $autorisationNotes, $comments) = $this->initPlanning($request, $view);

        // Pour tous les jours de la semaine
        $schedules = array();
        $end = 0;

        if ($view == 'week') {
            $end = $this->config('Dimanche') ? 6 : 5;
        }

        for ($j = 0; $j <= $end; $j++) {
            $schedule = array();
            $date = $view == 'week' ? $dates[$j] : $date;
            $schedule['date'] = $date;

            // Verrouillage du planning
            $this->getLockData($date);
            $locked = $this->locked;

            $schedule['locked'] = $locked;
            $schedule['lockDate'] = $this->lockDate;
            $schedule['lockPerson'] = $this->lockPerson;

            // ------------ Choix du tableau ----------- //
            $db = new \db();
            $db->select2('pl_poste_tab_affect', 'tableau', array('date' => $date, 'site' => $site));
            $tab = $db->result ? $db->result[0]['tableau'] : null;

            $schedule['tab'] = $tab;
            // ----------- FIN Choix du tableau --------- //

            // ----------- Vérification si le planning est validé ------------ //
            if ($locked or $autorisationN1) {

                // ------------ Planning display --------------------//

                // The following variables will be used in the createCell function.
                // We create them before calling them in a loop for performance reasons.
                $this->getAbsenceReasons();
                $this->getAbsences($date);
                $this->getCells($date, $site);
                $this->getHolidays($date);

                $tabs = $this->createTables($request, $tab, $locked, $date, $site);

                $schedule['tabs'] = $tabs;
            }

            $schedule['comments'] = $comments[$date][$site];
            $schedules[] = $schedule;
        }

        $this->schedules = $schedules;

        $this->templateParams(array(
            'locked' => $this->locked,
            'lockDate' => $this->lockDate,
            'lockPerson' => $this->lockPerson,
            'schedules' => $schedules,
            'view' => $view
        ));
    }

    private function createTables($request, $tab, $locked, $date, $site)
    {
        // Get framework structure, start and end hours.
        list($tabs, $startTime, $endTime) = $this->getFrameworkStructure($tab);

        $hiddenTables = $this->getHiddenTables($request, $tab);

        // Positions
        $positions = $this->positions;

        $l = 0;
        $sn = 1;

        foreach ($tabs as $index => $tab) {

            $hiddenTable = in_array($l, $hiddenTables) ? 'hidden-table' : null;
            $tabs[$index]['hiddenTable'] = $hiddenTable;
            $tabs[$index]['l'] = $l;

            // Comble les horaires laissés vides :
            // Créé la colonne manquante, les cellules de cette colonne seront grisées.
            $cellules_grises = array();
            $tmp = array();

            // Première colonne : si le début de ce tableau est supérieur au début d'un autre tableau.
            $k = 0;
            if ($tab['horaires'][0]['debut'] > $startTime) {
                $tmp[] = array(
                    'debut' => $startTime,
                    'fin' => $tab['horaires'][0]['debut']
                );
                $cellules_grises[] = $k++;
            }

            // Colonnes manquantes entre le début et la fin
            foreach ($tab['horaires'] as $key => $value) {
                if ($key == 0 or $value['debut'] == $tab['horaires'][$key-1]['fin']) {
                    $tmp[] = $value;
                } elseif ($value['debut'] > $tab['horaires'][$key-1]['fin']) {
                    $tmp[] = array(
                        'debut' => $tab['horaires'][$key-1]['fin'],
                        'fin' => $value['debut']
                    );
                    $tmp[] = $value;
                    $cellules_grises[] = $k++;
                }
                $k++;
            }

            // Dernière colonne : si la fin de ce tableau est inférieure à la fin d'un autre tableau.
            $nb = count($tab['horaires']) - 1;
            if ($tab['horaires'][$nb]['fin'] < $endTime) {
                $tmp[] = array(
                    'debut' => $tab['horaires'][$nb]['fin'],
                    'fin' => $endTime
                );
                $cellules_grises[] = $k;
            }

            $tab['horaires'] = $tmp;

            // Table name
            $tabs[$index]['titre2'] = $tab['titre'];
            if (!$tab['titre']) {
                $tabs[$index]['titre2'] = "Sans nom $sn";
                $sn++;
            }

            // Masquer les tableaux
            $masqueTableaux = null;
            if ($this->config('Planning-TableauxMasques')) {
                // FIXME HTML
                $masqueTableaux = "<span title='Masquer' class='pl-icon pl-icon-hide masqueTableau pointer noprint' data-id='$l' ></span>";
            }
            $tabs[$index]['masqueTableaux'] = $masqueTableaux;

            // Lignes horaires
            $colspan = 0;
            foreach ($tab['horaires'] as $key => $horaires) {

                $tabs[$index]['horaires'][$key]['colspan'] = nb30($horaires['debut'], $horaires['fin']);
                $tabs[$index]['horaires'][$key]['debut'] = $horaires['debut'];
                $tabs[$index]['horaires'][$key]['fin'] = $horaires['fin'];

                $colspan += nb30($horaires['debut'], $horaires['fin']);

            }
            $tabs[$index]['colspan'] = $colspan;

            // Lignes postes et grandes lignes
            foreach ($tab['lignes'] as $key => $ligne) {

                // Check if the line is empty.
                // Don't show empty lines if Planning-vides is disabled.
                $emptyLine = null;
                if (!$this->config('Planning-lignesVides') and $locked and $this->isAnEmptyLine($ligne['poste'])) {
                    $emptyLine="empty-line";
                }

                $ligne['emptyLine'] = $emptyLine;
                $ligne['is_position'] = '';
                $ligne['separation'] = '';

                // Position lines
                if ($ligne['type'] == 'poste' and $ligne['poste']) {

                    $ligne['is_position'] = 1;

                    // FIXME Check if 'classTD' is used

                    // Cell class depends if the position is mandatory or not.
                    $ligne['classTD'] = $positions[$ligne['poste']]['obligatoire'] == 'Obligatoire' ? 'td_obligatoire' : 'td_renfort';

                    // Line class depends if the position is mandatory or not.
                    $ligne['classTR'] = $positions[$ligne['poste']]['obligatoire'] == 'Obligatoire' ? 'tr_obligatoire' : 'tr_renfort';

                    // Line class depends on skills and categories.
                    $ligne['classTR'] .= ' ' . $positions[$ligne['poste']]['classes'];

                    // Position name
                    $ligne['position_name'] = $positions[$ligne['poste']]['nom'];

                    if ($this->config('Affichage-etages') and !empty($positions[$ligne['poste']]['etage'])) {
                        $ligne['position_name'] .= ' (' . $positions[$ligne['poste']]['etage'] . ')';
                    }

                    $i=1;
                    $k=1;
                    $ligne['line_time'] = array();
                    foreach ($tab['horaires'] as $horaires) {
                        // Recherche des infos à afficher dans chaque cellule

                        // Cell disabled.
                        // Cellules grisées si définies dans la configuration
                        // du tableau et si la colonne a été ajoutée automatiquement.
                        $horaires['disabled'] = 0;

                        if (in_array("{$ligne['ligne']}_{$k}", $tab['cellules_grises']) or in_array($i-1, $cellules_grises)) {
                            $horaires['disabled'] = 1;
                            $horaires['colspan'] = nb30($horaires['debut'], $horaires['fin']);

                            // If column added, that shift disabled cells.
                            // Si colonne ajoutée, ça décale les cellules grises initialement prévues.
                            // On se décale d'un cran en arrière pour rétablir l'ordre.
                            if (in_array($i - 1, $cellules_grises)) {
                                $k--;
                            }
                        }

                        // function createCell(date,debut,fin,colspan,affichage,poste,site)
                        else {
                            $horaires['position_cell'] = $this->createCell($date, $horaires['debut'], $horaires['fin'], nb30($horaires['debut'], $horaires['fin']), 'noms', $ligne['poste'], $site);
                        }
                        $i++;
                        $k++;
                        $ligne['line_time'][] = $horaires;
                    }
                }

                // Separation lines
                if ($ligne['type'] == 'ligne') {
                    $ligne['separation'] = $this->separations[$ligne['poste']] ?? null;
                }

                $tabs[$index]['lignes'][$key] = $ligne;
            }
            $l++;
        }

        return $tabs;
    }

    private function getAbsenceReasons()
    {
        $this->absenceReasons = $this->entityManager->getRepository(AbsenceReason::class);
    }

    private function getAbsences($date)
    {
        $a = new \absences();
        $a->valide = false;
        $a->documents = false;
        $a->rejected = false;
        $a->agents_supprimes = array(0,1,2);    // required for history
        $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date);
        $absences = $a->elements ?? array();

        usort($absences, 'cmp_nom_prenom_debut_fin');

        $this->absences = $absences;
    }

    private function getAbsencesPlanning($date, $site)
    {
        $site = $this->config('Absences-planning') != 3 ? $site : null;

        if ($site) {
            $site = [$site];
        }

        $a = new \absences();
        $a->valide = false;
        $a->documents = false;
        $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date, $site);

        $absences = $a->elements;

        // Add holidays
        $holidays = $this->getHolidays($date, $site);

        foreach ($holidays as $elem) {
            $elem['motif'] = 'Congé payé';
            $absences[] = $elem;
        }

        usort($absences, 'cmp_nom_prenom_debut_fin');

        $class = 'tr1';
        foreach ($absences as $index => $elem) {
            $absences[$index]['valide'] = 1;
            if ($elem['valide'] < 0 or ($elem['valide'] == 0 and $this->config('Absences-non-validees') == 0)) {
                unset ($absences[$index]);
                continue;
            }

            $heures = null;
            $debut = null;
            $fin = null;
            if ($elem['debut'] > "$date 00:00:00") {
                $debut = substr($elem['debut'], -8);
            }
            if ($elem['fin']<"$date 23:59:59") {
                $fin = substr($elem['fin'], -8);
            }
            if ($debut and $fin) {
                $heures = " de ".heure2($debut)." à ".heure2($fin);
            } elseif ($debut) {
                $heures = " à partir de ".heure2($debut);
            } elseif ($fin) {
                $heures = " jusqu'à ".heure2($fin);
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

            if ($this->config('Absences-planning') != 2) {
                $class = $class == 'tr1' ? 'tr2' : 'tr1';
                $absences[$index]['class'] = $class . ' ' . $bold;
            } else {
                $absences[$index]['class'] = $bold;
            }

            $absences[$index]['heures'] = $heures;
            $absences[$index]['nonValidee'] = $nonValidee;
        }

        return $absences;
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

    private function getCells($date, $site)
    {
        $dbprefix = $this->config('dbprefix');
        $skills = $this->getSkills();

        $db = new \db();
        $db->selectLeftJoin(
            array('pl_poste', 'perso_id'),
            array('personnel', 'id'),
            array('perso_id', 'debut', 'fin', 'poste', 'absent', 'supprime', 'grise'),
            array('nom', 'prenom', 'statut', 'service', 'postes', 'depart'),
            array('date' => $date, 'site' => $site),
            array(),
            "ORDER BY `{$dbprefix}pl_poste`.`absent` desc, `{$dbprefix}personnel`.`nom`, `{$dbprefix}personnel`.`prenom`"
        );

        $cellules = $db->result ? $db->result : array();
        usort($cellules, 'cmp_nom_prenom');

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
                foreach ($skills as $elem) {
                    if (in_array($elem['id'], $p)) {
                        $cellules[$k]['activites'][] = $elem['nom'];
                    }
                }
            }
        }

        $this->cells = $cellules;
    }

    private function getCurrentFramework($date, $site)
    {
        $db = new \db();
        $db->select2('pl_poste_tab_affect', 'tableau', array('date'=>$date, 'site'=>$site));

        $currentFramework = '';
        if (isset($db->result[0]['tableau'])) {
            $currentFramework = $db->result[0]['tableau'] ?? '';
        }

        return $currentFramework;
    }

    private function getFrameworksGroup()
    {
        $t = new Framework();
        $t->fetchAllGroups();

        return $t->elements;
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
            $debut = $elem['horaires'][0]['debut'] < $debut
                ? $elem['horaires'][0]['debut']
                : $debut;

            $nb = count($elem['horaires']) - 1;
            $fin = $elem['horaires'][$nb]['fin'] > $fin
                ? $elem['horaires'][$nb]['fin']
                : $fin;
        }
        return array($tabs, $debut, $fin);
    }

    private function getHiddenTables($request, $tab)
    {
        $session = $request->getSession();

        $hiddenTables = array();
        $db = new \db();
        $db->select2('hidden_tables', '*', array(
            'perso_id' => $session->get('loginId'),
            'tableau' => $tab
        ));

        if ($db->result) {
            $hiddenTables = json_decode(html_entity_decode($db->result[0]['hidden_tables'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        }

        return $hiddenTables;
    }

    private function getHolidays($date, $site = null)
    {
        if ($this->config('Conges-Enable')) {
            $c = new \conges();
            $holidays = $c->all($date.' 00:00:00', $date.' 23:59:59', 0, $site);

            $this->holidays = $holidays;
            return $holidays;
        }

        return [];
    }

    private function getInfoMessages($dates, $date, $view)
    {
        switch ($view) {
            case 'week' :
                $start = $dates[0];
                $end = $dates[6];
                break;
            default :
                $start = $date;
                $end = $date;
                break;
        }

        $messages_infos = null;

        $db = new \db();
        $start = $db->escapeString($start);
        $end = $db->escapeString($end);
        $db->select2('infos', '*', array('debut'=>"<={$end}", 'fin'=>">={$start}"), 'ORDER BY `debut`,`fin`');

        if ($db->result) {
            foreach ($db->result as $elem) {
                $messages_infos[] = $elem['texte'];
            }
            $messages_infos = implode(' - ', $messages_infos);
        }

        return $messages_infos;
    }

    private function getLockData(String $date = null)
    {
        $this->locked = 0;
        $this->lockDate = null;
        $this->lockPerson = null;

        $date = $date ?? $this->date;

        $lockData = $this->entityManager->getRepository(PlanningPositionLock::class)->findOneBy([
            'date' => \DateTime::createFromFormat('Y-m-d', $date),
            'site' => $this->site,
        ]);

        if ($lockData) {
            $this->locked = $lockData->getLock2();
            $this->lockDate = $lockData->getValidation2()->format('Y-m-d H:i:s');
            $this->lockPerson = nom($lockData->getUser2());
        }
    }

    private function getPermissionsFor($site)
    {
        $autorisationN1 = (in_array((300 + $site), $this->permissions)
            or in_array((1000 + $site), $this->permissions));

        $autorisationN2 = in_array((300 + $site), $this->permissions);

        $autorisationNotes = (in_array((300 + $site), $this->permissions)
            or in_array((800 + $site), $this->permissions)
            or in_array(1000 + $site, $this->permissions));

        $this->admin1 = $autorisationN1;
        $this->admin2 = $autorisationN2;
        $this->adminNotes = $autorisationNotes;

        return array($autorisationN1, $autorisationN2, $autorisationNotes);
    }

    private function getPositions()
    {
        $positions = array();

        $categories = $this->getCategories();
        $skills = $this->getSkills();

        $db = new \db();
        $db->select2('postes', '*', '1', 'ORDER BY `id`');

        $floorsE = $this->entityManager->getRepository(SelectFloor::class)->findAll();

        $floors = array();
        foreach($floorsE as $elem) {
            $floors[$elem->getId()] = $elem->getValue();
        }

        if ($db->result) {
            foreach ($db->result as $elem) {
                // Position CSS class
                $classesPoste = array();

                // Add classes according to skills
                $activitesPoste = $elem['activites'] ? json_decode(html_entity_decode($elem['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();

                foreach ($activitesPoste as $a) {
                    if (isset($skills[$a]['nom'])) {
                        $classesPoste[] = 'tr_activite_' . strtolower(removeAccents(str_replace(array(' ', '/'), '_', $skills[$a]['nom'])));
                    }
                }

                // Add classes according to required categories
                $categoriesPoste = $elem['categories'] ? json_decode(html_entity_decode($elem['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();
                foreach ($categoriesPoste as $cat) {
                    if (array_key_exists($cat, $categories)) {
                        $classesPoste[] = 'tr_' . str_replace(' ', '', removeAccents(html_entity_decode($categories[$cat], ENT_QUOTES|ENT_IGNORE, 'UTF-8')));
                    }
                }

                $positions[$elem['id']] = array(
                    'nom'         => $elem['nom'],
                    'etage'       => $floors[$elem['etage']] ?? null,
                    'obligatoire' => $elem['obligatoire'],
                    'teleworking' => $elem['teleworking'],
                    'classes'     => implode(' ', $classesPoste)
                );
            }
        }

        $this->positions = $positions;
    }

    private function getSeparations()
    {
        // Separation lines
        $separationE = $this->entityManager->getRepository(SeparationLine::class)->findAll();

        $separations = array();
        foreach ($separationE as $elem) {
            $separations[$elem->getId()] = $elem->getName();
        }

        $this->separations = $separations;
    }

    private function getSkills()
    {
        $a = new \activites();
        $a->deleted = true;
        $a->fetch();

        return $a->elements;
    }

    private function getWeekData($site, $semaine, $semaine3)
    {
        switch ($this->config('nb_semaine')) {
            case 2:
                $type_sem = $semaine % 2 ? 'Impaire' : 'Paire';
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

    private function initPlanning($request, $view)
    {
        $weekView = $view == 'week';
        $_SESSION['week'] = $weekView;

        $this->setDates($request);
        $date = $this->date;

        $this->setSite($request);
        $site = $this->site;

        $this->getLockData();

        list($d, $semaine, $semaine3, $jour, $dates) = $this->dates;

        // Selection des messages d'informations
        $messages_infos = $this->getInfoMessages($dates, $date, $view);

        // Vérification des droits de modification (Autorisation)
        list($autorisationN1, $autorisationN2, $autorisationNotes) = $this->getPermissionsFor($site);

        $affSem = $this->getWeekData($site, $semaine, $semaine3);

        // Positions and separation lines
        $this->getPositions();
        $this->getSeparations();

        // Planning's comments
        $p = new \planning();
        $p->date = $dates;
        $p->site = $site;
        $p->getNotes();
        $comments = $p->comments;

        // Parameters for planning's menu
        // (Calendar widget, days, week and action icons)
        $this->templateParams(array(
            'affSem'            => $affSem,
            'autorisationN1'    => $autorisationN1,
            'content_planning'  => true,
            'date'              => $date,
            'dates'             => $dates,
            'day'               => $jour,
            'messages_infos'    => $messages_infos,
            'public_holiday'    => jour_ferie($date),
            'site'              => $site,
            'week_view'         => $weekView,
        ));

        return array(
           $site,
           $date,
           $d,
           $semaine,
           $dates,
           $autorisationN1,
           $autorisationN2,
           $autorisationNotes,
           $comments,
       );
    }

    // Check if the line is empty or not
    private function isAnEmptyLine($position)
    {
        if (!$position) {
            return false;
        }

        foreach ($this->cells as $cell) {
            if ($position == $cell['poste'] && $cell['perso_id']) {
                return false;
            }
        }

        return true;
    }

    private function noWeekDataFor($dates, $site)
    {
        $dates = implode(",", $dates);
        $db = new \db();
        $db->select2('pl_poste', '*', array('date' => "IN$dates", 'site' => $site));

        if ($db->result) {
            return false;
        }

        return true;
    }

    private function positionExists($agent, $positions, $horaires)
    {
        if (!in_array($agent['poste'], $positions)) {
            return false;
        }

        foreach ($horaires as $h) {
            if ($h['debut'] == $agent['debut'] and $h['fin'] == $agent['fin']) {
                return true;
            }
        }

        return false;
    }

    private function setDates(Request $request)
    {
        $session = $request->getSession();

        $date = $request->get('date');
        $date = filter_var($date, FILTER_CALLBACK, ['options' => 'sanitize_dateSQL']);

        if (!$date and array_key_exists('PLdate', $_SESSION)) {
            $date = $_SESSION['PLdate'];
        } elseif (!$date and !array_key_exists('PLdate', $_SESSION)) {
            $date = date("Y-m-d");
        }

        $_SESSION['PLdate'] = $date;
        $session->set('date', $date);

        $d = new \datePl($date);

        $this->date = $date;

        $this->dates = array(
            $d,
            $d->semaine,
            $d->semaine3,
            $d->jour,
            $d->dates,
        );
    }

    private function setSite(Request $request)
    {
        $session = $request->getSession();

        $site = $request->get('site');

        // Multisites: default site is 1.
        // Site is $_GET['site'] if it is set, else we take
        // SESSION ['site'] or agent's site.

        if (!$site and !empty($_SESSION['site'])) {
            $site = $_SESSION['site'];
        }

        if (!$site) {
            $p = new \personnel();
            $p->fetchById($session->get('loginId'));
            $site = isset($p->elements[0]['sites'][0]) ? $p->elements[0]['sites'][0] : null;
        }

        $site = $site ? $site : 1;

        $_SESSION['site'] = $site;
        $session->set('site', $site);

        $this->site = $site;
    }
}
