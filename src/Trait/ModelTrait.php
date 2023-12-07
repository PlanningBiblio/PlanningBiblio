<?php

namespace App\Trait;

trait ModelTrait
{

    protected function delete_model($models, $CSRFToken)
    {
        if (!empty($models)) {

            if (!is_array($models)) {
                $models = array($models);
            }

            foreach ($models as $model) {
                $delete = new \db();
                $delete->CSRFToken = $CSRFToken;
                $delete->delete('pl_poste_modeles', array('model_id' => $model->model_id()));
                $delete = new \db();
                $delete->CSRFToken = $CSRFToken;
                $delete->delete('pl_poste_modeles_tab', array('model_id' => $model->model_id()));
            }
        }
    }

    protected function save_model($nom, $date, $semaine, $site, $CSRFToken, $origin = null)
    {
        $dbprefix=$GLOBALS['config']['dbprefix'];
        $d = new \datePl($date);
  
        $tab_db = null;
        $select = null;
  
        // Select data between monday and sunday
        // for the current week.
        if ($semaine) {
            // Select tables structures
            $tab_db = new \db();
            $tab_db->select2('pl_poste_tab_affect', '*', array(
                'date' => "BETWEEN{$d->dates[0]}AND{$d->dates[6]}",
                'site' => $site)
            );
  
            // Select agents put on cells.
            $select = new \db();
            $select->select2('pl_poste', '*', array(
                'date' => "BETWEEN{$d->dates[0]}AND{$d->dates[6]}",
                'site' => $site)
            );
        }
        // Select data of current day.
        else {
            // Select table's structure
            $tab_db = new \db();
            $tab_db->select2('pl_poste_tab_affect', '*', array(
                'date' => $date,
                'site' => $site)
            );
  
            // Select agents put on cells.
            $select = new \db();
            $select->select2('pl_poste', '*', array(
                'date' => $date,
                'site' => $site)
            );
        }
  
        if ($select->result and $tab_db->result) {
            // Model_id
            $db = new \db();
            $db->query('select MAX(`model_id`) AS `model` FROM `pl_poste_modeles_tab`;');
  
            $last_id = $db->result[0]['model'] ? intval($db->result[0]['model']) : 0;
            $model = $last_id + 1;
  
            $values = array();
            foreach ($select->result as $elem) {
                $jour=""; // $jour keeps null if we import only a day.
                if ($semaine) {
                    $d = new \datePl($elem['date']);
                    $jour = $d->position; // Week's day position (1=Monday , 2=Tuesday ...)
                    if ($jour == 0) {
                        $jour = 7;
                    }
                }
                $values[] = array(
                    ':model_id' => $model,
                    ':perso_id' => $elem['perso_id'],
                    ':poste' => $elem['poste'],
                    ':debut' => $elem['debut'],
                    ':fin' => $elem['fin'],
                    ':jour' => $jour,
                    ':site' => $site,
                );
            }
  
            $dbh = new \dbh();
            $dbh->CSRFToken = $CSRFToken;
            $dbh->prepare("INSERT INTO `{$dbprefix}pl_poste_modeles` (`model_id`, `perso_id`, `poste`, `debut`, `fin`, `jour`, `site`) VALUES (:model_id, :perso_id, :poste, :debut, :fin, :jour, :site);");
            foreach ($values as $value) {
                $dbh->execute($value);
            }
  
            foreach ($tab_db->result as $elem) {
                $jour = 9; // 9 means day of week is not specified.
                if ($semaine) {
                    $d = new \datePl($elem['date']);
                    $jour=$d->position; // Week's day position (1=Monday , 2=Tuesday ...)
                    if ($jour == 0) {
                        $jour = 7;
                    }
                }
                $insert = array(
                    'model_id' => $model,
                    'nom' => $nom,
                    'jour' => $jour,
                    'tableau' => $elem['tableau'],
                    'site' => $site,
                    'origin' => $origin,
                );
  
                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->insert('pl_poste_modeles_tab', $insert);
            }
        }
    }

}
