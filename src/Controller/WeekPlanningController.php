<?php

namespace App\Controller;

use App\Controller\BaseController;

use App\Model\AbsenceReason;
use App\Model\SelectFloor;
use App\PlanningBiblio\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

include_once(__DIR__ . '/../../public/planning/poste/class.planning.php');
include_once(__DIR__ . '/../../public/absences/class.absences.php');
include_once(__DIR__ . '/../../public/conges/class.conges.php');
include_once(__DIR__ . '/../../public/activites/class.activites.php');
include_once(__DIR__ . '/../../public/personnel/class.personnel.php');
include_once(__DIR__ . '/../../public/planning/poste/fonctions.php');

class WeekPlanningController extends BaseController
{
    /**
     * @Route("/week", name="planning.week", methods={"GET"})
     */
    public function week(Request $request)
    {
        $groupe = $request->get('groupe');
        $site = $request->get('site');
        $tableau = $request->get('tableau');
        $date = $request->get('date');

        $dbprefix = $GLOBALS['dbprefix'];
        $CSRFSession = $GLOBALS['CSRFSession'];

        $date = filter_var($date, FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));

        if (!$date and array_key_exists('PLdate', $_SESSION)) {
            $date = $_SESSION['PLdate'];
        } elseif (!$date and !array_key_exists('PLdate', $_SESSION)) {
            $date = date("Y-m-d");
        }

        $_SESSION['PLdate'] = $date;
        $d = new \datePl($date);
        $semaine = $d->semaine;
        $semaine3 = $d->semaine3;
        $jour = $d->jour;
        $dates = $d->dates;
        $dateAlpha = dateAlpha($date);

        $_SESSION['oups']['week']=true;

        // ------------------ TABLEAU -----------------------//
        // Multisites : la variable $site est égale à 1 par défaut.
        // Elle prend la valeur GET['site'] si elle existe, sinon la valeur de la SESSION ['site']
        // En dernier lieu, la valeur du site renseignée dans la fiche de l'agent
        if (!$site and array_key_exists('site', $_SESSION['oups'])) {
            $site = $_SESSION['oups']['site'];
        }
        if (!$site) {
            $p = new \personnel();
            $p->fetchById($_SESSION['login_id']);
            $site = $p->elements[0]['sites'][0] ?? 1;
        }
        $site = $site ? $site : 1;
        $_SESSION['oups']['site'] = $site;
        // ------------------ FIN TABLEAU -----------------------//

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

        // $absence_reasons will be used in the cellule_poste function. Using a global variable will avoid multiple access to the database and enhance performances
        global $absence_reasons;
        $absence_reasons = $this->entityManager->getRepository(AbsenceReason::class);

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

        // ---------- Récupération des postes -----------------//
        // $postes will also be used in the cellule_poste function.
        // Using a global variable will avoid multiple access
        // to the database and enhance performances.
        global $postes;
        $postes = array();

        // Récupération des activités pour appliquer
        // les classes aux lignes postes en fonction de celles-ci.
        $a = new \activites();
        $a->fetch();
        $activites = $a->elements;

