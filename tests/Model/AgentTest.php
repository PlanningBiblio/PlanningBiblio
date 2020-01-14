<?php

use App\Model\Agent;
use PHPUnit\Framework\TestCase;

class AgentTest extends TestCase
{
    public function testAdd() {
        global $entityManager;
        $agent = $entityManager->find(Agent::class, 1);

        $this->assertEquals('Administrateur', $agent->nom());
        $this->assertEquals('admin', $agent->login());
    }
}
