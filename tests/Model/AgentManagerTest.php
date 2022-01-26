<?php

use App\Model\Agent;
use App\Model\Manager;

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;

class AgentManagerTest extends TestCase
{
    protected $builder;
    protected $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;

        $this->builder = new FixtureBuilder();
        $this->builder->delete(Agent::class);

        $this->entityManager = $entityManager;
    }

    public function testIsManagerOf()
    {
        $agent_manager = $this->builder->build(Agent::class);
        $agent1 = $this->builder->build(Agent::class);
        $agent2 = $this->builder->build(Agent::class);

        $manager = new Manager();
        $manager->perso_id($agent1);
        $manager->notification_level1(0);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        $this->entityManager->refresh($agent1);
        $this->entityManager->refresh($agent2);
        $this->entityManager->refresh($agent_manager);

        $this->assertTrue($agent_manager->isManagerOf(array($agent1->id())));

        $this->assertFalse($agent_manager->isManagerOf(array($agent2->id())));

        $this->assertFalse($agent_manager->isManagerOf(array($agent1->id(), $agent2->id())));

        $manager2 = new Manager();
        $manager2->perso_id($agent2);
        $manager2->notification_level1(0);
        $agent_manager->addManaged($manager2);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        $this->assertTrue($agent_manager->isManagerOf(array($agent1->id(), $agent2->id())));
    }
}