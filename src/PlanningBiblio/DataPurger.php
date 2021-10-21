<?php

namespace App\PlanningBiblio;

include_once __DIR__ . '/../../public/absences/class.absences.php';

use App\Model\Absence;
use App\PlanningBiblio\Logger;

class DataPurger
{

    private $dbprefix;
    private $delay;
    private $entityManager;
    private $stdout;

    public function __construct($entityManager, $delay, $stdout)
    {
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->delay = $delay;
        $this->entityManager = $entityManager;
        $this->logger = new Logger($entityManager, $stdout);
    }

    public function purge() {
        $GLOBALS['entityManager'] = $this->entityManager;
        $this->log("Start purging $this->delay years old data");
        $absences = $this->entityManager->getRepository(Absence::class)->findBy(['id' => 14203]);
        foreach ($absences as $absenceModel) {
//            $this->log(print_r($absence, 1));
            $this->log("Purging absence id " . $absenceModel->id() . " perso_id " . $absenceModel->perso_id());
            $absence = new \absences();
            $absence->fetchById($absenceModel->id());
            $absence->purge();
        }
        
        $this->log("End purging old data");
    }

    private function log($message) {
        $this->logger->log($message, "DataPurger");
    }

}
