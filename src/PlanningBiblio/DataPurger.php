<?php

namespace App\PlanningBiblio;

include_once __DIR__ . '/../../public/absences/class.absences.php';

use App\Model\Absence;
use App\Model\AbsenceInfo;
use App\Model\AdminInfo;
use App\Model\CallForHelp;
use App\Model\Holiday;
use App\Model\HolidayInfo;
use App\Model\HoursAbsence;
use App\Model\IPBlocker;
use App\Model\Logs;
use App\Model\PlanningNote;
use App\Model\PlanningNotification;
use App\Model\PlanningPoste;
use App\Model\PublicServiceHours;
use App\Model\PublicHoliday;
use App\Model\RecurringAbsence;
use App\Model\SaturdayWorkingHours;
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
        $today = new \DateTime('NOW');
        $end_of_week_limit_date = $limit_date->sub(new \DateInterval('P6D')); 
        $three_years_limit_date = $today->sub(new \Dateinterval('P3Y'));
        $three_years_limit_date = ($limit_date > $three_years_limit_date) ? $three_years_limit_date : $limit_date;

        $this->log("limit date: " . $limit_date->format('Y-m-d H:i:s'));
        $this->log("three years limit date: " . $three_years_limit_date->format('Y-m-d H:i:s'));


        // Absences
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


        // Recurring Absences
        $builder = $this->entityManager->createQueryBuilder();
        $builder->delete()
                ->from(RecurringAbsence::class, 'a')
                ->andWhere('a.end = :ended')
                ->andWhere('a.timestamp < :limit_date')
                ->setParameter('ended', "1")
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();
        $this->log("Purging $results recurring absences");


        $this->simplePurge(AbsenceInfo::class,          'fin',       $limit_date);
        $this->simplePurge(CallForHelp::class,          'timestamp', $limit_date);
        $this->simplePurge(Holiday::class,              'fin',       $limit_date);
        $this->simplePurge(HolidayInfo::class,          'fin',       $limit_date);
        $this->simplePurge(SaturdayWorkingHours::class, 'semaine',   $end_of_week_limit_date);
        $this->simplePurge(HoursAbsence::class,         'semaine',   $end_of_week_limit_date);
        $this->simplePurge(PublicServiceHours::class,   'semaine',   $end_of_week_limit_date);
        $this->simplePurge(AdminInfo::class,            'fin',       $limit_date);
        $this->simplePurge(IPBlocker::class,            'timestamp', $limit_date);
        $this->simplePurge(PublicHoliday::class,        'jour',      $three_years_limit_date);
        $this->simplePurge(Logs::class,                 'timestamp', $limit_date);
        $this->simplePurge(PlanningNote::class,         'date',      $limit_date);
        $this->simplePurge(PlanningNotification::class, 'date',      $limit_date);
        $this->simplePurge(PlanningPoste::class,        'date',      $limit_date);

        $this->entityManager->flush();
        $this->log("End purging old data");
    }

    private function simplePurge($class, $field, $limit_date) {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->delete()
                ->from($class, 'a')
                ->andWhere('a.' . $field . ' < :limit_date')
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();
        $this->log("Purging $results $class");
    }

    private function log($message) {
        $this->logger->log($message, "DataPurger");
    }

}
