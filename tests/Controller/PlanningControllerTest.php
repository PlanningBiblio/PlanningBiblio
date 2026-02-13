<?php

use App\Entity\Agent;
use App\Entity\HiddenTables;
use App\Entity\PlanningPositionLock;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionTabAffectation;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class PlanningControllerTest extends PLBWebTestCase
{
    public function testPlanningNotReadyWithoutPermission(): void
    {
        $this->builder->delete(Agent::class);
        $this->builder->delete(PlanningPosition::class);
        $this->builder->delete(PlanningPositionTabAffectation::class);

        $agent = $this->builder->build(
            Agent::class,
            array(
                'login' => 'jdoenv',
            )
        );

        $this->logInAgent($agent, array(99,100));

        $date = new \DateTime();
        $today = date('d/m/Y');

        $crawler = $this->client->request('GET', '/');
        $result = $crawler->filter('.decalage-gauche p');
        $this->assertEquals("Le planning du $today n'est pas prêt.", $result->text('Node does not exist', false));

        $pl_post_lock = $this->builder->build
        (
            PlanningPositionLock::class,
            array(
                'date' => $date,
                'tableau' => 1,
                'verrou' => 0,
                'verrou2' => 0,
                'site' => 1,
                'perso2' => 0,
            )
        );

        $pl_post_tab_affect = $this->builder->build
        (
            PlanningPositionTabAffectation::class,
            array(
                'date' => $date,
                'tableau' => 1,
                'site' => 1,
            )
        );

        $crawler = $this->client->request('GET', '/');
        $result = $crawler->filter('.decalage-gauche p');
        $this->assertEquals("Le planning du $today n'est pas validé !", $result->text('Node does not exist', false), 'test index with no lock planning');
    }

    public function testAjaxGetHiddenTables()
    {
        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdoenv']);
        $rights = $this->entityManager->getRepository(Agent::class)->find(1)->getACL();
        $this->logInAgent($agent, $rights);
        $crawler = $this->client->request('GET', '/planning/hidden-tables',['tableId' => null]);
        $result = $crawler->filter('body')->text();
        $this->assertEquals('[]', $result, 'test ajax get hidden tables');// test with no hidden tables 

        $hiddenTables = new HiddenTables();
        $hiddenTables->setUserId($agent->getId());
        $hiddenTables->setTable(1);
        $hiddenTables->setHiddenTables([2, 3]);
        $this->entityManager->persist($hiddenTables);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/planning/hidden-tables',['tableId' => 1]);
        $result = $crawler->filter('body')->text();
        $this->assertEquals('[2,3]', $result, 'test ajax get hidden tables with hidden tables');

        $this->testSetHiddenTables();
    }

    private function testSetHiddenTables()
    {
        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdoenv']);

        $crawler = $this->client->request('GET', '/');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $this->client->request('POST', '/planning/hidden-tables', [
            'tableId' => 1,
            'hiddenTables' => [0, 1],
            '_token' => $token,
            'CSRFToken' => $token
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();

        $this->assertJson($response->getContent());

        $this->entityManager->clear();

        $results = $this->entityManager
            ->getRepository(HiddenTables::class)
            ->findBy(['perso_id' => $agent->getId(), 'tableau' => 1]);

        $this->assertCount(1, $results);

        $this->assertSame([0, 1], $results[0]->getHiddenTables());
    }

    private function testGetHiddenTables()
    {
        
    }
}
