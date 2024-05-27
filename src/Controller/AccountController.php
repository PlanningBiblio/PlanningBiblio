<?php

namespace App\Controller;

use App\Controller\BaseController;

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

        // Informations sur l'agent
        $p = new \personnel();
        $p->CSRFToken = $CSRFSession;
        $p->fetchById($session->get('loginId'));
        $sites = $p->elements[0]['sites'];

        // URL ICS
        $ics = null;
        if ($this->config('ICS-Export')) {
            $ics = $p->getICSURL($session->get('loginId'));
        }

        // Crédits (congés, récupérations)
        if ($this->config('Conges-Enable')) {

            // @note : $fulldayReferenceTime may change depending on param Conges-fullday-reference-time.
            // Its default value is 7 hours
            // Its value is always 7 hours if credits are managed in days (Conges-Mode=jours)

            $fulldayReferenceTime = !empty(floatval($this->config('Conges-fullday-reference-time'))) ? floatval($this->config('Conges-fullday-reference-time')) : 7;

            if ($this->config('Conges-Mode') == 'jours') {
                $fulldayReferenceTime = 7;
            }

            $credits['annuel'] = heure4($p->elements[0]['conges_annuel']);
            $credits['conges'] = heure4($p->elements[0]['conges_credit']);
            $credits['reliquat'] = heure4($p->elements[0]['conges_reliquat']);
            $credits['anticipation'] = heure4($p->elements[0]['conges_anticipation']);
            $credits['recuperation'] = heure4($p->elements[0]['comp_time']);
            $credits['joursAnnuel'] = number_format($p->elements[0]['conges_annuel']/$fulldayReferenceTime, 2, ",", " ");
            $credits['joursConges'] = number_format($p->elements[0]['conges_credit']/$fulldayReferenceTime, 2, ",", " ");
            $credits['joursReliquat'] = number_format($p->elements[0]['conges_reliquat']/$fulldayReferenceTime, 2, ",", " ");
            $credits['joursAnticipation'] = number_format($p->elements[0]['conges_anticipation']/$fulldayReferenceTime, 2, ",", " ");
        }

        // Liste de tous les agents (pour la fonction nom()
        $a = new \personnel();
        $a->supprime = array(0,1,2);
        $a->fetch();
        $agents = $a->elements;

        $p = new \planningHebdo();
        $p->perso_id = $session->get('loginId');
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
                "credits"          => $credits,
                "ics"              => $ics,
                "login"            => $login,
                "planning"         => $planning

            )
        );
        return $this->output('/myAccount.html.twig');
    }

}
