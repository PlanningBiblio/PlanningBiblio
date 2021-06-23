<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;
use App\Model\AbsenceReason;

use App\PlanningBiblio\WorkingHours;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/include/function.php');
require_once(__DIR__ . '/../../public/include/horaires.php');
require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/personnel/class.personnel.php');
require_once(__DIR__ . '/../../public/planning/poste/fonctions.php');
require_once(__DIR__ . '/../../public/planning/poste/class.planning.php');
require_once(__DIR__ . '/../../public/planning/volants/class.volants.php');
require_once(__DIR__ . '/../../public/planningHebdo/class.planningHebdo.php');

class PlanningJobController extends BaseController
{
    /**
     * @Route("/planningjob/contextmenu", name="planningjob.contextmenu", methods={"GET"})
     */
    public function contextmenu(Request $request)
    {
        $site = $request->get('site');
        $date = $request->get('date');
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $perso_id = $request->get('perso_id');
        $perso_nom = $request->get('perso_nom');
        $poste = $request->get('poste');
        $CSRFToken = $request->get('CSRFToken');

        $login_id = $_SESSION['login_id'];
        $this->droits = $GLOBALS['droits'];
        $dbprefix = $GLOBALS['dbprefix'];
        $tab_exclus = array(0);
        $absents = array(0);
        $absences_non_validees = array(0);
        $agents_qualif = array(0);
        $tab_deja_place = array(0);
        $journey = array();
        $absences_journey = array();
        $sr_init = null;
        $exclusion = array();
        $motifExclusion = array();

        $d = new \datePl($date);
        $j1 = $d->dates[0];
        $j7 = $d->dates[6];
        $semaine = $d->semaine;
        $semaine3 = $d->semaine3;

        $break_countdown = (
            $this->config('PlanningHebdo')
            && $this->config('PlanningHebdo-PauseLibre')
        ) ? 1 : 0;

        // PlanningHebdo and EDTSamedi are not compliant.
        // So, we disable EDTSamedi if
        // PlanningHebdo is enabled
        if ($this->config('PlanningHebdo')) {
            $this->config('EDTSamedi', 0);
        }

        // Check logged-in rights.
        $url = explode("?", $_SERVER['REQUEST_URI']);
        $url = $url[0];

        if (!$this->canManagePlanning($site)) {
            return $this->json('forbiden');
        }

        // Position's name and related skills
        $db = new \db;
        $db->select2('postes', null, array('id' => $poste));
        $posteNom = $db->result[0]['nom'];
        $activites = json_decode(html_entity_decode($db->result[0]['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        $stat = $db->result[0]['statistiques'];
        $teleworking = $db->result[0]['teleworking'];
        $bloquant = $db->result[0]['bloquant'];
        $categories = $db->result[0]['categories'] ? json_decode(html_entity_decode($db->result[0]['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();

        // Site's name
        $siteNom = null;
        if ($this->config('Multisites-nombre') > 1) {
            $siteNom = $this->config("Multisites-site$site");
        }

        // List all statuses related to
        // categories needed to be placed on this position.
        $categorie = null;
        $categories_nb = 0;
        $statuts = array();

        if (!empty($categories)) {
            $categories = join(",", $categories);
            $db = new \db();
            $categories = $db->escapeString($categories);
            $db->select('select_statuts', null, "categorie IN ($categories)");
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $statuts[] = $elem['valeur'];
                }
            }
            $db = new \db();
            $db->select2('select_categories', 'valeur', array('id' => "IN$categories"));

            $tmp = array();
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $tmp[] = str_replace('Cat&eacute;gorie ', null, $elem['valeur']);
                }
                $categorie = ' ('.implode(', ', $tmp).')';

                $categories_nb = $db->nb;
            }
        }

        // Looking for services.
        $db = new \db();
        $db->query("SELECT `{$dbprefix}personnel`.`service` AS `service`, `{$dbprefix}select_services`.`couleur` AS `couleur` FROM `{$dbprefix}personnel` INNER JOIN `{$dbprefix}select_services`
            ON `{$dbprefix}personnel`.`service`=`{$dbprefix}select_services`.`valeur` WHERE `{$dbprefix}personnel`.`service`<>'' GROUP BY `service`;");
        $services = $db->result;
        $services[] = array('service' => 'Sans service');

        // Recherche des agents volants # FIXME Looking for a correct translation.
        if ($this->config('Planning-agents-volants')) {
            $v = new \volants($date);
            $v->fetch($date);
            $agents_volants = $v->selected;
        }

        // Looking for agents already placed at this time slot.
        // Don't check if the position is a blocker one.
        if ($bloquant == '1') {
            $db = new \db();
            $dateSQL = $db->escapeString($date);
            $debutSQL = $db->escapeString($debut);
            $finSQL = $db->escapeString($fin);

            $req = "SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` FROM `{$dbprefix}pl_poste` "
            ."INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
            ."WHERE `{$dbprefix}pl_poste`.`debut`<'$finSQL' AND `{$dbprefix}pl_poste`.`fin`>'$debutSQL' "
                ."AND `{$dbprefix}pl_poste`.`date`='$dateSQL' AND `{$dbprefix}postes`.`bloquant`='1'";

            $db->query($req);
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $tab_exclus[] = $elem['perso_id'];
                    $tab_deja_place[] = $elem['perso_id'];
                }
            }

            // Search for remote job (add journey time)
            if ($this->config('Journey-time-between-sites') > 0) {
                $j_time = $this->config('Journey-time-between-sites');
                $start_with_journey = date('H:i:s', strtotime("-$j_time minutes", strtotime($debutSQL)));
                $end_with_journey = date('H:i:s', strtotime("+$j_time minutes", strtotime($finSQL)));

                if ($this->config('Multisites-nombre') > 1) {
                    $req = "SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` "
                        . "FROM `{$dbprefix}pl_poste` "
                        . "INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
                        . "WHERE `{$dbprefix}pl_poste`.`debut`<'$end_with_journey' AND `{$dbprefix}pl_poste`.`fin`>'$start_with_journey' "
                        . "AND `{$dbprefix}pl_poste`.`site` != $site "
                        . "AND `{$dbprefix}pl_poste`.`date`='$dateSQL' AND `{$dbprefix}postes`.`bloquant`='1'";

                    $db = new \db();
                    $db->query($req);
                    if ($db->result) {
                        foreach ($db->result as $elem) {
                            $journey[] = $elem['perso_id'];
                        }
                    }
                }
            }

            if ($this->config('Journey-time-between-areas') > 0) {
                $j_time = $this->config('Journey-time-between-areas');
                $start_with_journey = date('H:i:s', strtotime("-$j_time minutes", strtotime($debutSQL)));
                $end_with_journey = date('H:i:s', strtotime("+$j_time minutes", strtotime($finSQL)));

                $req = "SELECT `tableau` FROM `{$dbprefix}pl_poste_tab_affect` WHERE `date` = '$dateSQL' AND `site` = $site";
                $db = new \db();
                $db->query($req);
                $table_id = $db->result[0]['tableau'];

                $req = "SELECT `tableau` FROM `{$dbprefix}pl_poste_lignes` WHERE `numero` = $table_id AND `poste` = $poste AND `type` = 'poste'";
                $db = new \db();
                $db->query($req);
                $sub_table_id = $db->result[0]['tableau'];

                $req = "SELECT `poste` FROM `{$dbprefix}pl_poste_lignes` WHERE `numero` = $table_id AND `tableau` != $sub_table_id AND `type` = 'poste'";
                $db = new \db();
                $db->query($req);
                $autres_postes = array();
                if ($db->result) {
                    foreach ($db->result as $elem) {
                        $autres_postes[] = $elem['poste'];
                    }
                }

                $req="SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` "
                    . "FROM `{$dbprefix}pl_poste` "
                    . "INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
                    . "WHERE `{$dbprefix}pl_poste`.`debut`<'$end_with_journey' AND `{$dbprefix}pl_poste`.`fin`>'$start_with_journey' "
                    . "AND `{$dbprefix}pl_poste`.`poste` IN (" . join(",", $autres_postes) . ") "
                    . "AND `{$dbprefix}pl_poste`.`site` = $site "
                    . "AND `{$dbprefix}pl_poste`.`date`='$dateSQL' AND `{$dbprefix}postes`.`bloquant`='1'";
                $db = new \db();
                $db->query($req);
                if ($db->result) {
                    foreach ($db->result as $elem) {
                        $journey[] = $elem['perso_id'];
                    }
                }
            }
        }

        if ($this->config('Journey-time-for-absences') > 0) {
            $j_time = $this->config('Journey-time-for-absences');
            $start_with_journey = date('Y-m-d H:i:s', strtotime("-$j_time minutes", strtotime($debutSQL)));
            $end_with_journey = date('Y-m-d H:i:s', strtotime("+$j_time minutes", strtotime($finSQL)));

            $a = new \absences();
            $a->valide = true;
            $a->fetch(null, null, $start_with_journey, $end_with_journey, null);
            $absences = $a->elements;

            foreach ($absences as $absence) {
                $absences_journey[] = $absence['perso_id'];
            }
        }

        // Count day hours for all agent.
        $day_hours = array();
        if ($break_countdown) {
            $db = new \db();
            $dateSQL = $db->escapeString($date);

            $db->query("SELECT perso_id, debut, fin FROM `{$dbprefix}pl_poste` WHERE date = '$dateSQL' AND supprime = '0';");
            if ($db->result) {
                foreach ($db->result as $elem) {
                    // Get day duration as timestamp
                    // for an easier comparison.
                    $elem_duration = strtotime($elem['fin']) - strtotime($elem['debut']);

                    if (!isset($day_hours[$elem['perso_id']])) {
                        $day_hours[$elem['perso_id']] = 0;
                    }

                    $day_hours[$elem['perso_id']] += $elem_duration;
                }
            }
        }

        // Looking for agents to be excluded (absences).
        $db = new \db();
        $dateSQL=$db->escapeString($date);
        $debutSQL=$db->escapeString($debut);
        $finSQL=$db->escapeString($fin);

        $teleworking_exception = null;

        if ($teleworking) {
            $teleworking_reasons = array();
            $absence_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
            foreach ($absence_reasons as $reason) {
                $teleworking_reasons[] = $reason->valeur();
            }
            $teleworking_exception = (!empty($teleworking_reasons) and is_array($teleworking_reasons)) ? "AND `motif` NOT IN ('" . implode("','", $teleworking_reasons) . "')" : null;
        }

        $db->select('absences', 'perso_id,valide', "`debut`<'$dateSQL $finSQL' AND `fin` >'$dateSQL $debutSQL' $teleworking_exception");

        if ($db->result) {
            foreach ($db->result as $elem) {
                if ($elem['valide'] > 0 or $this->config('Absences-validation') == '0') {
                    $tab_exclus[]=$elem['perso_id'];
                    $absents[]=$elem['perso_id'];
                } elseif ($this->config('Absences-non-validees')) {
                    $absences_non_validees[] = $elem['perso_id'];
                }
            }
        }

        // Looking for agents to be excluded (holidays).
        if ($this->config('Conges-Enable')) {
            $c = new \conges();
            $c->debut = "$date $debut";
            $c->fin = "$date $fin";
            $c->valide = false;
            $c->supprime = false;
            $c->information = false;
            $c->bornesExclues = true;
            $c->fetch();

            foreach ($c->elements as $elem) {
                if ($elem['valide'] > 0) {
                    $tab_exclus[] = $elem['perso_id'];
                    $absents[] = $elem['perso_id'];
                } else {
                    $absences_non_validees[] = $elem['perso_id'];
                }
            }
        }

        // recherche des personnes à exclure (ne travaillant pas à cette heure)
        $db = new \db();
        $dateSQL = $db->escapeString($date);

        $db->query("SELECT * FROM `{$dbprefix}personnel` WHERE `actif` LIKE 'Actif' AND (`depart` >= '$dateSQL' OR `depart` = '0000-00-00');");

        // Chech agents working hours.
        $verif = true;
        // Don't check working hours for Saturday and Sunday
        // if ctrlHresAgents is disabled
        if (!$this->config('ctrlHresAgents') and ($d->position == 6 or $d->position == 0)) {
            $verif = false;
        }

        // If module PlanningHebdo is enabled,
        // looking for related PlanningHebdo' working hours
        if ($this->config('PlanningHebdo')) {
            $p = new \planningHebdo();
            $p->debut = $date;
            $p->fin = $date;
            $p->valide = true;
            $p->fetch();

            $tempsPlanningHebdo=array();
            $breaktimes = array();

            if (!empty($p->elements)) {
                foreach ($p->elements as $elem) {
                    $tempsPlanningHebdo[$elem["perso_id"]]=$elem["temps"];
                    $breaktimes[$elem["perso_id"]] = $elem["breaktime"];
                }
            }
        }

        if ($db->result and $verif) {
            foreach ($db->result as $elem) {
                $temps = array();

                // If PlanningHebdo module is enabled,
                // Get working hour from PlanningHebdo.
                if ($this->config('PlanningHebdo')) {
                    if (array_key_exists($elem['id'], $tempsPlanningHebdo)) {
                        $temps = $tempsPlanningHebdo[$elem['id']];
                    }
                } else {
                    // Get working hours from agent's table.
                    $temps = json_decode(html_entity_decode($elem['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                }

                $jour = $d->planning_day_index_for($elem['id']);

                // Handle exclusions.
                $exclusion[$elem['id']] = array();

                // Check id agent is present on
                // requested time slot.
                if (!calculSiPresent($debut, $fin, $temps, $jour)) {
                    $exclusion[$elem['id']][]="horaires";
                }

                if ($break_countdown) {
                    $day_hour = isset($day_hours[$elem['id']]) ? $day_hours[$elem['id']] : 0;
                    $requested_hours = strtotime($fin) - strtotime($debut);

                    $wh = new WorkingHours($temps);
                    $tab = $wh->hoursOf($jour);

                    $hours_limit = 0;
                    foreach ($tab as $t) {
                        $hours_limit += strtotime($t[1]) - strtotime($t[0]);
                    }

                    $breaktime = isset($breaktimes[$elem['id']][$jour]) ? $breaktimes[$elem['id']][$jour] * 3600 : 0;
                    $hours_limit = $hours_limit - $breaktime;

                    if ($day_hour + $requested_hours > $hours_limit) {
                        $exclusion[$elem['id']][]="break";
                    }

                }

                // Multisites: check if agent is on the requested site.
                // This filter concerns every agents.
                // An other filter will definitly exclude agents that
                // are not in the requested site.
                if ($this->config('Multisites-nombre') > 1) {
                    // index 4 is the site on which
                    // agent is working on.
                    $site_agent = !empty($temps[$jour][4]) ? $temps[$jour][4] : null;

                    // If agent has not site and
                    // he is not excluded for other 
                    // reason, so exclude it.
                    if (empty($site_agent) and !in_array('horaires', $exclusion[$elem['id']])) {
                        $exclusion[$elem['id']][]="sites";
                    }

                    // If agent has a site but it is
                    // not the requested site, so exclude.
                    if (!empty($site_agent) and $site_agent != -1 and $site_agent != $site) {
                        $exclusion[$elem['id']][]="autre_site";
                    }
                }
            }
        }

        // Check agents already occupying
        // the requested position.
        $deja = deja_place($date, $poste);

        // Check agents already placed
        // just before or after the
        // requested time slot.
        $deuxSP = deuxSP($date, $debut, $fin);

        // Retrieves the number of
        // agents already in the cell.
        $db = new \db();
        $dateSQL = $db->escapeString($date);
        $debutSQL = $db->escapeString($debut);
        $finSQL = $db->escapeString($fin);
        $posteSQL = $db->escapeString($poste);
        $siteSQL = $db->escapeString($site);

        // Is the cell disabled ?
        // Disabled == No one allowed to work on.
        $cellule_grise = false;

        $db->select("pl_poste", null, "`poste`='$posteSQL' AND `debut`='$debutSQL' AND `fin`='$finSQL' AND `date`='$dateSQL' AND `site`='$siteSQL'");

        $nbAgents = 0;
        if ($db->result) {
            // Exclude agents that are already
            // in the cell (if the position is a blocking.
            foreach ($db->result as $elem) {
                if ($elem['perso_id'] > 0) {
                    $tab_exclus[] = $elem['perso_id'];
                    $nbAgents++;
                }
                $cellule_grise = $elem['grise'] == 1 ? true : $cellule_grise;
            }
        }
        $exclus=join(',', $tab_exclus);

        // Looking for availables agents.
        $agents_dispo = array();

        $db = new \db();
        $dateSQL = $db->escapeString($date);

        $req="SELECT * FROM `{$dbprefix}personnel` "
          ."WHERE `actif` LIKE 'Actif' AND `arrivee` <= '$dateSQL' AND (`depart` >= '$dateSQL' OR `depart` = '0000-00-00') "
          ."AND `id` NOT IN ($exclus) ORDER BY `nom`,`prenom`;";

        $db->query($req);
        $agents_tmp = $db->result;

        if ($agents_tmp) {
            foreach ($agents_tmp as $elem) {
                // Remove agents without requested skills.
                if (is_array($activites)) {
                    $postes = json_decode(html_entity_decode($elem['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                    if (!is_array($postes)) {
                        $exclusion[$elem['id']][] = 'activites';
                    } else {
                        foreach ($activites as $a) {
                            if (!in_array($a, $postes)) {
                                $exclusion[$elem['id']][] = 'activites';
                                break;
                            }
                        }
                    }
                }

                // Remove agents that are not in requested category.
                if (!empty($statuts)) {
                    if (!in_array($elem['statut'], $statuts)) {
                        $exclusion[$elem['id']][] = 'categories';
                    }
                }

                // Remove agent working on other site.
                if ($this->config('Multisites-nombre') > 1) {
                    $sites = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                    if (!is_array($sites) or !in_array($site, $sites)) {
                        $exclusion[$elem['id']][] = 'sites';
                    }
                }

                if ($this->config('Planning-agents-volants') and in_array($elem['id'], $agents_volants)) {
                    $elem['statut'] = 'volants';
                }

                // If no exclusion for this agent,
                // put it in the availables list.
                if (empty($exclusion[$elem['id']])) {
                    $agents_dispo[] = $elem;
                }

                // Else if at least on exclusion,
                // keep the exclusion code (reason).
                else {
                    if (in_array('horaires', $exclusion[$elem['id']])) {
                        $motifExclusion[$elem['id']][]="times";
                    } elseif (in_array('break', $exclusion[$elem['id']])) {
                        $motifExclusion[$elem['id']][]="break";
                    }
                    if (in_array('autre_site', $exclusion[$elem['id']])) {
                        $motifExclusion[$elem['id']][]="other_site";
                    }
                    if (in_array('sites', $exclusion[$elem['id']])) {
                        $motifExclusion[$elem['id']][]="site";
                    }
                    if (in_array('activites', $exclusion[$elem['id']])) {
                        $motifExclusion[$elem['id']][]="skills";
                    }
                    if (in_array('categories', $exclusion[$elem['id']])) {
                        if ($categories_nb > 1) {
                            $motifExclusion[$elem['id']][]="no_cat";
                        } else {
                            $motifExclusion[$elem['id']][]="wrong_cat";
                        }

                    }
                }
            }
        }

        // $agents_tous == All availables agents.
        $agents_tous = $agents_dispo;


        // Looking for unavailables agents.
        foreach ($agents_dispo as $elem) {
            $agents_qualif[]=$elem['id'];
        }
        $agents_qualif = join(',', $agents_qualif);
        $absents = join(',', $absents);
        $tab_deja_place = join(',', $tab_deja_place);

        $db = new \db();
        $dateSQL = $db->escapeString($date);

        $req="SELECT * FROM `{$dbprefix}personnel` "
          ."WHERE `actif` LIKE 'Actif' AND `arrivee` <= '$dateSQL' AND (`depart` >= '$dateSQL' OR `depart` = '0000-00-00') AND `id` NOT IN ($agents_qualif) "
          ."AND `id` NOT IN ($tab_deja_place) AND `id` NOT IN ($absents)  ORDER BY `nom`,`prenom`;";

        $db->query($req);
        $autres_agents_tmp = $db->result;

        $autres_agents = array();
        if ($autres_agents_tmp) {
            foreach ($autres_agents_tmp as $elem) {
                // Remove agents that doesn't work on requested site.
                // Same check than above, but definitly remove them.
                if ($this->config('Multisites-nombre') > 1) {
                    $sites = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                    if (!is_array($sites) or !in_array($site, $sites)) {
                        continue;
                    }
                }

                if ($this->config('Planning-agents-volants') and in_array($elem['id'], $agents_volants)) {
                    $elem['statut'] = 'volants';
                }

                // FIXME Is setting 2 variables is needed ?
                $autres_agents[] = $elem;
                $agents_tous[] = $elem;
            }
        }

        // Groupe agents by service.
        $newtab = array();
        if ($agents_dispo) {
            foreach ($agents_dispo as $elem) {
                if ($elem['id']!=2) {
                    if (!trim($elem['service'])) {
                        $newtab["Sans service"][]=$elem['id'];
                    } else {
                        $newtab[$elem['service']][]=$elem['id'];
                    }
                }
            }
        }

        if ($autres_agents) {
            foreach ($autres_agents as $elem) {
                if ($elem['id']!=2) {
                    $newtab["Autres"][]=$elem['id'];
                }
            }
        }

        $listparservices = array();
        if (is_array($services)) {
            foreach ($services as $elem) {
                if (array_key_exists($elem['service'], $newtab)) {
                    $listparservices[] = join(',', $newtab[$elem['service']]);
                } else {
                    // FIXME is this useful to push null element ?
                    $listparservices[] = null;
                }
            }
        }

        if (array_key_exists("Autres", $newtab)) {
            $listparservices[] = join(',', $newtab['Autres']);
        } else {
            $listparservices[] = null;
        }
        $tab_agent = join(';', $listparservices);

        $tableaux = array(
            'position_name' => $posteNom, 'position_id' => $poste,
            'date' => $date, 'start' => $debut, 'start_hr' => heure2($debut),
            'end' => $fin, 'end_hr' => heure2($fin), 'site' => $site,
            'site_name' => $siteNom ? $siteNom : '', 'tab_agent' => $tab_agent,
            'group_tab_hide' => $this->config('ClasseParService') ? 1 : 0,
            'everybody' => $this->config('toutlemonde') ? 1 : 0,
            'cell_enabled' => $cellule_grise ? 0 : 1,
            'nb_agents' => $nbAgents, 'max_agents' => $this->config('Planning-NbAgentsCellule'),
            'last_four_weeks' => $this->config('hres4semaines') ? 1 : 0, 'agent_id' => $perso_id,
            'agent_name' => $perso_nom, 'call_for_help' => $this->config('Planning-AppelDispo') ? 1 : 0,
            'can_disable_cell' => in_array( 900 + $site, $this->droits) ? 1 : 0,
            'category' => $categorie, 'menu1' => array(),
            'display_times' => $this->config('Planning-Heures') ? 1 : 0,
        );

        // Prepare displaying of services.
        if ($services and $this->config('ClasseParService')) {
            $tableaux['services'] = array();
            $i=0;
            foreach ($services as $elem) {
                if (array_key_exists($elem['service'], $newtab) and !$cellule_grise) {
                    $elem['class'] = "service_".strtolower(removeAccents(str_replace(" ", "_", $elem['service'])));
                    $elem['tab_agent'] = $tab_agent;
                    $elem['id'] = $i;
                    $tableaux['services'][] = $elem;
                }
                $i++;
            }
        }

        // Show agents list in case
        // ClasseParService is disabled.
        if (!$this->config('ClasseParService') and !$cellule_grise) {
            $hide=false;
            $p = new \planning();
            $p->site = $site;
            $p->CSRFToken = $CSRFToken;
            $p->menudivAfficheAgents($poste, $agents_dispo, $date, $debut, $fin, $deja, $stat, $nbAgents, $sr_init, $hide, $deuxSP, $motifExclusion, $absences_non_validees, $journey, $absences_journey);
            $tableaux['menu1'] = $p->menudiv;
        }

        if (array_key_exists("Autres", $newtab) and $this->config('agentsIndispo') and !$cellule_grise) {
            $tableaux['unavailables_agents'] = array('id' => count($services));
        }

        // Link "call for help"
        if ($this->config('Planning-AppelDispo') and !$cellule_grise) {
            // Check if an email has already been sent.
            $db = new \db();
            $db->select2('appel_dispo', null, array('site' => $site, 'poste' => $poste, 'date' => $date, 'debut' => $debut, 'fin' => $fin), "ORDER BY `timestamp` desc");
            $nbEnvoi = $db->nb;
            $nbEnvoiInfo = '';
            if ($db->result) {
                $dateEnvoi = dateFr($db->result[0]['timestamp']);
                $heureEnvoi = heure2(substr($db->result[0]['timestamp'], 11, 5));
                $destinataires = count(explode(";", $db->result[0]['destinataires']));
                $s = $destinataires > 1 ? 's' : null;

                $nbEnvoiInfo = "L&apos;appel &agrave; disponibilit&eacute; a d&eacute;j&agrave; &eacute;t&eacute; envoy&eacute; $nbEnvoi fois&#013;";
                $nbEnvoiInfo .= "Dernier envoi le $dateEnvoi &agrave; $heureEnvoi&#013;";
                $nbEnvoiInfo .= "$destinataires personne{$s} contact&eacute;e{$s}";
            }

            $agents_appel_dispo = array();
            foreach ($agents_dispo as $a) {
                $agents_appel_dispo[] = array('id'=> $a['id'], 'nom'=> $a['nom'], 'prenom'=> $a['prenom'], 'mail' => $a['mail']);
            }
            $agents_appel_dispo = json_encode($agents_appel_dispo);
            $agents_appel_dispo = htmlentities($agents_appel_dispo, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);

            $tableaux['call_for_help_info'] = $nbEnvoiInfo;
            $tableaux['call_for_help_nb'] = $nbEnvoi;
            $tableaux['call_for_help_agents'] = $agents_appel_dispo;
        }

        // List of agents if ClasseParService is enabled.
        $tableaux['menu2'] = array();
        if ($agents_tous and $this->config('ClasseParService')) {
            $hide = true;
            $p = new \planning();
            $p->site = $site;
            $p->CSRFToken = $CSRFToken;
            $p->menudivAfficheAgents($poste, $agents_tous, $date, $debut, $fin, $deja, $stat, $nbAgents, $sr_init, $hide, $deuxSP, $motifExclusion, $absences_non_validees, $journey, $absences_journey);
            $tableaux['menu2'] = $p->menudiv;
        }

        // list of unavailables agents if ClasseParService is disabled.
        if ($autres_agents and !$this->config('ClasseParService') and $this->config('agentsIndispo')) {
            $hide = true;
            $p = new \planning();
            $p->site = $site;
            $p->CSRFToken = $CSRFToken;
            $p->menudivAfficheAgents($poste, $autres_agents, $date, $debut, $fin, $deja, $stat, $nbAgents, $sr_init, $hide, $deuxSP, $motifExclusion, $absences_non_validees, $journey, $absences_journey);
            $tableaux['menu2'] = $p->menudiv;
        }

        return $this->json($tableaux);
    }

    /**
     * @Route("/ajax/planningjob/checkcopy", name="ajax.planningjobcheckcopy", methods={"GET"})
     */
    public function checkCopy(Request $request)
    {
        // Initilisation des variables
        $date = $request->get('date');
        $start = $request->get('from');
        $end = $request->get('to');
        $agents = json_decode($request->get('agents'));

        $availables = array();
        $unavailables = array();
        $errors = array();

        foreach ($agents as $agent_id) {

            try {
                $agent = $this->entityManager->find(Agent::class, $agent_id);
                $fullname = $agent->prenom() . ' ' . $agent->nom();
                $available = true;

                if ($agent->isAbsentOn("$date $start", "$date $end")) {
                    $available = false;
                }

                if ($available and $agent->isOnVacationOn("$date $start", "$date $end")) {
                    $available = false;
                }

                if ($available) {
                    $d = new \datePl($date);
                    $day = $d->planning_day_index_for($agent_id);
                    $working_hours = $agent->getWorkingHoursOn($date);

                    if (!calculSiPresent($start, $end, $working_hours['temps'], $day)) {
                        $available = false;
                    }
                }

                if ($available and $agent->isBlockedOn($date, $start, $end)) {
                    $available = false;
                }

                if ($available) {
                    $availables[] = $agent_id;
                } else {
                    $unavailables[] = $fullname;
                }
            } catch(Exception $e) {
                $errors[] = $e;
            }
        }

        $unavailables_string = !empty($unavailables) ? "\n - " . implode("\n - ", $unavailables) : null;

        $result = array(
            'availables' => $availables,
            'unavailables' => $unavailables_string,
            'errors' => $errors,
        );

        return $this->json($result);
    }

    private function canManagePlanning($site)
    {
        if (!$_SESSION['login_id']) {
            return false;
        }

        if (!in_array((300 + $site), $this->droits) and !in_array((1000 + $site), $this->droits)) {
            return false;
        }

        return true;
    }
}