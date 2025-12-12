<?php

use App\Entity\Agent;
use App\Entity\Manager;

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

    public function testIsManagerOf(): void
    {
        $agent_manager = $this->builder->build(Agent::class);
        $agent1 = $this->builder->build(Agent::class);
        $agent2 = $this->builder->build(Agent::class);

        $manager = new Manager();
        $manager->setUser($agent1);
        $manager->setLevel1Notification(0);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        $this->entityManager->refresh($agent1);
        $this->entityManager->refresh($agent2);
        $this->entityManager->refresh($agent_manager);

        $this->assertTrue($agent_manager->isManagerOf(array($agent1->getId())));

        $this->assertFalse($agent_manager->isManagerOf(array($agent2->getId())));

        $this->assertFalse($agent_manager->isManagerOf(array($agent1->getId(), $agent2->getId())));

        $manager2 = new Manager();
        $manager2->setUser($agent2);
        $manager2->setLevel1Notification(0);
        $agent_manager->addManaged($manager2);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        $this->assertTrue($agent_manager->isManagerOf(array($agent1->getId(), $agent2->getId())));
    }

    public function testIsManagerOfByLevel(): void
    {
        $agent_manager = $this->builder->build(Agent::class);
        $agent1 = $this->builder->build(Agent::class);
        $agent2 = $this->builder->build(Agent::class);
        $agent3 = $this->builder->build(Agent::class);

        $manager = new Manager();
        $manager->setUser($agent1);
        $manager->setLevel1(1);
        $manager->setLevel2(0);
        $agent_manager->addManaged($manager);

        $manager2 = new Manager();
        $manager2->setUser($agent2);
        $manager2->setLevel1(0);
        $manager2->setLevel2(1);
        $agent_manager->addManaged($manager2);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        $this->entityManager->refresh($agent1);
        $this->entityManager->refresh($agent2);
        $this->entityManager->refresh($agent3);
        $this->entityManager->refresh($agent_manager);

        $this->assertTrue($agent_manager->isManagerOf(array($agent1->getId())));
        $this->assertTrue($agent_manager->isManagerOf(array($agent1->getId()), 'level1'));
        $this->assertFalse($agent_manager->isManagerOf(array($agent1->getId()), 'level2'));

        $this->assertTrue($agent_manager->isManagerOf(array($agent2->getId())));
        $this->assertFalse($agent_manager->isManagerOf(array($agent2->getId()), 'level1'));
        $this->assertTrue($agent_manager->isManagerOf(array($agent2->getId()), 'level2'));

        $this->assertFalse($agent_manager->isManagerOf(array($agent3->getId())));
        $this->assertFalse($agent_manager->isManagerOf(array($agent3->getId()), 'level1'));
        $this->assertFalse($agent_manager->isManagerOf(array($agent3->getId()), 'level2'));
    }
}
