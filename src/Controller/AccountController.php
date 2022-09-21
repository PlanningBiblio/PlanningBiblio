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
    /**
     * @Route("/myaccount", name="account.index", methods={"GET"})
     */
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

        // Contrôle si les périodes sont renseignées avant d'afficher les années universitaires dans le menu déroulant
        $annees = array();
        foreach ($tmp as $elem) {
            $p = new \planningHebdo();
            $p->dates = array($elem);
            $p->getPeriodes();
            if (($p->getPeriodes() != null) and $p->periodes[0][0] and $p->periodes[0][1] and $p->periodes[0][2] and $p->periodes[0][3]) {
                $annees[] = $elem;
            }
        }

        // Informations sur l'agent
        $p = new \personnel();
        $p->CSRFToken = $CSRFSession;
        $p->fetchById($_SESSION['login_id']);
        $sites = $p->elements[0]['sites'];

        // URL ICS
        $ics = null;
        if ($this->config('ICS-Export')) {
            $ics = $p->getICSURL($_SESSION['login_id']);
        }

        // Crédits (congés, récupérations)
        if ($this->config('Conges-Enable')) {
            $credits['annuel'] = heure4($p->elements[0]['conges_annuel']);
            $credits['conges'] = heure4($p->elements[0]['conges_credit']);
            $credits['reliquat'] = heure4($p->elements[0]['conges_reliquat']);
            $credits['anticipation'] = heure4($p->elements[0]['conges_anticipation']);
            $credits['recuperation'] = heure4($p->elements[0]['comp_time']);
            $credits['joursAnnuel'] = number_format($credits['annuel']/7, 2, ",", " ");
            $credits['joursConges'] = number_format($credits['conges']/7, 2, ",", " ");
            $credits['joursReliquat'] = number_format($credits['reliquat']/7, 2, ",", " ");
            $credits['joursAnticipation'] = number_format($credits['anticipation']/7, 2, ",", " ");
            $credits['joursRecuperation'] = number_format($credits['recuperation']/7, 2, ",", " ");
        }

        // Liste de tous les agents (pour la fonction nom()
        $a = new \personnel();
        $a->supprime = array(0,1,2);
        $a->fetch();
        $agents = $a->elements;

        $p = new \planningHebdo();
        $p->perso_id = $_SESSION['login_id'];
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

        $auth_mode = $_SESSION['oups']['Auth-Mode'];
        $login = array("name" => $_SESSION['login_prenom'], "surname" => $_SESSION['login_nom'], "id" => $_SESSION['login_id']);

        $this->templateParams(
            array(
                "auth_mode"        => $auth_mode,
                "credits"          => $credits,
                "ics"              => $ics,
                "login"            => $login,
                "planning"         => $planning

            )
        );
        return $this->output('/myAccount.html.twig');
    }

}
