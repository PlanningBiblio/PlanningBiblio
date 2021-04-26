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
}
