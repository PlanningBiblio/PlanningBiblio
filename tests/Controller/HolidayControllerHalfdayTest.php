<?php

use App\Model\Agent;
use App\Model\Holiday;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayControllerHalfdayTest extends PLBWebTestCase
{
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['config']['PlanningHebdo-Agents'] = 1;
        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Mode'] = 1;
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $_SESSION['oups']['CSRFToken'] = '00000';

        $this->builder = new FixtureBuilder();
        $this->deleteWorkingHours();
        $this->builder->delete(Agent::class);
        $this->builder->delete(Holiday::class);

        $admin = $this->builder->build(Agent::class, array('login' => 'kboivin'));
        $this->logInAgent($admin, array(100));
    }

    public function testHolidayOneAgentOnFullDay()
    {
        global $entityManager;

        $client = static::createClient();

        $jdevoe = $this->builder->build(Agent::class, array('login' => 'jdevoe'));

        $data = $this->getHolidayData(array('perso_ids' => array($jdevoe->id())));

        $client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $jdevoe_holiday->debut()->format('Y-m-d H:i:s');
        $this->assertEquals($start, '2022-01-24 00:00:00', 'Fullday holiday starts at 00:00:00');

        $end = $jdevoe_holiday->fin()->format('Y-m-d H:i:s');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Fullday holiday ends at 23:59:59');

        $this->assertEquals($jdevoe_holiday->halfday(), 1, 'Halfday is enabled');
        $this->assertEquals($jdevoe_holiday->start_halfday(), 'fullday', 'start_halfday is fullday');
        $this->assertEquals($jdevoe_holiday->end_halfday(), 'fullday', 'end_halfday is fullday');
    }

    public function testHolidayOneAgentOnMorning()
    {
        global $entityManager;

        $client = static::createClient();

        $jdevoe = $this->builder->build(Agent::class, array('login' => 'jdevoe'));
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array('perso_ids' => array($jdevoe->id()), 'start_halfday' => 'morning', 'end_halfday' => 'morning'));

        $client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $jdevoe_holiday->debut()->format('Y-m-d H:i:s');
        $this->assertEquals($start, '2022-01-24 00:00:00', 'Morning holiday starts at 00:00:00');

        $end = $jdevoe_holiday->fin()->format('Y-m-d H:i:s');
        $this->assertEquals($end, '2022-01-24 12:30:00', 'Morning holiday ends at 12:30:00');

        $this->assertEquals($jdevoe_holiday->halfday(), 1, 'Halfday is enabled');
        $this->assertEquals($jdevoe_holiday->start_halfday(), 'morning', 'start_halfday is morning');
        $this->assertEquals($jdevoe_holiday->end_halfday(), 'morning', 'end_halfday is morning');
    }

    public function testHolidayOneAgentOnAfternoon()
    {
        global $entityManager;

        $client = static::createClient();

        $jdevoe = $this->builder->build(Agent::class, array('login' => 'jdevoe'));
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array('perso_ids' => array($jdevoe->id()), 'start_halfday' => 'afternoon', 'end_halfday' => 'afternoon'));

        $client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $jdevoe_holiday->debut()->format('Y-m-d H:i:s');
        $this->assertEquals($start, '2022-01-24 13:30:00', 'Afternoon holiday starts at 13:30:00');

        $end = $jdevoe_holiday->fin()->format('Y-m-d H:i:s');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Morning holiday ends at 23:59:59');

        $this->assertEquals($jdevoe_holiday->halfday(), 1, 'Halfday is enabled');
        $this->assertEquals($jdevoe_holiday->start_halfday(), 'afternoon', 'start_halfday is afternoon');
        $this->assertEquals($jdevoe_holiday->end_halfday(), 'afternoon', 'end_halfday is afternoon');
    }

    public function testHolidayManyAgentsOnAfternoon()
    {
        global $entityManager;

        $client = static::createClient();

        $jdevoe = $this->builder->build(Agent::class, array('login' => 'jdevoe'));
        $abreton = $this->builder->build(Agent::class, array('login' => 'abreton'));
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));
        $this->addWorkingHours($abreton, array('09:00:00', '12:00:00', '13:00:00', '17:00:00'));

        $data = $this->getHolidayData(array('perso_ids' => array($jdevoe->id(), $abreton->id()), 'start_halfday' => 'afternoon', 'end_halfday' => 'afternoon'));

        $client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $jdevoe_holiday->debut()->format('Y-m-d H:i:s');
        $this->assertEquals($start, '2022-01-24 13:30:00', 'Afternoon holiday starts at 13:30:00');

        $end = $jdevoe_holiday->fin()->format('Y-m-d H:i:s');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Morning holiday ends at 23:59:59');

        $this->assertEquals($jdevoe_holiday->halfday(), 1, 'Halfday is enabled');
        $this->assertEquals($jdevoe_holiday->start_halfday(), 'afternoon', 'start_halfday is afternoon');
        $this->assertEquals($jdevoe_holiday->end_halfday(), 'afternoon', 'end_halfday is afternoon');

        $abreton_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $abreton->id())
        );

        $start = $abreton_holiday->debut()->format('Y-m-d H:i:s');
        $this->assertEquals($start, '2022-01-24 13:00:00', 'Afternoon holiday starts at 13:00:00');

        $end = $abreton_holiday->fin()->format('Y-m-d H:i:s');
        $this->assertEquals($end, '2022-01-24 23:59:59', 'Morning holiday ends at 23:59:59');

        $this->assertEquals($abreton_holiday->halfday(), 1, 'Halfday is enabled');
        $this->assertEquals($abreton_holiday->start_halfday(), 'afternoon', 'start_halfday is afternoon');
        $this->assertEquals($abreton_holiday->end_halfday(), 'afternoon', 'end_halfday is afternoon');
    }

    public function testHolidayOneAgentSeveralDays()
    {
        global $entityManager;

        $client = static::createClient();

        $jdevoe = $this->builder->build(Agent::class, array('login' => 'jdevoe'));
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $data = $this->getHolidayData(array('perso_ids' => array($jdevoe->id()),
            'start_halfday' => 'afternoon',
            'end_halfday' => 'morning',
            'debut' => '24/01/2022',
            'fin' => '28/01/2022',
        ));

        $client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $jdevoe_holiday->debut()->format('Y-m-d H:i:s');
        $this->assertEquals($start, '2022-01-24 13:30:00', 'Afternoon holiday starts at 13:30:00');

        $end = $jdevoe_holiday->fin()->format('Y-m-d H:i:s');
        $this->assertEquals($end, '2022-01-28 12:30:00', 'Morning holiday ends at 23:59:59');

        $this->assertEquals($jdevoe_holiday->halfday(), 1, 'Halfday is enabled');
        $this->assertEquals($jdevoe_holiday->start_halfday(), 'afternoon', 'start_halfday is afternoon');
        $this->assertEquals($jdevoe_holiday->end_halfday(), 'morning', 'end_halfday is afternoon');
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
            'perso_ids' => array(1),
            'halfday' => 'on',
            'debut' => '24/01/2022',
            'start_halfday' => 'fullday',
            'hre_debut' => '',
            'fin' => '24/01/2022',
            'end_halfday' => 'fullday',
            'hre_fin' => '',
            'debit' => 'credit',
            'commentaires' => '',
            'valide' => '0',
            'refus' => '',
            'valide_init' => '0',
        );

        foreach ($replace as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }
}
