<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\AbsenceDocument;
use App\Model\AbsenceReason;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use \DateTime;
use \DateTimeZone;

require_once(__DIR__ . '/../../public/absences/class.absences.php');

class AbsenceController extends BaseController
{
    /**
     * @Route("/absence", name="absence.index", methods={"GET"})
     */
    public function index(Request $request){

        // Initialisation des variables
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $reset = $request->get("reset");
        $droits = $GLOBALS['droits'];

        // Contrôle sanitize_dateFr en 2 temps pour éviter les erreurs CheckMarx
        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $debut = $debut ? $debut : (isset($_SESSION['oups']['absences_debut']) ? $_SESSION['oups']['absences_debut'] : null);
        $fin = $fin ? $fin : (isset($_SESSION['oups']['absences_fin']) ? $_SESSION['oups']['absences_fin'] : null);

        $this->droits = $GLOBALS['droits'];

        $p = new \personnel();
        $p->supprime = array(0,1,2);
        $p->fetch();
        $agents = $p->elements;

        if ($reset) {
            $debut=null;
            $fin=null;
            $agents_supprimes=false;
        }

        $temporary = array();
        foreach ($agents as $elem){
            if ($elem['id']>2){
                $temporary[] = $elem;
            }
        }
        $agents = $temporary;

        // Initialisation des variables
        $nbSites = $this->config('Multisites-nombre');
        $admin = false;
        $adminN2 = false;

        for($i = 1; $i <= $nbSites; $i++){
            if (in_array((200 + $i), $this->droits)){
                $admin = true;
            }

            if (in_array((500 + $i), $this->droits)){
                $admin = true;
                $adminN2 = true;
                break;
            }
        }

        if ($admin) {
            $perso_id = $request->get("perso_id");
            if ($perso_id===null) {
                $perso_id = isset($_SESSION['oups']['absences_perso_id']) ? $_SESSION['oups']['absences_perso_id'] : $_SESSION['login_id'];
            }
        } else {
            $perso_id = $_SESSION['login_id'];
        }

        if ($reset) {
            $perso_id = $_SESSION['login_id'];
        }

        $agents_supprimes = isset($_SESSION['oups']['absences_agents_supprimes']) ? $_SESSION['oups']['absences_agents_supprimes'] : false;
        $agents_supprimes = (isset($_GET['debut']) and isset($_GET['supprimes'])) ? true : $agents_supprimes;
        $agents_supprimes = (isset($_GET['debut']) and !isset($_GET['supprimes'])) ? false : $agents_supprimes;

        if ($reset) {
            $debut = null;
            $fin = null;
            $agents_supprimes = false;
        }

        $_SESSION['oups']['absences_debut'] = $debut;
        $_SESSION['oups']['absences_fin'] = $fin;
        $_SESSION['oups']['absences_perso_id'] = $perso_id;
        $_SESSION['oups']['absences_agents_supprimes'] = $agents_supprimes;

        $debutSQL = dateSQL($debut);
        $finSQL = dateSQL($fin);

        // Multisites : filtre pour n'afficher que les agents du site voulu
        $sites = null;
        if ($nbSites>1) {
            $sites = array();
            for ($i = 1; $i < 31; $i++) {
                if (in_array((200 + $i), $droits) or  in_array((500 + $i), $droits)) {
                    $sites[] = $i;
                }
            }
        }

        $a = new \absences();
        $a->groupe = true;
        if ($agents_supprimes) {
            $a->agents_supprimes = array(0,1);
        }
        $a->fetch(null, $perso_id, $debutSQL, $finSQL, $sites);

        $absences = $a->elements;

        $adminOnly = $this->config('Absences-adminSeulement');

        $sort = false;
        // Tri par défaut du tableau
        if ($admin or (!$adminOnly and in_array(6, $droits))) {
            $sort = true;
        }

        $selectedAgents = array();
        if ($admin) {
            $p = new \personnel();
            if ($agents_supprimes) {
                $p->supprime = array(0,1);
            }
            $p->responsablesParAgent = true;
            $p->fetch();
            $agents_menu = $p->elements;

            // Filtre pour n'afficher que les agents gérés en configuration multisites
            if ($nbSites > 1) {
                foreach ($agents_menu as $k => $v) {
                    $keep = false;
                    if (!is_array($v['sites'])) {
                        unset($agents_menu[$k]);
                        continue;
                    }
                    foreach ($v['sites'] as $site) {
                        if (in_array($site, $sites)) {
                            $keep = true;
                        }
                    }
                    if ($keep == false) {
                        unset($agents_menu[$k]);
                    }
                }
            }
            // Filtre pour n'afficher que les agents gérés si l'option "Absences-notifications-agent-par-agent" est cochée
            if ($this->config('Absences-notifications-agent-par-agent') and !$adminN2) {
                $tmp = array();
                foreach ($agents_menu as $elem) {
                    if ($elem['id'] > 2) {
                        if ($elem['id'] == $_SESSION['login_id']) {
                            $tmp[$elem['id']] = $elem;
                        } else {
                            foreach ($elem['responsables'] as $resp) {
                                if ($resp['responsable'] == $_SESSION['login_id']) {
                                    $tmp[$elem['id']] = $elem;
                                    break;
                                }
                            }
                        }
                    } else {
                        continue;
                    }
                }
                $agents_menu = $tmp;
            }

            // Liste des agents à conserver :
            $perso_ids = array_keys($agents_menu);
            foreach ($agents_menu as $agent) {
                $selected = $agent['id'] == $perso_id ? true :null;
                if ($selected != null){
                  $selectedAgents[] = $agent;
                }
            }
            $checked = $agents_supprimes ? true : null;
        }
        $absList = array ();

        if ($absences) {
            foreach ($absences as $elem) {
                $absLinks = array ();
                // Filtre les agents non-gérés (notamment avec l'option Absences-notifications-agent-par-agent)
                if ($admin) {
                    $continue = true;
                    foreach ($elem['perso_ids'] as $perso) {
                        if (in_array($perso, $perso_ids)) {
                            $continue = false;
                            break;
                        }
                    }
                    if ($continue) {
                        continue;
                    }
                }
                $id = $elem['id'];
                $absdocs = $this->entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $id]);
                $nom_n1a = $elem['valide_n1'] != 99999 ? nom($elem['valide_n1'], 'nom p', $agents).", " : null;
                $nom_n1b = $elem['valide_n1'] != -99999 ? nom(-$elem['valide_n1'], 'nom p', $agents).", " : null;
                $nom_n2a = $elem['valide'] != 99999 ? nom($elem['valide'], 'nom p', $agents).", " : null;
                $nom_n2b = $elem['valide'] != -99999 ? nom(-$elem['valide'], 'nom p', $agents).", " : null;
                $etat = "Demand&eacute;e";
                $etat = $elem['valide_n1'] > 0 ? "En attente de validation hierarchique, $nom_n1a".dateFr($elem['validation_n1'], true) : $etat;
                $etat = $elem['valide_n1'] < 0 ? "En attente de validation hierarchique, $nom_n1b".dateFr($elem['validation_n1'], true) : $etat;
                $etat = $elem['valide'] > 0 ? "Valid&eacute;e, $nom_n2a".dateFr($elem['validation'], true) : $etat;
                $etat = $elem['valide'] < 0 ? "Refus&eacute;e, $nom_n2b".dateFr($elem['validation'], true) : $etat;

                $begin = dateFr($elem['debut'], true);
                $end = dateFr($elem['fin'], true);

                $agentsList = implode( ",",$elem['agents']);
                $rrule = $elem['rrule'] ? $elem['rrule'] : null;
                $commentaires = $elem['commentaires'];
                $motif = $elem['motif_autre'] ? $elem['motif_autre'] : $elem['motif'];
                $requestDate = dateFr($elem['demande'], true);

                $pj1 = $elem['pj1'] ? 1 : 0;
                $pj2 = $elem['pj2'] ? 1 : 0;
                $so = $elem['so'] ? 1 : 0;

                foreach($absdocs as $absdoc){
                   $absLinks[]=array(
                    'link' =>"absences/document/". $absdoc->id() . "target='_blank'",
                    'name' => $absdoc->filename()
                  );
                }

                $absList[] = array(
                  'id'            => $id,
                  'rrule'         => $rrule,
                  'agentsList'    => $agentsList,
                  'state'         => $etat,
                  'absLinks'      => $absLinks,
                  'motive'        => $motif,
                  'comments'      => $commentaires,
                  'requestDate'   => $requestDate,
                  'begin'         => $begin,
                  'end'           => $end,
                  'pj1'           => $pj1,
                  'pj2'           => $pj2,
                  'so'            => $so
                );

            }
        }


        $this->templateParams(array(
            'admin'                 => $admin,
            'begin'                 => dateFr($debutSQL),
            'end'                   => dateFr($finSQL),
            'sort'                  => $sort,
            'perso_id'              => $perso_id,
            'agentsMenu'            => $agents_menu,
            'right6'                => in_array(6, $this->droits)?1:0,
            'right701'              => in_array(701, $this->droits)?1:0,
            'deletedAgents'         => $agents_supprimes ,
            'selectedAgents'        => $selectedAgents ,
            'absences'              => $absList,
            'absences_validation'   => $this->config('Absences-validation')
        ));

        return $this->output('absences/index.html.twig');
    }

    /**
     * @Route("/absence/add", name="absence.add", methods={"GET"})
     */
    public function add(Request $request)
    {
        $id = $request->get('id');

        $this->dbprefix = $GLOBALS['dbprefix'];
        $this->droits = $GLOBALS['droits'];

        $this->setAdminPermissions();

        $this->agents_multiples = ($this->admin or in_array(9, $this->droits));

        if ($this->config('Absences-adminSeulement') and !$this->admin) {
            return $this->output('access-denied.html.twig');
        }

        $this->setCommonTemplateParams();

        $this->templateParams(array(
            'reasons'               => $this->availablesReasons(),
            'reason_types'          => $this->reasonTypes(),
            'agents'                => $this->getAgents(),
            'fullday_checked'       => $this->config('Absences-journeeEntiere'),
        ));

        return $this->output('absences/add.html.twig');
    }

    /**
     * @Route("/absence/{id}", name="absence.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {

        $id = $request->get('id');

        $this->droits = $GLOBALS['droits'];

        $adminN1 = false;
        $adminN2 = false;

        // Si droit de gestion des absences N1 ou N2 sur l'un des sites : accès à cette page autorisé
        // Les droits d'administration des absences seront ajustés ensuite
        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((200+$i), $this->droits)) {
                $adminN1 = true;
            }
            if (in_array((500+$i), $this->droits)) {
                $adminN2 = true;
            }
        }

        $agents_multiples = ($adminN1 or $adminN2 or in_array(9, $this->droits));


        $a = new \absences();
        $a->fetchById($id);

        if (empty($a->elements)) {
            include __DIR__.'/../include/accessDenied.php';
        }

        $absence = $a->elements;
        $absence['motif'] = html_entity_decode($a->elements['motif'], ENT_QUOTES);
        $absence['motif_autre'] = html_entity_decode($a->elements['motif_autre'], ENT_QUOTES);
        $absence['commentaires'] = html_entity_decode($a->elements['commentaires'], ENT_QUOTES);

        $agents = $a->elements['agents'];
        $debutSQL = filter_var($a->elements['debut'], FILTER_CALLBACK, array("options"=>"sanitize_dateTimeSQL"));
        $finSQL = filter_var($a->elements['fin'], FILTER_CALLBACK, array("options"=>"sanitize_dateTimeSQL"));
        $valide = filter_var($a->elements['valide_n2'], FILTER_SANITIZE_NUMBER_INT);
        $valideN1 = $a->elements['valide_n1'];
        $ical_key = $a->elements['ical_key'];
        $cal_name = $a->elements['cal_name'];

        // Traitement des dates et des heures
        $absence['demande'] = dateFr($absence['demande'], true);
        $debut = dateFr3($debutSQL);
        $fin = dateFr3($finSQL);

        $hre_debut = substr($debut, -8);
        $hre_fin = substr($fin, -8);
        $debut = substr($debut, 0, 10);
        $fin = substr($fin, 0, 10);

        $admin = $adminN1 || $adminN2;

        $absence['editable'] = false;

        if ($admin
            or ($valide==0 and $valideN1==0)
            or $this->config('Absences-validation')==0) {
            $absence['editable'] = true;
        }

        $absence['status'] = 'ASKED';
        $absence['status_editable'] = ($adminN1 or $adminN2) ? true : false;
        if ($valide == 0 && $valideN1 > 0) {
            $absence['status'] = 'ACCEPTED_N1';
        }
        if ($valide > 0) {
            $absence['status'] = 'ACCEPTED_N2';
            $absence['status_editable'] = $adminN2 ? true : false;
            $absence['editable'] = $adminN2 ? true : false;
        }
        if ($valide == 0 && $valideN1 < 0) {
            $absence['status'] = 'REJECTED_N1';
        }
        if ($valide < 0) {
            $absence['status'] = 'REJECTED_N2';
            $absence['status_editable'] = $adminN2 ? true : false;
            $absence['editable'] = $adminN2 ? true : false;
        }

        // Si l'absence est importée depuis un agenda extérieur, on interdit la modification
        if ($ical_key and substr($cal_name, 0, 14) != 'PlanningBiblio') {
            $absence['editable'] = false;
            $admin=false;
        }

        // Si l'option "Absences-notifications-agent-par-agent" est cochée, adapte la variable $adminN1 en fonction des agents de l'absence. S'ils sont tous gérés, $adminN1 = true, sinon, $adminN1 = false
        if ($this->config('Absences-notifications-agent-par-agent') and $adminN1) {
            $perso_ids_verif = array(0);

            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $perso_ids_verif[] = $elem['perso_id'];
                }
            }

            foreach ($agents as $elem) {
                if (!in_array($elem['perso_id'], $perso_ids_verif)) {
                    $adminN1 = false;
                    break;
                }
            }
        }

        // Sécurité
        // Droit 6 = modification de ses propres absences
        // Les admins ont toujours accès à cette page
        $acces = ($adminN1 or $adminN2);
        if (!$acces) {
            // Les non admin ayant le droits de modifier leurs absences ont accès si l'absence les concerne
            $acces = (in_array(6, $this->droits) and $absence['perso_id'] == $_SESSION['login_id']) ? true : false;
        }
        // Si config Absences-adminSeulement, seuls les admins ont accès à cette page
        if ($this->config('Absences-adminSeulement') and !($adminN1 or $adminN2)) {
            $acces=false;
        }

        // Multisites, ne pas afficher les absences des agents d'un site non géré
        if ($this->config('Multisites-nombre') > 1) {
            // $sites_agents comprend l'ensemble des sites en lien avec les agents concernés par cette modification d'absence
            $sites_agents=array();
            foreach ($agents as $elem) {
                if (is_array($elem['sites'])) {
                    foreach ($elem['sites'] as $site) {
                        if (!in_array($site, $sites_agents)) {
                            $sites_agents[]=$site;
                        }
                    }
                }
            }

            if (!$this->config('Absences-notifications-agent-par-agent')) {
                $adminN1 = false;
            }
            $adminN2 = false;

            foreach ($sites_agents as $site) {
                if (!$this->config('Absences-notifications-agent-par-agent')) {
                    if (in_array((200+$site), $this->droits)) {
                        $adminN1 = true;
                    }
                }
                if (in_array((500+$site), $this->droits)) {
                    $adminN2 = true;
                }
            }

            if (!($adminN1 or $adminN2) and !$acces) {
                $acces = false;
            }
        }

        // Liste des agents
        $agents_tous = array();
        if ($agents_multiples) {
            $db_perso=new \db();
            $db_perso->select2("personnel", "*", array("supprime"=>0,"id"=>"<>2"), "order by nom,prenom");
            $agents_tous=$db_perso->result?$db_perso->result:array();
        }

        $display_autre = in_array(strtolower($absence['motif']), array("autre","other")) ? 1 : 0;

        $this->templateParams(array(
            'id'                    => $id,
            'access'                => $acces,
            'absences_tous'         => $this->config('Absences-tous'),
            'absences_validation'   => $this->config('Absences-validation'),
            'admin'                 => $admin ? 1 : 0,
            'adminN2'               => $adminN2 ? 1 : 0,
            'agents_multiples'      => $agents_multiples,
            'agents'                => $agents,
            'agents_tous'           => $agents_tous,
            'absence'               => $absence,
            'debut'                 => $debut,
            'fin'                   => $fin,
            'hre_debut'             => $hre_debut,
            'hre_fin'               => $hre_fin,
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'loggedin_id'           => $_SESSION['login_id'],
            'loggedin_name'         => $_SESSION['login_nom'],
            'loggedin_firstname'    => $_SESSION['login_prenom'],
            'reasons'               => $this->availablesReasons(),
            'reason_types'          => $this->reasonTypes(),
            'display_autre'         => $display_autre,
            'right701'              => in_array(701, $this->droits) ? 1 : 0,
        ));

        $this->templateParams(array('documents' => $this->getDocuments($a->id)));
        return $this->output('absences/edit.html.twig');
    }

    /**
     *@Route("/absence", name="absence.save", methods = {"POST"})
     */
    public function save_absence(Request $request, Session $session) {

        // Save absence(s).
        $this->dbprefix = $GLOBALS['dbprefix'];
        $this->droits = $GLOBALS['droits'];
        $this->setAdminPermissions();

        $session->getFlashBag()->clear();

        $this->agents_multiples = ($this->admin or in_array(9, $this->droits));

        if ($this->config('Absences-adminSeulement') and !$this->admin) {
            return $this->output('access-denied.html.twig');
        }

        $result = $this->save($request, $this->admin);
        $file = $request->files->get('documentFile');
        if (!empty($file)) {
            $token = $request->get("token");
            if (!$this->isCsrfTokenValid('upload', $token)) {
                return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,['content-type' => 'text/plain']);
            }
            $filename = $file->getClientOriginalName();
            $ad = new AbsenceDocument();
            $ad->absence_id($result['id']);
            $ad->filename($filename);
            $ad->date(new DateTime());
            $this->entityManager->persist($ad);
            $this->entityManager->flush();
            $file->move(__DIR__ . AbsenceDocument::UPLOAD_DIR . $result['id'] . '/' . $ad->id(), $filename);
        }

        $succes = urlencode("L'absence a été modifiée avec succès");
        $succes2 = urlencode("L'absence a été enregistrée");
        $succes3 = urlencode("La demande d'absence a été enregistrée");

        if ($result['msg'] === $succes || $result['msg'] === $succes2 || $result['msg'] === $succes3){
            $session->getFlashBag()->add('notice', urldecode($result['msg']));
        }

        if ($result['msg2'] != " "){
            $session->getFlashBag()->add('error', urldecode($result['msg2']));
        }
        return $this->redirectToRoute('absence.index');
    }

    private function getDocuments($id) {
        $docsarray = array();
        $absdocs = $this->entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $id]);
        foreach ($absdocs as $absdoc) {
           $docsarray[] = array('filename' => $absdoc->filename(), 'id' => $absdoc->id());
        }
        return $docsarray;
    }

    private function save(Request $request) {
        $perso_id = $request->get('perso_id');
        $id = $request->get('id');

        $perso_ids = array();
        if (!empty($perso_id)) {
            $perso_ids[] = $perso_id;
        } else {
            $perso_ids = $this->filterAgents($request->get('perso_ids'));
        }

        // Sécurité : Si l'agent enregistrant l'absence n'est pas admin et n'est pas dans la liste des absents ou pas autorisé à enregistrer des absences pour plusieurs agents, l'accès est refusé.
        $access = false;
        if ($this->admin) {
            $access = true;
        } elseif (count($perso_ids) == 1 and in_array($_SESSION['login_id'], $perso_ids)) {
            $access = true;
        } elseif ($this->agents_multiples and in_array($_SESSION['login_id'], $perso_ids)) {
            $access = true;
        }

        if (!$access) {
            return $this->output('access-denied.html.twig');
        }

        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $hre_debut = $request->get('hre_debut');
        $hre_fin = $request->get('hre_fin');
        $motif = $request->get('motif');
        $motif_autre = trim($request->get('motif_autre'));
        $commentaires = $request->get('commentaires');
        $CSRFToken = $request->get('CSRFToken');
        $rrule = $request->get('recurrence-hidden');
        $rcheckbox = $request->get('recurrence-checkbox');
        $recurrenceModif = $request->get('recurrence-modif');
        $valide = $request->get('valide');
        $allday = $request->get('allday');
        $groupe = $request->get('groupe');
        $etat = $request->get('etat');

        $hre_debut = !empty($hre_debut) ? $hre_debut : '00:00:00';
        $hre_fin = !empty($hre_fin) ? $hre_fin : '23:59:59';

        $pj1 = $request->get('pj1') ? 1 : 0;
        $pj2 = $request->get('pj2') ? 1 : 0;
        $so = $request->get('so') ? 1 : 0;

        // Récurrence : supprime la règle rrule si la case à cocher "Récurrence" n'est pas cochée
        if (!$rcheckbox) {
            $rrule = null;
        }

        // Force $valide = 0 si login non admin
        if (!$this->admin) {
            $valide = 0;
        }

        $a = new \absences();

        if(!$id){ //Si nouvelle absence, on ajoute
            $a->debut = $debut;
            $a->fin = $fin;
            $a->hre_debut = $hre_debut;
            $a->hre_fin = $hre_fin;
            $a->perso_ids = $perso_ids;
            $a->commentaires = $commentaires;
            $a->etat = $etat;
            $a->motif = $motif;
            $a->motif_autre = $motif_autre;
            $a->CSRFToken = $CSRFToken;
            $a->rrule = $rrule;
            $a->valide = $valide;
            $a->pj1 = $pj1;
            $a->pj2 = $pj2;
            $a->so = $so;
            $a->add();
            $msg2 = $a->msg2;
            $msg2Type = $a->msg2_type;

            // Confirmation de l'enregistrement
            if ($this->config('Absences-validation') and !$this->admin) {
                $msg="La demande d'absence a été enregistrée";
            } else {
                $msg="L'absence a été enregistrée";
            }
            $msg=urlencode($msg);
        } else { // Modification
            $rrule = $request->get('rrule');
            // perso_ids est un tableau de 1 ou plusieurs ID d'agent. Complété même si l'absence ne concerne qu'une personne
            $perso_ids = $request->get('perso_ids');
            $perso_ids=filter_var_array($perso_ids, FILTER_SANITIZE_NUMBER_INT);

            // Création du groupe si plusieurs agents et que le groupe n'est pas encore créé
            if (count($perso_ids)>1 and !$groupe) {
                // ID du groupe (permet de regrouper les informations pour affichage en une seule ligne et modification du groupe)
                $groupe=time()."-".rand(100, 999);
            }

            $fin = $fin ? $fin : $debut;

            $debutSQL = dateSQL($debut);
            $finSQL = dateSQL($fin);
            $debut_sql = $debutSQL." ".$hre_debut;
            $fin_sql = $finSQL." ".$hre_fin;

            // Récupération des informations des agents concernés par l'absence avant sa modification
            // ET autres informations concernant l'absence avant modification
            $a->fetchById($id);
            $agents = $a->elements['agents'];
            $commentaires1 = $a->elements['commentaires'];
            $debut1 = $a->elements['debut'];
            $fin1 = $a->elements['fin'];
            $motif1 = $a->elements['motif'];
            $motif_autre1 = $a->elements['motif_autre'];
            $perso_ids1 = $a->elements['perso_ids'];
            $pj1_1 = $a->elements['pj1'];
            $pj2_1 = $a->elements['pj2'];
            $so_1 = $a->elements['so'];
            $rrule1 = $a->elements['rrule'];
            $uid = $a->elements['uid'];
            $valide1_n1 = $a->elements['valide_n1'];
            $valide1_n2 = $a->elements['valide_n2'];
            $validation1_n1 = $a->elements['validation_n1'];
            $validation1_n2 = $a->elements['validation_n2'];

            if ($valide1_n2 > 0) {
                $valide1 = 1;
            } elseif ($valide1_n2 < 0) {
                $valide1 = -1;
            } elseif ($valide1_n1 > 0) {
                $valide1 = 2;
            } elseif ($valide1_n1 < 0) {
                $valide1 = -2;
            } else {
                $valide1 = 0;
            }

            // Si l'absence est importée depuis un agenda extérieur, on interdit la modification
            $iCalKey = $a->elements['ical_key'];
            $cal_name = $a->elements['cal_name'];
            if ($iCalKey and substr($cal_name, 0, 23) != 'PlanningBiblio-Absences') {
                include "include/accessDenied.php";
            }

            // Récuperation des informations des agents concernés par l'absence après sa modification (agents sélectionnés)
            $p = new \personnel();
            $p->supprime = array(0,1,2);
            $p->responsablesParAgent = true;
            $p->fetch();
            $agents_tous = $p->elements;

            // Tous les agents
            foreach ($agents_tous as $elem) {
                if (in_array($elem['id'], $perso_ids)) {
                    $agents_selectionnes[$elem['id']] = $elem;
                }
            }

            // Tous les agents concernés (ajoutés, supprimés, restants)
            $agents_concernes=array();
            // Ajoute au tableau $agents_concernes les agents qui étaient présents avant la modification
            foreach ($agents as $elem) {
                if (!array_key_exists($elem['perso_id'], $agents_concernes)) {
                    $agents_concernes[$elem['perso_id']] = $agents_tous[$elem['perso_id']];
                }
            }

            // Ajoute au tableau $agents_concernes les agents sélectionnés
            foreach ($agents_selectionnes as $elem) {
                if (!array_key_exists($elem['id'], $agents_concernes)) {
                    $agents_concernes[$elem['id']] = $elem;
                }
            }

            // Les agents supprimés de l'absence
            $agents_supprimes=array();
            foreach ($agents as $elem) {
                if (!array_key_exists($elem['perso_id'], $agents_selectionnes)) {
                    $agents_supprimes[$elem['perso_id']] = $agents_tous[$elem['perso_id']];
                }
            }

            // Les agents ajoutés à l'absence
            $agents_ajoutes=array();
            foreach ($agents_selectionnes as $elem) {
                if (!in_array($elem['id'], $perso_ids1)) {
                    $agents_ajoutes[]=$elem;
                }
            }

            // Comparaison des anciennes données et des nouvelles
            $modification = (
                !empty($agents_ajoutes)
                or !empty($agents_supprimes)
                or $debut1 != $debut_sql
                or $fin1 != $fin_sql
                or htmlentities($motif1, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false) != htmlentities($motif, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false)
                or htmlentities($motif_autre1, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false) != htmlentities($motif_autre, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false)
                or htmlentities($commentaires1, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false) != htmlentities($commentaires, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false)
                or $valide1 != $valide
                or $rrule1 != $rrule
                or empty($pj1_1) != empty($pj1)
                or empty($pj2_1) != empty($pj2)
                or empty($so_1) != empty($so)
            );

            // Si aucune modification, on retourne directement à la liste des absences
            if (!$modification) {
                $msg = urlencode("L'absence a été modifiée avec succès");
            }

            // Sécurité
            // Droit 6 = modification de ses propres absences
            // Droit 9 = Droit d'enregistrer des absences pour d'autres agents
            // Droits 20x = modification de toutes les absences (admin seulement)
            // Droits 50x = validation N2

            $acces = false;
            for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
                if (in_array((200+$i), $this->droits) or in_array((500+$i), $this->droits)) {
                    $acces = true;
                }
            }

            if (!$acces) {
                if ((in_array(6, $this->droits) and count($perso_ids) == 1 and in_array($_SESSION['login_id'], $perso_ids))
                or (in_array(9, $this->droits) and in_array(6, $this->droits) and in_array($_SESSION['login_id'], $perso_ids))) {
                    $acces = true;
                }
            }

            if (!$acces) {
                return $this->redirectToRoute('access-denied');
            }

            // Définition des droits d'accès pour les administrateurs en multisites
            // Multisites, ne pas modifier les absences si aucun agent n'appartient à un site géré
            if ($this->config('Multisites-nombre') > 1) {
                // $sites_agents comprend l'ensemble des sites en lien avec les agents concernés par cette modification d'absence
                $sites_agents=array();
                foreach ($agents_concernes as $elem) {
                    if (is_array($elem['sites'])) {
                        foreach ($elem['sites'] as $site) {
                            if (!in_array($site, $sites_agents)) {
                                $sites_agents[]=$site;
                            }
                        }
                    }
                }

                $admin = false;
                foreach ($sites_agents as $site) {
                    if (in_array((200+$i), $this->droits) or in_array((500+$i), $this->droits)) {
                        $admin = true;
                        break;
                    }
                }

            } else {
                $admin = in_array(201, $this->droits);
            }


            // Etats de validation. Par défaut, on met les états  initiaux (avant modification) pour conserver ces informations si aucun changement n'apparaît sur le champ "validation"
            $valide_n1 = $valide1_n1;
            $valide_n2 = $valide1_n2;
            $validation_n1 = $validation1_n1;
            $validation_n2 = $validation1_n2;

            // On met à jour les infos seulement si une modification apparaît sur le champ "validation"de façon à garder l'horodatage initial ($valide != $valide1)
            if ($this->config('Absences-validation') and $valide != $valide1) {

            // Initialisation et retour à l'état demandé
                $valide_n1 = 0;
                $validation_n1 = '0000-00-00 00:00:00';
                $valide_n2 = 0;
                $validation_n2 = '0000-00-00 00:00:00';

                // Validation ou refus niveau 2
                if ($valide == 1 or $valide == -1) {
                    $valide_n1 = $valide1_n1;
                    $validation_n1 = $validation1_n1;
                    $valide_n2 = $valide * $_SESSION['login_id'];
                    $validation_n2 = date("Y-m-d H:i:s");
                }
                // Validation ou refus niveau 1
                elseif ($valide == 2 or $valide == -2) {
                    $valide_n1 = ($valide/2) * $_SESSION['login_id'];
                    $validation_n1 = date("Y-m-d H:i:s");
                }
            }

            // Modification d'une absence récurrente
            if ($rrule) {

            // $nouvel_enregistrement permet de définir s'il y aura besoin d'un nouvel enregistrement dans le cas de l'ajout d'une exception ou de la modification des événements à venir
                $nouvel_enregistrement = false;
                switch ($recurrenceModif) {
                    case 'current':
                        // On ajoute une exception à l'événement ICS
                        $exdate = preg_replace('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', "$1$2$3T$4$5$6", $debut1);

                        foreach ($agents_concernes as $elem) {
                            $a = new \absences();
                            $a->CSRFToken = $CSRFToken;
                            $a->perso_id = $elem['id'];
                            $a->uid = $uid;
                            $a->ics_add_exdate($exdate);
                        }
                        // Un nouvel enregistrement sera créé pour l'occurence modifiée
                        $nouvel_enregistrement = true;
                        $rrule = false;
                        break;

                    case 'next':
                        // On sépare les événemnts précédents des suivants

                        // Récupère l'événement préalablement enregistré pour récupérer la date de début DTSTART et les dates d'exceptions EXDATE
                        $a = new \absences();
                        $a->uid = $uid;
                        $a->ics_get_event();
                        $event = $a->elements;

                        // Si la date de début correspond au premier événement de la série ($debut1 == DTSTART), la modification de l'occurence et des suivantes signifie la modification de toutes les occurrences
                        // On modifie donc toutes les occurences comme si "all" avait été choisi et on quitte sans ajouter de nouvel enregistrement
                        preg_match("/DTSTART.*:(\d*)/", $event, $matches);
                        if (date('Ymd', strtotime($debut1)) == $matches[1]) {
                            $a = new \absences();
                            $a->debut = $debut;
                            $a->fin = $fin;
                            $a->groupe = $groupe;
                            $a->hre_debut = $hre_debut;
                            $a->hre_fin = $hre_fin;
                            $a->perso_ids = $perso_ids;
                            $a->commentaires = $commentaires;
                            $a->motif = $motif;
                            $a->motif_autre = $motif_autre;
                            $a->CSRFToken = $CSRFToken;
                            $a->rrule = $rrule;
                            $a->valide_n1 = $valide_n1;
                            $a->valide_n2 = $valide_n2;
                            $a->validation_n1 = $validation_n1;
                            $a->validation_n2 = $validation_n2;
                            $a->pj1 = $pj1;
                            $a->pj2 = $pj2;
                            $a->so = $so;
                            $a->uid = $uid;
                            $a->id = $id;
                            $a->ics_update_event();

                            $nouvel_enregistrement = false;

                            break;
                        }

                        // On définie la date de fin de la première série. Cette date doit être sur le fuseau GMT
                        // On commence par retirer une seconde de façon à ce que la première série s'arrête bien avant la deuxième
                        $serie1_end = date('Ymd\THis', strtotime($debut1.' -1 second'));

                        // Puis on récupère la date du fuseau GMT
                        $datetime = new DateTime($serie1_end, new DateTimeZone(date_default_timezone_get()));
                        $datetime->setTimezone(new DateTimeZone('GMT'));
                        $serie1_end = $datetime->format('Ymd\THis\Z');

                        // On met à jour la première série : modification de RRULE en mettant UNTIL à la date de fin
                        foreach ($agents_concernes as $elem) {
                            $a = new \absences();
                            $a->CSRFToken = $CSRFToken;
                            $a->perso_id = $elem['id'];
                            $a->uid = $uid;
                            $a->ics_update_until($serie1_end);
                        }

                        // Un nouvel événement sera créé pour les occurences à venir
                        $nouvel_enregistrement = true;

                        // Si des exceptions existaient, on les réécrit dans le nouvel enregistrement
                        if (strpos($event, 'EXDATE')) {
                            preg_match("/(EXDATE.*\n)/", $event, $matches);
                            $add_exdate = $matches[1];
                        }

                        // Si la fin de récurrence est définie par l'attribut COUNT, il doit être adapté. Les occurences antérieures à $serie1_end doivent être déduites.
                        if (strpos($rrule, 'COUNT')) {
                            // Récupération du nombre d'occurences antérieures à la date de l'événement choisi
                            $db = new \db();
                            $db->select2('absences', 'debut', array('cal_name' => 'LIKEPlanningBiblio-Absences%', 'uid' => $uid, 'debut' => "<$debut1"), 'GROUP BY `debut`');
                            $nb = $db->nb;

                            // Récupération de la valeur initiale de COUNT
                            preg_match('/COUNT=(\d*)/', $rrule, $matches);

                            // Soustraction
                            $count = $matches[1] - $nb;

                            // Réécriture de la règle
                            $rrule = preg_replace('/COUNT=(\d*)/', "COUNT=$count", $rrule);
                        }

                        break;

                    case 'all':
                        // On modifie toutes les occurences de l'événement.
                        // Modification de l'événement ICS pour les agents qui en faisaient déjà partie, ajout pour les nouveaux, suppression pour les agents retirés

                        $a = new \absences();
                        $a->debut = $debut;
                        $a->fin = $fin;
                        $a->groupe = $groupe;
                        $a->hre_debut = $hre_debut;
                        $a->hre_fin = $hre_fin;
                        $a->perso_ids = $perso_ids;
                        $a->commentaires = $commentaires;
                        $a->motif = $motif;
                        $a->motif_autre = $motif_autre;
                        $a->CSRFToken = $CSRFToken;
                        $a->rrule = $rrule;
                        $a->valide_n1 = $valide_n1;
                        $a->valide_n2 = $valide_n2;
                        $a->validation_n1 = $validation_n1;
                        $a->validation_n2 = $validation_n2;
                        $a->pj1 = $pj1;
                        $a->pj2 = $pj2;
                        $a->so = $so;
                        $a->uid = $uid;
                        $a->id = $id;
                        $a->ics_update_event();

                        break;
                }

                if ($nouvel_enregistrement) {
                    // On enregistre l'événement modifié dans la base de données, et dans les fichiers ICS si $rrule
                    $a = new \absences();
                    $a->debut = $debut;
                    $a->fin = $fin;
                    $a->hre_debut = $hre_debut;
                    $a->hre_fin = $hre_fin;
                    $a->perso_ids = $perso_ids;
                    $a->commentaires = $commentaires;
                    $a->motif = $motif;
                    $a->motif_autre = $motif_autre;
                    $a->CSRFToken = $CSRFToken;
                    $a->rrule = $rrule;
                    if (!empty($add_exdate)) {
                        $a->exdate = $add_exdate;
                    }
                    $a->valide = $valide;
                    $a->pj1 = $pj1;
                    $a->pj2 = $pj2;
                    $a->so = $so;
                    $a->uid = $uid;
                    $a->id = $id;
                    $a->add();
                    $msg2 = $a->msg2;
                    $msg2_type = $a->msg2_type;
                }
            } else { // Si pas de récurrence, modifiation des informations directement dan la base de données
                // Mise à jour du champs 'absent' dans 'pl_poste'
                // Suppression du marquage absent pour tous les agents qui étaient concernés par l'absence avant sa modification
                // Comprend les agents supprimés et ceux qui restent
                /**
                * @note : le champ pl_poste.absent n'est plus mis à 1 lors de la validation des absences depuis la version 2.4
                * mais nous devons garder la mise à 0 pour la suppression ou modifications des absences enregistrées avant cette version.
                * NB : le champ pl_poste.absent est également utilisé pour barrer les agents depuis le planning, donc on ne supprime pas toutes ses valeurs
                */
                $dbprefix = $GLOBALS['dbprefix'];

                $ids = implode(",", $perso_ids1);
                $db = new \db();
                $debut1 = $db->escapeString($debut1);
                $fin1 = $db->escapeString($fin1);
                $ids = $db->escapeString($ids);
                $req = "UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
                CONCAT(`date`,' ',`debut`) < '$fin1' AND CONCAT(`date`,' ',`fin`) > '$debut1'
                AND `perso_id` IN ($ids)";
                $db->query($req);

                // Préparation des données pour mise à jour de la table absence et insertion pour les agents ajoutés
                $data = array('motif' => $motif, 'motif_autre' => $motif_autre, 'commentaires' => $commentaires, 'debut' => $debut_sql, 'fin' => $fin_sql, 'groupe' => $groupe,
                'valide' => $valide_n2, 'validation' => $validation_n2, 'valide_n1' => $valide_n1, 'validation_n1' => $validation_n1);

                if (in_array(701, $this->droits)) {
                    $data = array_merge($data, array("pj1"=>$pj1, "pj2"=>$pj2, "so"=>$so));
                }

                // Mise à jour de la table 'absences'
                // Sélection des lignes à modifier dans la base à l'aide du champ id car fonctionne également si le groupe n'existait pas au départ contrairement au champ groupe
                // (dans le cas d'une absence simple ou absence simple transformée en absence multiple).
                // Récupération de tous les ids de l'absence avant modification
                $ids = array();
                foreach ($agents as $agent) {
                    $ids[] = $agent['absence_id'];
                }
                $ids = implode(",", $ids);
                $where = array("id"=>"IN $ids");

                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update("absences", $data, $where);

                // Ajout de nouvelles lignes dans la table absences si des agents ont été ajoutés
                $insert = array();
                foreach ($agents_ajoutes as $agent) {
                    $insert[] = array_merge($data, array('perso_id'=>$agent['id']));
                }
                if (!empty($insert)) {
                    $db = new \db();
                    $db->CSRFToken = $CSRFToken;
                    $db->insert("absences", $insert);
                }


                // Suppresion des lignes de la table absences concernant les agents supprimés
                $agents_supprimes_ids=array();
                foreach ($agents_supprimes as $agent) {
                    $agents_supprimes_ids[] = $agent['id'];
                }
                $agents_supprimes_ids = implode(",", $agents_supprimes_ids);

                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->delete("absences", array("id"=>"IN $ids", "perso_id"=>"IN $agents_supprimes_ids"));
            }


            // Envoi d'un mail de notification
            $sujet = "Modification d'une absence";

            // Choix des destinataires des notifications selon le degré de validation
            // Si pas de validation, la notification est envoyée au 1er groupe
            if ($this->config('Absences-validation') == '0') {
                $notifications = 2;
            } else {
                if ($valide1_n2 <= 0 and $valide_n2 > 0) {
                    $sujet = "Validation d'une absence";
                    $notifications = 4;
                } elseif ($valide1_n2 >= 0 and $valide_n2 < 0) {
                    $sujet="Refus d'une absence";
                    $notifications = 4;
                } elseif ($valide1_n1 <= 0 and $valide_n1 > 0) {
                    $sujet = "Acceptation d'une absence (en attente de validation hiérarchique)";
                    $notifications = 3;
                } elseif ($valide1_n1 >= 0 and $valide_n1 < 0) {
                    $sujet = "Refus d'une absence (en attente de validation hiérarchique)";
                    $notifications = 3;
                } else {
                    $sujet = "Modification d'une absence";
                    $notifications = 2;
                }
            }

            $workflow = 'A';
            $entityManager = $GLOBALS['entityManager'];
            $reason = $entityManager->getRepository(AbsenceReason::class)->findoneBy(['valeur' => $motif]);
            if ($reason) {
                $workflow = $reason->notification_workflow();
            }
            $notifications = "-$workflow$notifications";

            // Liste des responsables
            // Pour chaque agent, recherche des responsables absences

            /** Si le paramètre "Absences-notifications-agent-par-agent" est coché,
             * les notifications de modification d'absence sans validation sont envoyés aux responsables enregistrés dans dans la page Validations / Notifications
             * Les absences validées au niveau 1 sont envoyés aux agents ayant le droit de validation niveau 2
             * Les absences validées au niveau 2 sont envoyés aux agents concernés par l'absence
             */

            if ($this->config('Absences-notifications-agent-par-agent')) {
                $a = new \absences();
                $a->getRecipients2($agents_tous, $agents_concernes, $notifications, 500, $debutSQL, $finSQL);
                $destinataires = $a->recipients;
            } else {
                $responsables = array();
                foreach ($agents_concernes as $agent) {
                    $a = new  \absences();
                    $a->getResponsables($debutSQL, $finSQL, $agent['id']);
                    $responsables = array_merge($responsables, $a->responsables);
                }

                // Pour chaque agent, recherche des destinataires de notification en fonction de la config. (responsables absences, responsables directs, agent).
                $ids = array_column($agents, 'perso_id');
                $staff_members = $entityManager->getRepository(Agent::class)->findById($ids);
                $destinataires = array();
                foreach ($staff_members as $member) {
                    $a = new \absences();
                    $a->getRecipients($notifications, $responsables, $member);
                    $destinataires = array_merge($destinataires, $a->recipients);
                }

                // Suppresion des doublons dans les destinataires
                $tmp = array();
                foreach ($destinataires as $elem) {
                    if (!in_array($elem, $tmp)) {
                        $tmp[] = $elem;
                    }
                }
                $destinataires = $tmp;
            }

            // Recherche des plages de SP concernées pour ajouter cette information dans le mail.
            $a = new \absences();
            $a->debut = $debut_sql;
            $a->fin = $fin_sql;
            $a->perso_ids=$perso_ids;
            $a->infoPlannings();
            $infosPlanning = $a->message;

            // Message
            usort($agents_selectionnes, "cmp_prenom_nom");
            usort($agents_supprimes, "cmp_prenom_nom");

            $message = "<b><u>$sujet</u></b> :";
            $message .= "<ul><li>";
            if ((count($agents_selectionnes) + count($agents_supprimes)) >1) {
                $message .= "Agents :<ul>\n";
                foreach ($agents_selectionnes as $agent) {
                    $message .= "<li><strong>{$agent['prenom']} {$agent['nom']}</strong></li>\n";
                }
                foreach ($agents_supprimes as $agent) {
                    $message .= "<li><span class='striped'>{$agent['prenom']} {$agent['nom']}</span></li>\n";
                }
                $message .= "</ul>\n";
            } else {
                $message .= "Agent : <strong>{$agents_selectionnes[0]['prenom']} {$agents_selectionnes[0]['nom']}</strong>\n";
            }
            $message .= "</li>\n";

            $message .= "<li>Début : <strong>$debut";
            if ($hre_debut!="00:00:00") {
                $message .= " ".heure3($hre_debut);
            }
            $message .= "</strong></li><li>Fin : <strong>$fin";
            if ($hre_fin != "23:59:59") {
                $message .= " ".heure3($hre_fin);
            }
            $message .= "</strong></li>";

            if ($rrule) {
                $rruleText = recurrenceRRuleText($rrule);
                $message .= "<li>Récurrence : $rruleText</li>";
            }

            $message.="<li>Motif : $motif";
            if ($motif_autre) {
                $message.=" / $motif_autre";
            }
            $message.="</li>";

            if ($this->config('Absences-validation')) {
                $validationText = "Demand&eacute;e";
                if ($valide_n2>0) {
                    $validationText="Valid&eacute;e";
                } elseif ($valide_n2<0) {
                    $validationText = "Refus&eacute;e";
                } elseif ($valide_n1>0) {
                    $validationText = "Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
                } elseif ($valide_n1<0) {
                    $validationText = "Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
                }

                $message .= "<li>Validation : $validationText</li>\n";
            }

            if ($commentaires) {
                $message .= "<li>Commentaire:<br/>$commentaires</li>";
            }
            $message .= "</ul>";

            // Ajout des informations sur les plannings
            $message .= $infosPlanning;

            // Ajout du lien permettant de rebondir sur l'absence
            $url = createURL("/absence/edit/$id");
            $message .= "<br/><br/>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a><br/><br/>";

            // Envoi du mail
            $m = new \CJMail();
            $m->subject = $sujet;
            $m->message = $message;
            $m->to = $destinataires;
            $m->send();

            // Si erreur d'envoi de mail, affichage de l'erreur
            $msg2 = null;
            $msg2Type = null;
            if ($m->error) {
                $msg2 = urlencode($m->error_CJInfo);
                $msg2Type = "error";
            }

            $msg = urlencode("L'absence a été modifiée avec succès");
        }


        return array(
            'msg' => $msg,
            'msg2' => $msg2,
            'msg2_type' => $msg2Type,
            'id' => $a->id
        );

    }

    private function filterAgents($perso_ids) {
        $valid_ids = array();

        if (!is_array($perso_ids)) {
            $perso_ids = array($perso_ids);
        }

        foreach ($perso_ids as $elem) {
            if (!empty($elem)) {
                $valid_ids[] = $elem;
            }
        }

        // Right "add absences for others"
        if (in_array(9, $_SESSION['droits'])) {
            return $valid_ids;
        }

        // Keep only managed agent on multi-sites mode
        if ($this->config('Multisites-nombre') > 1 and !$this->config('Absences-notifications-agent-par-agent')) {

            $managed_sites = array();
            for ($i = 1; $i < 31; $i++) {
                if (in_array((200 + $i), $_SESSION['droits']) or in_array((500 + $i), $_SESSION['droits'])) {
                    $managed_sites[] = $i;
                }
            }

            $agents = array();
            $agents_db = $this->entityManager->getRepository(Agent::class)->findBy(array('id' => $valid_ids));

            foreach ($agents_db as $elem) {
                $agents[$elem->id()] = $elem;
            }

            foreach ($valid_ids as $k => $v) {
                $keep = false;
                $agent_sites = json_decode($agents[$v]->sites());
                if (!is_array($agent_sites)) {
                    unset($valid_ids[$k]);
                    continue;
                }
                foreach ($agent_sites as $site) {
                    if (in_array($site, $managed_sites)) {
                        $keep = true;
                    }
                }
                if ($keep == false) {
                    unset($valid_ids[$k]);
                }
            }
        }

        // If Absences-notifications-agent-par-agent is true,
        // delete all agents the logged in user cannot create absences for.
        if ($this->config('Absences-notifications-agent-par-agent') and !$this->adminN2) {
            $accepted_ids = array($_SESSION['login_id']);

            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $accepted_ids[] = $elem['perso_id'];
                }
            }
            foreach ($valid_ids as $k => $v) {
                if (!in_array($v, $accepted_ids)) {
                    unset($valid_ids[$k]);
                }
            }
        }

        return $valid_ids;
    }

    private function setAdminPermissions()
    {
        // If can validate level 1: admin = true.
        // If can validate level 2: adminN2 = true.
        $this->adminN2 = false;
        $this->admin = false;
        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((200+$i), $this->droits)) {
                $this->admin = true;
            }
            if (in_array((500+$i), $this->droits)) {
                $this->admin = true;
                $this->adminN2 = true;
                break;
            }
        }
    }

    private function getAgents()
    {
        $agents = array();
        if ($this->agents_multiples) {
            $db = new \db();
            $db->select2('personnel', 'id', array('supprime' => '0', 'id' => '<>2'));
            $results = $db->result;
            $perso_ids = array();
            foreach ($results as $r) {
                $perso_ids[] = $r['id'];
            }
            $perso_ids = $this->filterAgents($perso_ids);

            $in = implode(',', $perso_ids);

            $db_perso = new \db();
            $db_perso->select2('personnel', null, array('supprime' => '0', 'id' => "IN$in"), 'ORDER BY nom,prenom');
            $agents = $db_perso->result ? $db_perso->result : array();
        }

        return $agents;
    }

    private function availablesReasons()
    {
        $db_reasons=new \db();
        $db_reasons->select("select_abs", null, null, "order by rang");

        // Liste des motifs utilisés
        $reasons_used = array();
        $db_reasons_used = new \db();
        $db_reasons_used->select("absences", "motif", null, "group by motif");
        if ($db_reasons_used->result) {
            foreach ($db_reasons_used->result as $elem) {
                $reasons_used[] = $elem['motif'];
            }
        }

        $reasons = array();
        if (is_array($db_reasons->result)) {
            foreach ($db_reasons->result as $elem) {
                $elem['unused'] = false;
                if (!in_array($elem['valeur'], $reasons_used)) {
                    $elem['unused'] = true;
                }
                $elem['valeur'] = html_entity_decode($elem['valeur'], ENT_QUOTES);
                $reasons[] = $elem;
            }
        }

        return $reasons;
    }

    private function reasonTypes()
    {
        $reason_types = array(
            array(
                'id' => 0,
                'valeur' => 'N1 cliquable'
            ),
            array(
                'id' => 1,
                'valeur' => 'N1 non-cliquable'
            ),
            array(
                'id' => 2,
                'valeur' => 'N2'
            )
        );

        return $reason_types;
    }

    private function absenceInfos()
    {
        $date = date("Y-m-d");
        $db = new \db();
        $db->query("SELECT * FROM `{$this->dbprefix}absences_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
        $absences_infos = array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $elem['debut_fr'] = dateFr($elem['debut']);
                $elem['fin_fr'] = dateFr($elem['fin']);
                $absences_infos[] = $elem;
            }
        }

        return $absences_infos;
    }

    private function setCommonTemplateParams()
    {
        $this->templateParams(array(
            'agent_preselection'    => $this->config('Absences-agent-preselection'),
            'absences_tous'         => $this->config('Absences-tous'),
            'absences_validation'   => $this->config('Absences-validation'),
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'admin'                 => $this->admin ? 1 : 0,
            'adminN2'               => $this->adminN2 ? 1 : 0,
            'loggedin_id'           => $_SESSION['login_id'],
            'loggedin_name'         => $_SESSION['login_nom'],
            'loggedin_firstname'    => $_SESSION['login_prenom'],
            'agents_multiples'      => $this->agents_multiples,
            'right701'              => in_array(701, $this->droits) ? 1 : 0,
            'abences_infos'         => $this->absenceInfos(),
        ));
    }
}
