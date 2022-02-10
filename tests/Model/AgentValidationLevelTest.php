<?php

use App\Model\Agent;
use App\Model\Manager;
use App\Model\ConfigParam;

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;

class AgentValidationLevelTest extends TestCase
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

    protected function setParam($name, $value)
    {
        $param = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $name]);

        $param->valeur($value);
        $this->entityManager->persist($param);
        $this->entityManager->flush();
    }

    public function testgetValidationLevelForWorkingHours()
    {

        $this->setParam('PlanningHebdo-notifications-agent-par-agent', 0);

        $agent1 = $this->builder->build(Agent::class,
            array(
                'droits' => array(1101,1201,99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('workinghour')
            ->getValidationLevelFor($agent1->id());

        $this->assertTrue($adminN1, 'Agent 1 has admin level 1 for working hours');
        $this->assertTrue($adminN2, 'Agent 1 has admin level 2 for working hours');

        $agent2 = $this->builder->build(Agent::class,
            array(
                'droits' => array(1101,99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('workinghour')
            ->getValidationLevelFor($agent2->id());

        $this->assertTrue($adminN1, 'Agent 2 has admin level 1 for working hours');
        $this->assertFalse($adminN2, 'Agent 2 doesn\'t admin level 2 for working hours');

        $agent3 = $this->builder->build(Agent::class,
            array(
                'droits' => array(99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('workinghour')
            ->getValidationLevelFor($agent3->id());

        $this->assertFalse($adminN1, 'Agent 3 doesn\'t have admin level 1 for working hours');
        $this->assertFalse($adminN2, 'Agent 3 doesn\'t have admin level 2 for working hours');
    }

    public function testgetValidationLevelForWorkingHoursByAgent()
    {
        $this->setParam('PlanningHebdo-notifications-agent-par-agent', 1);

        $agent_manager = $this->builder->build(Agent::class);
        $agent1 = $this->builder->build(Agent::class);
        $agent2 = $this->builder->build(Agent::class);
        $agent3 = $this->builder->build(Agent::class);

        // Manager L1 of agent 1
        $manager = new Manager();
        $manager->perso_id($agent1);
        $manager->level1(1);
        $manager->level2(0);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('workinghour')
            ->getValidationLevelFor($agent_manager->id(), $agent1->id());

        $this->assertTrue($adminN1, 'Manager is admin L1 for agent 1');
        $this->assertFalse($adminN2, 'Manager is not admin L2 for agent 1');

        // Manager L2 of agent 2
        $manager = new Manager();
        $manager->perso_id($agent2);
        $manager->level1(0);
        $manager->level2(1);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('workinghour')
            ->getValidationLevelFor($agent_manager->id(), $agent2->id());

        $this->assertFalse($adminN1, 'Manager is not admin L1 for agent 2');
        $this->assertTrue($adminN2, 'Manager is admin L2 for agent 2');

        // Not manager of agent 3
        $manager = new Manager();
        $manager->perso_id($agent3);
        $manager->level1(0);
        $manager->level2(0);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('workinghour')
            ->getValidationLevelFor($agent_manager->id(), $agent3->id());

        $this->assertFalse($adminN1, 'Manager is not admin L1 for agent 3');
        $this->assertFalse($adminN2, 'Manager is not admin L2 for agent 3');
    }

    public function testgetValidationLevelForAbsences()
    {

        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('Multisites-nombre', 1);

        $agent1 = $this->builder->build(Agent::class,
            array(
                'login' => 'aaaaaaaaaaaaaaaaaa',
                'droits' => array(201,501,99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent1->id());

        $this->assertTrue($adminN1, 'Agent 1 has admin level 1 for absences');
        $this->assertTrue($adminN2, 'Agent 1 has admin level 2 for absences');

        $agent2 = $this->builder->build(Agent::class,
            array(
                'droits' => array(201,99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent2->id());

        $this->assertTrue($adminN1, 'Agent 2 has admin level 1 for absence');
        $this->assertFalse($adminN2, 'Agent 2 doesn\'t admin level 2 for absence');

        $agent3 = $this->builder->build(Agent::class,
            array(
                'droits' => array(99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent3->id());

        $this->assertFalse($adminN1, 'Agent 3 doesn\'t have admin level 1 for absence');
        $this->assertFalse($adminN2, 'Agent 3 doesn\'t have admin level 2 for absence');
    }

    public function testgetValidationLevelForAbsencesMultiSites()
    {

        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('Multisites-nombre', 2);

        $agent1 = $this->builder->build(Agent::class,
            array(
                'login' => 'aaaaaaaaaaaaaaaaaa',
                'droits' => array(201,502,99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent1->id());

        $this->assertTrue($adminN1, 'Agent 1 has admin level 1 for absences');
        $this->assertTrue($adminN2, 'Agent 1 has admin level 2 for absences');

        $agent2 = $this->builder->build(Agent::class,
            array(
                'droits' => array(201, 202, 99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent2->id());

        $this->assertTrue($adminN1, 'Agent 2 has admin level 1 for absence');
        $this->assertFalse($adminN2, 'Agent 2 doesn\'t admin level 2 for absence');

        $agent3 = $this->builder->build(Agent::class,
            array(
                'droits' => array(501,502,99,100)
            )
        );

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent3->id());

        $this->assertFalse($adminN1, 'Agent 3 doesn\'t have admin level 1 for absence');
        $this->assertTrue($adminN2, 'Agent 3 have admin level 2 for absence');
    }

    public function testgetValidationLevelForAbsencesByAgent()
    {

        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Multisites-nombre', 1);

        $agent_manager = $this->builder->build(Agent::class);
        $agent1 = $this->builder->build(Agent::class);
        $agent2 = $this->builder->build(Agent::class);
        $agent3 = $this->builder->build(Agent::class);

        // Manager L1 of agent 1
        $manager = new Manager();
        $manager->perso_id($agent1);
        $manager->level1(1);
        $manager->level2(0);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent_manager->id(), $agent1->id());

        $this->assertTrue($adminN1, 'Manager is admin L1 for agent 1');
        $this->assertFalse($adminN2, 'Manager is not admin L2 for agent 1');

        // Manager L2 of agent 2
        $manager = new Manager();
        $manager->perso_id($agent2);
        $manager->level1(0);
        $manager->level2(1);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent_manager->id(), $agent2->id());

        $this->assertFalse($adminN1, 'Manager is not admin L1 for agent 2');
        $this->assertTrue($adminN2, 'Manager is admin L2 for agent 2');

        // Not manager of agent 3
        $manager = new Manager();
        $manager->perso_id($agent3);
        $manager->level1(0);
        $manager->level2(0);
        $agent_manager->addManaged($manager);

        $this->entityManager->persist($agent_manager);
        $this->entityManager->flush();

        list($adminN1, $adminN2) = $this->entityManager->getRepository(Agent::class)
            ->setModule('absence')
            ->getValidationLevelFor($agent_manager->id(), $agent3->id());

        $this->assertFalse($adminN1, 'Manager is not admin L1 for agent 3');
        $this->assertFalse($adminN2, 'Manager is not admin L2 for agent 3');
    }
}
