<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\AbsenceDocument;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/absences/class.absences.php');

class AbsenceController extends BaseController
{


    /**
     * @Route("/absence", name="absence.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $reset = $request->get('reset');
        $droits = $GLOBALS['droits'];

        $debut = $debut ? $debut : (isset($_SESSION['oups']['absences_debut'])?$_SESSION['oups']['absences_debut']:null);
        $fin = $fin ? $fin : (isset($_SESSION['oups']['absences_fin'])?$_SESSION['oups']['absences_fin']:null);

        $p = new \personnel();
        $p->supprime = array(0,1,2);
        $p->fetch();
        $agents = $p->elements;


        //	Initialisation des variables
        $admin = false;
        $adminN2 = false;

        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((200+$i), $droits)) {
                $admin = true;
            }
            if (in_array((500+$i), $droits)) {
                $admin = true;
                $adminN2 = true;
                break;
            }
        }

        if ($admin) {
            $perso_id = filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);
            if ($perso_id === null) {
                $perso_id = isset($_SESSION['oups']['absences_perso_id'])?$_SESSION['oups']['absences_perso_id']:$_SESSION['login_id'];
            }
        } else {
            $perso_id = $_SESSION['login_id'];
        }
        if ($reset) {
            $perso_id = $_SESSION['login_id'];
        }

        $agents_supprimes = isset($_SESSION['oups']['absences_agents_supprimes'])?$_SESSION['oups']['absences_agents_supprimes']:false;
        $agents_supprimes = (isset($_GET['debut']) and isset($_GET['supprimes']))?true:$agents_supprimes;
        $agents_supprimes = (isset($_GET['debut']) and !isset($_GET['supprimes']))?false:$agents_supprimes;

        if ($reset) {
            $debut = null;
            $fin = null;
            $agents_supprimes = false;
        }

        $_SESSION['oups']['absences_debut'] = $debut;
        $_SESSION['oups']['absences_fin'] = $fin;
        $_SESSION['oups']['absences_perso_id'] = $perso_id;
        $_SESSION['oups']['absences_agents_supprimes'] = $agents_supprimes;

        $debutSQL=dateSQL($debut);
        $finSQL=dateSQL($fin);

        // Multisites : filtre pour n'afficher que les agents du site voulu
        $sites = null;
        if ($this->config('Multisites-nombre')>1) {
            $sites = array();
            for ($i=1; $i<31; $i++) {
                if (in_array((200 + $i), $droits) or in_array((500 + $i), $droits)) {
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

        // Tri par défaut du tableau
        $sort="[[0],[1]]";
        if ($admin or (!$this->config('Absences-adminSeulement') and in_array(6, $droits))) {
            $sort="[[1],[2]]";
        }

        $this->templateParams(array(
            'debut'     => $debut,
            'fin'       => $fin,
            'perso_id'  => $perso_id,
            'admin'     => $admin,
            'sort'      => $sort,
        ));

        if ($admin) {
            $p = new \personnel();
            if ($agents_supprimes) {
                $p->supprime = array(0,1);
            }
            $p->responsablesParAgent = true;
            $p->fetch();
            $agents_menu = $p->elements;

            // Filtre pour n'afficher que les agents gérés en configuration multisites
            if ($this->config('Multisites-nombre') > 1) {
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
                }

                $agents_menu = $tmp;
            }

            // Liste des agents à conserver :
            $perso_ids = array_keys($agents_menu);

            $this->templateParams(array(
                'agents_menu'           => $agents_menu,
                'agents_deleted'        => $agents_supprimes,
            ));
        }

        $this->templateParams(array(
            'absences'              => $absences,
            'can_manage_sup_doc'    => in_array(701, $droits) ? 1 : 0,
        ));

        $visibles_absences = array();
        if ($absences) {
            foreach ($absences as $elem) {

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

                $id=$elem['id'];

                $elem['absdocs'] = $this->entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $id]);

                $elem['nom_n1a'] = $elem['valide_n1'] != 99999 ? nom($elem['valide_n1'], 'nom p', $agents).", " : null;
                $elem['nom_n1b'] = $elem['valide_n1'] != -99999 ? nom(-$elem['valide_n1'], 'nom p', $agents).", " : null;
                $elem['nom_n2a'] = $elem['valide'] != 99999 ? nom($elem['valide'], 'nom p', $agents).", " : null;
                $elem['nom_n2b'] = $elem['valide'] != -99999 ? nom(-$elem['valide'], 'nom p', $agents).", " : null;
                $etat="Demandée";
                $etat=$elem['valide_n1']>0?"En attente de validation hierarchique, {$elem['nom_n1a']}".dateFr($elem['validation_n1'], true):$etat;
                $etat=$elem['valide_n1']<0?"En attente de validation hierarchique, {$elem['nom_n1b']}".dateFr($elem['validation_n1'], true):$etat;
                $etat=$elem['valide']>0?"Valid&eacute;e, {$elem['nom_n2a']}".dateFr($elem['validation'], true):$etat;
                $etat=$elem['valide']<0?"Refus&eacute;e, {$elem['nom_n2b']}".dateFr($elem['validation'], true):$etat;
                $etatStyle=$elem['valide']==0?"font-weight:bold;":null;
                $etatStyle=$elem['valide']<0?"color:red;":$etatStyle;
                $elem['status'] = $etat;
                $elem['status_style'] = $etatStyle;

                $elem['view_details'] = 0;
                if ($admin or (!$this->config('Absences-adminSeulement') and in_array(6, $droits))) {
                    $elem['view_details'] = 1;
                }
                $visibles_absences[] = $elem;
            }

            $this->templateParams(array(
                'visibles_absences' => $visibles_absences,
            ));
        }

        return $this->output('absences/index.html.twig');
    }

    /**
     * @Route("/absence/add", name="absence.add", methods={"GET"})
     */
    public function add(Request $request)
    {
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
     * @Route("/absence", name="absence.save", methods={"POST"})
     */
    public function save(Request $request)
    {
        $this->droits = $GLOBALS['droits'];

        $this->setAdminPermissions();

        $this->agents_multiples = ($this->admin or in_array(9, $this->droits));

        if ($this->config('Absences-adminSeulement') and !$this->admin) {
            return $this->output('access-denied.html.twig');
        }

        $result = $this->save_new($request, $this->admin);

        $file = $request->files->get('documentFile');
        if (!empty($file)) {
            $token = $request->get("token");
            if (!$this->isCsrfTokenValid('upload', $token)) {
                return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
            }

            $filename = $file->getClientOriginalName();

            $ad = new AbsenceDocument();
            $ad->absence_id($result['id']);
            $ad->filename($filename);
            $ad->date(new \DateTime());
            $this->entityManager->persist($ad);
            $this->entityManager->flush();

            $absenceDocument = new AbsenceDocument();
            $file->move($absenceDocument->upload_dir() . $result['id'] . '/' . $ad->id(), $filename);

        }

        $msg = $result['msg'];
        $msg2 = $result['msg2'];
        $msg2_type = $result['msg2_type'];

        return $this->redirectToRoute("absence.index", array('msg' => $msg, 'msgType' => 'success', 'msg2' => $msg2, 'msg2Type' => $msg2_type));
    }

    /**
     * @Route("/absence/{id}", name="absence.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {

        $id = $request->get('id');

        $this->dbprefix = $GLOBALS['dbprefix'];
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

        $agents=$a->elements['agents'];
        $debutSQL=filter_var($a->elements['debut'], FILTER_CALLBACK, array("options"=>"sanitize_dateTimeSQL"));
        $finSQL=filter_var($a->elements['fin'], FILTER_CALLBACK, array("options"=>"sanitize_dateTimeSQL"));
        $valide=filter_var($a->elements['valide_n2'], FILTER_SANITIZE_NUMBER_INT);
        $valideN1=$a->elements['valide_n1'];
        $ical_key=$a->elements['ical_key'];
        $cal_name=$a->elements['cal_name'];

        // Traitement des dates et des heures
        $absence['demande'] = dateFr($absence['demande'], true);
        $debut=dateFr3($debutSQL);
        $fin=dateFr3($finSQL);

        $hre_debut=substr($debut, -8);
        $hre_fin=substr($fin, -8);
        $debut=substr($debut, 0, 10);
        $fin=substr($fin, 0, 10);

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

        if ($acces && $this->config('Absences-validation') == 0) {
            $absence['editable'] = true;
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

    private function getDocuments($id) {
        $docsarray = array();
        $absdocs = $this->entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $id]);
        foreach ($absdocs as $absdoc) {
           $docsarray[] = array('filename' => $absdoc->filename(), 'id' => $absdoc->id());
        }
        return $docsarray;
    }

    private function save_new(Request $request) {
        $perso_id = $request->get('perso_id');
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
        $valide = $request->get('valide');
        $allday = $request->get('allday');

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
        $a->valide = $valide;
        $a->pj1 = $pj1;
        $a->pj2 = $pj2;
        $a->so = $so;
        $a->add();
        $msg2 = $a->msg2;
        $msg2_type = $a->msg2_type;


        // Confirmation de l'enregistrement
        if ($this->config('Absences-validation') and !$this->admin) {
            $msg="La demande d&apos;absence a &eacute;t&eacute; enregistr&eacute;e";
        } else {
            $msg="L&apos;absence a &eacute;t&eacute; enregistr&eacute;e";
        }
        $msg=urlencode($msg);

        // Si erreur d'envoi de mail
        if ($msg2_type) {
            $msg2=urlencode("<ul>".$msg2."</ul>");
        }

        return array(
            'msg' => $msg,
            'msg2' => $msg2,
            'msg2_type' => $msg2_type,
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
