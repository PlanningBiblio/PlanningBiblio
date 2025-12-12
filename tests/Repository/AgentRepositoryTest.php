<?php

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;
use App\Model\Agent;

class AgentRepositoryTest extends TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        global $entityManager;
        $this->entityManager = $entityManager;
    }

    public function testGetSitesForAgents(){
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $agent1 = $builder->build(Agent::class, array('login' => 'Mike', 'sites' => '["1","2"]'));
        $agent2 = $builder->build(Agent::class, array('login' => 'Erik', 'sites' => '["1","3"]'));
        $perso_ids = array($agent1->id(), $agent2->id());

        $GLOBALS['config']['Multisites-nombre'] = 1;
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
        $this->assertEquals($sites, array('1'));

        $GLOBALS['config']['Multisites-nombre'] = 3;
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
        $this->assertEquals($sites, array('1', '2', '3'));

        $agent3 = $builder->build(Agent::class, array('login' => 'Melvin', 'sites' => ''));
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents(array($agent3->id()));
        $this->assertEquals($sites, array());


    }
}
