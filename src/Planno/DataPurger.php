<?php

namespace App\Planno;

use App\Entity\Absence;
use App\Entity\AbsenceInfo;
use App\Entity\AdminInfo;
use App\Entity\Agent;
use App\Entity\CallForHelp;
use App\Entity\OverTime;
use App\Entity\Detached;
use App\Entity\Holiday;
use App\Entity\HolidayInfo;
use App\Entity\IPBlocker;
use App\Entity\Log;
use App\Entity\PlanningNote;
use App\Entity\PlanningNotification;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionLock;
use App\Entity\PlanningPositionTab;
use App\Entity\PlanningPositionTabAffectation;
use App\Entity\Position;
use App\Entity\PublicHoliday;
use App\Entity\RecurringAbsence;
use App\Entity\SaturdayWorkingHours;
use App\Entity\Skill;
use App\Entity\WorkingHour;

use App\Entity\HiddenTables;

class DataPurger
{
    use \App\Traits\LoggerTrait;
    private $dbprefix;
    private $delay;
    private $entityManager;
    private $stdout;

    public function __construct($entityManager, $delay, $stdout)
    {
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->delay = $delay;
        $this->entityManager = $entityManager;
    }

    public function purge() {
        $GLOBALS['entityManager'] = $this->entityManager;
        $this->logMessage("Start purging $this->delay years old data");

        $first_of_january = new \DateTime('first day of January this year');
        $limit_date = clone $first_of_january;
        $limit_date->sub(new \DateInterval('P' . $this->delay . 'Y'));

        $end_of_week_limit_date = clone $limit_date;
        $end_of_week_limit_date->sub(new \DateInterval('P6D'));

        $three_years_limit_date = clone $first_of_january;
        $three_years_limit_date->sub(new \Dateinterval('P3Y'));
        $three_years_limit_date = ($limit_date > $three_years_limit_date) ? $three_years_limit_date : $limit_date;

        $this->logMessage("limit date: " . $limit_date->format('Y-m-d H:i:s'));
        $this->logMessage("end of week limit date: " . $end_of_week_limit_date->format('Y-m-d H:i:s'));
        $this->logMessage("three years limit date: " . $three_years_limit_date->format('Y-m-d H:i:s'));

        $this->simplePurge(AbsenceInfo::class,                    'fin',       '<', $limit_date);
        $this->simplePurge(AdminInfo::class,                      'fin',       '<', $limit_date);
        $this->simplePurge(CallForHelp::class,                    'timestamp', '<', $limit_date);
        $this->simplePurge(OverTime::class,                       'date',      '<', $limit_date);
        $this->simplePurge(Detached::class,                       'date',      '<', $limit_date);
        $this->simplePurge(Holiday::class,                        'fin',       '<', $limit_date);
        $this->simplePurge(HolidayInfo::class,                    'fin',       '<', $limit_date);
        $this->simplePurge(IPBlocker::class,                      'timestamp', '<', $limit_date);
        $this->simplePurge(Log::class,                            'timestamp', '<', $limit_date);
        $this->simplePurge(PlanningNote::class,                   'date',      '<', $limit_date);
        $this->simplePurge(PlanningNotification::class,           'date',      '<', $limit_date);
        $this->simplePurge(PlanningPosition::class,               'date',      '<', $limit_date);
        $this->simplePurge(PlanningPositionLock::class,           'date',      '<', $limit_date);
        $this->simplePurge(PlanningPositionTabAffectation::class, 'date',      '<', $limit_date);
        $this->simplePurge(PublicHoliday::class,                  'jour',      '<', $three_years_limit_date);
        $this->simplePurge(SaturdayWorkingHours::class,           'semaine',   '<', $end_of_week_limit_date);
        $this->simplePurge(WorkingHour::class,                    'fin',       '<', $limit_date);

        $this->logMessage("Purging special cases:");

        // Absences
        $deleted_absences = $this->entityManager->getRepository(Absence::class)->purgeAll($limit_date);
        $this->logMessage("Purging $deleted_absences App\Entity\Absence");

        // Agents
        $deleted_agents = $this->entityManager->getRepository(Agent::class)->purgeAll();
        $this->logMessage("Purging $deleted_agents App\Entity\Agent");

        // Planning Position Tab
        $deleted_planning_position_tab = $this->entityManager->getRepository(PlanningPositionTab::class)->purgeAll($limit_date);
        $this->logMessage("Purging $deleted_planning_position_tab App\Entity\PlanningPositionTab");

        // Position
        $deleted_position = $this->entityManager->getRepository(Position::class)->purgeAll($limit_date);
        $this->logMessage("Purging $deleted_position App\Entity\Position");

        // Skills
        $deleted_skill = $this->entityManager->getRepository(Skill::class)->purgeAll($limit_date);
        $this->logMessage("Purging $deleted_skill App\Entity\Skill");

        // Recurring Absences
        $builder = $this->entityManager->createQueryBuilder();
        $builder->delete()
                ->from(RecurringAbsence::class, 'a')
                ->andWhere('a.end = :ended')
                ->andWhere('a.timestamp < :limit_date')
                ->setParameter('ended', "1")
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();
        $this->logMessage("Purging $results App\Entity\RecurringAbsence");

        $this->entityManager->flush();
        $this->logMessage("End purging old data");
    }

    private function simplePurge($class, $field, $operator, $value) {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->delete()
                ->from($class, 'a')
                ->andWhere('a.' . $field . ' ' . $operator . ' :value')
                ->setParameter('value', $value);
        $results = $builder->getQuery()->getResult();
        $this->logMessage("Purging $results $class");
    }

    private function logMessage($message) {
        $this->log($message, "DataPurger");
    }

}
