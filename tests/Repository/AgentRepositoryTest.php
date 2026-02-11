<?php

namespace App\Tests;

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\Manager;
use App\Entity\OverTime;
use App\Entity\WorkingHour;
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
        $eric = $this->createAgent(array('login' => 'Eric', 'prenom' => 'Eric', 'supprime' => 1, 'actif' => 'Supprimé'));
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

    private function createAgent(array $agentInfo): \App\Entity\Agent
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

        $leo = $repo->get('Actif', 'Léo');
        $managerLogins = array_map(
            fn($m) => $m->getManager()->getLogin(),
            $leo[0]->getManagers()
        );
        $this->assertCount(3, $managerLogins);
        $this->assertContains('Mike', $managerLogins);
        $this->assertContains('Eric', $managerLogins);
        $this->assertContains('John', $managerLogins);

        $agentsSupprime1 = $repo->get( 'Supprimé', null);
        $logins = array_map(
            fn($a) => $a->getLogin(),
            $agentsSupprime1
        );
        $this->assertCount(2, $logins);
        $this->assertContains('Mike', $logins);
        $this->assertContains('Eric', $logins);

        $agentsActif = $repo->get('Actif');
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

    public function testUpdateAsDeletedAndDepartTodayById(): void
    {

        $leo = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'Leo']);

        $this->assertEquals(0, $leo->getDeletion());
        $this->assertNull($leo->getDeparture());

        $this->entityManager->getRepository(Agent::class)->updateAsDeletedAndDepartTodayById([$leo->getId()]);

        $this->entityManager->clear();

        $leo = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'Leo']);
        $this->assertEquals(1, $leo->getDeletion());
        $this->assertEquals((new \DateTime())->format('Y-m-d'), $leo->getDeparture()->format('Y-m-d'));
    }

    public function testGetExportIcsURLWithExistingCode(): void
    {
        $this->setParam('ICS-Code', 1);

        $leo = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'Leo']);

        $leo->setICSCode('existing_code_123');

        $this->entityManager->persist($leo);
        $this->entityManager->flush();

        $id = $leo->getId();

        $url = $this->entityManager->getRepository(Agent::class)->getExportIcsURL($id);

        $this->assertEquals(
            "/ical?id=$id&code=existing_code_123",
            $url
        );
    }

    public function testNewAgentHasAnIcsCode(): void
    {
        $leo = new Agent();
        $leo->setLogin(rand(100,999));
        $this->entityManager->persist($leo);
        $this->entityManager->flush();

        $id = $leo->getId();
        $url = $this->entityManager->getRepository(Agent::class)->getExportIcsURL($id);
        $this->assertStringStartsWith("/ical?id=$id&code=", $url);

        $this->assertNotNull($leo->getICSCode());
        $this->assertNotEmpty($leo->getICSCode());
    }

    public function testFindAllLoginsNotDeleted(): void
    {
        $result = $this->entityManager->getRepository(Agent::class)->findAllLoginsNotDeleted();

        $this->assertContains('Mike', array_column($result, 'login'));
        $this->assertContains('Eric', array_column($result, 'login'));
        $this->assertNotContains('john', array_column($result, 'login'));
    }

    public function testFetchCredits(): void
    {
        $userId = null;
        $result = $this->entityManager->getRepository(Agent::class)->fetchCredits($userId);

        foreach ($result as $value) {
            $this->assertSame(0, $value);
        }
    }

    public function testDelete(): void
    {
        $builder = new FixtureBuilder();

        $leo = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'Leo']);
        $leoId = $leo->getId();

        $builder->build(
            Absence::class,
            array('perso_id' => $leoId, 'commentaires' => 'Test absence', 'groupe' => '')
        );

        $builder->build(
            Holiday::class,
            array('perso_id' => $leoId, 'commentaires' => 'Test holiday')
        );

        $builder->build(
            OverTime::class,
            array('perso_id' => $leoId)
        );

        $builder->build(
            WorkingHour::class,
            array('perso_id' => $leoId)
        );

        $absence = $this->entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $leoId]);
        $holiday = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $leoId]);
        $overtime = $this->entityManager->getRepository(OverTime::class)->findOneBy(['perso_id' => $leoId]);
        $workingHour = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $leoId]);

        $this->assertEquals(0, $leo->getDeletion());
        $this->assertNotEmpty($absence->getComment());
        $this->assertNotEmpty($holiday->getComment());
        $this->assertNotNull($overtime);
        $this->assertNotNull($workingHour);

        $this->entityManager->getRepository(Agent::class)->delete([$leoId]);

        $this->entityManager->clear();

        $deletedAgent = $this->entityManager->getRepository(Agent::class)->find($leoId);
        $deleteAbsences = $this->entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $leoId]);
        $deleteHolidays = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $leoId]);
        $deleteOvertimes = $this->entityManager->getRepository(OverTime::class)->findOneBy(['perso_id' => $leoId]);
        $deleteWorkingHours = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $leoId]);
        $this->assertEquals(2, $deletedAgent->getDeletion());
        $this->assertEmpty($deleteAbsences->getComment());
        $this->assertEmpty($deleteHolidays->getComment());
        $this->assertNull($deleteOvertimes);
        $this->assertNull($deleteWorkingHours);
    }
}
