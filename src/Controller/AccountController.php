<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once (__DIR__."/../../public/personnel/class.personnel.php");
require_once (__DIR__."/../../public/planningHebdo/class.planningHebdo.php");
require_once (__DIR__."/../../public/include/function.php");


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

    /**
     * @Route("/myaccount/password", name="account.password", methods={"GET"})
     */
    public function password(Request $request, Session $session)
    {
        $this->templateParams(
            array(
                'op'                => '',
                "login_name"        => $_SESSION['login_nom'],
                "login_firstname"   => $_SESSION['login_prenom'],
            )
        );
        return $this->output('/password.html.twig');
    }

    /**
     * @Route("/myaccount/password", name="account.updatepassword", methods={"POST"})
     */
    public function password_change(Request $request, Session $session)
    {

        $ancien = $request->get('ancien');
        $confirm = $request->get('confirm');
        $nouveau = $request->get('nouveau');

        $this->templateParams(
            array(
                'op'                => 'valid',
                'login_name'        => $_SESSION['login_nom'],
                'login_firstname'   => $_SESSION['login_prenom'],
                'cjinfo'            => '',
            )
        );

        $db = new \db();
        $db->query("select login,password,mail from {$dbprefix}personnel where id=".$_SESSION['login_id'].";");

        $login = $db->result[0]['login'];
        $mail = $db->result[0]['mail'];

        $password_verify = true;
        $password_match = true;

        if (!password_verify($ancien, $db->result[0]['password'])) {
            $password_verify = false;
        } elseif ($nouveau!=$confirm) {
            $password_match = false;
        } else {
            $mdp=$nouveau;
            $mdp_crypt = password_hash($mdp, PASSWORD_BCRYPT);
            $db=new \db();
            $db->query("update {$dbprefix}personnel set password='".$mdp_crypt."' where id=".$_SESSION['login_id'].";");

            $message = "Votre mot de passe Planning Biblio a &eacute;t&eacute; modifi&eacute;";
            $message .= "<ul><li>Login : $login</li><li>Mot de passe : $mdp</li></ul>";

            // Send email
            $m=new \CJMail();
            $m->subject="Modification du mot de passe";
            $m->message = $message;
            $m->to = $mail;
            $m->send();

            // Pass email error.
            if ($m->error_CJInfo) {
                $this->templateParams(array('cjinfo' => $m->error_CJInfo));
            }
        }

        $this->templateParams(
            array(
                'password_verify'   => $password_verify,
                'password_match'    => $password_match,
            )
        );

        return $this->output('/password.html.twig');
    }
}