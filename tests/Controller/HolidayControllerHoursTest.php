<?php

use App\Entity\Agent;
use App\Entity\Holiday;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayControllerHoursTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['config']['PlanningHebdo-Agents'] = 1;
        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Mode'] = 'heures';
        $GLOBALS['config']['Conges-demi-journees'] = 0;
        $GLOBALS['config']['Conges-Recuperations'] = 0;

        $this->deleteWorkingHours();
        $this->builder->delete(Agent::class);
        $this->builder->delete(Holiday::class);

        $admin = $this->builder->build(Agent::class, array('login' => 'kboivin'));
        $this->logInAgent($admin, array(100));
    }

    public function testHolidayOneAgentAllDayNonValidated(): void
    {
        $entityManager = $this->entityManager;

        $jdevoe = $this->builder->build(Agent::class,
            array(
                'login' => 'jdevoe',
                'conges_credit' => 175,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 35
            )
        );
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array(
            'perso_id' => $jdevoe->getId(),
            'valide' => 0,
            'valide_n1' => 0
        ));

        $this->client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->getId())
        );

        $start = $holiday->getStart()->format('Y-m-d H:i:s');
        $end = $holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->getHours(), 7.00, 'end_halfday is empty');
        $this->assertEquals($holiday->getPreviousCredit(), null, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->getActualCredit(), null, 'New holiday credit is 168');
        $this->assertEquals($holiday->getPreviousCompTime(), null, 'previous com time credit is 35');
        $this->assertEquals($holiday->getActualCompTime(), null, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->getHolidayCredit(), 175, 'credit has not been updated yet');
        $this->assertEquals($jdevoe->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->getCompTime(), 35, "comp time didn't change");
    }

    public function testHolidayOneAgentAllDay(): void
    {
        $entityManager = $this->entityManager;

        $jdevoe = $this->builder->build(Agent::class,
            array(
                'login' => 'jdevoe',
                'conges_credit' => 175,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 35
            )
        );
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array('perso_id' => $jdevoe->getId()));

        $this->client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->getId())
        );

        $start = $holiday->getStart()->format('Y-m-d H:i:s');
        $end = $holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->getHours(), 7.00, 'end_halfday is empty');
        $this->assertEquals($holiday->getPreviousCredit(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->getActualCredit(), 168, 'New holiday credit is 168');
        $this->assertEquals($holiday->getPreviousCompTime(), 35, 'previous com time credit is 35');
        $this->assertEquals($holiday->getActualCompTime(), 35, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->getHolidayCredit(), 168, 'credit has been updated');
        $this->assertEquals($jdevoe->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->getCompTime(), 35, "comp time didn't change");
    }

    public function testHolidayOneAgentOneHour(): void
    {
        $entityManager = $this->entityManager;

        $jdevoe = $this->builder->build(Agent::class,
            array(
                'login' => 'jdevoe',
                'conges_credit' => 175,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 35
            )
        );
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array(
            'perso_id' => $jdevoe->getId(),
            'hre_debut' => '09:00:00',
            'hre_fin' => '10:00:00',
            'allday' => '',
        ));

        $this->client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->getId())
        );

        $start = $holiday->getStart()->format('Y-m-d H:i:s');
        $end = $holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 09:00:00', 'Holiday starts at 09:00:00');
        $this->assertEquals($end, '2022-01-24 10:00:00', 'Holiday ends at 10:00:00');
        $this->assertEquals($holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->getDebit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->getHours(), 1.00, 'end_halfday is empty');
        $this->assertEquals($holiday->getPreviousCredit(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->getActualCredit(), 174, 'New holiday credit is 174');
        $this->assertEquals($holiday->getPreviousCompTime(), 35, 'previous com time credit is 35');
        $this->assertEquals($holiday->getActualCompTime(), 35, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->getHolidayCredit(), 174, 'credit has been updated');
        $this->assertEquals($jdevoe->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->getCompTime(), 35, "comp time didn't change");

    }

    public function testHolidayOneAgentOneDayOnCompTime(): void
    {
        $entityManager = $this->entityManager;

        $jdevoe = $this->builder->build(Agent::class,
            array(
                'login' => 'jdevoe',
                'conges_credit' => 175,
                'conges_reliquat' => 0,
                'conges_anticipation' => 0,
                'comp_time' => 35
            )
        );
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array(
            'perso_id' => $jdevoe->getId(),
            'debit' => 'recuperation',
        ));

        $this->client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->getId())
        );

        $start = $holiday->getStart()->format('Y-m-d H:i:s');
        $end = $holiday->getEnd()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->getHalfDay(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->getHalfDayStart(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->getHalfDayEnd(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->getDebit(), 'recuperation', 'Debit on comp-time account');
        $this->assertEquals($holiday->getHours(), 7.00, 'end_halfday is empty');
        $this->assertEquals($holiday->getPreviousCredit(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->getActualCredit(), 175, 'New holiday credit is 174');
        $this->assertEquals($holiday->getPreviousCompTime(), 35, 'previous com time credit is 35');
        $this->assertEquals($holiday->getActualCompTime(), 28, 'New com time credit updated');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->getHolidayCredit(), 175, "credit didn't change");
        $this->assertEquals($jdevoe->getRemainder(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->getAnticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->getCompTime(), 28, "comp time was updated");

    }

    private function deleteWorkingHours(): void
    {
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
    }

    /**
     * @return mixed[]
     */
    private function getHolidayData(array $replace = array()): array
    {
        $data = array(
            'CSRFToken' => '00000',
            'confirm' => 'confirm',
            'perso_id' => '1',
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

    private function addWorkingHours($agent, array $times): void
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
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->getId(),
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
