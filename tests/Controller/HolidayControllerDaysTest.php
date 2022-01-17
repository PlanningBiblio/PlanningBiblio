<?php

use App\Model\Agent;
use App\Model\Holiday;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayControllerDaysTest extends PLBWebTestCase
{
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['config']['PlanningHebdo-Agents'] = 1;
        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 0;
        $GLOBALS['config']['Conges-Recuperations'] = 1;
        $_SESSION['oups']['CSRFToken'] = '00000';

        $this->builder = new FixtureBuilder();
        $this->deleteWorkingHours();
        $this->builder->delete(Agent::class);
        $this->builder->delete(Holiday::class);

        $admin = $this->builder->build(Agent::class, array('login' => 'kboivin'));
        $this->logInAgent($admin, array(100));
    }

    public function testHolidayOneAgentManyDays()
    {
        global $entityManager;

        $client = static::createClient();

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
            'perso_id' => $jdevoe->id(),
            'debut' => '24/01/2022',
            'fin' => '28/01/2022'
        ));

        $client->request('POST', '/holiday', $data);

        $holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $start = $holiday->debut()->format('Y-m-d H:i:s');
        $end = $holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-28 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($holiday->heures(), 35.00, 'end_halfday is empty');
        $this->assertEquals($holiday->solde_prec(), 67, 'Previous holiday credit is 175');
        $this->assertEquals($holiday->solde_actuel(), 32, 'New holiday credit is 168');
        $this->assertEquals($holiday->recup_prec(), 0, 'previous com time credit is 35');
        $this->assertEquals($holiday->recup_actuel(), 0, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->conges_credit(), 32, 'credit has updated');
        $this->assertEquals($jdevoe->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->comp_time(), 0, "comp time didn't change");
    }

    public function testHolidayManyAgentManyDays()
    {
        global $entityManager;

        $client = static::createClient();

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
            'perso_ids' => array($jdevoe->id(), $adenis->id()),
            'debut' => '24/01/2022',
            'fin' => '26/01/2022'
        ));

        $client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $adenis_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $adenis->id())
        );

        $start = $jdevoe_holiday->debut()->format('Y-m-d H:i:s');
        $end = $jdevoe_holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($jdevoe_holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($jdevoe_holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($jdevoe_holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->heures(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($jdevoe_holiday->solde_prec(), 67, 'Previous holiday credit is 175');
        $this->assertEquals($jdevoe_holiday->solde_actuel(), 46, 'New holiday credit is 168');
        $this->assertEquals($jdevoe_holiday->recup_prec(), 0, 'previous com time credit is 35');
        $this->assertEquals($jdevoe_holiday->recup_actuel(), 0, 'New com time credit is unchanged');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->conges_credit(), 46, 'credit has updated');
        $this->assertEquals($jdevoe->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($jdevoe->comp_time(), 0, "comp time didn't change");

        $start = $adenis_holiday->debut()->format('Y-m-d H:i:s');
        $end = $adenis_holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($adenis_holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($adenis_holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($adenis_holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->heures(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($adenis_holiday->solde_prec(), 110, 'Previous holiday credit is 175');
        $this->assertEquals($adenis_holiday->solde_actuel(), 89, 'New holiday credit is 168');
        $this->assertEquals($adenis_holiday->recup_prec(), 10, 'previous com time credit is 35');
        $this->assertEquals($adenis_holiday->recup_actuel(), 10, 'New com time credit is unchanged');

        $entityManager->refresh($adenis);
        $this->assertEquals($adenis->conges_credit(), 89, 'credit has updated');
        $this->assertEquals($adenis->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($adenis->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($adenis->comp_time(), 10, "comp time didn't change");
    }

    public function testHolidayManyAgentManyDaysWithRemainingHoliday()
    {
        global $entityManager;

        $client = static::createClient();

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
            'perso_ids' => array($jdevoe->id(), $adenis->id()),
            'debut' => '24/01/2022',
            'fin' => '26/01/2022'
        ));

        $client->request('POST', '/holiday', $data);

        $jdevoe_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $jdevoe->id())
        );

        $adenis_holiday = $entityManager->getRepository(Holiday::class)->findOneBy(
            array('perso_id' => $adenis->id())
        );

        $start = $jdevoe_holiday->debut()->format('Y-m-d H:i:s');
        $end = $jdevoe_holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($jdevoe_holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($jdevoe_holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($jdevoe_holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($jdevoe_holiday->heures(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($jdevoe_holiday->solde_prec(), 11, 'Previous holiday credit is 11');
        $this->assertEquals($jdevoe_holiday->solde_actuel(), 0, 'New holiday credit is 0');
        $this->assertEquals($jdevoe_holiday->anticipation_prec(), 0, 'Previous remaining is 0');
        $this->assertEquals($jdevoe_holiday->anticipation_actuel(), 10, 'New remaining is 10');

        $entityManager->refresh($jdevoe);
        $this->assertEquals($jdevoe->conges_credit(), 0, 'credit has updated');
        $this->assertEquals($jdevoe->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($jdevoe->conges_anticipation(), 10, "anticipation didn't change");
        $this->assertEquals($jdevoe->comp_time(), 0, "comp time didn't change");

        $start = $adenis_holiday->debut()->format('Y-m-d H:i:s');
        $end = $adenis_holiday->fin()->format('Y-m-d H:i:s');

        $this->assertEquals($start, '2022-01-24 00:00:00', 'Holiday starts at 00:00:00');
        $this->assertEquals($end, '2022-01-26 23:59:59', 'Holiday ends at 23:59:59');
        $this->assertEquals($adenis_holiday->halfday(), 0, 'Holiday is not on halfday');
        $this->assertEquals($adenis_holiday->start_halfday(), '', 'start_halfday is empty');
        $this->assertEquals($adenis_holiday->end_halfday(), '', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->debit(), 'credit', 'end_halfday is empty');
        $this->assertEquals($adenis_holiday->heures(), 21.00, 'Hours is 21 (3 days)');
        $this->assertEquals($adenis_holiday->solde_prec(), 175, 'Previous holiday credit is 175');
        $this->assertEquals($adenis_holiday->solde_actuel(), 164, 'New holiday credit is 164');
        $this->assertEquals($adenis_holiday->reliquat_prec(), 10, 'Prev remaining is 10');
        $this->assertEquals($adenis_holiday->reliquat_actuel(), 0, 'New remaining is 0');

        $entityManager->refresh($adenis);
        $this->assertEquals($adenis->conges_credit(), 164, 'credit has updated');
        $this->assertEquals($adenis->conges_reliquat(), 0, "reliquat didn't change");
        $this->assertEquals($adenis->conges_anticipation(), 0, "anticipation didn't change");
        $this->assertEquals($adenis->comp_time(), 10, "comp time didn't change");
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
