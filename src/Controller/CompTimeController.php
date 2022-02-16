<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


include_once(__DIR__ . '/../../public/conges/class.conges.php');
include_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class CompTimeController extends BaseController
{
    /**
     * @Route("/comp-time", name="comp-time.index", methods={"GET"})
     */
    public function index(Request $request)
    {

        $holiday_helper = new HolidayHelper();

        $annee = $request->get('annee');
        $reset = $request->get('reset');
        $perso_id = $request->get('perso_id');

        $this->droits = $GLOBALS['droits'];
        $lang = $GLOBALS['lang'];

        list($admin, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday', false)
            ->getValidationLevelFor($_SESSION['login_id']);

        if ($admin and $perso_id === null) {
            $perso_id = isset($_SESSION['oups']['recup_perso_id'])
                ? $_SESSION['oups']['recup_perso_id']
                : $_SESSION['login_id'];
        } elseif ($perso_id === null) {
            $perso_id = $_SESSION['login_id'];
        }

        if (!$annee) {
            $annee = isset($_SESSION['oups']['recup_annee'])
                ? $_SESSION['oups']['recup_annee']
                : (date("m")<9?date("Y")-1:date("Y"));
        }

        if ($reset) {
            $annee = date("m") < 9 ? date("Y") - 1 : date("Y");
            $perso_id = $_SESSION['login_id'];
        }

        $_SESSION['oups']['recup_annee'] = $annee;
        $_SESSION['oups']['recup_perso_id'] = $perso_id;

        $debut = $annee . '-09-01';
        $fin = ($annee + 1) . '-08-31';
        $message = null;

        // Search for existing comp-times
        $c = new \conges();
        $c->admin = $admin;
        $c->debut = $debut;
        $c->fin = $fin;
        if ($perso_id != 0) {
            $c->perso_id = $perso_id;
        }
        $c->getRecup();
        $recup = $c->elements;

        // Search agents
        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedFor($_SESSION['login_id']);

        $perso_ids = array_map(function($a) { return $a->id(); }, $managed);

        // School year
        $annees = array();
        for ($d = date("Y") + 2; $d > date("Y") - 11; $d--) {
            $annees[]= array($d, $d . '-' . ($d + 1));
        }

        $this->templateParams(array(
            'years'     => $annees,
            'year_from' => $annee,
            'year_to'   => $annee + 1,
            'admin'     => $admin,
        ));

        $comptimes = array();
        foreach ($recup as $elem) {

          // Filtre les agents non-gérés (notamment avec l'option Absences-notifications-agent-par-agent)
            if (!in_array($elem['perso_id'], $perso_ids)) {
                continue;
            }

            $validation="Demandé";
            $validation_date = dateFr($elem['saisie'], true);
            $validationStyle="font-weight:bold;";
            if ($elem['saisie_par'] and $elem['saisie_par']!=$elem['perso_id']) {
                $validation.=" par ".nom($elem['saisie_par']);
            }
            $credits=null;

            if ($elem['valide']>0) {
                $validation = $lang['leave_table_accepted'] ." par ". nom($elem['valide']);
                $validation_date = dateFr($elem['validation'], true);
                $validationStyle=null;
                if ($elem['solde_prec']!=null and $elem['solde_actuel']!=null) {
                    $credits=heure4($elem['solde_prec'])." → ".heure4($elem['solde_actuel']);
                    if ($holiday_helper->showHoursToDays()) {
                        $credits .= "<br />" . $holiday_helper->hoursToDays($elem['solde_prec'], $elem['perso_id']) . "j &rarr; " . $holiday_helper->hoursToDays($elem['solde_actuel'], $elem['perso_id']) . "j";
                    }
                }
            } elseif ($elem['valide']<0) {
                $validation = $lang['leave_table_refused'] ." par ". nom(-$elem['valide']);
                $validation_date = dateFr($elem['validation'], true);
                $validationStyle="color:red;font-weight:bold;";
            } elseif ($elem['valide_n1'] > 0) {
                $validation = $lang['leave_table_accepted_pending'] .", ". nom($elem['valide_n1']);
                $validation_date = dateFr($elem['validation_n1'], true);
                $validationStyle="font-weight:bold;";
            } elseif ($elem['valide_n1'] < 0) {
                $validation = $lang['leave_table_refused_pending'] .", ". nom(-$elem['valide_n1']);
                $validation_date = dateFr($elem['validation_n1'], true);
                $validationStyle="font-weight:bold;";
            }

            $date2 = ($elem['date2'] and $elem['date2']!="0000-00-00") ? " & ".dateFr($elem['date2']) : null;

            $comptime = array(
                'id'                => $elem['id'],
                'date'              => dateFr($elem['date']),
                'date2'             => $date2,
                'name'              => nom($elem['perso_id']),
                'hours'             => heure4($elem['heures']),
                'validation_style'  => $validationStyle,
                'validation'        => $validation,
                'validation_date'   => $validation_date,
                'credits'           => $credits,
                'commentaires'      => html_entity_decode($elem['commentaires'], ENT_QUOTES|ENT_HTML5),
            );

            $comptime['hourstodays'] = null;
            if ($this->config('Conges-Recuperations') == 0 && $holiday_helper->showHoursToDays()) {
                $comptime['hourstodays'] = $holiday_helper->hoursToDays($elem['heures'], $elem['perso_id']);
            }

            $comptimes[]= $comptime;
        }

        $this->templateParams(array(
            'comptimes' => $comptimes,
        ));

        $categories = array();
        foreach ($managed as $index => $m) {
            $categories[$m->id()] = $m->categorie();
        }

        $this->templateParams(array(
            'recup_delaidefaut'         => $this->config('Recup-DelaiDefaut'),
            'recup_delaititulaire1'     => $this->config('Recup-DelaiTitulaire1'),
            'recup_delaititulaire2'     => $this->config('Recup-DelaiTitulaire2'),
            'recup_delaicontractuel1'   => $this->config('Recup-DelaiContractuel1'),
            'recup_delaicontractuel2'   => $this->config('Recup-DelaiContractuel2'),
            'recup_deuxsamedis'         => $this->config('Recup-DeuxSamedis'),
            'recup_samediseulement'     => $this->config('Recup-SamediSeulement') ? 'true' : 'false',
            'recup_uneparjour'          => $this->config('Recup-Uneparjour') ? 'true' : 'false',
            'perso_id'                  => $perso_id,
            'perso_name'                => nom($perso_id, 'prenom nom'),
            'managed'                   => $managed,
            'categories'                => json_encode($categories, JSON_HEX_APOS),
            'label'                     => ($this->config('Recup-DeuxSamedis')) ? "Date (1<sup>er</sup> samedi)" : "Date",
            'saturday'                  => "Date (2<sup>ème</sup> samedi) (optionel)",
        ));

        return $this->output('comp_time/index.html.twig');
    }
}