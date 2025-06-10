<?php

namespace App\Controller;

use App\Controller\BaseController;

use App\PlanningBiblio\Helper\HolidayHelper;
use App\PlanningBiblio\Helper\HourHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once (__DIR__."/../../public/personnel/class.personnel.php");
require_once (__DIR__."/../../public/planningHebdo/class.planningHebdo.php");


class AccountController extends BaseController
{
    #[Route(path: '/myaccount', name: 'account.index', methods: ['GET'])]
    public function index(Request $request, Session $session)
    {
        // Initialisation des variables
        // Working hours
        // Années universitaires (si utilisation des périodes définies)
        $tmp = array();
        $tmp[0] = date("n") < 9 ? (date("Y")-1)."-".(date("Y")) : (date("Y"))."-".(date("Y")+1);
        $tmp[1] = date("n") < 9 ? (date("Y"))."-".(date("Y")+1) : (date("Y")+1)."-".(date("Y")+2);
        $message = null;
        $CSRFSession = $GLOBALS['CSRFSession'];
        $credits = array();
        $perso_id = $session->get('loginId');

        // Informations sur l'agent
        $p = new \personnel();
        $p->CSRFToken = $CSRFSession;
        $p->fetchById($perso_id);
        $sites = $p->elements[0]['sites'];

        // URL ICS
        $ics = null;
        if ($this->config('ICS-Export')) {
            $ics = $p->getICSURL($perso_id);
        }

        // Crédits (congés, récupérations)
        if ($this->config('Conges-Enable')) {

            $holiday_helper = new HolidayHelper();

            $credits['annuel']       = HourHelper::decimalToHoursMinutes($p->elements[0]['conges_annuel'])['as_string'];
            $credits['conges']       = HourHelper::decimalToHoursMinutes($p->elements[0]['conges_credit'])['as_string'];
            $credits['reliquat']     = HourHelper::decimalToHoursMinutes($p->elements[0]['conges_reliquat'])['as_string'];
            $credits['anticipation'] = HourHelper::decimalToHoursMinutes($p->elements[0]['conges_anticipation'])['as_string'];
            $credits['recuperation'] = HourHelper::decimalToHoursMinutes($p->elements[0]['comp_time'])['as_string'];

            $credits['joursAnnuel']       = $holiday_helper->hoursToDays($p->elements[0]['conges_annuel'],       $perso_id, null, true);
            $credits['joursConges']       = $holiday_helper->hoursToDays($p->elements[0]['conges_credit'],       $perso_id, null, true);
            $credits['joursReliquat']     = $holiday_helper->hoursToDays($p->elements[0]['conges_reliquat'],     $perso_id, null, true); 
            $credits['joursAnticipation'] = $holiday_helper->hoursToDays($p->elements[0]['conges_anticipation'], $perso_id, null, true); 

            $hours_per_day = HourHelper::decimalToHoursMinutes($holiday_helper->hoursPerDay($perso_id));

            $this->templateParams(
                array(
                    'show_hours_to_days' => $holiday_helper->showHoursToDays(),
                    'hours_per_day'      => $hours_per_day['as_string'],
                    'hours_per_day_full' => $hours_per_day['as_full_string'],
                )
            );
        }

        // Liste de tous les agents (pour la fonction nom()
        $a = new \personnel();
        $a->supprime = array(0,1,2);
        $a->fetch();
        $agents = $a->elements;

        $p = new \planningHebdo();
        $p->perso_id = $perso_id;
        $p->merge_exception = false;
        $p->fetch();
        $planning = $p->elements;

        foreach ($planning as &$elem) {
            $validation = "N'est pas validé";
            if ($elem['valide']) {
                $validation = nom($elem['valide'], "nom p", $agents).", ".dateFr($elem['validation'], true);
            }
            $elem['validation'] = $validation;

            $planningRemplace = $elem['remplace'] == 0 ? dateFr($elem['saisie'], true) : $planningRemplace;
            $commentaires = $elem['remplace'] ? "Remplace les heures <br/>du $planningRemplace" : null;
            $commentaires = $elem['exception'] ? 'Exception' : $commentaires;

            $elem['commentaires'] = $commentaires;
            $elem['debut'] = dateFr($elem['debut']);
            $elem['fin'] = dateFr($elem['fin']);
            $elem['saisie'] = dateFr($elem['saisie'], true);

        }

        $login = array("name" => $_SESSION['login_prenom'], "surname" => $_SESSION['login_nom'], "id" => $session->get('loginId'));

        $this->templateParams(
            array(
                "credits"  => $credits,
                "ics"      => $ics,
                "login"    => $login,
                "planning" => $planning
            )
        );
        return $this->output('/myAccount.html.twig');
    }

}
