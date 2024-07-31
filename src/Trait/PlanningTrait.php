<?php

namespace App\Trait;

use App\Model\SelectFloor;

trait PlanningTrait
{

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
        $floors = $this->entityManager->getRepository(SelectFloor::class);

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
                    'etage'       => $floors->find($elem['etage']) ? $floors->find($elem['etage'])->valeur() : null,
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
