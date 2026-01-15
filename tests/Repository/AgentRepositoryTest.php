<?php

namespace App\Tests;

use App\Entity\Agent;
use App\Entity\Manager;
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

    public function testcreateAgents(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $builder->build(Agent::class, array('login' => 'Mike', 'supprime' => 1, 'actif' => 'Supprimé'));
        $builder->build(Agent::class, array('login' => 'Erik', 'supprime' => 1, 'actif' => 'Supprim&eacute;'));
        $builder->build(Agent::class, array('login' => 'John', 'supprime' => 0, 'actif' => 'Actif'));
        $builder->build(Agent::class, array('login' => 'Leo', 'prenom' => 'Léo', 'supprime' => 0, 'actif' => 'Actif'));

        $repo = $this->entityManager->getRepository(Agent::class);
        $mike = $repo->findOneBy(['login' => 'Mike']);
        $eric = $repo->findOneBy(['login' => 'Erik']);
        $john = $repo->findOneBy(['login' => 'John']);
        $leo = $repo->findOneBy(['login' => 'Leo']);

        $builder->build(Manager::class, array('perso_id' => $mike));
        $builder->build(Manager::class, array('perso_id' => $eric));
        $builder->build(Manager::class, array('perso_id' => $john));
        $builder->build(Manager::class, array('perso_id' => $leo));
    }

    public function testGetByDeletion(): void
    {
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

    public function testGet(): void
    {
        $repo = $this->entityManager->getRepository(Agent::class);
        $leo = $repo->get('nom', 'Actif', 'Léo');
        $this->assertNotNull($leo);

        $agentsSupprime1 = $repo->get('nom', 'Supprimé', null);
        $this->assertCount(2, $agentsSupprime1);
        $agentsSupprime2 = $repo->get('nom', 'Supprim&eacute;', null);
        $this->assertCount(2, $agentsSupprime2);

        $agentActif = $repo->get('nom', 'Actif', null);
        $this->assertNotNull($agentActif);
    }
}
