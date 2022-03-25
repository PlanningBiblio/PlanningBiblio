<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\AbsenceDocument;
use App\Model\Absence;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');

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


        // Initialisation des variables
        list($admin, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($_SESSION['login_id']);

        if ($admin or $adminN2) {
            $perso_id = $request->get('perso_id');
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

        $a = new \absences();
        $a->groupe = true;
        if ($agents_supprimes) {
            $a->agents_supprimes = array(0,1);
        }
        $a->fetch(null, $perso_id, $debutSQL, $finSQL);
        $absences = $a->elements;

        // Tri par défaut du tableau
        $sort="[[0],[1]]";
        if ($admin or $adminN2 or (!$this->config('Absences-adminSeulement') and in_array(6, $droits))) {
            $sort="[[1],[2]]";
        }

        $this->templateParams(array(
            'debut'     => $debut,
            'fin'       => $fin,
            'perso_id'  => $perso_id,
            'sort'      => $sort,
        ));

        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('absence')
            ->getManagedFor($_SESSION['login_id']);

        // Liste des agents à conserver :
        $perso_ids = array_map(function($a) { return $a->id(); }, $managed);

        $this->templateParams(array(
            'managed'               => $managed,
            'agents_deleted'        => $agents_supprimes,
            'can_manage_sup_doc'    => in_array(701, $droits) ? 1 : 0,
        ));

        $visibles_absences = array();
        if ($absences) {
            foreach ($absences as $elem) {

                if ($admin or $adminN2) {
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

                $elem['nom_n1a'] = $elem['valide_n1'] != 99999 ? nom($elem['valide_n1'], 'nom p', $agents).", " : null;
                $elem['nom_n1b'] = $elem['valide_n1'] != -99999 ? nom(-$elem['valide_n1'], 'nom p', $agents).", " : null;
                $elem['nom_n2a'] = $elem['valide'] != 99999 ? nom($elem['valide'], 'nom p', $agents).", " : null;
                $elem['nom_n2b'] = $elem['valide'] != -99999 ? nom(-$elem['valide'], 'nom p', $agents).", " : null;
                $etat="Demandée";
                $etat=$elem['valide_n1']>0?"En attente de validation hiérarchique, {$elem['nom_n1a']}".dateFr($elem['validation_n1'], true):$etat;
                $etat=$elem['valide_n1']<0?"En attente de validation hiérarchique, {$elem['nom_n1b']}".dateFr($elem['validation_n1'], true):$etat;
                $etat=$elem['valide']>0?"Validée, {$elem['nom_n2a']}".dateFr($elem['validation'], true):$etat;
                $etat=$elem['valide']<0?"Refusée, {$elem['nom_n2b']}".dateFr($elem['validation'], true):$etat;
                $etatStyle=$elem['valide']==0?"font-weight:bold;":null;
                $etatStyle=$elem['valide']<0?"color:red;":$etatStyle;
                $elem['status'] = $etat;
                $elem['status_style'] = $etatStyle;

                $elem['view_details'] = 0;
                if ($admin or $adminN2 or (!$this->config('Absences-adminSeulement') and in_array(6, $droits))) {
                    $elem['view_details'] = 1;
                }
                $visibles_absences[] = $elem;
            }

        }

        $this->templateParams(array(
            'visibles_absences' => $visibles_absences,
        ));

        return $this->output('absences/index.html.twig');
    }

    /**
     * @Route("/absence/add", name="absence.add", methods={"GET"})
     */
    public function add(Request $request)
    {
        $this->dbprefix = $GLOBALS['dbprefix'];
        $this->droits = $GLOBALS['droits'];

        list($this->admin, $this->adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($_SESSION['login_id']);

        $this->agents_multiples = (($this->admin or $this->adminN2) or in_array(9, $this->droits));

        if ($this->config('Absences-adminSeulement') and !($this->admin or $this->adminN2)) {
            return $this->output('access-denied.html.twig');
        }

        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('absence')
            ->getManagedFor($_SESSION['login_id']);

        // If logged in agent has the permission
        // to "create absences for other agents",
        // add all agents.
        if (in_array(9, $this->droits)) {
            $managed = $this->entityManager->getRepository(Agent::class)
            ->getAgentsList();
        }

        $agent_preselection = $this->config('Absences-agent-preselection');

        // If logged is not admin,
        // force him to be pre-selected.
        if (!$this->admin and !$this->adminN2) {
            $agent_preselection = 1;
        }

        $this->templateParams(array(
            'abences_infos'         => $this->absenceInfos(),
            'admin'                 => $this->admin || $this->adminN2,
            'adminN1'               => $this->admin ? 1 : 0,
            'adminN2'               => $this->adminN2 ? 1 : 0,
            'agent_preselection'    => $agent_preselection,
            'agents'                => $managed,
            'agents_multiples'      => $this->agents_multiples,
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'fullday_checked'       => $this->config('Absences-journeeEntiere'),
            'loggedin_id'           => $_SESSION['login_id'],
            'loggedin_name'         => $_SESSION['login_nom'],
            'loggedin_firstname'    => $_SESSION['login_prenom'],
            'reason_types'          => $this->reasonTypes(),
            'reasons'               => $this->availablesReasons(),
            'right701'              => in_array(701, $this->droits) ? 1 : 0,
        ));

        return $this->output('absences/add.html.twig');
    }

    /**
     * @Route("/absence", name="absence.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        $this->droits = $GLOBALS['droits'];

        $this->setAdminPermissions();

        $this->agents_multiples = ($this->admin or $this->adminN2 or in_array(9, $this->droits));

        if ($this->config('Absences-adminSeulement') and !$this->admin and !$this->adminN2) {
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
        $msg2_type = $result['msg2_type'] == 'error' ? 'error' : 'notice';

        $session->getFlashBag()->add('notice', $msg);

        if ($msg2 && $msg2 != '<li></li>') {
            $session->getFlashBag()->add($msg2_type, $msg2);
        }

        return $this->redirectToRoute("absence.index");
    }

    /**
     * @Route("/absence/{id}", name="absence.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {

        $id = $request->get('id');

        $this->dbprefix = $GLOBALS['dbprefix'];
        $this->droits = $GLOBALS['droits'];

        $a = new \absences();
        $a->fetchById($id);

        if (empty($a->elements)) {
            return $this->output('access-denied.html.twig');
        }

        $absence = $a->elements;
        $absence['motif'] = html_entity_decode($a->elements['motif'], ENT_QUOTES);
        $absence['motif_autre'] = html_entity_decode($a->elements['motif_autre'], ENT_QUOTES);
        $absence['commentaires'] = html_entity_decode($a->elements['commentaires'], ENT_QUOTES);
        $agents=$a->elements['agents'];

        $adminN1 = true;
        $adminN2 = true;
        foreach ($agents as $agent) {
            list($N1, $N2) = $this->entityManager
                ->getRepository(Agent::class)
                ->setModule('absence')
                ->forAgent($agent['perso_id'])
                ->getValidationLevelFor($_SESSION['login_id']);

            $adminN1 = $N1 === false ? $N1 : $adminN1;
            $adminN2 = $N2 === false ? $N2 : $adminN2;
        }

        $agents_multiples = ($adminN1 or $adminN2 or in_array(9, $this->droits));

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

        // Prevent non admin to edit
        // own absence wit other agents
        if (!$admin and count($agents) > 1 and !in_array(9, $this->droits)) {
            $absence['editable'] = false;
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

        // Sécurité
        // Droit 6 = modification de ses propres absences
        // Les admins ont toujours accès à cette page
        $acces = ($adminN1 or $adminN2);
        if (!$acces) {
            // Les non admin ayant le droits de modifier leurs absences ont accès si l'absence les concerne
            $agent_ids = array_map(function($a) { return $a['perso_id'];}, $agents);
            $acces = (in_array(6, $this->droits) and in_array($_SESSION['login_id'], $agent_ids)) ? true : false;
        }
        // Si config Absences-adminSeulement, seuls les admins ont accès à cette page
        if ($this->config('Absences-adminSeulement') and !($adminN1 or $adminN2)) {
            $acces=false;
        }

        if ($acces && $this->config('Absences-validation') == 0) {
            $absence['editable'] = true;
        }

        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('absence')
            ->getManagedFor($_SESSION['login_id']);

        // If logged in agent has the permission
        // to "create absences for other agents",
        // add all agents.
        if (in_array(9, $this->droits)) {
            $managed = $this->entityManager->getRepository(Agent::class)
            ->getAgentsList();
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
            'agents_tous'           => $managed,
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

        $this->templateParams(array('documents' => $this->getDocuments($a)));
        return $this->output('absences/edit.html.twig');
    }

    /**
     * @Route("/absence", name="absence.delete", methods={"DELETE"})
     */
    public function delete_absence(Request $request)
    {

        $CSRFToken = $request->get('CSRFToken');
        $id = $request->get('id');
        $recurrent = $request->get('rec');

        $this->dbprefix = $GLOBALS['dbprefix'];
        $this->droits = $GLOBALS['droits'];
        $errors=array();

        $a = new \absences();
        $a->fetchById($id);
        $debut = $a->elements['debut'];
        $fin = $a->elements['fin'];
        $perso_id = $a->elements['perso_id'];
        $motif = $a->elements['motif'];
        $commentaires = $a->elements['commentaires'];
        $valideN1 = $a->elements['valide_n1'];
        $valideN2 = $a->elements['valide_n2'];
        $groupe = $a->elements['groupe'];
        $agents = $a->elements['agents'];
        $perso_ids = $a->elements['perso_ids'];
        $uid = $a->elements['uid'];

        $this->setAdminPermissions();

        // If "Absences-notifications-agent-par-agent" is enabled,
        // check if logged in agent can manage all agents in absence.
        // Else, admin = false.
        if ($this->config('Absences-notifications-agent-par-agent') and $this->admin) {
            $logged_in = $this->entityManager->find(Agent::class, $_SESSION['login_id']);
            $this->admin = $logged_in->isManagerOf($perso_ids);
        }

        if ($this->admin or $this->adminN2) {
            $acces = true;
        }

        if (!$acces) {
            $acces=(in_array(6, $this->droits) and $perso_id==$_SESSION['login_id'] and !$groupe)?true:false;
        }

        if (!$acces) {
            $json_response['msg'] = 'Suppression refusée';
            $json_response['msgType'] = 'error';
            return $this->json($json_response);
        }

        $this->entityManager->getRepository(Absence::class)->deleteAllDocuments($id);

        // Send an email to agent and in charge.
        $message="<b><u/>Suppression d'une absence</u></b> : \n";

        if (count($agents)>1) {
            $message.="<br/><br/>Agents :<ul>\n";
            foreach ($agents as $agent) {
                $message.="<li>{$agent['prenom']} {$agent['nom']}</li>\n";
            }
            $message.="</ul>\n";
        } else {
            $message.="<br/><br/>Agent : {$agents[0]['prenom']} {$agents[0]['nom']}<br/><br/>\n";
        }

        $message.="Début : ".dateFr($debut);
        $hre_debut=substr($debut, -8);
        $hre_fin=substr($fin, -8);
        if ($hre_debut!="00:00:00") {
            $message.=" ".heure3($hre_debut);
        }
        $message.="<br/>Fin : ".dateFr($fin);
        if ($hre_fin!="23:59:59") {
            $message.=" ".heure3($hre_fin);
        }
        $message.="<br/><br/>Motif : $motif<br/>";

        if ($this->config('Absences-validation')) {
            $validationText="Demand&eacute;e";
            if ($valideN2>0) {
                $validationText="Valid&eacute;e";
            } elseif ($valideN2<0) {
                $validationText="Refus&eacute;e";
            } elseif ($valideN1>0) {
                $validationText="Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
            } elseif ($valideN1<0) {
                $validationText="Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
            }

            $message.="<br/>Validation pr&eacute;c&eacute;dente : <br/>\n";
            $message.=$validationText;
            $message.="<br/>\n";
        }

        if ($commentaires) {
            $message.="<br/>Commentaire:<br/>$commentaires<br/>";
        }

        if ($this->config('Absences-notifications-agent-par-agent')) {
            $a=new \absences();
            $a->getRecipients2(null, $agents, 2, 500, $debut, $fin);
            $destinataires = $a->recipients;
        } else {
            // Foreach agent, search for agents in charge of absences.
            $responsables=array();
            foreach ($agents as $agent) {
                $a=new \absences();
                $a->getResponsables($debut, $fin, $agent['perso_id']);
                $responsables=array_merge($responsables, $a->responsables);
            }

            // Foreach agent, search recipients according to
            // configuration.
            $ids = array_column($agents, 'perso_id');
            $staff_members = $this->entityManager->getRepository(Agent::class)->findById($ids);
            $destinataires=array();
            foreach ($staff_members as $member) {
                $a=new \absences();
                $a->getRecipients('-A2', $responsables, $member);
                $destinataires=array_merge($destinataires, $a->recipients);
            }

            // Delete duplicates recipients
            $tmp=array();
            foreach ($destinataires as $elem) {
                if (!in_array($elem, $tmp)) {
                    $tmp[]=$elem;
                }
            }
            $destinataires=$tmp;
        }

        // Send email
        $m=new \CJMail();
        $m->subject="Suppression d'une absence";
        $m->message=$message;
        $m->to=$destinataires;
        $m->send();

        // Si erreur d'envoi de mail, affichage de l'erreur
        if ($m->error) {
            $errors[]=$m->error_CJInfo;
        }

        // Mise à jour du champs 'absent' dans 'pl_poste'
        /**
         * @note : le champ pl_poste.absent n'est plus mis à 1 lors de la validation des absences depuis la version 2.4
         * mais nous devons garder la mise à 0 pour la suppresion des absences enregistrées avant cette version
         * NB : le champ pl_poste.absent est également utilisé pour barrer les agents depuis le planning, donc on ne supprime pas toutes ses valeurs
         */
        foreach ($agents as $agent) {
            $db=new \db();
            $req="UPDATE `{$this->dbprefix}pl_poste` SET `absent`='0' WHERE
            CONCAT(`date`,' ',`debut`) < '$fin' AND CONCAT(`date`,' ',`fin`) > '$debut'
            AND `perso_id`='{$agent['perso_id']}'";
            $db->query($req);
        }

        // If recurrence, delete or update ICS event and delete all occurences.
        if ($recurrent) {
            switch ($recurrent) {
            case 'all':
              foreach ($perso_ids as $elem) {
                  $a = new \absences();
                  $a->CSRFToken = $CSRFToken;
                  $a->perso_id = $elem;
                  $a->uid = $uid;
                  $a->update_db = true;
                  $a->ics_delete_event();
              }
              break;

            case 'current':
              // Add exception to ICS event.
              // This will delete selected occurence.
              $exdate = preg_replace('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', "$1$2$3T$4$5$6", $debut);

              foreach ($perso_ids as $elem) {
                  $a = new \absences();
                  $a->CSRFToken = $CSRFToken;
                  $a->perso_id = $elem;
                  $a->uid = $uid;
                  $a->ics_add_exdate($exdate);
              }

              break;

            case 'next':

              // On modifie la date de fin de la récurrence. Elle s'arrêtera juste avant l'occurence sélectionnée
              $serie1_end = date('Ymd\THis', strtotime($debut.' -1 second'));

              // Puis on récupère la date du fuseau GMT
              $datetime = new \DateTime($serie1_end, new \DateTimeZone(date_default_timezone_get()));
              $datetime->setTimezone(new \DateTimeZone('GMT'));
              $serie1_end = $datetime->format('Ymd\THis\Z');

              // On met à jour la série : modification de RRULE en mettant UNTIL à la date de fin
              foreach ($perso_ids as $elem) {
                  $a = new \absences();
                  $a->CSRFToken = $CSRFToken;
                  $a->perso_id = $elem;
                  $a->uid = $uid;
                  $a->ics_update_until($serie1_end);
              }

              break;
          }

        // Si pas de récurrence, suppression dans la table 'absences'
        } else {
            if ($groupe) {
                $db=new \db();
                $db->CSRFToken = $CSRFToken;
                $db->delete("absences", array("groupe"=>$groupe));
            } else {
                $db=new \db();
                $db->CSRFToken = $CSRFToken;
                $db->delete("absences", array("id"=>$id));
            }
        }

        $msg=urlencode("L'absence a été supprimée avec succès");
        $msgType="success";

        $msg2 = null;
        if (!empty($errors) && $errors[0] != '') {
            $msg2="<ul>";
            foreach ($errors as $error) {
                    $msg2.="<li>$error</li>";
            }
            $msg2.="</ul>";
            $msg2=urlencode($msg2);
            $msg2Type="error";
        }

        $json_response['msg'] = $msg;
        $json_response['msgType'] = $msgType;
        if ($msg2) {
          $json_response['msg2'] = $msg2;
          $json_response['msg2Type'] = $msg2Type;
        }
        return $this->json($json_response);
    }

    /**
     * @Route("/absence-statuses", name="absence.statuses", methods={"GET"})
     */
    public function absence_validation_statuses(Request $request)
    {
        $agent_ids = $request->get('ids');
        $module = $request->get('module');
        $absence_id = $request->get('id');

        if (!$agent_ids) {
            return $this->json(array('adminN1' => false, 'adminN2' => false));
        }

        $absence_valide_n1 = 0;
        if ($absence_id) {
            if ($module == 'absence') {
                $absence = new \absences();
                $absence->fetchById($absence_id);
                $absence_valide_n1 = $absence->elements['valide_n1'];
            } else {
                $c = new \conges();
                $c->id = $absence_id;
                $c->fetch();
                $absence_valide_n1 = $c->elements[0]['valide_n1'];
            }
        }

        $adminN1 = true;
        $adminN2 = true;
        foreach ($agent_ids as $id) {
            list($N1, $N2) = $this->entityManager
                ->getRepository(Agent::class)
                ->setModule($module)
                ->forAgent($id)
                ->getValidationLevelFor($_SESSION['login_id']);

            $adminN1 = $N1 ? $adminN1 : false;
            $adminN2 = $N2 ? $adminN2 : false;
        }

        $vaidation_N2_option = ($module == 'absence') ? 'Absences-Validation-N2' : 'Conges-Validation-N2';

        // Prevent logged in to directly validate l2
        // if Validation-N2 needs Validation-N1 before.
        if ($this->config($vaidation_N2_option) && $absence_valide_n1 == 0) {
            $adminN2 = false;
        }

        return $this->json(array('adminN1' => $adminN1, 'adminN2' => $adminN2));
    }

    private function getDocuments($absence) {
        $groupe = $absence->elements['groupe'];
        $absdocs = array();

        if (!$groupe) {
            $absdocs = $this->entityManager
                ->getRepository(AbsenceDocument::class)
                ->findBy(['absence_id' => $absence->id]);

        }

        // For grouped absences (with multiple agents),
        // we search for all absences of the same group
        // to find the one with documents.
        else {
            $db = new \db();
            $db->select('absences', 'id', "groupe='$groupe'");
            $grouped_absences = $db->result;
            if (!$grouped_absences) {
                return;
            }

            foreach ($grouped_absences as $a) {
                $absdocs = array_merge($absdocs,
                    $this->entityManager
                    ->getRepository(AbsenceDocument::class)
                    ->findBy(['absence_id' => $a['id']]));
            }
        }

        $docsarray = array();
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

        if (preg_match('/^(\d+):(\d+)$/', $hre_debut)) {
            $hre_debut .= ':00';
        }

        if (preg_match('/^(\d+):(\d+)$/', $hre_fin)) {
            $hre_fin .= ':00';
        }

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
            $msg="La demande d'absence a été enregistrée";
        } else {
            $msg="L'absence a été enregistrée";
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
            $logged_in = $this->entityManager->find(Agent::class, $_SESSION['login_id']);
            $accepted_ids = array_map(function($m) { return $m->perso_id()->id(); }, $logged_in->getManaged());
            $accepted_ids[] = $_SESSION['login_id'];

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
        $db->sanitize_string = false;
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
}
