<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__.'/../../public/conges/class.conges.php');
require_once(__DIR__.'/../../public/personnel/class.personnel.php');
require_once(__DIR__.'/../../public/postes/class.postes.php');

class ICalendarController extends BaseController
{
    /**
     * @Route("ical", name = "ical.index", methods={"GET"})
     */
    public function index(Request $request, Session $session){

        $module = 'Ical export';

        if (!$this->config('ICS-Export')) {
            return $this->returnError("L'exportation ICS est désactivée", $module, 403);
        }

        $interval_get = $request->get('interval');
        $code = $request->get('code');
        $id = $request->get('id');
        $login = $request->get('login');
        $mail = $request->get('mail');
        $get_absences = $request->get('absences');

        $agent = null;

        // Définition de l'id de l'agent si l'argument login est donné
        if (!$id and $login) {
            $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(array('login' => $login));
            if ($agent) {
                $id = $agent->id();
            } else {
                return $this->returnError("Impossible de trouver l'id associé au login $login", $module, 400);
            }
        }

        // Définition de l'id de l'agent si l'argument mail est donné
        if (!$id and $mail) {
            $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(array('mail' => $mail));
            if ($agent) {
                $id = $agent->id();
            } else {
                return $this->returnError("Impossible de trouver l'id associé au mail $mail", $module, 400);
            }
        }

        if (!$agent && $id) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($id);
            if (empty($agent)) {
                return $this->returnError("id inconnu", $module, 400);
            }
        }

        if (!$id) {
            return $this->returnError("L'id de l'agent n'est pas fourni", $module, 400);
        }

        if ($this->config('ICS-Code')) {
            $agent_ics_code = $agent->code_ics();
            if ($agent_ics_code != $code) {
                return $this->returnError("Accès refusé", $module, 401);
            }
        }

        $icsInterval = null;
        if ($this->config('ICS-Interval') != '' && intval($this->config('ICS-Interval'))) {
            $icsInterval = $this->config('ICS-Interval');
        }

        if ($interval_get != '' && intval($interval_get)) {
            $icsInterval = $interval_get;
        }

        $db=new \db();
        $db->selectInnerJoin(
            array("pl_poste","perso_id"),
            array("personnel","id"),
            array("date", "debut", "fin", "poste", 'site', 'absent', 'supprime'),
            array(),
            array("perso_id"=>$id),
            array('supprime' => 0),
            ($icsInterval ? "AND `date` > DATE_SUB(curdate(), INTERVAL $icsInterval DAY) " : '') . "ORDER BY `date` DESC, `debut` DESC, `fin` DESC"
        );
        if ($db->result) {
            $planning = $db->result;
        }

        // Recherche des postes pour affichage du nom des postes
        $p = new \postes();
        $p->fetch();
        $postes=$p->elements;

        // Liste des sites
        if ($this->config('Multisites-nombre') > 1) {
            $sites = array();
            for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
                $sites[$i] = html_entity_decode($this->config("Multisites-site$i"), ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            }
        }

        // Recherche des plannings verrouillés pour exclure les plages concernant des plannings en attente
        $verrou = array();
        $db = new \db();
        $db->select2("pl_poste_verrou", null, array('verrou2'=>'1'), ($icsInterval ? "AND `date` > DATE_SUB(curdate(), INTERVAL $icsInterval DAY) " : ''));
        if ($db->result) {
            foreach ($db->result as $elem) {
                $verrou[$elem['date'].'_'.$elem['site']] = array('date' => $elem['validation2'], 'agent' => $elem['perso2']);
            }
        }
        // Recherche des absences
        $a = new \absences();
        $a->valide = true;
        $a->documents = false;
        $a->fetch("`debut`,`fin`", $id, ($icsInterval ? date('Y-m-d',strtotime(date('Y-m-d') . " - $icsInterval days")) : '0000-00-00 00:00:00'), date('Y-m-d', strtotime(date('Y-m-d').' + 2 years')));
        $absences = $a->elements;

        // Recherche des congés (si le module est activé)
        if ($this->config('Conges-Enable')) {
            $c = new \conges();
            $c->perso_id = $id;
            $c->debut = ($icsInterval ? date('Y-m-d',strtotime(date('Y-m-d') . " - $icsInterval days")) : '0000-00-00 00:00:00');
            $c->fin = date('Y-m-d', strtotime(date('Y-m-d').' + 2 years'));
            $c->valide = true;
            $c->fetch();
            $absences = array_merge($absences, $c->elements);
        }

        // Nom de l'agent pour X-WR-CALNAME
        $agent = nom($id);

        // Tableaux contenant les noms et emails de tous les agents, permet de renseigner le champ ORGANIZER avec le nom de l'agent ayant vérrouillé le planning
        $p = new \personnel();
        $p->supprime = array(0,1,2);
        $p->fetch();
        $agents=$p->elements;

