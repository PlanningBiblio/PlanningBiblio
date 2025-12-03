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
    public function testSomething(): void
    {
        $this->restore();

	    $date = new DateTime();
        $date->modify('-5 years');
	    for ($i = 0; $i < 11 ; $i ++) {
            $this->builder->build(AbsenceInfo::class,                    ['fin' => $date]);
            $this->builder->build(AdminInfo::class,                      ['fin' => $date]);
            $this->builder->build(CallForHelp::class,                    ['timestamp' => $date, 'date' =>$date->format('Y-m-d'), 'debut'=>$date->format('H:m:s'), 'fin'=>$date->format('H:m:s')]);
            $this->builder->build(OverTime::class,                       ['date' => $date, 'perso_id'=> 9999]);
            $this->builder->build(Detached::class,                       ['date' => $date, 'perso_id'=> 9999]);
            $this->builder->build(Holiday::class,                        ['fin' => $date, 'perso_id'=> 9999]);
            $this->builder->build(HolidayInfo::class,                    ['fin' => $date]);
            $this->builder->build(IPBlocker::class,                      ['timestamp' => $date, 'status' => 'success']);
            $this->builder->build(Log::class,                            ['timestamp' => $date]);
            $this->builder->build(PlanningNote::class,                   ['date' => $date, 'perso_id'=> 9999]);
            $this->builder->build(PlanningNotification::class,           ['date' => $date]);
            $this->builder->build(PlanningPosition::class,               ['date' => $date, 'debut'=>$date, 'fin'=>$date, 'perso_id'=> 9999]);
            $this->builder->build(PlanningPositionLock::class,           ['date' => $date, 'perso'=> 99, 'perso2'=> 99]);
            $this->builder->build(PlanningPositionTabAffectation::class, ['date' => $date, 'tableau' => 999]);
            $this->builder->build(PublicHoliday::class,                  ['jour' => $date, 'annee'=>'2025']);
            $this->builder->build(WorkingHour::class,                    ['fin' => $date, 'perso_id'=> 9999]);
            $this->builder->build(Absence::class,                    ['fin' => $date, 'groupe' => 1, 'perso_id'=> 9999]);
            $this->builder->build(Agent::class,                    ['supprime' => 2]);
            $this->builder->build(PlanningPositionTab::class,                    ['supprime' => $date]);
            $this->builder->build(Position::class,                    ['supprime' => $date]);
            $this->builder->build(Skill::class,                    ['supprime' => $date]);
            $this->builder->build(RecurringAbsence::class,                    ['timestamp' => $date, 'perso_id'=> 9999, 'end' => 1]);
            $this->builder->build(SaturdayWorkingHours::class,           ['semaine' => $date, 'perso_id'=> 9999]);

            $date->modify('+6 months');
	    }

        $this->builder->build(Agent::class,                    ['supprime' => 1]);
        $this->builder->build(Agent::class,                    ['supprime' => 0]);

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
        $this->assertSame(11, $countBeforeAbsenceInfo                   , '11 should be founded');
        $this->assertSame(11, $countBeforeAdminInfo                     , '11 should be founded');
        $this->assertSame(11, $countBeforeCallForHelp                   , '11 should be founded');
        $this->assertSame(11, $countBeforeOverTime                      , '11 should be founded');
        $this->assertSame(11, $countBeforeDetached                      , '11 should be founded');
        $this->assertSame(11, $countBeforeHoliday                       , '11 should be founded');
        $this->assertSame(11, $countBeforeHolidayInfo                   , '11 should be founded');
        $this->assertSame(11, $countBeforeSaturdayWorkingHours          , '11 should be founded');
        $this->assertSame(11, $countBeforeIPBlocker                     , '11 should be founded');
        $this->assertSame(11, $countBeforeLog                           , '11 should be founded');
        $this->assertSame(11, $countBeforePlanningNote                  , '11 should be founded');
        $this->assertSame(11, $countBeforePlanningNotification          , '11 should be founded');
        $this->assertSame(11, $countBeforePlanningPosition              , '11 should be founded');
        $this->assertSame(11, $countBeforePlanningPositionLock          , '11 should be founded');
        $this->assertSame(11, $countBeforePlanningPositionTabAffectation, '11 should be founded');
        $this->assertSame(11, $countBeforePublicHoliday                 , '11 should be founded');
        $this->assertSame(11, $countBeforeWorkingHour                   , '11 should be founded');
        $this->assertSame(11, $countBeforeAbsence                       , '11 should be founded');
        $this->assertSame(15, $countBeforeAgent                         , '15 should be founded');//Administrateur, Tout le monde and 2 whose supprime != 2
        $this->assertSame(12, $countBeforePlanningPositionTab           , '12 should be founded');
        $this->assertSame(43, $countBeforePosition                      , '43 position should be founded');
        $this->assertSame(23, $countBeforeSkill                         , '23 should be founded');
        $this->assertSame(11, $countBeforeRecurringAbsence              , '11 should be founded');


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
        $this->assertSame(6, $countAfterAbsenceInfo                   , '6 should be founded');
        $this->assertSame(6, $countAfterAdminInfo                     , '6 should be founded');
        $this->assertSame(6, $countAfterCallForHelp                   , '6 should be founded');
        $this->assertSame(6, $countAfterOverTime                      , '6 should be founded');
        $this->assertSame(6, $countAfterDetached                      , '6 should be founded');
        $this->assertSame(6, $countAfterHoliday                       , '6 should be founded');
        $this->assertSame(6, $countAfterHolidayInfo                   , '6 should be founded');
        $this->assertSame(6, $countAfterSaturdayWorkingHours          , '6 should be founded');
        $this->assertSame(6, $countAfterIPBlocker                     , '6 should be founded');
        $this->assertSame(35, $countAfterLog                          , '35 should be founded');//4 plus 29 logs from DataPurger
        $this->assertSame(6, $countAfterPlanningNote                  , '6 should be founded');
        $this->assertSame(6, $countAfterPlanningNotification          , '6 should be founded');
        $this->assertSame(6, $countAfterPlanningPosition              , '6 should be founded');
        $this->assertSame(6, $countAfterPlanningPositionLock          , '6 should be founded');
        $this->assertSame(6, $countAfterPlanningPositionTabAffectation, '6 should be founded');
        $this->assertSame(8, $countAfterPublicHoliday                 , '8 should be founded');//Purge datas older than 3 years; keep the last 3 years of data.
        $this->assertSame(6, $countAfterWorkingHour                   , '6 should be founded');
        $this->assertSame(6, $countAfterAbsence                       , '6 should be founded');
        $this->assertSame(4, $countAfterAgent                         , '4 should be founded');
        $this->assertSame(7, $countAfterPlanningPositionTab           , '7 should be founded');
        $this->assertSame(38, $countAfterPosition                      , '38 should be founded');
        $this->assertSame(18, $countAfterSkill                         , '18 should be founded');
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
