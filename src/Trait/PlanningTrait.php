<?php

namespace App\Trait;

use App\Model\SelectFloor;
use App\Model\SeparationLine;
use App\PlanningBiblio\Framework;

require_once(__DIR__ . '/../../public/activites/class.activites.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/planning/poste/fonctions.php');
require_once(__DIR__ . '/../../public/planning/volants/class.volants.php');

trait PlanningTrait
{

    private function createTables($tab, $verrou)
    {
        // Separation lines
        $separationE = $this->entityManager->getRepository(SeparationLine::class)->findAll();

        $separations = array();
        foreach ($separationE as $elem) {
            $separations[$elem->id()] = $elem->nom();
        }

        // Get framework structure, start and end hours.
        list($tabs, $startTime, $endTime) = $this->getFrameworkStructure($tab);

        $hiddenTables = $this->getHiddenTables($tab);

        $l = 0;
        $sn = 1;

        foreach ($tabs as $index => $tab) {

            $hiddenTable = in_array($l, $hiddenTables) ? 'hidden-table' : null;
            $tabs[$index]['hiddenTable'] = $hiddenTable;
            $tabs[$index]['l'] = $l;

            // Comble les horaires laissés vides :
            // Créé la colonne manquante, les cellules de cette colonne seront grisées.
            $cellules_grises = array();
            $tmp = array();

            // Première colonne : si le début de ce tableau est supérieur au début d'un autre tableau.
            $k = 0;
            if ($tab['horaires'][0]['debut'] > $startTime) {
                $tmp[] = array(
                    'debut' => $startTime,
                    'fin' => $tab['horaires'][0]['debut']
                );
                $cellules_grises[] = $k++;
            }

            // Colonnes manquantes entre le début et la fin
            foreach ($tab['horaires'] as $key => $value) {
                if ($key == 0 or $value['debut'] == $tab['horaires'][$key-1]['fin']) {
                    $tmp[] = $value;
                } elseif ($value['debut'] > $tab['horaires'][$key-1]['fin']) {
                    $tmp[] = array(
                        'debut' => $tab['horaires'][$key-1]['fin'],
                        'fin' => $value['debut']
                    );
                    $tmp[] = $value;
                    $cellules_grises[] = $k++;
                }
                $k++;
            }

            // Dernière colonne : si la fin de ce tableau est inférieure à la fin d'un autre tableau.
            $nb = count($tab['horaires']) - 1;
            if ($tab['horaires'][$nb]['fin'] < $endTime) {
                $tmp[] = array(
                    'debut' => $tab['horaires'][$nb]['fin'],
                    'fin' => $endTime
                );
                $cellules_grises[] = $k;
            }

            $tab['horaires'] = $tmp;

            // Table name
            $tabs[$index]['titre2'] = $tab['titre'];
            if (!$tab['titre']) {
                $tabs[$index]['titre2'] = "Sans nom $sn";
                $sn++;
            }

            // Masquer les tableaux
            $masqueTableaux = null;
            if ($this->config('Planning-TableauxMasques')) {
                // FIXME HTML
                $masqueTableaux = "<span title='Masquer' class='pl-icon pl-icon-hide masqueTableau pointer noprint' data-id='$l' ></span>";
            }
            $tabs[$index]['masqueTableaux'] = $masqueTableaux;

            // Lignes horaires
            $colspan = 0;
            foreach ($tab['horaires'] as $key => $horaires) {

                $tabs[$index]['horaires'][$key]['start_nb30'] = nb30($horaires['debut'], $horaires['fin']);
                $tabs[$index]['horaires'][$key]['start_h3'] = heure3($horaires['debut']) ;
                $tabs[$index]['horaires'][$key]['end_h3'] = heure3($horaires['fin']) ;

                $colspan += nb30($horaires['debut'], $horaires['fin']);
            }
            $tabs[$index]['colspan'] = $colspan;

            // Lignes postes et grandes lignes
            foreach ($tab['lignes'] as $key => $ligne) {

                // Check if the line is empty.
                // Don't show empty lines if Planning-vides is disabled.
                $emptyLine = null;
                if (!$this->config('Planning-lignesVides') and $verrou and isAnEmptyLine($ligne['poste'])) {
                    $emptyLine="empty-line";
                }

                $ligne['emptyLine'] = $emptyLine;
                $ligne['is_position'] = '';
                $ligne['separation'] = '';

                // Position lines
                if ($ligne['type'] == 'poste' and $ligne['poste']) {

                    $ligne['is_position'] = 1;

                    // FIXME Check if 'classTD' is used

                    // Cell class depends if the position is mandatory or not.
                    $ligne['classTD'] = $postes[$ligne['poste']]['obligatoire'] == 'Obligatoire' ? 'td_obligatoire' : 'td_renfort';

                    // Line class depends if the position is mandatory or not.
                    $ligne['classTR'] = $postes[$ligne['poste']]['obligatoire'] == 'Obligatoire' ? 'tr_obligatoire' : 'tr_renfort';

                    // Line class depends on skills and categories.
                    $ligne['classTR'] .= ' ' . $postes[$ligne['poste']]['classes'];

                    // Position name
                    $ligne['position_name'] = $postes[$ligne['poste']]['nom'];

                    if ($this->config('Affichage-etages') and !empty($postes[$ligne['poste']]['etage'])) {
                        $ligne['position_name'] .= ' (' . $postes[$ligne['poste']]['etage'] . ')';
                    }

                    $i=1;
                    $k=1;
                    $ligne['line_time'] = array();
                    foreach ($tab['horaires'] as $horaires) {
                        // Recherche des infos à afficher dans chaque cellule

                        // Cell disabled.
                        // Cellules grisées si définies dans la configuration
                        // du tableau et si la colonne a été ajoutée automatiquement.
                        $horaires['disabled'] = 0;

                        if (in_array("{$ligne['ligne']}_{$k}", $tab['cellules_grises']) or in_array($i-1, $cellules_grises)) {
                            $horaires['disabled'] = 1;
                            $horaires['colspan'] = nb30($horaires['debut'], $horaires['fin']);

                            // If column added, that shift disabled cells.
                            // Si colonne ajoutée, ça décale les cellules grises initialement prévues.
                            // On se décale d'un cran en arrière pour rétablir l'ordre.
                            if (in_array($i - 1, $cellules_grises)) {
                                $k--;
                            }
                        }

                        // function cellule_poste(date,debut,fin,colspan,affichage,poste,site)
                        else {
                            $horaires['position_cell'] = cellule_poste($date, $horaires['debut'], $horaires['fin'], nb30($horaires['debut'], $horaires['fin']), 'noms', $ligne['poste'], $site);
                        }
                        $i++;
                        $k++;
                        $ligne['line_time'][] = $horaires;
                    }
                }

                // Separation lines
                if ($ligne['type'] == 'ligne') {
                    $ligne['separation'] = $separations[$ligne['poste']] ?? null;
                }

                $tabs[$index]['lignes'][$key] = $ligne;
            }
            $l++;
        }

        return $tabs;
    }


