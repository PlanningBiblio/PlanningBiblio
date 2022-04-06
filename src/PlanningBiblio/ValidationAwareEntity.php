<?php

namespace App\PlanningBiblio;

require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/planningHebdo/class.planningHebdo.php');

class ValidationAwareEntity
{
    private $type = null;

    private $entity = null;

    private $status_desc = array(
        'feminine' => array(
            0 => 'Demandée',
            1 => 'Acceptée',
            -1 => 'Refusée',
            2 => 'Acceptée (En attente de validation hiérarchique)',
            -2 => 'Refusée (En attente de validation hiérarchique)',
        ),
        'male' => array(
            0 => 'Demandé',
            1 => 'Accepté',
            -1 => 'Refusé',
            2 => 'Accepté (En attente de validation hiérarchique)',
            -2 => 'Refusé (En attente de validation hiérarchique)',
        ),
    );

    private $config = null;

    public function __construct($entity_type, $entity_id)
    {
        if (!in_array($entity_type, array('absence', 'holiday', 'comptime', 'workinghour'))) {
            throw new \Exception("ValidationAwareEntity::new: Unsupported entity $entity_type");
        }

        $this->config = $GLOBALS['config'];
        $this->type = $entity_type;

        if (!$entity_id) {
            return $this;
        }

        if ($entity_type == 'absence') {
            $this->entity = self::load_absence($entity_id);
        }

        if ($entity_type == 'holiday') {
            $this->entity = self::load_holiday($entity_id);
        }

        if ($entity_type == 'comptime') {
            $this->entity = self::load_comptime($entity_id);
        }

        if ($entity_type == 'workinghour') {
            $this->entity = self::load_workinghours($entity_id);
        }
    }

    public function needsValidationL1()
    {
        if ($this->type == 'absence') {
            return $this->config['Absences-Validation-N2'];
        }

        if ($this->type == 'holiday' or $this->type == 'comptime') {
            return $this->config['Conges-Validation-N2'];
        }

        if ($this->type == 'workinghour') {
            return $this->config['PlanningHebdo-Validation-N2'];
        }

        return 0;
    }

    public function status()
    {
        $mode = 'male';
        if ($this->type == 'absence') {
            $mode = 'feminine';
        }

        // New entity.
        if (!$this->entity) {
            return array(0, $this->status_desc[$mode][0]);
        }

        $valide_n1 = null;
        $valide_n2 = null;

        if ($this->type == 'absence') {
            $valide_n2 = $this->entity['valide_n2'];
            $valide_n1 = $this->entity['valide_n1'];
        }

        if ($this->type == 'holiday'
            or $this->type == 'workinghour'
            or $this->type == 'comptime') {

            $valide_n2 = $this->entity['valide'];
            $valide_n1 = $this->entity['valide_n1'];
        }

        // Accepted level 2.
        if ($valide_n2 > 0) {
            return array(1, $this->status_desc[$mode][1]);
        }

        // Rejected level 2.
        if ($valide_n2 < 0) {
            return array(-1, $this->status_desc[$mode][-1]);
        }

        // Accepted level 1
        if ($valide_n1 > 0) {
            return array(2, $this->status_desc[$mode][2]);
        }

        // Rejected level 1
        if ($valide_n1 < 0) {
            return array(-2, $this->status_desc[$mode][-2]);
        }

        return array(0, $this->status_desc[$mode][0]);
    }

    private static function load_absence($id)
    {
        $absence = new \absences();
        $absence->fetchById($id);

        return $absence->elements;
    }

    private static function load_holiday($id)
    {
        $c = new \conges();
        $c->id = $id;
        $c->fetch();

        return $c->elements[0];
    }

    private static function load_comptime($id)
    {
        $c = new \conges();
        $c->recupId = $id;
        $c->getRecup();

        return $c->elements[0];
    }

    private static function load_workinghours($id)
    {
        $p = new \planningHebdo();
        $p->id = $id;
        $p->fetch();

        return $p->elements[0];
    }
}
