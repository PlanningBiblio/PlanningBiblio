<?php

use App\Model\Agent;
use App\Model\Holiday;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayControllerDaysTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['config']['PlanningHebdo-Agents'] = 1;
        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 0;
        $GLOBALS['config']['Conges-Recuperations'] = 1;

        $this->deleteWorkingHours();
        $this->builder->delete(Agent::class);
        $this->builder->delete(Holiday::class);

        $admin = $this->builder->build(Agent::class, array('login' => 'kboivin'));
        $this->logInAgent($admin, array(100));
    }

    public function testHolidayOneAgentManyDays()
    {
        $entityManager = $this->entityManager;

        $jdevoe = $this->builder->build(Agent::class,
            array(
                'login' => 'jdevoe',
                'conges_credit' => 67,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 0
            )
        );

        $this->addWorkingHours($jdevoe, array('08:30:00', '12:30:00', '13:30:00', '17:30:00'));

        $data = $this->getHolidayData(array(
            'perso_id' => $jdevoe->getId(),
            'debut' => '24/01/2022',
            'fin' => '28/01/2022'
        ));

        $this->client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->getId())
        );

        $start = $holiday->getStart()->format('Y-m-d H:i:s');
        $end = $holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-28 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->getHours(), 35.00, 'end_halfday is empty');
        $this->assertEquals($holiday->getPreviousCredit(), 67, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->getActualCredit(), 32, 'New holiday credit is 168');
        $this->assertEquals($holiday->getPreviousCompTime(), 0, 'previous com time credit is 35');
        $this->assertEquals($holiday->getActualCompTime(), 0, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->getHolidayCredit(), 32, 'credit has updated');
        $this->assertEquals($jdevoe->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->getCompTime(), 0, "comp time didn't change");
    }

    public function testHolidayManyAgentManyDays()
    {
        $entityManager = $this->entityManager;

        $jdevoe = $this->builder->build(Agent::class,
            array(
                'login' => 'jdevoe',
                'conges_credit' => 67,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 0
            )
        );

        $adenis = $this->builder->build(Agent::class,
            array(
                'login' => 'adenis',
                'conges_credit' => 110,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 10
            )
        );

        $this->addWorkingHours($jdevoe, array('08:30:00', '12:30:00', '13:30:00', '17:30:00'));
        $this->addWorkingHours($adenis, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array(
            'perso_ids' => array($jdevoe->getId(), $adenis->getId()),
            'debut' => '24/01/2022',
            'fin' => '26/01/2022'
        ));

        $this->client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->getId())
        );

        $adenis_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $adenis->getId())
        );

        $start = $jdevoe_holiday->getStart()->format('Y-m-d H:i:s');
        $end = $jdevoe_holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($jdevoe_holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($jdevoe_holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($jdevoe_holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->getHours(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($jdevoe_holiday->getPreviousCredit(), 67, 'Previous holiday credit is 175');
        $this->assertEquals($jdevoe_holiday->getActualCredit(), 46, 'New holiday credit is 168');
        $this->assertEquals($jdevoe_holiday->getPreviousCompTime(), 0, 'previous com time credit is 35');
        $this->assertEquals($jdevoe_holiday->getActualCompTime(), 0, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->getHolidayCredit(), 46, 'credit has updated');
        $this->assertEquals($jdevoe->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->getCompTime(), 0, "comp time didn't change");

        $start = $adenis_holiday->getStart()->format('Y-m-d H:i:s');
        $end = $adenis_holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($adenis_holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($adenis_holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($adenis_holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->getHours(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($adenis_holiday->getPreviousCredit(), 110, 'Previous holiday credit is 175');
        $this->assertEquals($adenis_holiday->getActualCredit(), 89, 'New holiday credit is 168');
        $this->assertEquals($adenis_holiday->getPreviousCompTime(), 10, 'previous com time credit is 35');
        $this->assertEquals($adenis_holiday->getActualCompTime(), 10, 'New com time credit is unchanged');

        $entityManager->refresh($adenis);
        $this->assertEquals($adenis->getHolidayCredit(), 89, 'credit has updated');
        $this->assertEquals($adenis->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($adenis->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($adenis->getCompTime(), 10, "comp time didn't change");
    }

    public function testHolidayManyAgentManyDaysWithRemainingHoliday()
    {
        $entityManager = $this->entityManager;

        $jdevoe = $this->builder->build(Agent::class,
            array(
                'login' => 'jdevoe',
                'conges_credit' => 11,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 0
            )
        );

        $adenis = $this->builder->build(Agent::class,
            array(
                'login' => 'adenis',
                'conges_credit' => 175,
                'conges_reliquat' => 10,
                'conges_anticipation' => 0,
                'comp_time' => 10
            )
        );

        $this->addWorkingHours($jdevoe, array('08:30:00', '12:30:00', '13:30:00', '17:30:00'));
        $this->addWorkingHours($adenis, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array(
            'perso_ids' => array($jdevoe->getId(), $adenis->getId()),
            'debut' => '24/01/2022',
            'fin' => '26/01/2022'
        ));

        $this->client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->getId())
        );

        $adenis_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $adenis->getId())
        );

        $start = $jdevoe_holiday->getStart()->format('Y-m-d H:i:s');
        $end = $jdevoe_holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($jdevoe_holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($jdevoe_holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($jdevoe_holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->getHours(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($jdevoe_holiday->getPreviousCredit(), 11, 'Previous holiday credit is 11');
        $this->assertEquals($jdevoe_holiday->getActualCredit(), 0, 'New holiday credit is 0');
        $this->assertEquals($jdevoe_holiday->anticipation_prec(), 0, 'Previous remaining is 0');
        $this->assertEquals($jdevoe_holiday->anticipation_actuel(), 10, 'New remaining is 10');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->getHolidayCredit(), 0, 'credit has updated');
        $this->assertEquals($jdevoe->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->getAnticipation(), 10, "anticipation didn't change");
        $this->assertEquals($jdevoe->getCompTime(), 0, "comp time didn't change");

        $start = $adenis_holiday->getStart()->format('Y-m-d H:i:s');
        $end = $adenis_holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($adenis_holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($adenis_holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($adenis_holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->getHours(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($adenis_holiday->getPreviousCredit(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($adenis_holiday->getActualCredit(), 164, 'New holiday credit is 164');
        $this->assertEquals($adenis_holiday->reliquat_prec(), 10, 'Prev remaining is 10');
        $this->assertEquals($adenis_holiday->reliquat_actuel(), 0, 'New remaining is 0');

        $entityManager->refresh($adenis);
        $this->assertEquals($adenis->getHolidayCredit(), 164, 'credit has updated');
        $this->assertEquals($adenis->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($adenis->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($adenis->getCompTime(), 10, "comp time didn't change");
    }

    private function deleteWorkingHours()
    {
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
    }

    private function getHolidayData($replace = array())
    {
        $data = array(
            'CSRFToken' => '00000',
            'confirm' => 'confirm',
            'allday' => 'on',
            'debut' => '24/01/2022',
            'hre_debut' => '',
            'fin' => '24/01/2022',
            'hre_fin' => '',
            'hours_per_day' => '',
            'debit' => 'credit',
            'commentaires' => '',
            'valide' => '1',
            'refus' => '',
            'valide_init' => '1',
            'valide_n1' => '1',
            'validation' => '2022-01-12 13:34:07',
            'validation_n1' => '2022-01-12 13:34:07',
        );

        foreach ($replace as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    private function addWorkingHours($agent, $times)
    {
        $workinghours = array(
            0 => array('0' => $times[0], '1' => $times[1], '2' => $times[2], '3' => $times[3]),
            1 => array('0' => $times[0], '1' => $times[1], '2' => $times[2], '3' => $times[3]),
            2 => array('0' => $times[0], '1' => $times[1], '2' => $times[2], '3' => $times[3]),
            3 => array('0' => $times[0], '1' => $times[1], '2' => $times[2], '3' => $times[3]),
            4 => array('0' => $times[0], '1' => $times[1], '2' => $times[2], '3' => $times[3]),
            5 => array('0' => $times[0], '1' => $times[1], '2' => $times[2], '3' => $times[3]),
        );

        $db = new \db();
        $db->CSRFToken = '00000';
        $id = $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2021-01-01',
                'fin' => '2090-12-31',
                'temps' => json_encode($workinghours),
                'valide_n1' => 1,
                'valide' => 1,
                'nb_semaine' => 1
            )
        );
    }
}
