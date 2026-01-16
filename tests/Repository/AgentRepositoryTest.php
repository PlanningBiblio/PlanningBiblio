<?php

namespace App\Tests;

use App\Entity\Agent;
use App\Entity\Manager;
use PHPStan\Type\Php\GettypeFunctionReturnTypeExtension;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

use function PHPUnit\Framework\assertEquals;

class AgentRepositoryTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        global $entityManager;
        $this->entityManager = $entityManager;
        
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Manager::class);

        $managers = new Agent();
        $managers->setLogin('managers');

        $mike = $this->createAgent(array('login' => 'Mike', 'prenom' => 'Mike', 'supprime' => 1, 'actif' => 'Supprimé'));
        $eric = $this->createAgent(array('login' => 'Eric', 'prenom' => 'Eric', 'supprime' => 1, 'actif' => 'Supprim&eacute;'));
        $john = $this->createAgent(array('login' => 'John', 'prenom' => 'John', 'supprime' => 0, 'actif' => 'Actif'));
        $leo = $this->createAgent(array('login' => 'Leo', 'prenom' => 'Léo', 'supprime' => 0, 'actif' => 'Actif'));

        // $agent_manager = $this->builder->build(Agent::class);
        // $agent1 = $this->builder->build(Agent::class);
        // $manager = new Manager();
        // $manager->setUser($agent1);
        // $manager->setLevel1Notification(0);
        // $agent_manager->addManaged($manager);
        // $this->assertTrue($agent_manager->isManagerOf(array($agent1->getId())));

        $managerMike = new Manager();
        $managerMike->setUser($managers);
        // $managerMike->setManager($mike);
        $mike->addManaged($managerMike);
        $managerMike->setLevel1Notification(1);

        $mikeEric = new Manager();
        $mikeEric->setUser($mike);
        $mikeEric->setManager($eric);
        $mikeEric->setLevel1Notification(2);

        $ericJohn = new Manager();
        $ericJohn->setUser($eric);
        $ericJohn->setManager($john);
        $ericJohn->setLevel1Notification(3);

        $johnLeo = new Manager();
        $johnLeo->setUser($john);
        $johnLeo->setManager($leo);
        $johnLeo->setLevel1Notification(4);

        $this->entityManager->persist($managers);
        $this->entityManager->persist($managerMike);
        $this->entityManager->persist($mikeEric);
        $this->entityManager->persist($ericJohn);
        $this->entityManager->persist($johnLeo);
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
        // $levels = [];
        // foreach ($agentsSupprime1 as $agent) {
        //     echo($agent->getLogin());
        //     echo(gettype($agent->getManagers()));
        //     echo($agent->getManagers()[0]->getNotificationLevel1());
        //     foreach ($agent->getManagers() as $relation) {
        //         $levels[] = $relation->getNotificationLevel1();
        //     }
        // }

        // $this->assertTrue($mike->isManagerOf(array($managers->getId())));

        // $this->assertEquals(1, $levels[0]);
        // $this->assertEquals(2, $levels[1]);

        // $agentsSupprime2 = $repo->get('nom', 'Supprim&eacute;', null);
        // $logins2 = array_column($agentsSupprime2, 'notification_level1');

        // $agentActif = $repo->get('nom', 'Actif', null);
        // $loginsActif = array_column($agentActif, 'notification_level1');
        // $this->assertEquals(3, $loginsActif[0]);
        // $this->assertEquals(4, $loginsActif[1]);
    }

    public function testGet(): void
    {
        $repo = $this->entityManager->getRepository(Agent::class);
        $leo = $repo->get('nom', 'Actif', 'Léo');
        $this->assertNotEmpty($leo);

        $agentsSupprime1 = $repo->get('nom', 'Supprimé', null);
        $logins1 = array_column($agentsSupprime1, 'prenom');
        var_dump($logins1);
        $this->assertContains('Mike', $logins1);
        $this->assertContains('Eric', $logins1);
        $this->assertNotContains('John', $logins1);
        $this->assertNotContains('Léo', $logins1);

        $agentsSupprime2 = $repo->get('nom', 'Supprim&eacute;', null);
        $logins2 = array_column($agentsSupprime2, 'prenom');
        $this->assertSame($logins1, $logins2);

        $agentActif = $repo->get('nom', 'Actif', null);
        $loginsActif = array_column($agentActif, 'prenom');
        $this->assertContains('John', $loginsActif);
        $this->assertContains('Léo', $loginsActif);
        $this->assertNotContains('Mike', $loginsActif);
        $this->assertNotContains('Eric', $loginsActif);
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
