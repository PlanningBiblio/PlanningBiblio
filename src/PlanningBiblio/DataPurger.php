<?php

namespace App\PlanningBiblio;

include_once __DIR__ . '/../../public/absences/class.absences.php';

use App\Model\Absence;
use App\Model\RecurringAbsence;
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

        // TODO : change me to use $delay
        $limit_date = new \DateTime('NOW');

        $this->log("Start purging absences");
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select('a')
                ->from(Absence::class, 'a')
                ->andWhere('a.fin < :limit_date')
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();

        foreach ($results as $result) {
            $this->log("Purging absence id " . $result->id() . " perso_id " . $result->perso_id());
            $absence = new \absences();
            $absence->fetchById($result->id());
            //$absence->purge();
        }

        $this->log("Start purging recurring absences");
        $builder = $this->entityManager->createQueryBuilder();
        // TODO: Use delete since we have nothing else to do
        $builder->select('a')
                ->from(RecurringAbsence::class, 'a')
                ->andWhere('a.end = 1')
                ->andWhere('a.timestamp < :limit_date')
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();
        foreach ($results as $result) {
            $this->log("Purging recurring absence id " . $result->id() . " perso_id " . $result->perso_id());
#            $absence = new \absences();
#            $absence->fetchById($absenceModel->id());
            //$absence->purge();
        }


        $this->log("Start purging absences_info");
#        $absences_infos = $this->entityManager->getRepository(AbsenceInfo::class)->findBy(['id' => 14203]);
        

        $this->log("Start purging appel_dispo");


        $this->entityManager->flush();
        $this->log("End purging old data");
    }

    private function log($message) {
        $this->logger->log($message, "DataPurger");
    }

}
