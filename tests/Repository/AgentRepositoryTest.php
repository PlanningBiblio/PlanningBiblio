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

        $mike = $this->createAgent(array('login' => 'Mike', 'prenom' => 'Mike', 'supprime' => 1, 'actif' => 'Supprimé'));
        $eric = $this->createAgent(array('login' => 'Eric', 'prenom' => 'Eric', 'supprime' => 1, 'actif' => 'Supprim&eacute;'));
        $john = $this->createAgent(array('login' => 'John', 'prenom' => 'John', 'supprime' => 2, 'actif' => 'Actif'));
        $leo = $this->createAgent(array('login' => 'Leo', 'prenom' => 'Léo', 'supprime' => 0, 'actif' => 'Actif'));

        $mMike = new Manager();
        $mMike->setUser($leo);
        $mMike->setManager($mike);
        $mMike->setLevel1(1);
        $mMike->setLevel1Notification(1);
        $mMike->setLevel2(0);
        $mMike->setLevel2Notification(0);

        $mEric = new Manager();
        $mEric->setUser($leo);
        $mEric->setManager($eric);
        $mEric->setLevel1Notification(2);

        $mJohn = new Manager();
        $mJohn->setUser($leo);
        $mJohn->setManager($john);
        $mJohn->setLevel1Notification(3);

        $this->entityManager->persist($leo);
        $this->entityManager->persist($mMike);
        $this->entityManager->persist($mEric);
        $this->entityManager->persist($mJohn);
        $this->entityManager->flush();
        $this->entityManager->clear();
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

    public function testGet(): void
    {
        $repo = $this->entityManager->getRepository(Agent::class);

        $leo = $repo->get('nom', 'Actif', 'Léo');
        $managerLogins = array_map(
            fn($m) => $m->getManager()->getLogin(),
            $leo->getManagers()
        );
        $this->assertCount(3, $managerLogins);
        $this->assertContains('Mike', $managerLogins);
        $this->assertContains('Eric', $managerLogins);
        $this->assertContains('John', $managerLogins);

        $agentsSupprime1 = $repo->get('nom', 'Supprimé', null);
        $logins = array_map(
            fn($a) => $a->getLogin(),
            $agentsSupprime1
        );
        $this->assertCount(2, $logins);
        $this->assertContains('Mike', $logins);
        $this->assertContains('Eric', $logins);

        $agentsSupprime2 = $repo->get('nom', 'Supprim&eacute;', null);
        $logins = array_map(
            fn($a) => $a->getLogin(),
            $agentsSupprime1
        );
        $this->assertCount(2, $logins);
        $this->assertContains('Mike', $logins);
        $this->assertContains('Eric', $logins);


        $agentsActif = $repo->get('nom', 'Actif');
        $this->assertCount(1, $agentsActif);
        $this->assertSame('Leo', $agentsActif[0]->getLogin());
    }

    public function testGetByDeletion(): void
    {
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0]);
        $this->assertEquals(count($repo), 3);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([1]);
        $this->assertEquals(count($repo), 2);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([2]);
        $this->assertEquals(count($repo), 1);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0,1,2]);
        $this->assertEquals(count($repo), 6);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0,1]);
        $this->assertEquals(count($repo), 5);
        $repo = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([1,2]);
        $this->assertEquals(count($repo), 3);
    }

    public function testGetSitesForAgents(): void{
        global $entityManager;

        $mike = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'Mike']);
        $mike->setSites(["1","2"]);
        $this->entityManager->persist($mike);
        $eric = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'Eric']);
        $eric->setSites(["1","3"]);
        $this->entityManager->persist($eric);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $perso_ids = array($mike->getId(), $eric->getId());

        $GLOBALS['config']['Multisites-nombre'] = 1;
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
        $this->assertEquals($sites, array('1'));

        $GLOBALS['config']['Multisites-nombre'] = 3;
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids);
        $this->assertEquals($sites, array('1', '2', '3'));

        $agent3 = $this->createAgent(array('login' => 'Melvin', 'prenom' => 'Melvin', 'supprime' => 0, 'actif' => 'Actif', 'sites' => []));
        $sites = $this->entityManager->getRepository(Agent::class)->getSitesForAgents(array($agent3->getId()));
        $this->assertEquals($sites, array());

    }
}
