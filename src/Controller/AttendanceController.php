<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__. '/../../public/planningHebdo/class.planningHebdo.php');
require_once(__DIR__. '/../../public/personnel/class.personnel.php');

class AttendanceController extends BaseController
{

    /**
     * @Route("/attendance", name="attendance.index", methods={"GET"})
     */
    public function index(Request $request, Session $session){
        // Initialisation des variables
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $reset = $request->get("reset");
        $droits = $GLOBALS['droits'];
        $lang = $GLOBALS['lang'];

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $reset = filter_var($reset, FILTER_CALLBACK, array("options"=>"sanitize_on"));

        if (!$debut) {
            $debut = array_key_exists("planningHebdoDebut", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoDebut']:null;
        }

        if (!$fin) {
            $fin = array_key_exists("planningHebdoFin", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoFin']:null;
        }

        if ($reset) {
            $debut = null;
            $fin = null;
        }
        $_SESSION['oups']['planningHebdoDebut'] = $debut;
        $_SESSION['oups']['planningHebdoFin'] = $fin;
        $message = null;

        // Droits d'administration
        // Seront utilisés pour n'afficher que les agents gérés si l'option "PlanningHebdo-notifications-agent-par-agent" est cochée
        $adminN1 = in_array(1101, $droits);
        $adminN2 = in_array(1201, $droits);

        $notAdmin = !($adminN1 or $adminN2);
        $admin = ($adminN1 or $adminN2);

        // Droits de gestion des plannings de présence agent par agent
        if ($adminN1 and $this->config('PlanningHebdo-notifications-agent-par-agent')) {
            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));

            if (!$adminN2) {
                $perso_ids = array($_SESSION['login_id']);
                if ($db->result) {
                    foreach ($db->result as $elem) {
                        $perso_ids[] = $elem['perso_id'];
                    }
                }
            }
        }

        // Recherche des plannings
        $p = new \planningHebdo();
        $p->merge_exception = false;
        $p->debut=  dateFr($debut);
        $p->fin = dateFr($fin);
        if (!empty($perso_ids)) {
            $p->perso_ids = $perso_ids;
        }
        $p->fetch();

        $a = new \personnel();
        $a->supprime = array(0,1,2);
        $a->fetch();
        $agents = $a->elements;

        $tab = $p->elements ;

        foreach ($tab as &$elem) {
            $actuel = $elem['actuel'] ? "Oui" : null;

            // Validation
            $validation_class = 'bold';
            $validation_date = dateFr($elem['saisie'], true);
            $validation = 'Demandé';

            // Validation niveau 1
            if ($elem['valide_n1'] > 0) {
                $validation_date = dateFr($elem['validation_n1'], true);
                $validation = $lang['work_hours_dropdown_accepted_pending'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide_n1'] != 99999) {
                    $validation .= ", ".nom($elem['valide_n1'], 'nom p', $agents);
                }
            } elseif ($elem['valide_n1'] < 0) {
                $validation_date = dateFr($elem['validation_n1'], true);
                $validation = $lang['work_hours_dropdown_refused_pending'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide_n1'] != 99999) {
                    $validation.=", ".nom(-$elem['valide_n1'], 'nom p', $agents);
                }
            }
            // Validation niveau 2
            if ($elem['valide'] > 0) {
                $validation_date = dateFr($elem['validation'], true);
                $validation = $lang['work_hours_dropdown_accepted'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide'] != 99999) {
                    $validation.=", ".nom($elem['valide'], 'nom p', $agents);
                }
            } elseif ($elem['valide'] < 0) {
                $validation_class = 'red';
                $validation_date = dateFr($elem['validation'], true);
                $validation = $lang['work_hours_dropdown_refused'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide'] != 99999) {
                    $validation.=", ".nom(-$elem['valide'], 'nom p', $agents);
                }
            }

            $planningRemplace = $elem['remplace'] == 0 ? dateFr($elem['saisie'], true) : $planningRemplace;
            $commentaires = $elem['remplace']?"Remplace les heures <br/>du $planningRemplace" : null;
            $commentaires = $elem['exception'] ? 'Exception' : $commentaires;

            $elem['debut'] = dateFr($elem['debut']);
            $elem['fin'] = dateFr($elem['fin']);
            $elem['saisie'] = dateFr($elem['saisie'], true);
            $elem['validation'] = $validation;
            $elem['validation_date'] = $validation_date;
            $elem['commentaires'] = $commentaires;
        }

        $this->templateParams(
            array(
                "debut" => $debut,
                "fin"   => $fin,
                "tab"   => $tab
            )
        );
        return $this->output('/attendance/index.html.twig');
    }
}