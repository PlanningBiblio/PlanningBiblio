<?php

use App\Model\Agent;
use App\Model\Holiday;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayControllerHoursTest extends PLBWebTestCase
{
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['config']['PlanningHebdo-Agents'] = 1;
        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Mode'] = 'heures';
        $GLOBALS['config']['Conges-demi-journees'] = 0;
        $GLOBALS['config']['Conges-Recuperations'] = 0;
        $_SESSION['oups']['CSRFToken'] = '00000';

        $this->builder = new FixtureBuilder();
        $this->deleteWorkingHours();
        $this->builder->delete(Agent::class);
        $this->builder->delete(Holiday::class);

        $admin = $this->builder->build(Agent::class, array('login' => 'kboivin'));
        $this->logInAgent($admin, array(100));
    }

    public function testHolidayOneAgentAllDayNonValidated()
    {
        global $entityManager;

        $client = static::createClient();

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
            'perso_id' => $jdevoe->id(),
            'valide' => 0,
            'valide_n1' => 0
        ));

        $client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $holiday->debut()->format('Y-m-d H:i:s');
        $end = $holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->heures(), 7.00, 'end_halfday is empty');
        $this->assertEquals($holiday->solde_prec(), null, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->solde_actuel(), null, 'New holiday credit is 168');
        $this->assertEquals($holiday->recup_prec(), null, 'previous com time credit is 35');
        $this->assertEquals($holiday->recup_actuel(), null, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->conges_credit(), 175, 'credit has not been updated yet');
        $this->assertEquals($jdevoe->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->comp_time(), 35, "comp time didn't change");
    }

    public function testHolidayOneAgentAllDay()
    {
        global $entityManager;

        $client = static::createClient();

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

        $data = $this->getHolidayData(array('perso_id' => $jdevoe->id()));

        $client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $holiday->debut()->format('Y-m-d H:i:s');
        $end = $holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->heures(), 7.00, 'end_halfday is empty');
        $this->assertEquals($holiday->solde_prec(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->solde_actuel(), 168, 'New holiday credit is 168');
        $this->assertEquals($holiday->recup_prec(), 35, 'previous com time credit is 35');
        $this->assertEquals($holiday->recup_actuel(), 35, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->conges_credit(), 168, 'credit has been updated');
        $this->assertEquals($jdevoe->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->comp_time(), 35, "comp time didn't change");
    }

    public function testHolidayOneAgentOneHour()
    {
        global $entityManager;

        $client = static::createClient();

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
            'perso_id' => $jdevoe->id(),
            'hre_debut' => '09:00:00',
            'hre_fin' => '10:00:00',
        ));

        $client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $holiday->debut()->format('Y-m-d H:i:s');
        $end = $holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 09:00:00', 'Holiday starts at 09:00:00');
        $this->assertEquals($end, '2022-01-24 10:00:00', 'Holiday ends at 10:00:00');
        $this->assertEquals($holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->heures(), 1.00, 'end_halfday is empty');
        $this->assertEquals($holiday->solde_prec(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->solde_actuel(), 174, 'New holiday credit is 174');
        $this->assertEquals($holiday->recup_prec(), 35, 'previous com time credit is 35');
        $this->assertEquals($holiday->recup_actuel(), 35, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->conges_credit(), 174, 'credit has been updated');
        $this->assertEquals($jdevoe->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->comp_time(), 35, "comp time didn't change");

    }

    public function testHolidayOneAgentOneDayOnCompTime()
    {
        global $entityManager;

        $client = static::createClient();

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
            'perso_id' => $jdevoe->id(),
            'debit' => 'recuperation',
        ));

        $client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $holiday->debut()->format('Y-m-d H:i:s');
        $end = $holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->debit(), 'recuperation', 'Debit on comp-time account');
        $this->assertEquals($holiday->heures(), 7.00, 'end_halfday is empty');
        $this->assertEquals($holiday->solde_prec(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->solde_actuel(), 175, 'New holiday credit is 174');
        $this->assertEquals($holiday->recup_prec(), 35, 'previous com time credit is 35');
        $this->assertEquals($holiday->recup_actuel(), 28, 'New com time credit updated');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->conges_credit(), 175, "credit didn't change");
        $this->assertEquals($jdevoe->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->comp_time(), 28, "comp time was updated");

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