<?php

namespace App\Controller;

use App\Model\AbsenceReason;
use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/planning/poste/class.planning.php');

class WeekPlanningController extends BaseController
{
    use \App\Trait\PlanningTrait;

    #[Route(path: '/week', name: 'planning.week', methods: ['GET'])]
    public function week(Request $request)
    {
        $groupe = $request->get('groupe');
        $site = $request->get('site');
        $tableau = $request->get('tableau');
        $date = $request->get('date');

        $site = $this->setSite($request);

        $dbprefix = $GLOBALS['dbprefix'];
        $CSRFSession = $GLOBALS['CSRFSession'];

        $date = filter_var($date, FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));

        if (!$date and array_key_exists('PLdate', $_SESSION)) {
            $date = $_SESSION['PLdate'];
        } elseif (!$date and !array_key_exists('PLdate', $_SESSION)) {
            $date = date("Y-m-d");
        }

        $_SESSION['PLdate'] = $date;
        $_SESSION['week'] = true;

        list($d, $semaine, $semaine3, $jour, $dates, $datesSemaine, $dateAlpha)
            = $this->getDatesPlanning($date);

        global $idCellule;
        $idCellule=0;

        //-------- Vérification des droits de modification (Autorisation) -------------//
        $autorisationN1 = (in_array((300 + $site), $this->permissions)
            or in_array((1000 + $site), $this->permissions));

        // ------ FIN Vérification des droits de modification (Autorisation) -----//

        $fin = $this->config('Dimanche') ? 6 : 5;

        //	Selection des messages d'informations
        $db = new \db();
        $dateDebut = $db->escapeString($dates[0]);
        $dateFin = $db->escapeString($dates[$fin]);
        $db->query("SELECT * FROM `{$dbprefix}infos` WHERE `debut`<='$dateFin' AND `fin`>='$dateDebut' ORDER BY `debut`,`fin`;");
        $messages_infos = null;
        if ($db->result) {
            foreach ($db->result as $elem) {
                $messages_infos[] = $elem['texte'];
            }
            $messages_infos = implode(' - ', $messages_infos);
        }

        switch ($this->config('nb_semaine')) {
            case 2:
                $type_sem = $semaine % 2 ? 'Impaire' : 'Paire';
                $affSem = "$type_sem ($semaine)";
                break;
            case 3:
                $type_sem = $semaine3;
                $affSem = "$type_sem ($semaine)";
                break;
            default:
                $affSem = $semaine;
                break;
        }

        // Parameters for planning's menu
        // (Calendar widget, days, week and action icons)
        $this->templateParams(array(
            'affSem'            => $affSem,
            'autorisationN1'    => $autorisationN1,
            'content_planning'  => true,
            'CSRFSession'       => $CSRFSession,
            'date'              => $date,
            'dates'             => $dates,
            'day'               => $jour,
            'messages_infos'    => $messages_infos,
            'public_holiday'    => jour_ferie($date),
            'site'              => $site,
            'week_view'         => true,
        ));

        // div id='tabsemaine1' : permet d'afficher les tableaux masqués.
        // La fonction JS afficheTableauxDiv utilise $('#tabsemaine1').after()
        // pour afficher les liens de récupération des tableaux.

        // ---------- FIN Affichage du titre et du calendrier ------------//

        // Pour tous les jours de la semaine
        $days = array();
        for ($j=0;$j<=$fin;$j++) {
            $day = array();
            $date=$dates[$j];
            $day['date'] = $date;

            // ---------- Verrouillage du planning ----------- //
            $perso2 = null;
            $date_validation2 = null;
            $heure_validation2 = null;
            $verrou = false;

            $db = new \db();
            $db->select2('pl_poste_verrou', '*', array('date' => $date, 'site' => $site));
            if ($db->result) {
                $verrou = $db->result[0]['verrou2'];
                $perso = nom($db->result[0]['perso']);
                $perso2 = nom($db->result[0]['perso2']);
                $date_validation = dateFr(substr($db->result[0]['validation'], 0, 10));
                $heure_validation = substr($db->result[0]['validation'], 11, 5);
                $date_validation2 = dateFr(substr($db->result[0]['validation2'], 0, 10));
                $heure_validation2 = substr($db->result[0]['validation2'], 11, 5);
                $validation2 = $db->result[0]['validation2'];
            }
            $day['perso2'] = $perso2;
            $day['date_validation2'] = $date_validation2;
            $day['heure_validation2'] = $heure_validation2;

            // ------------ Choix du tableau ----------- //
            $db = new \db();
            $db->select2('pl_poste_tab_affect', 'tableau', array('date' => $date, 'site' => $site));
            $tab = $db->result ? $db->result[0]['tableau'] : null;

            $day['tab'] = $tab;
            $day['verrou'] = $verrou;
            // ----------- FIN Choix du tableau --------- //

            // ----------- Vérification si le planning est validé ------------ //
            if ($verrou or $autorisationN1) {

                // ------------ Planning display --------------------//

                // $cellules will be used in the cellule_poste function.
                global $cellules;
                $activites = $this->getSkills();
                $cellules = $this->getCells($date, $site, $activites);

                // $absence_reasons will be used in the cellule_poste function.
                global $absence_reasons;
                $absence_reasons = $this->entityManager->getRepository(AbsenceReason::class);

                // Looking for absences.
                global $absences;
                $absences = $this->getAbsences($date);

                // $conges will be used in the cellule_poste function and added to $absences_planning
                global $conges;
                $conges = $this->getHolidays($date);

                $tabs = $this->createTables($tab, $verrou, $date);

                $day['tabs'] = $tabs;
            }

            // Notes : Affichage
            $p = new \planning();
            $p->date = $date;
            $p->site = $site;
            $p->getNotes();
            $notes = $p->notes;
            $notesDisplay = trim($notes) ? null : "style='display:none;'";
            $day['notes'] = $notes;
            $day['notesDisplay'] = $notesDisplay;
            $days[] = $day;
        }

        $this->templateParams(array(
            'days' => $days
        ));

        return $this->output('planning/poste/week.html.twig');
    }
}
