<?php

namespace App\Tests;

use App\Entity\Agent;
use App\Entity\Manager;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AgentRepositoryTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        global $entityManager;
        $this->entityManager = $entityManager;
        
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Manager::class);

        $mike = $this->createAgent(array('login' => 'Mike', 'prenom' => 'Mike', 'supprime' => 1, 'actif' => 'Supprimé'));
        $eric = $this->createAgent(array('login' => 'Eric', 'prenom' => 'Eric', 'supprime' => 1, 'actif' => 'Supprim&eacute;'));
        $john = $this->createAgent(array('login' => 'John', 'prenom' => 'John', 'supprime' => 0, 'actif' => 'Actif'));
        $leo = $this->createAgent(array('login' => 'Leo', 'prenom' => 'Léo', 'supprime' => 0, 'actif' => 'Actif'));

        $mMike = new Manager();
        $mMike->setUser($mike);
        $mMike->setManager($john);
        $mEric = new Manager();
        $mEric->setUser($eric);
        $mEric->setManager($john);
        $mJohn = new Manager();
        $mJohn->setUser($john);
        $mJohn->setManager($leo);
        $mLeo = new Manager();
        $mLeo->setUser($leo);
        $mLeo->setManager($john);

        $this->entityManager->persist($mMike);
        $this->entityManager->persist($mEric);
        $this->entityManager->persist($mJohn);
        $this->entityManager->persist($mLeo);
        $this->entityManager->flush();
    }

    private function createAgent(array $agentInfo)
    {
        $agent = new Agent();
        $agent->setLogin($agentInfo['login']);
        $agent->setFirstname($agentInfo['prenom']);
        $agent->setActive($agentInfo['actif']);
        $agent->setDeletion($agentInfo['supprime']);

        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        return $agent;
    }

    public function testGetWithConfig(): void
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);

        $repo = $this->entityManager->getRepository(Agent::class);
        $leo = $repo->get('nom', 'Actif', 'Léo');
        $this->assertNotEmpty($leo);

        $agentsSupprime1 = $repo->get('nom', 'Supprimé', null);
        $this->assertCount(2, $agentsSupprime1);
        $agentsSupprime2 = $repo->get('nom', 'Supprim&eacute;', null);
        $this->assertCount(2, $agentsSupprime2);

        $agentActif = $repo->get('nom', 'Actif', null);
        $this->assertNotNull($agentActif);
    }

    public function testGet(): void
    {
        $repo = $this->entityManager->getRepository(Agent::class);
        $leo = $repo->get('nom', 'Actif', 'Léo');
        $this->assertNotEmpty($leo);

        $agentsSupprime1 = $repo->get('nom', 'Supprimé', null);
        $this->assertCount(2, $agentsSupprime1);
        $agentsSupprime2 = $repo->get('nom', 'Supprim&eacute;', null);
        $this->assertCount(2, $agentsSupprime2);

        $agentActif = $repo->get('nom', 'Actif', null);
        $this->assertNotNull($agentActif);
    }

//     public function testGetByDeletion(): void
//     {
//         $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0]);
//         $this->assertEquals(count($repo), 3);
//         $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([1]);
//         $this->assertEquals(count($repo), 1);
//         $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([2]);
//         $this->assertEquals(count($repo), 1);
//         $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0,1,2]);
//         $this->assertEquals(count($repo), 5);
//         $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0,1]);
//         $this->assertEquals(count($repo), 4);
//         $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([1,2]);
//         $this->assertEquals(count($repo), 2);
//     }

//     public function testGetSitesForAgents(): void{
//         global $entityManager;
//         $builder = new FixtureBuilder();
//         $builder->delete(Agent::class);
//         $builder->delete(Manager::class);

//         $agent1 = $builder->build(Agent::class, array('login' => 'Mike', 'sites' => '["1","2"]'));
//         $agent2 = $builder->build(Agent::class, array('login' => 'Erik', 'sites' => '["1","3"]'));
//         $perso_ids = array($agent1->getId(), $agent2->getId());

//         $GLOBALS['config']['Multisites-nombre'] = 1;
//         $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
//         $this->assertEquals($sites, array('1'));

//         $GLOBALS['config']['Multisites-nombre'] = 3;
//         $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
//         $this->assertEquals($sites, array('1', '2', '3'));

//         $agent3 = $builder->build(Agent::class, array('login' => 'Melvin', 'sites' => ''));
//         $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents(array($agent3->getId()));
//         $this->assertEquals($sites, array());

//     }

}