        $tz = date_default_timezone_get();
        // Tableau $ical
        $ical=array();
        $ical[]="BEGIN:VCALENDAR";
        $ical[]="X-WR-CALNAME:Service Public $agent";
        $ical[]="PRODID:Planning-Biblio-Calendar";
        $ical[]="VERSION:2.0";
        $ical[]="METHOD:PUBLISH";
        $ical[]="X-PUBLISHED-TTL:PT15M";
        $ical[]="REFRESH-INTERVAL;VALUE=DURATION:PT15M";
        $ical[]="BEGIN:VTIMEZONE";
        $ical[]="TZID:$tz";
        $ical[]="BEGIN:STANDARD";
        $ical[]="DTSTART:16010101T030000";
        $ical[]="TZOFFSETTO:+0100";
        $ical[]="TZOFFSETFROM:+0200";
        $ical[]="RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=10;BYDAY=-1SU";
        $ical[]="TZNAME:CET";
        $ical[]="END:STANDARD";
        $ical[]="BEGIN:DAYLIGHT";
        $ical[]="DTSTART:16010101T020000";
        $ical[]="TZOFFSETTO:+0200";
        $ical[]="TZOFFSETFROM:+0100";
        $ical[]="RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=3;BYDAY=-1SU";
        $ical[]="TZNAME:CEST";
        $ical[]="END:DAYLIGHT";
        $ical[]="END:VTIMEZONE";

        $tab = array();
        $i=0;
        if (isset($planning)) {
            // Exclusion des planning non validés
            foreach ($planning as $elem) {

                if (!array_key_exists($elem['date'].'_'.$elem['site'], $verrou)) {
                    continue;
                }

                // Exclusion des absences
                foreach ($absences as $a) {
                    if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin'] > $elem['date'].' '.$elem['debut']) {
                        continue 2;
                    }
                }

                if ($elem['absent'] == 1) {
                    continue;
                }

                // Regroupe les plages de SP qui se suivent sur le même poste
                if (isset($tab[$i-1])
                    and $tab[$i-1]['date'] == $elem['date']
                    and $tab[$i-1]['debut'] == $elem['fin']
                    and $tab[$i-1]['poste'] == $elem['poste']
                    and $tab[$i-1]['site'] == $elem['site']
                    and $tab[$i-1]['supprime'] == $elem['supprime']
                    and $tab[$i-1]['absent'] == $elem['absent']) {
                    $tab[$i-1]['debut'] = $elem['debut'];
                } else {
                    $tab[$i++] = $elem;
                }
            }

            // Complète le tableau $ical
            foreach ($tab as $elem) {

                // Organizer
                $organizer = null;
                if (isset($agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']])) {
                    $tmp = $agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']];
                    $organizer = $tmp['prenom'] . ' ' . $tmp['nom'];
                    $organizer .= ':mailto:'.$tmp['mail'];
                }

                $params = [
                    'id' => $id,
                    'start' => strtotime($elem['date']." ".$elem['debut']),
                    'end' => strtotime($elem['date']." ".$elem['fin']),
                    'site' => !empty($sites[$elem['site']]) ? $sites[$elem['site']] : null,
                    'siteId' => $elem['site'],
                    'floor' => !empty($postes[$elem['poste']]['etage']) ? ' ' . $postes[$elem['poste']]['etage'] : null,
                    'position' => $postes[$elem['poste']]['nom'],
                    'positionId' => $elem['poste'],
                    'organizer' => $organizer,
                    'lastModified' => strtotime($verrou[$elem['date'].'_'.$elem['site']]['date']),
                ];

                $event = \CJICS::createIcsEvent($params);
                $ical = array_merge($ical, $event);
            }
        }

        if (isset($absences) and $get_absences) {

          // Complète le tableau $ical

          foreach ($absences as $elem) {

            $params = [
                'id' => $id,
                'start' => strtotime($elem['debut']),
                'end' => strtotime($elem['fin']),
                'reason' => isset($elem['motif']) ? $elem['motif'] : 'Congé Payé',
                'comment' => $elem['commentaires'],
                'status' => $elem['valide'] ? 'CONFIRMED' : 'TENTATIVE',
                'createdAt' => isset($elem['demande']) ? strtotime($elem['demande']) : null,
                'lastModified' => strtotime($elem['validation']),
            ];

            $event = \CJICS::createIcsEvent($params);
            $ical = array_merge($ical, $event);
          }
        }

        $ical[]="END:VCALENDAR";

        $ical=implode("\n", $ical);

        $response = new Response();
        $response->setContent($ical);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'inline; filename=calendar.ics');
        return $response;
    }

}

?>
