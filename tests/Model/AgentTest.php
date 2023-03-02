<?php

use App\Model\Agent;
use App\Model\Access;

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;

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

    public function test_get_manager_emails(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        // MT39529: No managers should return an empty array, not an array with an empty value.
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'mails_responsables' => ''));
        $this->assertEquals(sizeof($agent->get_manager_emails()), 0);

        $builder->delete(Agent::class);

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'mails_responsables' => 'jcharles@mail.fr;jmarc@mail.fr;j.paul@mail.com'));
        $this->assertEquals($agent->get_manager_emails(), ['jcharles@mail.fr', 'jmarc@mail.fr', 'j.paul@mail.com']);
    }
}
