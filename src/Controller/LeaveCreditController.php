<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\Model\AbsenceReason;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class LeaveCreditController extends BaseController
{
    /**
     * @Route("/leavecredit", name="leavecredit.index", methods={"GET", "POST"})
     */
    public function index(Request $request)
    {
        $admin = false;
        $sites = array();
        $nbSites = $this->config('Multisites-nombre');
        $droits = $GLOBALS['droits'];

        for ($i = 1; $i <= $nbSites; $i++) {
            if (in_array((400+$i), $droits) or in_array((600+$i), $droits)) {
                $admin = true;
                $sites[] = $i;
            }
        }

        if (!$admin) {
            return $this->redirectToRoute('access-denied');
        }

        // Initialisation des variables
        $agents_supprimes = isset($_SESSION['oups']['conges_agents_supprimes']) ? $_SESSION['oups']['conges_agents_supprimes'] : false;
        $agents_supprimes = (isset($_GET['get']) and isset($_GET['supprimes'])) ? true : $agents_supprimes;
        $agents_supprimes = (isset($_GET['get']) and !isset($_GET['supprimes'])) ? false : $agents_supprimes;

        $credits_effectifs = isset($_SESSION['oups']['conges_credits_effectifs']) ? $_SESSION['oups']['conges_credits_effectifs'] : true;
        $credits_effectifs = (isset($_GET['get']) and isset($_GET['effectifs'])) ? true : $credits_effectifs;
        $credits_effectifs = (isset($_GET['get']) and !isset($_GET['effectifs'])) ? false : $credits_effectifs;

        $credits_en_attente = isset($_SESSION['oups']['conges_credits_attente']) ? $_SESSION['oups']['conges_credits_attente'] : true;
        $credits_en_attente = (isset($_GET['get']) and isset($_GET['attente'])) ? true : $credits_en_attente;
        $credits_en_attente = (isset($_GET['get']) and !isset($_GET['attente'])) ? false : $credits_en_attente;

        $_SESSION['oups']['conges_agents_supprimes'] = $agents_supprimes;
        $_SESSION['oups']['conges_credits_effectifs'] = $credits_effectifs;
        $_SESSION['oups']['conges_credits_attente'] = $credits_en_attente;

        $c = new \conges();
        if ($agents_supprimes) {
            $c->agents_supprimes = array(0,1);
        }
        if ($nbSites > 1) {
            $c->sites = $sites;
        }
        $c->fetchAllCredits();
        $conges = $c->elements;

        foreach ($conges as &$elem) {
            $elem['conge_annuel'] = heure4($elem['conge_annuel']);
            $elem['conge_initial'] = heure4($elem['conge_initial']);
            $elem['conge_utilise'] = heure4($elem['conge_utilise']);
            $elem['conge_restant'] = heure4($elem['conge_restant']);
            $elem['conge_demande'] = heure4($elem['conge_demande']);
            $elem['conge_en_attente'] = heure4($elem['conge_en_attente']);
            $elem['reliquat_initial'] = heure4($elem['reliquat_initial']);
            $elem['reliquat_utilise'] = heure4($elem['reliquat_utilise']);
            $elem['reliquat_restant'] = heure4($elem['reliquat_restant']);
            $elem['reliquat_demande'] = heure4($elem['reliquat_demande']);
            $elem['reliquat_en_attente'] = heure4($elem['reliquat_en_attente']);
            $elem['recup_initial'] = heure4($elem['recup_initial']);
            $elem['recup_utilise'] = heure4($elem['recup_utilise']);
            $elem['recup_restant'] = heure4($elem['recup_restant']);
            $elem['recup_demande'] = heure4($elem['recup_demande']);
            $elem['recup_en_attente'] = heure4($elem['recup_en_attente']);
            $elem['anticipation_initial'] = heure4($elem['anticipation_initial']);
            $elem['anticipation_utilise'] = heure4($elem['anticipation_utilise']);
            $elem['anticipation_restant'] = heure4($elem['anticipation_restant']);
            $elem['anticipation_demande'] = heure4($elem['anticipation_demande']);
            $elem['anticipation_en_attente'] = heure4($elem['anticipation_en_attente']);
        }

        $this->templateParams(
            array(
                "agents_supprimes"   => $agents_supprimes,
                "conges"             => $conges,
                "credits_effectifs"  => $credits_effectifs,
                "credits_en_attente" => $credits_en_attente
            )
        );

        return $this->output('leavecredit/index.html.twig');
    }
}
