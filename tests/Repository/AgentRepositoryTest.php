<?php

namespace App\Tests;

use App\Entity\Agent;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

class AgentRepositoryTest extends TestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        global $entityManager;
        $this->entityManager = $entityManager;
    }

    public function testGetByDeletion(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $agent1 = $builder->build(Agent::class, array('login' => 'Mike', 'supprime' => '0'));
        $agent2 = $builder->build(Agent::class, array('login' => 'Erik', 'supprime' => '1'));
        $agent3 = $builder->build(Agent::class, array('login' => 'John', 'supprime' => '2'));

        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0]);
        $this->assertEquals(count($repo), 3);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([1]);
        $this->assertEquals(count($repo), 1);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([2]);
        $this->assertEquals(count($repo), 1);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0,1,2]);
        $this->assertEquals(count($repo), 5);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0,1]);
        $this->assertEquals(count($repo), 4);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([1,2]);
        $this->assertEquals(count($repo), 2);
    }

    public function testGetSitesForAgents(): void{
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $agent1 = $builder->build(Agent::class, array('login' => 'Mike', 'sites' => '["1","2"]'));
        $agent2 = $builder->build(Agent::class, array('login' => 'Erik', 'sites' => '["1","3"]'));
        $perso_ids = array($agent1->getId(), $agent2->getId());

        $GLOBALS['config']['Multisites-nombre'] = 1;
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
        $this->assertEquals($sites, array('1'));

        $GLOBALS['config']['Multisites-nombre'] = 3;
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
        $this->assertEquals($sites, array('1', '2', '3'));

        $agent3 = $builder->build(Agent::class, array('login' => 'Melvin', 'sites' => ''));
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents(array($agent3->getId()));
        $this->assertEquals($sites, array());


    }
}
