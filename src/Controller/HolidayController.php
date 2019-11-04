<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class HolidayController extends BaseController
{
    /**
     * @Route("/holiday/index/{recovery}", defaults={"recovery"=0}, name="holiday.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $annee = $request->get('annee');
        $congesAffiches = $request->get('congesAffiches');
        $perso_id = $request->get('perso_id');
        $reset = $request->get('reset');
        $supprimes = $request->get('supprimes');
        $voir_recup = $request->get('recup');

        // Gestion des droits d'administration
        // NOTE : Ici, pas de différenciation entre les droits niveau 1 et niveau 2
        // NOTE : Les agents ayant les droits niveau 1 ou niveau 2 sont admin ($admin, droits 40x et 60x)
        // TODO : différencier les niveau 1 et 2 si demandé par les utilisateurs du plugin
        $admin = false;
        $adminN2 = false;
        $droits = $GLOBALS['droits'];
        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((400+$i), $droits) or in_array((600+$i), $droits)) {
                $admin = true;
            }
            if (in_array((600+$i), $droits)) {
                $adminN2 = true;
            }
        }

        if ($admin and $perso_id==null) {
            $perso_id=isset($_SESSION['oups']['conges_perso_id'])?$_SESSION['oups']['conges_perso_id']:$_SESSION['login_id'];
        } elseif ($perso_id==null) {
            $perso_id=$_SESSION['login_id'];
        }

        $agents_supprimes=isset($_SESSION['oups']['conges_agents_supprimes'])?$_SESSION['oups']['conges_agents_supprimes']:false;
        $agents_supprimes=($annee and $supprimes)?true:$agents_supprimes;
        $agents_supprimes=($annee and !$supprimes)?false:$agents_supprimes;

        if (!$annee) {
            $annee=isset($_SESSION['oups']['conges_annee'])?$_SESSION['oups']['conges_annee']:(date("m")<9?date("Y")-1:date("Y"));
        }

        if (!$congesAffiches) {
            $congesAffiches=isset($_SESSION['oups']['congesAffiches'])?$_SESSION['oups']['congesAffiches']:"aVenir";
        }

        if ($reset) {
            $annee=date("m")<9?date("Y")-1:date("Y");
            $perso_id=$_SESSION['login_id'];
            $agents_supprimes=false;
        }
        $_SESSION['oups']['conges_annee']=$annee;
        $_SESSION['oups']['congesAffiches']=$congesAffiches;
        $_SESSION['oups']['conges_perso_id']=$perso_id;
        $_SESSION['oups']['conges_agents_supprimes']=$agents_supprimes;


        $debut=$annee."-09-01";
        $fin=($annee+1)."-08-31";

        if ($congesAffiches=="aVenir") {
            $debut=date("Y-m-d");
        }

        $c = new \conges();
        $c->debut = $debut;
        $c->fin = $fin . " 23:59:59";
        if ($perso_id != 0) {
            $c->perso_id = $perso_id;
        }
        if ($agents_supprimes) {
            $c->agents_supprimes = array(0,1);
        }

        $addLink = '/index.php?page=conges/enregistrer.php';
        // Si la gestion des congés et des récupérations est dissociée, on ne recherche que les infos voulues
        if ($this->config('Conges-Recuperations') == '1') {
            if ($voir_recup) {
                $c->debit='recuperation';
                $addLink = '/index.php?page=conges/recup_pose.php';
            } else {
                $c->debit='credit';
            }
        }
        $this->templateParams(array('addlink' => $addLink));
        $c->fetch();

        // Recherche des agents pour le menu
        $agents_menu = array();
        if ($admin) {
            $p=new \personnel();
            $p->responsablesParAgent = true;
            if ($agents_supprimes) {
                $p->supprime=array(0,1);
            }
            $p->fetch();
            $agents_menu=$p->elements;

            // Filtre pour n'afficher que les agents gérés si l'option "Absences-notifications-agent-par-agent" est cochée
            if ($this->config('Absences-notifications-agent-par-agent') and !$adminN2) {
                $tmp = array();

                foreach ($agents_menu as $elem) {
                    if ($elem['id'] == $_SESSION['login_id']) {
                        $tmp[$elem['id']] = $elem;
                    } else {
                        foreach ($elem['responsables'] as $resp) {
                            if ($resp['responsable'] == $_SESSION['login_id']) {
                                $tmp[$elem['id']] = $elem;
                                break;
                            }
                        }
                    }
                }

                $agents_menu = $tmp;
            }

            // Liste des agents à conserver :
            $perso_ids = array_keys($agents_menu);
            $perso_ids = array_merge($perso_ids, array($_SESSION['login_id']));
        } else {
            $perso_ids = array($_SESSION['login_id']);
        }

        // Recherche des agents pour la fonction nom()
        $p = new \personnel();
        $p->supprime=array(0,1,2);
        $p->fetch();
        $agents=$p->elements;

        // Années universitaires
        $annees=array();
        for ($d=date("Y")+2;$d>date("Y")-11;$d--) {
            $annees[]=array($d,$d."-".($d+1));
        }

        $this->templateParams(array(
            'admin'                 => $admin,
            'perso_id'              => $perso_id,
            'agents_menu'           => $agents_menu,
            'deleted_agents'        => $agents_supprimes ? 1 : 0,
            'conges_recuperation'   => $this->config('Conges-Recuperations'),
            'show_recovery'         => $voir_recup,
            'agent_name'            => nom($perso_id, "prenom nom", $agents),
            'from_year'             => $annee,
            'to_year'               => $annee + 1,
            'years'                 => $annees,
            'forthcoming'           => $congesAffiches == "aVenir" ? 1 : 0,
            'balance'               => $this->config('Conges-Recuperations') == '0' or !$voir_recup ? 1 : 0,
            'recovery'              => $this->config('Conges-Recuperations') == '0' or $voir_recup ? 1 : 0,
            'perso_ids'             => $perso_ids,
        ));

        $holidays = array();
        foreach ($c->elements as $elem) {

          // Filtre les agents non-gérés (notamment avec l'option Absences-notifications-agent-par-agent)
            if (!in_array($elem['perso_id'], $perso_ids)) {
                continue;
            }

            // Si la gestion des congés et des récupérations est dissociée, la requête recherche également les mises à jour des crédits.
            // Ici, on filtre les lignes "Mises à jour des crédits" pour n'afficher que celles qui concernent les récupérations ou les congés.
            if ($this->config('Conges-Recuperations') == '1') {
                if ($elem['debit'] == null) {
                    if ($voir_recup and $elem['recup_actuel'] == $elem['recup_prec']) {
                        continue;
                    }
                    if (!$voir_recup
                and $elem['solde_actuel'] == $elem['solde_prec']
                and $elem['reliquat_actuel'] == $elem['reliquat_prec']
                and $elem['anticipation_actuel'] == $elem['anticipation_prec']) {
                        continue;
                    }
                }
            }

            $elem['start'] = str_replace("00h00", "", dateFr($elem['debut'], true));
            $elem['end'] = str_replace("23h59", "", dateFr($elem['fin'], true));
            $elem['hours'] = heure4($elem['heures']);
            $elem['status'] = "Demandé, ".dateFr($elem['saisie'], true);
            $elem['validationDate'] = dateFr($elem['saisie'], true);
            $elem['validationStyle'] = "font-weight:bold;";

            $elem['credits'] = '';
            $elem['reliquat'] = '';
            $elem['recuperations'] = '';
            $elem['anticipation'] = '';
            $elem['creditClass'] = '';
            $elem['reliquatClass'] = '';
            $elem['recuperationsClass'] = '';
            $elem['anticipationClass'] = '';

            if ($elem['saisie_par'] and $elem['perso_id']!=$elem['saisie_par']) {
                $elem['status'] .= " par ".nom($elem['saisie_par'], 'nom p', $agents);
            }

            if ($elem['valide']<0) {
                $elem['status'] = "Refusé, ".nom(-$elem['valide'], 'nom p', $agents);
                $elem['validationDate'] = dateFr($elem['validation'], true);
                $elem['validationStyle'] = "color:red;";
            } elseif ($elem['valide'] or $elem['information']) {
                $elem['status'] = "Validé, ".nom($elem['valide'], 'nom p', $agents);
                $elem['validationDate'] = dateFr($elem['validation'], true);
                $elem['validationStyle'] = '';

                $credits = heure4($elem['solde_prec']);
                $elem['creditClass'] = 'aRight ';
                if ($elem['solde_prec']!=$elem['solde_actuel']) {
                    $elem['credits'] = heure4($elem['solde_prec'], true)." → ".heure4($elem['solde_actuel'], true);
                    $elem['creditClass'] .= "bold";
                }

                $elem['recuperations'] = heure4($elem['recup_prec']);
                $elem['recuperationsClass'] = "aRight ";
                if ($elem['recup_prec']!=$elem['recup_actuel']) {
                    $elem['recuperations'] = heure4($elem['recup_prec'], true)." &rarr; ".heure4($elem['recup_actuel'], true);
                    $elem['recuperationsClass'] .= "bold";
                }

                $elem['reliquat'] = heure4($elem['reliquat_prec']);
                $elem['reliquatClass'] = "aRight ";
                if ($elem['reliquat_prec']!=$elem['reliquat_actuel']) {
                    $elem['reliquat'] = heure4($elem['reliquat_prec'], true)." &rarr; ".heure4($elem['reliquat_actuel'], true);
                    $elem['reliquatClass'] .= "bold";
                }

                $elem['anticipation'] = heure4($elem['anticipation_prec']);
                $elem['anticipationClass'] = "aRight ";
                if ($elem['anticipation_prec']!=$elem['anticipation_actuel']) {
                    $elem['anticipation'] = heure4($elem['anticipation_prec'], true)." &rarr; ".heure4($elem['anticipation_actuel'], true);
                    $elem['anticipationClass'] .= "bold";
                }
            } elseif ($elem['valide_n1']) {
                $elem['status'] = $elem['valide_n1'] > 0 ? $lang['leave_table_accepted_pending'] : $lang['leave_table_refused_pending'];
                $elem['validationDate'] = dateFr($elem['validation_n1'], true);
                $elem['validationStyle'] = "font-weight:bold;";
            }

            if ($elem['information']) {
                $elem['nom'] = $elem['information']<999999999?nom($elem['information'], 'nom p', $agents).", ":null;	// >999999999 = cron
                $elem['status'] = "Mise à jour des crédits, " . $elem['nom'];
                $elem['validationDate'] = dateFr($elem['info_date'], true);
                $elem['validationStyle'] = '';
            } elseif ($elem['supprime']) {
                $elem['status'] = "Supprimé, ".nom($elem['supprime'], 'nom p', $agents);
                $elem['validationDate'] = dateFr($elem['suppr_date'], true);
                $elem['validationStyle'] = '';
            }

            $elem['nom'] = $admin ? nom($elem['perso_id'], 'nom p', $agents) : '';

            $holidays[] = $elem;
        }

        $this->templateParams(array('holidays' => $holidays));

        return $this->output('conges/index.html.twig');
    }
}