    private function getAbsences($date)
    {
        $a = new \absences();
        $a->valide = false;
        $a->documents = false;
        $a->rejected = false;
        $a->agents_supprimes = array(0,1,2);    // required for history
        $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date);
        $absences = $a->elements ?? array();

        usort($absences, 'cmp_nom_prenom_debut_fin');

        return $absences;
    }

    private function getCategories()
    {
        $categories = array();

        $db = new \db();
        $db->select2('select_categories');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $categories[$elem['id']] = $elem['valeur'];
            }
        }

        return $categories;
    }

    private function getCells($date, $site, $activites)
    {
        $db = new \db();
        $db->selectLeftJoin(
            array('pl_poste', 'perso_id'),
            array('personnel', 'id'),
            array('perso_id', 'debut', 'fin', 'poste', 'absent', 'supprime', 'grise'),
            array('nom', 'prenom', 'statut', 'service', 'postes', 'depart'),
            array('date' => $date, 'site' => $site),
            array(),
            "ORDER BY `{$this->dbprefix}pl_poste`.`absent` desc, `{$this->dbprefix}personnel`.`nom`, `{$this->dbprefix}personnel`.`prenom`"
        );

        $cellules = $db->result ? $db->result : array();
        usort($cellules, 'cmp_nom_prenom');

        // Recherche des agents volants
        if ($this->config('Planning-agents-volants')) {
            $v = new \volants($date);
            $v->fetch($date);
            $agents_volants = $v->selected;

            // Modification du statut pour les agents volants afin de personnaliser l'affichage
            foreach ($cellules as $k => $v) {
                if (in_array($v['perso_id'], $agents_volants)) {
                    $cellules[$k]['statut'] = 'volants';
                }
            }
        }

        // Ajoute les qualifications de chaque agent (activités) dans le tableaux $cellules pour personnaliser l'affichage des cellules en fonction des qualifications
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

        return $cellules;
    }

    private function getDatesPlanning($date)
    {
        $d = new \datePl($date);
        $semaine = $d->semaine;
        $semaine3 = $d->semaine3;
        $jour = $d->jour;
        $dates = $d->dates;
        $datesSemaine = implode(',', $dates);
        $dateAlpha = dateAlpha($date);

        return array($d, $d->semaine, $d->semaine3,
            $d->jour, $d->dates,
            implode(",", $d->dates),
            dateAlpha($date)
        );
    }

    private function getFrameworkStructure($tab)
    {
        $t = new Framework();
        $t->id = $tab;
        $t->get();
        $tabs = $t->elements;

        $debut = '23:59';
        $fin = null;
        foreach ($tabs as $elem) {
            $debut = $elem['horaires'][0]['debut'] < $debut
                ? $elem['horaires'][0]['debut']
                : $debut;

            $nb = count($elem['horaires']) - 1;
            $fin = $elem['horaires'][$nb]['fin'] > $fin
                ? $elem['horaires'][$nb]['fin']
                : $fin;
        }
        return array($tabs, $debut, $fin);
    }

    private function getHiddenTables($request, $tab)
    {
        $session = $request->getSession();

        $hiddenTables = array();
        $db = new \db();
        $db->select2('hidden_tables', '*', array(
            'perso_id' => $session->get('loginId'),
            'tableau' => $tab
        ));

        if ($db->result) {
            $hiddenTables = json_decode(html_entity_decode($db->result[0]['hidden_tables'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        }

        return $hiddenTables;
    }

    private function getHolidays($date)
    {
        $conges = array();

        if ($this->config('Conges-Enable')) {
            $c = new \conges();
            $conges = $c->all($date.' 00:00:00', $date.' 23:59:59');
        }

        return $conges;
    }

    private function getPositions($activites, $categories)
    {
        $postes=array();

        $db = new \db();
        $db->select2('postes', '*', '1', 'ORDER BY `id`');

        $floorsE = $this->entityManager->getRepository(SelectFloor::class)->findAll();

        $floors = array();
        foreach($floorsE as $elem) {
            $floors[$elem->id()] = $elem->valeur();
        }

        if ($db->result) {
            foreach ($db->result as $elem) {
                // Position CSS class
                $classesPoste = array();

                // Add classes according to skills
                $activitesPoste = $elem['activites'] ? json_decode(html_entity_decode($elem['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();

                foreach ($activitesPoste as $a) {
                    if (isset($activites[$a]['nom'])) {
                        $classesPoste[] = 'tr_activite_' . strtolower(removeAccents(str_replace(array(' ', '/'), '_', $activites[$a]['nom'])));
                    }
                }

                // Add classes according to required categories
                $categoriesPoste = $elem['categories'] ? json_decode(html_entity_decode($elem['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();
                foreach ($categoriesPoste as $cat) {
                    if (array_key_exists($cat, $categories)) {
                        $classesPoste[] = 'tr_' . str_replace(' ', '', removeAccents(html_entity_decode($categories[$cat], ENT_QUOTES|ENT_IGNORE, 'UTF-8')));
                    }
                }

                $postes[$elem['id']] = array(
                    'nom'         => $elem['nom'],
                    'etage'       => $floors[$elem['etage']] ?? null,
                    'obligatoire' => $elem['obligatoire'],
                    'teleworking' => $elem['teleworking'],
                    'classes'     => implode(' ', $classesPoste)
                );
            }
        }

        return $postes;
    }

    private function getSkills()
    {
        $a = new \activites();
        $a->deleted = true;
        $a->fetch();

        return $a->elements;
    }

}
