<?php

use App\Model\Agent;
use App\Model\Holiday;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayControllerHolidayCreditTest extends PLBWebTestCase
{
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['config']['PlanningHebdo-Agents'] = 1;
        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-demi-journees'] = 1;
        $_SESSION['oups']['CSRFToken'] = '00000';

        $this->builder = new FixtureBuilder();
        $this->deleteWorkingHours();
        $this->builder->delete(Agent::class);

        $admin = $this->builder->build(Agent::class, array('login' => 'kboivin'));
        $this->logInAgent($admin, array(100));
    }

    public function testHolidayOnHalfday()
    {
        global $entityManager;

        $client = static::createClient();

        $jdevoe = $this->builder->build(Agent::class, array('login' => 'jdevoe'));
        $this->addWorkingHours($jdevoe, array('09:00:00', '12:30:00', '13:30:00', '17:00:00'));

        $jdevoe_id = $jdevoe->id();
        $url = "/ajax/holiday-credit?debut=24/01/2022&fin=24/01/2022&hre_debut=00:00:00&hre_fin=23:59:59&perso_id=$jdevoe_id&start_halfday=morning&end_halfday=morning";

        $client->request('GET', $url);
        $response = $client->getResponse()->getContent();
        $result = json_decode($response);

        $this->assertEquals($result->days, 0.5, 'Morning Holiday equals a half day');
        $this->assertEquals($result->hours, 3, 'Morning Holiday hours equals 3');
        $this->assertEquals($result->minutes, 50, 'Morning Holiday minutes equals 50');
        $this->assertEquals($result->hr_hours, '3h30', 'Morning Holiday human readable equals 3h30');
        $this->assertEquals($result->rest, 0, 'Morning Holiday rest is 0');

        $url = "/ajax/holiday-credit?debut=24/01/2022&fin=24/01/2022&hre_debut=00:00:00&hre_fin=23:59:59&perso_id=$jdevoe_id&start_halfday=fullday&end_halfday=fullday";

        $client->request('GET', $url);
        $response = $client->getResponse()->getContent();
        $result = json_decode($response);

        $this->assertEquals($result->days, 1, 'Morning Holiday equals one day');
        $this->assertEquals($result->hours, 7, 'Morning Holiday hours equals 7');
        $this->assertEquals($result->minutes, 0, 'Morning Holiday minutes equals 0');
        $this->assertEquals($result->hr_hours, '7h00', 'Morning Holiday human readable equals 7h00');
        $this->assertEquals($result->rest, 0, 'Morning Holiday rest is 0');
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
}
