<?php

namespace App\Tests\Command;

use DateTime;
use Tests\PLBWebTestCase;
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
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
class PurgeDataCommandTest extends PLBWebTestCase
{
    public function testPurgeDataCommand(): void
    {
        $this->restore();

	    $date = new DateTime();
        $thisYear = $date->format('Y');
        $limit_date = (clone $date)->modify('first day of January this year')->modify('-2 years');
        $end_of_week_limit_date = (clone $limit_date)->modify('-6 days');
        $three_years_limit_date = (clone $date)->modify('first day of January this year')->modify('-3 years');

        // datas to be deleted before limit_date
        $dates_to_delete = [
            (clone $limit_date)->modify('-1 day'),
            (clone $limit_date)->modify('-2 days'),
            (clone $limit_date)->modify('-3 days'),
            (clone $limit_date)->modify('-6 months'),
            (clone $limit_date)->modify('-1 year'),
        ];

        // datas should not be deleted after (>=) limit_date）
        $dates_to_keep = [
            clone $limit_date,
            (clone $limit_date)->modify('+1 day'),
            (clone $limit_date)->modify('+2 days'),
            (clone $limit_date)->modify('+1 month'),
            (clone $limit_date)->modify('+6 months'),
        ];

        // cover three_years_limit_date
        $public_holiday_dates = [
            (clone $three_years_limit_date)->modify('-1 day'),   //to be deleted
            (clone $three_years_limit_date)->modify('-2 days'),
            clone $three_years_limit_date,                       // should not be deleted
            (clone $three_years_limit_date)->modify('+1 day'),
            (clone $three_years_limit_date)->modify('+1 month'),
        ];

        // cover SaturdayWorkingHours
        $week_dates = [
            (clone $end_of_week_limit_date)->modify('-1 day'),   // to be deleted
            (clone $end_of_week_limit_date)->modify('-2 days'),
            clone $end_of_week_limit_date,                       // should not be deleted
            (clone $end_of_week_limit_date)->modify('+1 day'),
            (clone $end_of_week_limit_date)->modify('+1 month'),
        ];

        $agentRef = $this->builder->build(Agent::class, ['supprime' => 0]); // only this agent should not be deleted
        $refId = $agentRef->getId();

	    foreach (array_merge($dates_to_delete, $dates_to_keep) as $date) {
            $this->builder->build(AbsenceInfo::class,                    ['fin' => $date]);
            $this->builder->build(AdminInfo::class,                      ['fin' => $date]);
            $this->builder->build(CallForHelp::class,                    ['timestamp' => $date, 'date' => $date, 'debut' => $date, 'fin' => $date]);
            $this->builder->build(OverTime::class,                       ['date' => $date, 'perso_id'=> $refId]);
            $this->builder->build(Detached::class,                       ['date' => $date, 'perso_id'=> $refId]);
            $this->builder->build(Holiday::class,                        ['fin' => $date, 'perso_id'=> $refId]);
            $this->builder->build(HolidayInfo::class,                    ['fin' => $date]);
            $this->builder->build(IPBlocker::class,                      ['timestamp' => $date, 'status' => 'success']);
            $this->builder->build(Log::class,                            ['timestamp' => $date]);
            $this->builder->build(PlanningNote::class,                   ['date' => $date, 'perso_id'=> $refId]);
            $this->builder->build(PlanningNotification::class,           ['date' => $date]);
            $this->builder->build(PlanningPosition::class,               ['date' => $date, 'debut'=>$date, 'fin'=>$date, 'perso_id'=> $refId]);
            $this->builder->build(PlanningPositionLock::class,           ['date' => $date, 'perso'=> $refId, 'perso2'=> $refId]);
            $this->builder->build(PlanningPositionTabAffectation::class, ['date' => $date, 'tableau' => $refId]);
            $this->builder->build(WorkingHour::class,                    ['fin' => $date, 'perso_id'=> $refId]);
            $this->builder->build(Absence::class,                    ['fin' => $date, 'groupe' => 1, 'perso_id'=> $refId]);
            $this->builder->build(Agent::class,                    ['supprime' => 2]);
            $this->builder->build(PlanningPositionTab::class,                    ['supprime' => $date]);
            $this->builder->build(Position::class,                    ['supprime' => $date]);
            $this->builder->build(Skill::class,                    ['supprime' => $date]);
            $this->builder->build(RecurringAbsence::class,                    ['timestamp' => $date, 'perso_id'=> $refId, 'end' => 1]);
	    }

        foreach ($public_holiday_dates as $d) {
            $this->builder->build(PublicHoliday::class, ['jour' => $d, 'annee' => $thisYear]);
        }

        foreach ($week_dates as $d) {
            $this->builder->build(SaturdayWorkingHours::class, ['semaine' => $d, 'perso_id'=> $refId]);
        }

        // RecurringAbsence
        $this->builder->build(RecurringAbsence::class, ['timestamp' => (clone $limit_date)->modify('-1 day'), 'end' => 1, 'perso_id'=> $refId]);
        $this->builder->build(RecurringAbsence::class, ['timestamp' => clone $limit_date, 'end' => 1, 'perso_id'=> $refId]); // should not be deleted


        $countBeforeAbsenceInfo                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences_infos");
        $countBeforeAdminInfo                       = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM infos");
        $countBeforeCallForHelp                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM appel_dispo");
        $countBeforeOverTime                        = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM recuperations");
        $countBeforeDetached                        = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM volants");
        $countBeforeHoliday                         = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM conges");
        $countBeforeHolidayInfo                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM conges_infos");
        $countBeforeSaturdayWorkingHours            = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM edt_samedi");
        $countBeforeIPBlocker                       = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM ip_blocker");
        $countBeforeLog                             = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM log");
        $countBeforePlanningNote                    = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_notes");
        $countBeforePlanningNotification            = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_notifications");
        $countBeforePlanningPosition                = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste");
        $countBeforePlanningPositionLock            = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste_verrou");
        $countBeforePlanningPositionTabAffectation  = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste_tab_affect");
        $countBeforePublicHoliday                   = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM jours_feries");
        $countBeforeWorkingHour                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM planning_hebdo");
        $countBeforeAbsence                         = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $countBeforeAgent                           = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM personnel");
        $countBeforePlanningPositionTab             = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste_tab");
        $countBeforePosition                        = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM postes");
        $countBeforeSkill                           = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM activites");
        $countBeforeRecurringAbsence                = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences_recurrentes");
        $this->assertSame(10, $countBeforeAbsenceInfo                   , '10 should be founded');
        $this->assertSame(10, $countBeforeAdminInfo                     , '10 should be founded');
        $this->assertSame(10, $countBeforeCallForHelp                   , '10 should be founded');
        $this->assertSame(10, $countBeforeOverTime                      , '10 should be founded');
        $this->assertSame(10, $countBeforeDetached                      , '10 should be founded');
        $this->assertSame(10, $countBeforeHoliday                       , '10 should be founded');
        $this->assertSame(10, $countBeforeHolidayInfo                   , '10 should be founded');
        $this->assertSame(5, $countBeforeSaturdayWorkingHours           , '5 should be founded');
        $this->assertSame(10, $countBeforeIPBlocker                     , '10 should be founded');
        $this->assertSame(10, $countBeforeLog                           , '10 should be founded');
        $this->assertSame(10, $countBeforePlanningNote                  , '10 should be founded');
        $this->assertSame(10, $countBeforePlanningNotification          , '10 should be founded');
        $this->assertSame(10, $countBeforePlanningPosition              , '10 should be founded');
        $this->assertSame(10, $countBeforePlanningPositionLock          , '10 should be founded');
        $this->assertSame(10, $countBeforePlanningPositionTabAffectation, '10 should be founded');
        $this->assertSame(5, $countBeforePublicHoliday                  , '5 should be founded');
        $this->assertSame(10, $countBeforeWorkingHour                   , '10 should be founded');
        $this->assertSame(10, $countBeforeAbsence                       , '10 should be founded');
        $this->assertSame(13, $countBeforeAgent                         , '13 should be founded');//Administrateur, Tout le monde
        $this->assertSame(11, $countBeforePlanningPositionTab           , '11 should be founded');
        $this->assertSame(42, $countBeforePosition                      , '42 position should be founded');
        $this->assertSame(22, $countBeforeSkill                         , '22 should be founded');
        $this->assertSame(12, $countBeforeRecurringAbsence              , '12 should be founded');

        $this->execute();

        $countAfterAbsenceInfo                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences_infos");
        $countAfterAdminInfo                       = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM infos");
        $countAfterCallForHelp                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM appel_dispo");
        $countAfterOverTime                        = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM recuperations");
        $countAfterDetached                        = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM volants");
        $countAfterHoliday                         = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM conges");
        $countAfterHolidayInfo                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM conges_infos");
        $countAfterSaturdayWorkingHours            = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM edt_samedi");
        $countAfterIPBlocker                       = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM ip_blocker");
        $countAfterLog                             = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM log");
        $countAfterPlanningNote                    = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_notes");
        $countAfterPlanningNotification            = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_notifications");
        $countAfterPlanningPosition                = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste");
        $countAfterPlanningPositionLock            = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste_verrou");
        $countAfterPlanningPositionTabAffectation  = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste_tab_affect");
        $countAfterPublicHoliday                   = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM jours_feries");
        $countAfterWorkingHour                     = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM planning_hebdo");
        $countAfterAbsence                         = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $countAfterAgent                           = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM personnel");
        $countAfterPlanningPositionTab             = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM pl_poste_tab");
        $countAfterPosition                        = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM postes");
        $countAfterSkill                           = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM activites");
        $countAfterRecurringAbsence                = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences_recurrentes");

        $this->assertSame(5, $countAfterAbsenceInfo                   , '5 should be founded');
        $this->assertSame(5, $countAfterAdminInfo                     , '5 should be founded');
        $this->assertSame(5, $countAfterCallForHelp                   , '5 should be founded');
        $this->assertSame(5, $countAfterOverTime                      , '5 should be founded');
        $this->assertSame(5, $countAfterDetached                      , '5 should be founded');
        $this->assertSame(5, $countAfterHoliday                       , '5 should be founded');
        $this->assertSame(5, $countAfterHolidayInfo                   , '5 should be founded');
        $this->assertSame(3, $countAfterSaturdayWorkingHours          , '3 should be founded');
        $this->assertSame(5, $countAfterIPBlocker                     , '5 should be founded');
        $this->assertSame(34, $countAfterLog                          , '34 should be founded');
        $this->assertSame(5, $countAfterPlanningNote                  , '5 should be founded');
        $this->assertSame(5, $countAfterPlanningNotification          , '5 should be founded');
        $this->assertSame(5, $countAfterPlanningPosition              , '5 should be founded');
        $this->assertSame(5, $countAfterPlanningPositionLock          , '5 should be founded');
        $this->assertSame(5, $countAfterPlanningPositionTabAffectation, '5 should be founded');
        $this->assertSame(3, $countAfterPublicHoliday                 , '3 should be founded');//Purge datas older than 3 years; keep the last 3 years of data.
        $this->assertSame(5, $countAfterWorkingHour                   , '5 should be founded');
        $this->assertSame(5, $countAfterAbsence                       , '5 should be founded');
        $this->assertSame(3, $countAfterAgent                         , '7 should be founded');
        $this->assertSame(6, $countAfterPlanningPositionTab           , '6 should be founded');
        $this->assertSame(37, $countAfterPosition                      , '37 should be founded');
        $this->assertSame(17, $countAfterSkill                         , '17 should be founded');
        $this->assertSame(6, $countAfterRecurringAbsence              , '6 should be founded');
        
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:purge:data');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'delay' => '2',
        ]);
        $commandTester->assertCommandIsSuccessful();

    }
}
