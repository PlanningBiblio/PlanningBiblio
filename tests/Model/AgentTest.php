<?php

use App\Model\Agent;
use App\Model\Access;
use App\Model\Holiday;
use App\Model\Skill;
use App\Model\Position;
use App\Model\PlanningPosition;
use App\Model\WeekPlanning;
use App\Model\Absence;

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../public/conges/class.conges.php');

class AgentTest extends TestCase
{
    public function testAdd() {
        global $entityManager;
        $agent = $entityManager->find(Agent::class, 1);

        $this->assertEquals('Administrateur', $agent->nom());
        $this->assertEquals('admin', $agent->login());
    }

    public function testCanAccess() {

        $access = new Access();
        $access->groupe_id(99);

        $access_bad = new Access();
        $access_bad->groupe_id(201);

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array(
            'droits' => array('99', '100')
        ));

        $this->assertTrue($agent->can_access(array($access)));
        $this->assertFalse($agent->can_access(array($access_bad)));
    }

    public function testIsOnVacationOn(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $start = \DateTime::createFromFormat("d/m/Y", '08/10/2022');
        $end = \DateTime::createFromFormat("d/m/Y", '15/10/2022');


        $builder->delete(Holiday::class);
        $holiday = $builder->build(Holiday::class, array('debut' => $start, 'fin' => $end, 'perso_id' => $agent->id(), 'valide_n1' => 1, 'valide' =>1, 'supprime' => 0, 'information' => 0));

        $this->assertFalse($agent->isOnVacationOn('2022-12-10', '2022-12-15'));
        $this->assertTrue($agent->isOnVacationOn($start->format('Y-m-d'), $end->format('Y-m-d')));
        $this->assertTrue($agent->isOnVacationOn('2022-10-10', '2022-10-11'));
        $this->assertTrue($agent->isOnVacationOn('2022-10-14', '2022-10-16'));
        $this->assertTrue($agent->isOnVacationOn('2022-10-06', '2022-10-09'));
        $this->assertFalse($agent->isOnVacationOn('2022-10-14', '2022-10-07'));
        $this->assertFalse($agent->isOnVacationOn('2022-10-10', ''));
        $this->assertTrue($agent->isOnVacationOn('', '2022-10-15'));
        $this->assertFalse($agent->isOnVacationOn('', ''));
    }

    public function testIsBlockedOn(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $start = \DateTime::createFromFormat("H:i:s", '08:00:00');
        $end = \DateTime::createFromFormat("H:i:s", '17:00:00');
        $date_ok = \DateTime::createFromFormat("d/m/Y", '09/10/2022');
        $date_false = \DateTime::createFromFormat("d/m/Y", '01/08/2022');

        $post = $builder->build(Position::class, array('statistiques' => 0, 'teleworking' => 0, 'bloquant' => 1));

        $builder->delete(PlanningPosition::class);
        $pl_post = $builder->build(
            PlanningPosition::class,
            array(
                'date' => $date_ok,
                'poste' => $post->id(),
                'debut' => $start,
                'fin' => $end,
                'perso_id' => $agent->id(),
                'absent' => 0,
                'supprime' => 0,
                'grise'=>1
            )
        );

        $this->assertFalse($agent->isBlockedOn($date_false->format('Y-m-d'), $start->format('H:i:s'), $end->format('H:i:s')));
        $this->assertFalse($agent->isBlockedOn($date_ok->format('Y-m-d'), '18:00:00', '20:00:00'));
        $this->assertTrue($agent->isBlockedOn($date_ok->format('Y-m-d'), $start->format('H:i:s'), $end->format('H:i:s')));

        $agent2 = $builder->build(Agent::class, array('login' => 'jpie'));
        $post2 = $builder->build(Position::class, array('statistiques' => 0, 'teleworking' => 0, 'bloquant' => 0));
        $pl_post2 = $builder->build(
            PlanningPosition::class,
            array(
                'date' => $date_ok,
                'poste' => $post2->id(),
                'debut' => $start, 'fin' => $end,
                'perso_id' => $agent2->id(),
                'absent' => 0,
                'supprime' => 0,
                'grise'=>1
            )
        );

        $this->assertFalse($agent2->isBlockedOn($date_false->format('Y-m-d'), $start->format('H:i:s'), $end->format('H:i:s')));
        $this->assertFalse($agent2->isBlockedOn($date_ok->format('Y-m-d'), '18:00:00', '20:00:00'));
        $this->assertFalse($agent2->isBlockedOn($date_ok->format('Y-m-d'), $start->format('H:i:s'), $end->format('H:i:s')));
    }

    public function testGetWorkingHoursOn(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $date1 = \DateTime::createFromFormat("d/m/Y", '08/10/2022');
        $date2 = \DateTime::createFromFormat("d/m/Y", '22/12/2022');
        $start = \DateTime::createFromFormat("H:i:s", '08:00:00');
        $end = \DateTime::createFromFormat("H:i:s", '19:00:00');

        //without planningHebdo
        $agent1 = $builder->build(
            Agent::class,
            array(
                'login' => 'jdevoe',
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "1" => ["09:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "2" => ["09:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "3" => ["08:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "4" => ["08:00:00","12:30:00","13:15:00","14:45:00","4"],
                        "5" => ["","","","","4"],
                        "7" => ["09:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "8" => ["09:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "9" => ["09:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "10" => ["08:00:00","12:30:00","13:15:00","17:15:00","4"],
                        "11" => ["08:00:00","12:30:00","13:15:00","14:45:00","4"]
                    )
                )
            )
        );

        $this->assertEquals($agent1->getWorkingHoursOn($date1)['temps']['0']['0'], '09:00:00','check Working Hours of the agent without planningHebdo');
        $this->assertEquals($agent1->getWorkingHoursOn($date1)['temps']['0']['2'], '13:15:00','check Working Hours of the agent without planningHebdo');
        $this->assertEquals($agent1->getWorkingHoursOn($date1)['temps']['0']['1'], '12:30:00','check Working Hours of the agent without planningHebdo');
        $this->assertEquals($agent1->getWorkingHoursOn($date1)['temps']['0']['3'], '17:15:00','check Working Hours of the agent without planningHebdo');

        $this->assertEquals($agent1->getWorkingHoursOn($date2)['temps']['0']['0'], '09:00:00','check Working Hours of the agent without planningHebdo');
        $this->assertEquals($agent1->getWorkingHoursOn($date2)['temps']['0']['2'], '13:15:00','check Working Hours of the agent without planningHebdo');
        $this->assertEquals($agent1->getWorkingHoursOn($date2)['temps']['0']['1'], '12:30:00','check Working Hours of the agent without planningHebdo');
        $this->assertEquals($agent1->getWorkingHoursOn($date2)['temps']['0']['3'], '17:15:00','check Working Hours of the agent without planningHebdo');

        //with planningHebdo
        $d = date("d")+4;
        $m = date("m");
        $Y = date("Y");
        $depart = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");
        $date3 = \DateTime::createFromFormat("d/m/Y", date('d/m/Y'));
        $agent2 = $builder->build(Agent::class, array('login' => 'jmarg', 'depart' => $depart));

        $GLOBALS['config']['PlanningHebdo'] = 1;
        $config=$GLOBALS['config']['PlanningHebdo'];

        $builder->delete(WeekPlanning::class);
        $pl_post = $builder->build
        (
            WeekPlanning::class,
            array(
                'perso_id' => $agent2->id(),
                'debut' => $start,
                'fin' => $end,
                'valide_n1' => 1,
                'valide' => 0,
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "1" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "2" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "3" => ["08:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "4" => ["08:00:00","12:30:00","13:30:00","17:00:00","4"]
                        )
                )
            )
        );
        $this->assertEquals($agent2->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][0][0], '09:00:00','check Working Hours of the agent with planningHebdo');
        $this->assertEquals($agent2->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][3][0], '08:00:00','check Working Hours of the agent with planningHebdo');
        $this->assertEquals($agent2->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][4][1], '12:30:00','check Working Hours of the agent with planningHebdo');
        $this->assertNotEquals($agent2->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][3][2], '12:30:00','check Working Hours of the agent with planningHebdo');

        $agent3 = $builder->build(Agent::class, array('login' => 'ldave', 'depart' => $depart));
        $pl_post2 = $builder->build
        (
            WeekPlanning::class,
            array(
                'perso_id' => $agent3->id(),
                'debut' => $start,
                'fin' => $end,
                'valide_n1' => 0,
                'valide' => 0,
                'temps' => json_encode(
                    array(
                        "0" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "1" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "2" => ["09:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "3" => ["08:00:00","12:30:00","13:30:00","17:00:00","4"],
                        "4" => ["08:00:00","12:30:00","13:30:00","17:00:00","4"]
                    )
                )
            )
        );

        $this->assertEquals($agent3->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][0][0], '09:00:00','check Working Hours of the agent with planningHebdo');
        $this->assertEquals($agent3->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][3][0], '08:00:00','check Working Hours of the agent with planningHebdo');
        $this->assertEquals($agent3->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][4][1], '12:30:00','check Working Hours of the agent with planningHebdo');
        $this->assertNotEquals($agent3->getWorkingHoursOn($date3->format('Y-m-d'))['temps'][3][2], '12:30:00','check Working Hours of the agent with planningHebdo');
    }

    public function testSkills(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $date = \DateTime::createFromFormat("d/m/Y", date('d/m/Y'));
        $start = \DateTime::createFromFormat("H:i:s", '00:01:00');
        $end = \DateTime::createFromFormat("H:i:s", '23:59:00');

        $skill1 = $builder->build(Skill::class, array('nom' => 'basket'));
        $id1 = $skill1->id();
        $skill2 = $builder->build(Skill::class, array('nom' => 'volley'));
        $id2 = $skill2->id();
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'postes' => json_encode(["$id1", "$id2"])));

        $this->assertEquals($id1, $agent->skills()[0]);
        $this->assertEquals($id2, $agent->skills()[1]);
        $this->assertCount(2, $agent->skills());

    }

    public function test_get_planning_unit_mails(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $start = \DateTime::createFromFormat("H:i:s", '08:00:00');
        $end = \DateTime::createFromFormat("H:i:s", '17:00:00');
        $date = \DateTime::createFromFormat("d/m/Y", '09/10/2022');

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'mail' => 'j.devoe@mail.com'));

        $GLOBALS['config']['Mail-Planning'] = 'j.devoe@mail.com;jm@mail.fr;j.devoe@mail.com';

        $this->assertEquals($agent->get_planning_unit_mails(),['j.devoe@mail.com','jm@mail.fr']);

        //Multi sites :

        $GLOBALS['config']['Multisites-nombre'] = 4;
        $GLOBALS['config']['Mail-Planning'] = '';

        $agent2 = $builder->build(Agent::class, array('login' => 'jmarc', 'sites' => json_encode(["1", "2", "3","4"])));
        $GLOBALS['config']['Multisites-site1-mail'] ='jmarc@mail.fr;jcharles@mail.fr;jdevoe@mail.com';
        $GLOBALS['config']['Multisites-site2-mail'] ='jcharles@mail.fr;jmarc@mail.fr;j.paul@mail.com';
        $GLOBALS['config']['Multisites-site3-mail'] ='j.claude@mail.com;jmarc@mail.fr;jcharles@mail.fr';
        $GLOBALS['config']['Multisites-site4-mail'] ='j.paul@mail.com;j.claude@mail.com';

        $this->assertEquals(
            $agent2->get_planning_unit_mails(),
            [
                0 => 'jmarc@mail.fr',
                1 => 'jcharles@mail.fr',
                2 => 'jdevoe@mail.com',
                5 => 'j.paul@mail.com',
                6 => 'j.claude@mail.com'
            ]
        );

        $agent3 = $builder->build(Agent::class, array('login' => 'ldave', 'sites' => json_encode(["1"])));
        $this->assertEquals(
            $agent3->get_planning_unit_mails(),
            [
                0 => 'jmarc@mail.fr',
                1 => 'jcharles@mail.fr',
                2 => 'jdevoe@mail.com'
            ]
        );

        $agent4 = $builder->build(Agent::class, array('login' => 'ldavy', 'sites' => json_encode(["1","3"])));
        $this->assertEquals($agent3->get_planning_unit_mails(),[0 => 'jmarc@mail.fr', 1 => 'jcharles@mail.fr', 2 => 'jdevoe@mail.com']);

    }

    public function testIsAbsentOn(){

        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $start = \DateTime::createFromFormat("d/m/Y", '10/12/2022');
        $end = \DateTime::createFromFormat("d/m/Y", '17/12/2022');

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $off = $builder->build(
            Absence::class,
            array(
                'debut' => $start,
                'fin' => $end,
                'perso_id' => $agent->id(),
                'valide_n1' => 1,
                'valide' =>1,
                'supprime' => 0,
                'groupe' => 1
            )
        );

        $this->assertTrue($agent->isAbsentOn('2022-12-12', '2022-12-15'));
        $this->assertFalse($agent->isAbsentOn('2022-12-18', '2022-12-25'));
        $this->assertTrue($agent->isAbsentOn('2022-12-12', '2022-12-12'));
        $this->assertFalse($agent->isAbsentOn('2022-12-12', ''));
        $this->assertTrue($agent->isAbsentOn('2022-12-14', '2022-12-25'));
    }

    public function test_get_manager_emails(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'mails_responsables' => 'jcharles@mail.fr;jmarc@mail.fr;j.paul@mail.com'));

        $this->assertEquals($agent->get_manager_emails(), ['jcharles@mail.fr', 'jmarc@mail.fr', 'j.paul@mail.com']);
    }
}