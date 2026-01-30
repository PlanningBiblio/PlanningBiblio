<?php

namespace App\Planno;

use App\Entity\AbsenceReason;
use App\Entity\Agent;
use App\Entity\Config;

require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../legacy/Class/class.postes.php');

class PlanningExportUtils
{
    private $config;
    private $entityManager;

    public function __construct($entityManager)
    {
        // TODO / FIXME : sans doute une meilleure façon d'obtenir $entityManager (pas avec $GLOBALS)
        $this->entityManager = $entityManager;
        $this->config = $entityManager->getRepository(Config::class)->getAll();
    }

    public function export($userIds = [], $start = null, $exportAbsences = false)
    {

        // Cas ICS
        $id = $userIds[0];

        $planning_tmp = [];
        $db=new \db();
        $db->selectInnerJoin(
            array("pl_poste","perso_id"),
            array("personnel","id"),
            array("date", "debut", "fin", "poste", 'site', 'absent', 'supprime'),
            array(),
            array("perso_id"=>$id),
            array('supprime' => 0),
            ($start ? 'AND `date` > "' . $start->format('Y-m-d') .'" ' : '') . "ORDER BY `date` DESC, `debut` DESC, `fin` DESC"
        );
        if ($db->result) {
            $planning_tmp = $db->result;
        }

        // Recherche des postes pour affichage du nom des postes
        $p = new \postes();
        $p->fetch();
        $positions = $p->elements;

        // Liste des sites
        $sites = [];
        if ($this->config['Multisites-nombre'] > 1) {
            for ($i = 1; $i <= $this->config['Multisites-nombre']; $i++) {
                $sites[$i] = $this->config['Multisites-site' . $i];
            }
        }

        // Recherche des plannings verrouillés pour exclure les plages concernant des plannings en attente
        $locks = [];
        $db = new \db();
        $db->select2('pl_poste_verrou', null, ['verrou2'=>'1'], ($start ? 'AND `date` > "' . $start->format('Y-m-d') .'" ' : ''));
        
        if ($db->result) {
            foreach ($db->result as $elem) {
                $locks[$elem['date'].'_'.$elem['site']] = array('date' => $elem['validation2'], 'agent' => $elem['perso2']);
            }
        }

        // Teleworking reasons
        $teleworkingReasons = $this->entityManager->getRepository(AbsenceReason::class)
            ->getRemoteWorkingDescriptions();

        // Recherche des absences
        $absences = [];

        if ($this->config['Absences-Exclusion'] != 2) {
            $a = new \absences();
            $a->valide = true;
            $a->documents = false;
            $a->fetch(
                '`debut`,`fin`',
                $id,
                ($start ? $start->format('Y-m-d') : '0000-00-00 00:00:00'),
                date('Y-m-d', strtotime(date('Y-m-d').' + 2 years'))
            );

            if ($this->config['Absences-Exclusion'] == 0) {
                $absences = $a->elements;
            } else {
                foreach ($a->elements as $abs) {
                    if ($abs['valide'] != 99999) {
                        $absences[] = $abs;
                    }
                }
            }
        }

        // Recherche des congés (si le module est activé)
        if ($this->config['Conges-Enable']) {
            $c = new \conges();
            $c->perso_id = $id;
            $c->debut = ($start ? $start->format('Y-m-d') : '0000-00-00 00:00:00');
            $c->fin = date('Y-m-d', strtotime(date('Y-m-d').' + 2 years'));
            $c->valide = true;
            $c->fetch();
            $absences = array_merge($absences, $c->elements);
        }

        // Nettoyage du tableau $planning.
        $planning = [];
        foreach ($planning_tmp as $elem) {
            // Exclusion des planning non validés
            if (!array_key_exists($elem['date'].'_'.$elem['site'], $locks)) {
                continue;
            }

            // Exclusion des absences
            foreach ($absences as $a) {
                if ($a['debut'] < $elem['date'].' '.$elem['fin']
                    and $a['fin'] > $elem['date'].' '.$elem['debut']
                    and !($positions[$elem['poste']]['teleworking'] and isset($a['motif']) and in_array($a['motif'], $teleworkingReasons))
                    ) {
                    continue 2;
                }
            }

            if ($elem['absent'] == 1) {
                continue;
            }

            $planning[] = $elem;
        }

        // Tableaux contenant les noms et emails de tous les agents, permet de renseigner le champ ORGANIZER avec le nom de l'agent ayant vérrouillé le planning
        $p = new \personnel();
        $p->supprime = array(0,1,2);
        $p->fetch();
        $agents = $p->elements;

        // Regroupe les plages de SP qui se suivent sur le même poste
        $i = 0;
        $tab = [];
        foreach ($planning as $elem) {

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

        // Add extra information
        foreach ($tab as $key => $elem) {
            // Organizer
            $organizer = null;
            if (isset($agents[$locks[$elem['date'].'_'.$elem['site']]['agent']])) {
                $tmp = $agents[$locks[$elem['date'].'_'.$elem['site']]['agent']];
                $organizer = $tmp['prenom'] . ' ' . $tmp['nom'];
                $organizer .= ':mailto:'.$tmp['mail'];
                $tab[$key]['organizer'] = $organizer;
            }

            $tab[$key]['start'] = \DateTime::createFromFormat('Y-m-d H:i:s', $elem['date'] . ' ' .$elem['debut']);
            $tab[$key]['end'] = \DateTime::createFromFormat('Y-m-d H:i:s', $elem['date'] . ' ' .$elem['fin']);
            $tab[$key]['floor'] = !empty($positions[$elem['poste']]['etage']) ? ' ' . $positions[$elem['poste']]['etage'] : null;
            $tab[$key]['lastModified'] = \DateTime::createFromFormat('Y-m-d H:i:s', $locks[$elem['date'].'_'.$elem['site']]['date']);
            $tab[$key]['position'] = $positions[$elem['poste']]['nom'];
            $tab[$key]['siteName'] = !empty($sites[$elem['site']]) ? $sites[$elem['site']] : null;
            $tab[$key]['userId'] = $id;
        }

        // Add absences
        if ($exportAbsences) {
            foreach ($absences as $elem) {
                $tab[] = [
                    'userId' => $id,
                    'start' => \DateTime::createFromFormat('Y-m-d H:i:s', $elem['debut']),
                    'end' => \DateTime::createFromFormat('Y-m-d H:i:s', $elem['fin']),
                    'reason' => isset($elem['motif']) ? $elem['motif'] : 'Congé Payé',
                    'comment' => $elem['commentaires'],
                    'status' => $elem['valide'] ? 'CONFIRMED' : 'TENTATIVE',
                    'createdAt' => isset($elem['demande']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $elem['demande']) : null,
                    'lastModified' => \DateTime::createFromFormat('Y-m-d H:i:s', $elem['validation']),
                ];
            }
        }

        return $tab;
    }
}