        // Récupération des catégories pour appliquer
        // les classes aux lignes postes en fonction de celles-ci.
        $categories = array();
        $db = new \db();
        $db->select2('select_categories');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $categories[$elem['id']] = $elem['valeur'];
            }
        }

        // Floors
        $floors = $this->entityManager->getRepository(SelectFloor::class);

        // Récupération des postes
        $db = new \db();
        $db->select2('postes', '*', '1', 'ORDER BY `id`');

        if ($db->result) {
            foreach ($db->result as $elem) {
                // Classes CSS du poste
                $classesPoste = array();

                // Ajout des classes en fonction des activités
                $activitesPoste = $elem['activites'] ? json_decode(html_entity_decode($elem['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();

                foreach ($activitesPoste as $a) {
                    if (isset($activites[$a]['nom'])) {
                        $classesPoste[] = 'tr_activite_'.strtolower(removeAccents(str_replace(array(' ','/'), '_', $activites[$a]['nom'])));
                    }
                }

                // Ajout des classes de la ligne en fonction
                // des catégories requises par le poste (A,B ou C).
                $categoriesPoste = $elem['categories'] ? json_decode(html_entity_decode($elem['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();
                foreach ($categoriesPoste as $cat) {
                    if (array_key_exists($cat, $categories)) {
                        $classesPoste[] = 'tr_' .str_replace(" ", "", removeAccents(html_entity_decode($categories[$cat], ENT_QUOTES|ENT_IGNORE, "UTF-8")));
                    }
                }

                // Tableau $postes
                $postes[$elem['id']] = array(
                    'nom' => $elem['nom'],
                    'etage' => $floors->find($elem['etage']) ? $floors->find($elem['etage'])->valeur() : null,
                    'obligatoire' => $elem['obligatoire'],
                    'teleworking' => $elem['teleworking'],
                    'classes' => implode(' ', $classesPoste)
                );
            }
        }
        // --------FIN Récupération des postes -----------//

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

        // Lignes de separation
        $db = new \db();
        $db->select2('lignes');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $lignes_sep[$elem['id']] = $elem['nom'];
            }
        }

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
                // ------------ Recherche des infos cellules ------------ //
                // Toutes les infos seront stockées danx un tableau et
                // utilisées par les fonctions cellules_postes.
                $db = new \db();
                $db->selectLeftJoin(
                    array('pl_poste', 'perso_id'),
                    array('personnel', 'id'),
                    array('perso_id', 'debut', 'fin', 'poste', 'absent', 'supprime', 'grise'),
                    array('nom', 'prenom', 'statut', 'service', 'postes'),
                    array('date' => $date, 'site' => $site),
                    array(),
                    "ORDER BY `{$dbprefix}pl_poste`.`absent` desc,`{$dbprefix}personnel`.`nom`, `{$dbprefix}personnel`.`prenom`"
            );

                // $cellules will be used in the cellule_poste function.
                // Using a global variable will avoid multiple access to
                // the database and enhance performances.
                global $cellules;
                $cellules = $db->result ? $db->result:array();
                usort($cellules, 'cmp_nom_prenom');

                // Recherche des absences
                // Le tableau $absences sera utilisé par
                // la fonction cellule_poste pour barrer les
                // absents dans le plannings et pour afficher
                // les absents en bas du planning.
                // $cellules will be used in the cellule_poste function.
                // Using a global variable will avoid multiple access to
                // the database and enhance performances.
                $a = new \absences();
                $a->valide = false;
                $a->documents = false;
                $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date);
                global $absences;
                $absences = $a->elements ?? array();

                // Ajoute les qualifications de chaque agent (activités)
                // dans le tableaux $cellules pour personnaliser
                // l'affichage des cellules en fonction des qualifications.
                foreach ($cellules as $k => $v) {
                    if ($v['postes']) {
                        $p = json_decode(html_entity_decode($v['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                        $cellules[$k]['activites'] = array();
                        foreach ($activites as $elem) {
                            if (in_array($elem['id'], $p)) {
                                $cellules[$k]['activites'][] = $elem['nom'];
                            }
                        }
                    }
                }

                // Tri des absences par nom
                usort($absences, 'cmp_nom_prenom_debut_fin');

                // Informations sur les congés
                // $conges will be used in the cellule_poste function.
                // Using a global variable will avoid multiple access
                // to the database and enhance performances.
                $conges = array();
                global $conges;
                if ($this->config('Conges-Enable')) {
                    $c = new \conges();
                    $conges = $c->all($date.' 00:00:00', $date.' 23:59:59');
                }
                // ------------ FIN Recherche des infos cellules ---------- //

                // ------------ Affichage du tableau ---------- //
                // Récupération de la structure du tableau
                $t = new Framework();
                $t->id = $tab;
                $t->get();
                $tabs = $t->elements;

                // Repère les heures de début et de fin de
                // chaque tableau pour ajouter des colonnes
                // si ces heures sont différentes.
                $hre_debut = '23:59';
                $hre_fin = null;
                foreach ($tabs as $elem) {
                    $hre_debut = $elem['horaires'][0]['debut'] < $hre_debut
                        ? $elem['horaires'][0]['debut'] : $hre_debut;

                    $nb = count($elem['horaires']) - 1;
                    $hre_fin = $elem['horaires'][$nb]['fin'] > $hre_fin
                        ? $elem['horaires'][$nb]['fin'] : $hre_fin;
                }

                $l=0;
                foreach ($tabs as $tab_index => $tab) {
                    $tab['l'] = $l;
                    // Comble les horaires laissés vides : créé la colonne manquante,
                    // les cellules de cette colonne seront grisées.
                    $cellules_grises = array();
                    $tmp = array();

                    // Première colonne : si le début de ce tableau est
                    // supérieur au début d'un autre tableau.
                    $k = 0;
                    if ($tab['horaires'][0]['debut'] > $hre_debut) {
                        $tmp[] = array(
                            'debut' => $hre_debut,
                            'fin' => $tab['horaires'][0]['debut']
                        );
                        $cellules_grises[] = $k++;
                    }

                    // Colonnes manquantes entre le début et la fin
                    foreach ($tab['horaires'] as $key => $value) {
                        if ($key == 0 or $value['debut'] == $tab['horaires'][$key-1]['fin']) {
                            $tmp[] = $value;
                        } elseif ($value['debut'] > $tab['horaires'][$key-1]['fin']) {
                            // FIXME why ?
                            $tmp[] = array(
                                'debut' => $tab['horaires'][$key-1]['fin'],
                                'fin' => $value['debut']
                            );
                            $tmp[] = $value;
                            $cellules_grises[] = $k++;
                        }
                        $k++;
                    }

                    // Dernière colonne : si la fin de ce tableau est
                    // inférieure à la fin d'un autre tableau.
                    $nb = count($tab['horaires'])-1;
                    if ($tab['horaires'][$nb]['fin'] < $hre_fin) {
                        $tmp[] = array(
                            'debut' => $tab['horaires'][$nb]['fin'],
                            'fin' => $hre_fin
                        );
                        $cellules_grises[] = $k;
                    }

                    $tab['horaires'] = $tmp;

                    $colspan = 0;
                    foreach ($tab['horaires'] as $horaires) {
                        $colspan+=nb30($horaires['debut'], $horaires['fin']);
                    }
                    $tab['colspan'] = $colspan;

                    //	Lignes postes et grandes lignes
                    foreach ($tab['lignes'] as $line_index => $ligne) {
                        $emptyLine = null;
                        if (!$this->config('Planning-lignesVides') and $verrou and isAnEmptyLine($ligne['poste'])) {
                            $emptyLine = "empty-line";
                        }
                        $ligne['emptyLine'] = $emptyLine;

                        if ($ligne['type'] == 'poste' and $ligne['poste']) {
                            $ligne['classTD'] = $postes[$ligne['poste']]['obligatoire'] == 'Obligatoire'
                                ? 'td_obligatoire' : 'td_renfort';
                            // Classe de la ligne en fonction du type de poste
                            // (obligatoire ou de renfort)
                            $ligne['classTR'] = $postes[$ligne['poste']]['obligatoire'] == 'Obligatoire'
                                ? 'tr_obligatoire' : 'tr_renfort';

                            // Classe de la ligne en fonction des activités et des catégories
                            $ligne['classTR'] .= ' ' . $postes[$ligne['poste']]['classes'];
                            $ligne['position_name'] = $postes[$ligne['poste']]['nom'];
                            $ligne['position_floor'] = $postes[$ligne['poste']]['etage'];

                            $i=1;
                            $k=1;
                            $ligne['line_time'] = array();
                            foreach ($tab['horaires'] as $horaires) {
                                // Recherche des infos à afficher dans chaque cellule
                                // Cellules grisées si définies dans la configuration
                                // du tableau et si la colonne a été ajoutée automatiquement.
                                $horaires['disabled'] = 0;
                                if (in_array("{$ligne['ligne']}_{$k}", $tab['cellules_grises'])
                                    or in_array($i-1, $cellules_grises)) {
                                    $horaires['disabled'] = 1;
                                    // Si colonne ajoutée, ça décale les cellules
                                    // grises initialement prévues.
                                    // On se décale d'un cran en arrière pour rétablir l'ordre.
                                    if (in_array($i - 1, $cellules_grises)) {
                                        $k--;
                                    }
                                }
                                else {
                                    $horaires['position_cell'] = cellule_poste($date, $horaires['debut'], $horaires['fin'], nb30($horaires['debut'], $horaires['fin']), 'noms', $ligne['poste'], $site);
                                }
                                $i++;
                                $k++;
                                $ligne['line_time'][] = $horaires;
                            }
                        }
                        if ($ligne['type']=="ligne") {
                            $ligne['line_sep'] = $lignes_sep[$ligne['poste']];
                        }
                        $tab['lignes'][$line_index] = $ligne;
                    }
                    $l++;
                    $day['tabs'][$tab_index] = $tab;
                }
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
